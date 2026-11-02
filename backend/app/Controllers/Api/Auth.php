<?php

namespace App\Controllers\Api;

use App\Services\AuthService;
use App\Services\OAuthService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Authentication API Controller
 */
class Auth extends BaseApiController
{
    protected AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * POST /api/auth/register
     * Register a new user account (Generates 6-digit OTP for email verification)
     */
    public function register(): ResponseInterface
    {
        try {
            $data = $this->request->getJSON(true) ?? [];

            $result = $this->authService->register($data);

            return $this->success($result, $result['message'] ?? 'Đăng ký thành công', 201);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\Throwable $e) {
            log_message('error', 'Auth register error: ' . $e->getMessage());
            return $this->error('Đăng ký thất bại. Vui lòng thử lại sau.', 500);
        }
    }

    /**
     * POST /api/auth/verify-otp
     * Verify 6-digit email OTP and activate user account
     */
    public function verifyOtp(): ResponseInterface
    {
        try {
            $data = $this->request->getJSON(true) ?? [];
            $email = $data['email'] ?? '';
            $otp = $data['otp'] ?? '';

            $result = $this->authService->verifyOtp($email, $otp);

            // Set cookie for token
            set_cookie('access_token', $result['token'], 7 * 86400, '', '/', '', false, true, 'Lax');

            return $this->success([
                'user'  => $result['user'],
                'token' => $result['token'],
            ], 'Xác minh email thành công');
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\Throwable $e) {
            log_message('error', 'Auth verifyOtp error: ' . $e->getMessage());
            return $this->error('Xác minh mã thất bại', 500);
        }
    }

    /**
     * POST /api/auth/resend-otp
     * Resend 6-digit email OTP
     */
    public function resendOtp(): ResponseInterface
    {
        try {
            $data = $this->request->getJSON(true) ?? [];
            $email = $data['email'] ?? '';

            if (empty($email)) {
                return $this->error('Thiếu thông tin email', 400);
            }

            $result = $this->authService->resendOtp($email);

            return $this->success($result, $result['message']);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\Throwable $e) {
            log_message('error', 'Auth resendOtp error: ' . $e->getMessage());
            return $this->error('Không thể gửi lại mã xác nhận', 500);
        }
    }

    /**
     * POST /api/auth/login
     * Login with email OR username and password
     */
    public function login(): ResponseInterface
    {
        try {
            $credentials = $this->request->getJSON(true) ?? [];

            $result = $this->authService->login($credentials);

            // If account needs email verification first
            if (isset($result['status']) && $result['status'] === 'need_verification') {
                return $this->success($result, $result['message'], 200);
            }

            // Set token cookie
            set_cookie('access_token', $result['token'], 7 * 86400, '', '/', '', false, true, 'Lax');

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
     */
    public function logout(): ResponseInterface
    {
        try {
            $token = $this->request->getHeaderLine('Authorization');
            if (str_starts_with($token, 'Bearer ')) {
                $token = substr($token, 7);
            }
            if (empty($token)) {
                $token = $this->request->getCookie('access_token');
            }

            $this->authService->logout($token);
            $this->response->deleteCookie('access_token');

            return $this->success(null, 'Đăng xuất thành công');
        } catch (\Throwable $e) {
            log_message('error', 'Auth logout error: ' . $e->getMessage());
            return $this->error('Đăng xuất thất bại', 500);
        }
    }

    /**
     * GET /api/auth/me
     */
    public function me(): ResponseInterface
    {
        try {
            $user = $this->authService->getCurrentUser();
            if ($user === null) {
                return $this->error('Không tìm thấy người dùng', 401);
            }

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
     */
    public function profile(): ResponseInterface
    {
        try {
            $currentUser = $this->authService->getCurrentUser();
            if ($currentUser === null) {
                return $this->error('Vui lòng đăng nhập', 401);
            }

            $data = $this->request->getJSON(true) ?? [];
            $this->authService->updateProfile($currentUser['id'], $data);
            $profile = $this->authService->getUserProfile($currentUser['id']);

            return $this->success(['profile' => $profile], 'Cập nhật hồ sơ thành công');
        } catch (\Throwable $e) {
            log_message('error', 'Auth profile update error: ' . $e->getMessage());
            return $this->error('Cập nhật hồ sơ thất bại', 500);
        }
    }

    public function options()
    {
        return $this->response->setStatusCode(204);
    }

    // ==================== OAuth Endpoints ====================

    protected function getFrontendUrl(): string
    {
        $frontendUrl = env('FRONTEND_URL') ?: (getenv('FRONTEND_URL') ?: 'http://localhost:5173');
        return rtrim($frontendUrl, '/');
    }

    /**
     * GET /api/auth/oauth/google
     * Supports both direct browser redirect and JSON API response
     */
    public function oauthGoogle()
    {
        try {
            $oauthService = new OAuthService();
            $authUrl = $oauthService->getGoogleAuthUrl();

            // If user clicked direct link in browser, redirect immediately
            $accept = $this->request->getHeaderLine('Accept');
            if (str_contains($accept, 'text/html') || !$this->request->isAJAX()) {
                return redirect()->to($authUrl);
            }
            
            return $this->success([
                'url' => $authUrl,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'OAuth Google URL error: ' . $e->getMessage());
            return $this->error('Không thể tạo liên kết Google: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/auth/oauth/google/callback
     */
    public function oauthGoogleCallback()
    {
        $frontendUrl = $this->getFrontendUrl();

        try {
            $error = $this->request->getGet('error');
            if (!empty($error)) {
                $msg = ($error === 'access_denied') ? 'Đăng nhập Google đã bị hủy' : 'Lỗi từ Google: ' . $error;
                return redirect()->to($frontendUrl . '/login?error=' . urlencode($msg));
            }

            $code = $this->request->getGet('code');
            $state = $this->request->getGet('state');

            if (empty($code)) {
                return redirect()->to($frontendUrl . '/login?error=' . urlencode('Thiếu mã xác thực từ Google'));
            }

            $savedState = session()->get('oauth_google_state');
            if (!empty($savedState) && !empty($state) && !hash_equals($savedState, $state)) {
                return redirect()->to($frontendUrl . '/login?error=' . urlencode('Lỗi bảo mật CSRF: State không hợp lệ'));
            }
            session()->remove('oauth_google_state');

            $oauthService = new OAuthService();
            $userInfo = $oauthService->handleGoogleCallbackCode($code);
            if ($userInfo === null) {
                return redirect()->to($frontendUrl . '/login?error=' . urlencode('Không thể xác thực thông tin tài khoản Google'));
            }

            $result = $oauthService->handleOAuthUser($userInfo);

            set_cookie('access_token', $result['token'], 7 * 86400, '', '/', '', false, true, 'Lax');

            return redirect()->to($frontendUrl . '/login?token=' . urlencode($result['token']) . '&status=oauth_success')->withCookies();
        } catch (\Throwable $e) {
            log_message('error', 'OAuth Google callback error: ' . $e->getMessage());
            return redirect()->to($frontendUrl . '/login?error=' . urlencode('Đăng nhập Google thất bại: ' . $e->getMessage()));
        }
    }

    /**
     * GET /api/auth/oauth/facebook
     */
    public function oauthFacebook()
    {
        try {
            $oauthService = new OAuthService();
            $authUrl = $oauthService->getFacebookAuthUrl();

            $accept = $this->request->getHeaderLine('Accept');
            if (str_contains($accept, 'text/html') || !$this->request->isAJAX()) {
                return redirect()->to($authUrl);
            }
            
            return $this->success([
                'url' => $authUrl,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'OAuth Facebook URL error: ' . $e->getMessage());
            return $this->error('Không thể tạo liên kết Facebook: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/auth/oauth/facebook/callback
     */
    public function oauthFacebookCallback()
    {
        $frontendUrl = $this->getFrontendUrl();

        try {
            $error = $this->request->getGet('error');
            if (!empty($error)) {
                $errorDesc = $this->request->getGet('error_description') ?: 'Đăng nhập Facebook đã bị hủy';
                return redirect()->to($frontendUrl . '/login?error=' . urlencode($errorDesc));
            }

            $code = $this->request->getGet('code');
            $state = $this->request->getGet('state');

            if (empty($code)) {
                return redirect()->to($frontendUrl . '/login?error=' . urlencode('Thiếu mã xác thực từ Facebook'));
            }

            $savedState = session()->get('oauth_facebook_state');
            if (!empty($savedState) && !empty($state) && !hash_equals($savedState, $state)) {
                return redirect()->to($frontendUrl . '/login?error=' . urlencode('Lỗi bảo mật CSRF: State không hợp lệ'));
            }
            session()->remove('oauth_facebook_state');

            $oauthService = new OAuthService();
            $userInfo = $oauthService->handleFacebookCallbackCode($code);
            if ($userInfo === null) {
                return redirect()->to($frontendUrl . '/login?error=' . urlencode('Không thể xác thực thông tin tài khoản Facebook'));
            }

            $result = $oauthService->handleOAuthUser($userInfo);

            set_cookie('access_token', $result['token'], 7 * 86400, '', '/', '', false, true, 'Lax');

            return redirect()->to($frontendUrl . '/login?token=' . urlencode($result['token']) . '&status=oauth_success')->withCookies();
        } catch (\Throwable $e) {
            log_message('error', 'OAuth Facebook callback error: ' . $e->getMessage());
            return redirect()->to($frontendUrl . '/login?error=' . urlencode('Đăng nhập Facebook thất bại: ' . $e->getMessage()));
        }
    }
}
