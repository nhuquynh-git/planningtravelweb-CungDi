<?php

namespace App\Controllers\Api;

use App\Services\AuthService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Authentication API Controller
 * 
 * Handles:
 * - POST /api/auth/register - User registration
 * - POST /api/auth/login - User login
 * - POST /api/auth/logout - User logout
 * - GET /api/auth/me - Get current user
 * - PUT /api/auth/profile - Update user profile
 */
class Auth extends BaseApiController
{
    protected AuthService $authService;

    public function __construct()
    {
        parent::__construct();
        $this->authService = new AuthService();
    }

    /**
     * POST /api/auth/register
     * Register a new user account
     */
    public function register(): ResponseInterface
    {
        try {
            $credentials = $this->request->getJSON(true) ?? [];

            $result = $this->authService->register($credentials);

            return $this->success([
                'user'  => $result['user'],
                'token' => $result['token'],
            ], 'Đăng ký thành công', 201);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\Throwable $e) {
            log_message('error', 'Auth register error: ' . $e->getMessage());
            return $this->error('Đăng ký thất bại. Vui lòng thử lại sau.', 500);
        }
    }

    /**
     * POST /api/auth/login
     * Login with email and password
     */
    public function login(): ResponseInterface
    {
        try {
            $credentials = $this->request->getJSON(true) ?? [];

            $result = $this->authService->login($credentials);

            return $this->success([
                'user'  => $result['user'],
                'token' => $result['token'],
            ], 'Đăng nhập thành công');
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (\Throwable $e) {
            log_message('error', 'Auth login error: ' . $e->getMessage());
            return $this->error('Đăng nhập thất bại. Vui lòng thử lại sau.', 500);
        }
    }

    /**
     * POST /api/auth/logout
     * Logout and revoke current token
     */
    public function logout(): ResponseInterface
    {
        try {
            // Get token from header or cookie
            $token = $this->request->getHeaderLine('Authorization');
            
            // Remove 'Bearer ' prefix if present
            if (str_starts_with($token, 'Bearer ')) {
                $token = substr($token, 7);
            }

            $this->authService->logout($token);

            return $this->success(null, 'Đăng xuất thành công');
        } catch (\Throwable $e) {
            log_message('error', 'Auth logout error: ' . $e->getMessage());
            return $this->error('Đăng xuất thất bại', 500);
        }
    }

    /**
     * GET /api/auth/me
     * Get current authenticated user
     */
    public function me(): ResponseInterface
    {
        try {
            // Check if user is authenticated via the filter
            $user = $this->authService->getCurrentUser();

            if ($user === null) {
                return $this->error('Không tìm thấy người dùng', 401);
            }

            // Get additional profile data
            $profile = $this->authService->getUserProfile($user['id']);

            $user['profile'] = $profile;

            return $this->success($user);
        } catch (\Throwable $e) {
            log_message('error', 'Auth me error: ' . $e->getMessage());
            return $this->error('Lỗi khi lấy thông tin người dùng', 500);
        }
    }

    /**
     * PUT /api/auth/profile
     * Update user profile
     */
    public function profile(): ResponseInterface
    {
        try {
            // Get authenticated user
            $currentUser = $this->authService->getCurrentUser();

            if ($currentUser === null) {
                return $this->error('Vui lòng đăng nhập', 401);
            }

            $data = $this->request->getJSON(true) ?? [];

            // Validate input
            $validationRules = [];
            
            if (isset($data['display_name'])) {
                $validationRules['display_name'] = 'max_length[100]';
            }
            if (isset($data['phone'])) {
                $validationRules['phone'] = 'max_length[20]';
            }
            if (isset($data['bio'])) {
                $validationRules['bio'] = 'max_length[1000]';
            }

            if (!empty($validationRules)) {
                $validation = service('validation');
                $validation->setRules($validationRules);

                if (!$validation->run($data)) {
                    return $this->error(
                        'Dữ liệu không hợp lệ',
                        422,
                        $validation->getErrors()
                    );
                }
            }

            $this->authService->updateProfile($currentUser['id'], $data);

            // Get updated profile
            $profile = $this->authService->getUserProfile($currentUser['id']);

            return $this->success([
                'profile' => $profile,
            ], 'Cập nhật hồ sơ thành công');
        } catch (\Throwable $e) {
            log_message('error', 'Auth profile update error: ' . $e->getMessage());
            return $this->error('Cập nhật hồ sơ thất bại', 500);
        }
    }

    /**
     * OPTIONS /api/auth
     * Handle CORS preflight for auth routes
     */
    public function options()
    {
        return $this->response->setStatusCode(204);
    }

    /**
     * POST /api/auth/refresh
     * Refresh access token
     */
    public function refresh(): ResponseInterface
    {
        try {
            // Get authenticated user
            $currentUser = $this->authService->getCurrentUser();

            if ($currentUser === null) {
                return $this->error('Vui lòng đăng nhập', 401);
            }

            // Get the user from the model
            $userModel = new \CodeIgniter\Shield\Models\UserModel();
            $user = $userModel->findById($currentUser['id']);

            if ($user === null) {
                return $this->error('Người dùng không tồn tại', 401);
            }

            // Generate new token
            $identityModel = new \CodeIgniter\Shield\Models\UserIdentityModel();
            
            // Find and delete old token
            $oldToken = $this->request->getHeaderLine('Authorization');
            if (str_starts_with($oldToken, 'Bearer ')) {
                $oldToken = substr($oldToken, 7);
                $identityModel->where('type', 'access_token')
                    ->where('secret', $oldToken)
                    ->delete();
            }

            // Generate new token
            $newToken = bin2hex(random_bytes(32));
            $identityModel->insert([
                'user_id' => $user->id,
                'type'    => 'access_token',
                'secret'  => $newToken,
                'name'    => 'API Access Token',
                'expires' => date('Y-m-d H:i:s', time() + config('AuthToken')->unusedTokenLifetime),
            ]);

            return $this->success([
                'token' => $newToken,
            ], 'Token đã được làm mới');
        } catch (\Throwable $e) {
            log_message('error', 'Auth refresh error: ' . $e->getMessage());
            return $this->error('Làm mới token thất bại', 500);
        }
    }
}