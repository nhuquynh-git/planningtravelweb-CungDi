<?php

namespace App\Services;

use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Models\UserIdentityModel;
use App\Models\UserProfileModel;

/**
 * Authentication Service
 * 
 * Handles user registration with 6-digit email OTP verification,
 * login via email or username, logout, and token management.
 */
class AuthService
{
    protected $userProvider;
    protected $identityProvider;
    protected $auth;

    public function __construct()
    {
        $this->userProvider = model(\CodeIgniter\Shield\Models\UserModel::class);
        $this->identityProvider = model(\CodeIgniter\Shield\Models\UserIdentityModel::class);
        $this->auth = auth('tokens');
    }

    /**
     * Register a new user and generate 6-digit OTP for email verification
     * 
     * @param array $data email, username, password
     * @return array
     */
    public function register(array $data): array
    {
        $rules = [
            'email'    => 'required|valid_email|max_length[254]',
            'username' => 'required|min_length[3]|max_length[30]|regex_match[/^[a-zA-Z0-9_]+$/]',
            'password' => 'required|min_length[8]|max_length[72]',
        ];

        $messages = [
            'username' => [
                'regex_match' => 'Tên tài khoản chỉ được chứa chữ cái, số và dấu gạch dưới',
                'min_length'  => 'Tên tài khoản phải có ít nhất 3 ký tự',
            ],
            'password' => [
                'min_length' => 'Mật khẩu phải có ít nhất 8 ký tự',
            ],
            'email' => [
                'valid_email' => 'Địa chỉ email không hợp lệ',
            ]
        ];

        $validation = service('validation');
        $validation->setRules($rules, $messages);

        if (!$validation->run($data)) {
            throw new \RuntimeException(implode(', ', $validation->getErrors()));
        }

        $email = strtolower(trim($data['email']));
        $username = trim($data['username']);

        // Check if email already exists
        $existingEmail = $this->identityProvider
            ->where('type', 'email_password')
            ->where('secret', $email)
            ->first();

        if ($existingEmail !== null) {
            // Check if user is active
            $user = $this->userProvider->findById($existingEmail->user_id);
            if ($user !== null && !$user->isActivated()) {
                // If account exists but not activated, regenerate OTP and allow verifying
                $otp = $this->generateAndSendOtp($user, $email);
                return [
                    'status'       => 'need_verification',
                    'email'        => $email,
                    'username'     => $user->username,
                    'message'      => 'Tài khoản chưa được kích hoạt. Mã OTP mới đã được gửi tới email của bạn.',
                    'otp_preview'  => (ENVIRONMENT !== 'production') ? $otp : null,
                ];
            }
            throw new \RuntimeException('Địa chỉ email này đã được sử dụng');
        }

        // Check if username already exists
        $existingUsername = $this->userProvider
            ->where('username', $username)
            ->first();

        if ($existingUsername !== null) {
            throw new \RuntimeException('Tên tài khoản này đã được sử dụng');
        }

        // Create inactive user
        $user = new User([
            'email'    => $email,
            'username' => $username,
            'password' => $data['password'],
            'active'   => 0, // Inactive until OTP is verified
        ]);

        $this->userProvider->save($user);

        $userId = $this->userProvider->getInsertID();
        $user = $this->userProvider->findById($userId);

        if ($user === null) {
            throw new \RuntimeException('Không thể tạo tài khoản');
        }

        // Add to default 'user' group
        $user->addGroup('user');

        // Generate and send 6-digit OTP
        $otp = $this->generateAndSendOtp($user, $email);

        return [
            'status'       => 'need_verification',
            'email'        => $email,
            'username'     => $username,
            'message'      => 'Mã xác nhận 6 số đã được gửi tới email của bạn',
            'otp_preview'  => (ENVIRONMENT !== 'production') ? $otp : null,
        ];
    }

    /**
     * Generate 6-digit OTP, store in database and send email
     */
    public function generateAndSendOtp(User $user, string $email): string
    {
        $otp = (string) random_int(100000, 999999);
        $expires = date('Y-m-d H:i:s', time() + 300); // 5 minutes lifetime

        // Delete any existing OTP for this user
        $this->identityProvider
            ->where('user_id', $user->id)
            ->where('type', 'email_otp')
            ->delete();

        // Save new OTP
        $this->identityProvider->insert([
            'user_id' => $user->id,
            'type'    => 'email_otp',
            'secret'  => $otp,
            'name'    => 'Email Verification OTP',
            'expires' => $expires,
        ]);

        // Attempt to send email
        $this->sendOtpEmail($email, $otp);

        return $otp;
    }

    /**
     * Send OTP email via CodeIgniter Email service
     */
    protected function sendOtpEmail(string $email, string $otp): void
    {
        try {
            $emailService = service('email');
            $emailService->setTo($email);
            $emailService->setSubject('Mã xác thực tài khoản Cùng Đi Travel Planner: ' . $otp);
            $emailService->setMessage(
                "Xin chào,\n\n" .
                "Mã xác thực của bạn trên Cùng Đi Travel Planner là: $otp\n" .
                "Mã này có hiệu lực trong 5 phút.\n\n" .
                "Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email."
            );
            if (! $emailService->send(false)) {
                log_message('error', 'Send OTP mail failed: ' . $emailService->printDebugger(['headers']));
            }
        } catch (\Throwable $e) {
            log_message('error', 'Error sending OTP email: ' . $e->getMessage());
        }

        // Log OTP in writable/logs for convenient local testing
        log_message('info', ">>> OTP for [$email]: $otp (Valid for 5 mins) <<<");
    }

    /**
     * Verify 6-digit OTP and activate user account
     */
    public function verifyOtp(string $email, string $otp): array
    {
        $email = strtolower(trim($email));
        $otp = trim($otp);

        if (empty($email) || empty($otp)) {
            throw new \RuntimeException('Vui lòng nhập đầy đủ email và mã xác nhận');
        }

        // Find email identity
        $identityEmail = $this->identityProvider
            ->where('type', 'email_password')
            ->where('secret', $email)
            ->first();

        if ($identityEmail === null) {
            throw new \RuntimeException('Không tìm thấy tài khoản với email này');
        }

        $user = $this->userProvider->findById($identityEmail->user_id);
        if ($user === null) {
            throw new \RuntimeException('Tài khoản không tồn tại');
        }

        // Find OTP identity
        $identityOtp = $this->identityProvider
            ->where('user_id', $user->id)
            ->where('type', 'email_otp')
            ->orderBy('id', 'DESC')
            ->first();

        if ($identityOtp === null) {
            throw new \RuntimeException('Mã xác nhận không tồn tại hoặc đã được sử dụng. Vui lòng gửi lại mã.');
        }

        // Check expiration
        if (!empty($identityOtp->expires) && strtotime($identityOtp->expires) < time()) {
            throw new \RuntimeException('Mã xác nhận đã hết hạn. Vui lòng bấm Gửi lại mã.');
        }

        // Check if OTP matches
        if ($identityOtp->secret !== $otp) {
            throw new \RuntimeException('Mã xác nhận không đúng. Vui lòng kiểm tra lại.');
        }

        // OTP is valid! Activate user and delete OTP
        $this->identityProvider->where('id', $identityOtp->id)->delete();

        $user->active = 1;
        $this->userProvider->save($user);

        // Generate access token for automatic login
        $token = $this->generateToken($user, 7 * 86400);

        return [
            'user'  => $this->formatUser($user),
            'token' => $token,
        ];
    }

    /**
     * Resend OTP to email
     */
    public function resendOtp(string $email): array
    {
        $email = strtolower(trim($email));

        $identityEmail = $this->identityProvider
            ->where('type', 'email_password')
            ->where('secret', $email)
            ->first();

        if ($identityEmail === null) {
            throw new \RuntimeException('Không tìm thấy tài khoản với email này');
        }

        $user = $this->userProvider->findById($identityEmail->user_id);
        if ($user === null) {
            throw new \RuntimeException('Tài khoản không tồn tại');
        }

        $otp = $this->generateAndSendOtp($user, $email);

        return [
            'success'     => true,
            'message'     => 'Mã OTP mới đã được gửi tới email của bạn',
            'otp_preview' => (ENVIRONMENT !== 'production') ? $otp : null,
        ];
    }

    /**
     * Login user with Email OR Username + Password
     * 
     * @param array $credentials login (or email), password, remember_me
     * @return array User data and access token
     */
    public function login(array $credentials): array
    {
        $loginInput = trim($credentials['login'] ?? $credentials['email'] ?? $credentials['username'] ?? '');
        $password = $credentials['password'] ?? '';
        $remember = !empty($credentials['remember_me'] ?? $credentials['remember'] ?? false);

        if (empty($loginInput)) {
            throw new \RuntimeException('Vui lòng nhập email hoặc tên tài khoản');
        }
        if (empty($password)) {
            throw new \RuntimeException('Vui lòng nhập mật khẩu');
        }

        $user = null;

        // Check if login input is an email
        if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
            $existingEmail = $this->identityProvider
                ->where('type', 'email_password')
                ->where('secret', strtolower($loginInput))
                ->first();

            if ($existingEmail !== null) {
                $user = $this->userProvider->findById($existingEmail->user_id);
            }
        } else {
            // Otherwise, search by username
            $user = $this->userProvider
                ->where('username', $loginInput)
                ->first();
        }

        if ($user === null) {
            throw new \RuntimeException('Tên tài khoản hoặc mật khẩu không chính xác');
        }

        // Verify password
        $emailIdentity = $user->getEmailIdentity();
        if ($emailIdentity === null || empty($emailIdentity->secret2)) {
            throw new \RuntimeException('Tài khoản này chưa thiết lập mật khẩu (có thể đăng ký qua Google/Facebook)');
        }

        $passwords = service('passwords');
        if (!$passwords->verify($password, $emailIdentity->secret2)) {
            throw new \RuntimeException('Tên tài khoản hoặc mật khẩu không chính xác');
        }

        // Check if user is activated (completed OTP verification)
        if (!$user->isActivated()) {
            $otp = $this->generateAndSendOtp($user, $user->email);
            return [
                'status'       => 'need_verification',
                'email'        => $user->email,
                'username'     => $user->username,
                'message'      => 'Tài khoản chưa được kích hoạt email. Vui lòng nhập mã OTP để tiếp tục.',
                'otp_preview'  => (ENVIRONMENT !== 'production') ? $otp : null,
            ];
        }

        // Lifetime: 30 days if remember_me, otherwise 7 days
        $lifetime = $remember ? (30 * 86400) : (7 * 86400);
        $token = $this->generateToken($user, $lifetime);

        return [
            'status' => 'success',
            'user'   => $this->formatUser($user),
            'token'  => $token,
        ];
    }

    /**
     * Logout user and revoke current token
     */
    public function logout(?string $token = null): bool
    {
        if ($token) {
            $this->identityProvider->where('type', 'access_token')
                ->where('secret', hash('sha256', $token))
                ->delete();
        }

        $this->auth->logout();
        return true;
    }

    /**
     * Get current authenticated user
     */
    public function getCurrentUser(): ?array
    {
        if (! $this->auth->loggedIn()) {
            return null;
        }

        $user = $this->auth->user();
        if ($user === null || ! $user->isActivated()) {
            return null;
        }

        return $this->formatUser($user);
    }

    /**
     * Validate access token and return user
     */
    public function validateToken(string $token): ?User
    {
        $identity = $this->identityProvider->where('type', 'access_token')
            ->where('secret', hash('sha256', $token))
            ->first();

        if ($identity === null) {
            return null;
        }

        // Check expiration
        if (!empty($identity->expires) && strtotime($identity->expires) < time()) {
            $this->identityProvider->where('id', $identity->id)->delete();
            return null;
        }

        $user = $this->userProvider->findById($identity->user_id);
        if ($user === null || !$user->isActivated()) {
            return null;
        }

        $this->auth->setUser($user);
        return $user;
    }

    /**
     * Generate a new access token for user
     */
    protected function generateToken(User $user, int $lifetime = 604800): string
    {
        $accessToken = $user->generateAccessToken('api-token');

        $this->identityProvider->update($accessToken->id, [
            'expires' => date('Y-m-d H:i:s', time() + $lifetime),
        ]);

        return $accessToken->raw_token;
    }

    /**
     * Format user data for API response
     */
    protected function formatUser(User $user): array
    {
        $groups = $user->getGroups();
        $permissions = $user->getPermissions();

        return [
            'id'          => $user->id,
            'email'       => $user->email,
            'username'    => $user->username ?? '',
            'groups'      => $groups,
            'permissions' => array_keys($permissions),
            'created_at'  => $user->created_at?->format('Y-m-d H:i:s'),
            'is_active'   => $user->isActivated(),
        ];
    }

    /**
     * Get user profile with additional fields
     */
    public function getUserProfile(int $userId): ?array
    {
        $profileModel = new UserProfileModel();
        $profile = $profileModel->find($userId);

        if ($profile === null) {
            return null;
        }

        $profile = is_array($profile) ? $profile : $profile->toArray();

        return [
            'display_name' => $profile['display_name'] ?? '',
            'avatar'       => $profile['avatar'] ?? '',
            'phone'        => $profile['phone'] ?? '',
            'bio'          => $profile['bio'] ?? '',
        ];
    }

    /**
     * Update user profile
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
