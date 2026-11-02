<?php

namespace App\Services;

use App\Controllers\Api\BaseApiController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Authentication\AuthenticationException;
use CodeIgniter\Shield\Authentication\AuthenticatorInterface;
use CodeIgniter\Shield\Authentication\TokenRemindableInterface;
use App\Models\UserProfileModel;
use CodeIgniter\Shield\Collectors\Session;
use CodeIgniter\Config\Services;

/**
 * Authentication Service
 * 
 * Handles user registration, login, logout, and token management
 * following the approved Q-015b token policy:
 * - Token expires after 7 days of inactivity
 * - Current token is revoked on logout server-side
 * - Token stored in HttpOnly cookie (via bridge)
 */
class AuthService
{
    protected $userProvider;
    protected $identityProvider;
    protected $auth;

    public function __construct()
    {
        // Get the user provider - use model() helper with fully qualified class name
        $this->userProvider = model(\CodeIgniter\Shield\Models\UserModel::class);
        $this->identityProvider = model(\CodeIgniter\Shield\Models\UserIdentityModel::class);
        $this->auth = auth('tokens');
    }

    /**
     * Register a new user
     * 
     * @param array $credentials Email and password
     * @return array User data and access token
     */
    public function register(array $credentials): array
    {
        // Validate input
        $rules = [
            'email'    => 'required|valid_email|max_length[254]',
            'password' => 'required|min_length[8]|max_length[72]',
        ];

        $validation = service('validation');
        $validation->setRules($rules);

        if (!$validation->run($credentials)) {
            throw new \RuntimeException(implode(', ', $validation->getErrors()));
        }

        // Check if email already exists by looking in auth_identities
        $existingIdentity = $this->identityProvider
            ->where('type', 'email_password')
            ->where('secret', $credentials['email'])
            ->first();
            
        if ($existingIdentity !== null) {
            throw new \RuntimeException('Email đã được sử dụng');
        }

        // Create user entity
        $user = new User([
            'email'    => $credentials['email'],
            'username' => explode('@', $credentials['email'])[0] . '_' . time(),
        ]);

        // Store the password
        $user->createPassword($credentials['password']);

        // Save user using the user provider
        $this->userProvider->save($user);

        // Get the saved user with ID
        $userId = $this->userProvider->getInsertID();
        $user = $this->userProvider->findById($userId);

        if ($user === null) {
            throw new \RuntimeException('Không thể tạo người dùng');
        }

        // Add to default 'user' group
        $user->addGroup('user');

        // Generate access token
        $token = $this->generateToken($user);

        return [
            'user'  => $this->formatUser($user),
            'token' => $token,
        ];
    }

    /**
     * Login user with credentials
     * 
     * @param array $credentials Email and password
     * @return array User data and access token
     */
    public function login(array $credentials): array
    {
        // Validate input
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        $validation = service('validation');
        $validation->setRules($rules);

        if (!$validation->run($credentials)) {
            throw new \RuntimeException(implode(', ', $validation->getErrors()));
        }

        // Attempt to login
        $result = $this->auth->attempt($credentials);

        if (!$result->isOK()) {
            $error = $result->reason() ?? 'Email hoặc mật khẩu không đúng';
            throw new \RuntimeException($error);
        }

        /** @var User $user */
        $user = $result->extraInfo();

        // Generate access token
        $token = $this->generateToken($user);

        return [
            'user'  => $this->formatUser($user),
            'token' => $token,
        ];
    }

    /**
     * Logout user and revoke current token
     * 
     * @param string|null $token Raw token from cookie/header
     * @return bool
     */
    public function logout(?string $token = null): bool
    {
        if ($token) {
            // Find and delete the specific token
            $this->identityProvider->where('type', 'access_token')
                ->where('secret', $token)
                ->delete();
        }

        // Logout from authenticator
        $this->auth->logout();

        return true;
    }

    /**
     * Get current authenticated user
     * 
     * @return array|null User data
     */
    public function getCurrentUser(): ?array
    {
        $user = $this->auth->user();

        if ($user === null) {
            return null;
        }

        return $this->formatUser($user);
    }

    /**
     * Verify if user is authenticated
     * 
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return $this->auth->check();
    }

    /**
     * Validate access token and return user
     * 
     * @param string $token Raw access token
     * @return User|null
     */
    public function validateToken(string $token): ?User
    {
        // Find identity by token secret
        $identity = $this->identityProvider->where('type', 'access_token')
            ->where('secret', $token)
            ->first();

        if ($identity === null) {
            return null;
        }

        // Get user by ID
        $user = $this->userProvider->findById($identity->user_id);

        if ($user === null) {
            return null;
        }

        // Set user in authenticator for session tracking
        $this->auth->setUser($user);

        // Check if token is valid and not expired
        if (!$this->auth->check()) {
            return null;
        }

        return $user;
    }

    /**
     * Generate a new access token for user
     * 
     * @param User $user
     * @return string Raw access token
     */
    protected function generateToken(User $user): string
    {
        // Generate a secure random token
        $token = bin2hex(random_bytes(32));

        // Store token in database
        $this->identityProvider->insert([
            'user_id' => $user->id,
            'type'    => 'access_token',
            'secret'  => $token,
            'name'    => 'API Access Token',
            'expires' => date('Y-m-d H:i:s', time() + config('AuthToken')->unusedTokenLifetime),
        ]);

        return $token;
    }

    /**
     * Format user data for API response
     * 
     * @param User $user
     * @return array
     */
    protected function formatUser(User $user): array
    {
        $groups = $user->getGroups();
        $permissions = $user->getPermissions();

        return [
            'id'         => $user->id,
            'email'      => $user->email,
            'username'   => $user->username ?? '',
            'groups'     => $groups,
            'permissions'=> array_keys($permissions),
            'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
            'is_active'  => $user->isActivated(),
        ];
    }

    /**
     * Get user profile with additional fields
     * 
     * @param int $userId
     * @return array|null
     */
    public function getUserProfile(int $userId): ?array
    {
        $profileModel = new UserProfileModel();
        $profile = $profileModel->find($userId);

        if ($profile === null) {
            return null;
        }

        return [
            'display_name' => $profile->display_name ?? '',
            'avatar'       => $profile->avatar ?? '',
            'phone'        => $profile->phone ?? '',
            'bio'          => $profile->bio ?? '',
        ];
    }

    /**
     * Update user profile
     * 
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public function updateProfile(int $userId, array $data): bool
    {
        $profileModel = new UserProfileModel();

        $profileData = [
            'user_id' => $userId,
        ];

        if (isset($data['display_name'])) {
            $profileData['display_name'] = $data['display_name'];
        }
        if (isset($data['avatar'])) {
            $profileData['avatar'] = $data['avatar'];
        }
        if (isset($data['phone'])) {
            $profileData['phone'] = $data['phone'];
        }
        if (isset($data['bio'])) {
            $profileData['bio'] = $data['bio'];
        }

        $existing = $profileModel->find($userId);

        if ($existing === null) {
            return $profileModel->insert($profileData);
        }

        return $profileModel->update($userId, $profileData);
    }
}