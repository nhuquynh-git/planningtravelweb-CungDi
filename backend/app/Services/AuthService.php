<?php

namespace App\Services;

use App\Models\UserProfileModel;
use CodeIgniter\Shield\Entities\User;

/**
 * Authentication Service
 *
 * - Đăng ký: lưu đăng ký TẠM + gửi OTP; chỉ tạo user thật sau khi OTP đúng.
 * - Đăng nhập email/username, đăng xuất, token.
 * - Quên mật khẩu (OTP) / đặt lại mật khẩu.
 * - Đặt mật khẩu lần đầu cho tài khoản Google/Facebook.
 */
class AuthService
{
    protected $userProvider;
    protected $identityProvider;
    protected $auth;
    protected OtpService $otp;

    public function __construct()
    {
        $this->userProvider     = model(\CodeIgniter\Shield\Models\UserModel::class);
        $this->identityProvider = model(\CodeIgniter\Shield\Models\UserIdentityModel::class);
        $this->auth             = auth('tokens');
        $this->otp              = new OtpService();
    }

    // ==================== Đăng ký + OTP ====================

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
            ],
        ];

        $validation = service('validation');
        $validation->setRules($rules, $messages);

        if (! $validation->run($data)) {
            throw new \RuntimeException(implode(', ', $validation->getErrors()));
        }

        $email    = strtolower(trim($data['email']));
        $username = trim($data['username']);

        if ($this->emailExists($email)) {
            throw new \RuntimeException('Địa chỉ email này đã được sử dụng');
        }
        if ($this->usernameExists($username)) {
            throw new \RuntimeException('Tên tài khoản này đã được sử dụng');
        }

        // KHÔNG tạo user ở bước này. Chỉ lưu tạm (mật khẩu đã băm) cùng OTP.
        $otp = $this->otp->issue($email, OtpService::PURPOSE_REGISTER, [
            'username'      => $username,
            'password_hash' => service('passwords')->hash($data['password']),
        ]);

        return [
            'status'      => 'need_verification',
            'email'       => $email,
            'username'    => $username,
            'message'     => 'Mã xác nhận 6 số đã được gửi tới email của bạn',
            'otp_preview' => (ENVIRONMENT !== 'production') ? $otp : null,
        ];
    }

    /**
     * Xác minh OTP -> tạo tài khoản thật (đã kích hoạt) -> đăng nhập luôn.
     */
    public function verifyOtp(string $email, string $otp): array
    {
        $email = strtolower(trim($email));
        $otp   = trim($otp);

        if ($email === '' || $otp === '') {
            throw new \RuntimeException('Vui lòng nhập đầy đủ email và mã xác nhận');
        }

        $payload = $this->otp->verify($email, OtpService::PURPOSE_REGISTER, $otp);

        if (empty($payload['username']) || empty($payload['password_hash'])) {
            throw new \RuntimeException('Dữ liệu đăng ký không hợp lệ. Vui lòng đăng ký lại.');
        }

        // Kiểm tra lại phòng trường hợp trong 5 phút có người khác lấy mất
        if ($this->emailExists($email)) {
            throw new \RuntimeException('Địa chỉ email này đã được sử dụng');
        }
        if ($this->usernameExists($payload['username'])) {
            throw new \RuntimeException('Tên tài khoản này vừa được người khác sử dụng. Vui lòng đăng ký lại với tên khác.');
        }

        $user = new User([
            'email'    => $email,
            'username' => $payload['username'],
            'password' => bin2hex(random_bytes(16)), // tạm, ghi đè bằng hash đã lưu ngay bên dưới
            'active'   => 1,
        ]);
        $this->userProvider->save($user);

        $user = $this->userProvider->findById($this->userProvider->getInsertID());
        if ($user === null) {
            throw new \RuntimeException('Không thể tạo tài khoản');
        }

        // Dùng đúng mật khẩu người dùng đã nhập lúc đăng ký
        db_connect()->table('auth_identities')
            ->where(['user_id' => $user->id, 'type' => 'email_password'])
            ->update(['secret2' => $payload['password_hash']]);

        $user->addGroup('user');
        $this->markPasswordSet($user->id, true);

        $token = $this->generateToken($user, 7 * 86400);

        return [
            'user'  => $this->formatUser($user),
            'token' => $token,
        ];
    }

    public function resendOtp(string $email): array
    {
        $otp = $this->otp->resend($email, OtpService::PURPOSE_REGISTER);

        return [
            'success'     => true,
            'message'     => 'Mã OTP mới đã được gửi tới email của bạn',
            'otp_preview' => (ENVIRONMENT !== 'production') ? $otp : null,
        ];
    }

    // ==================== Đăng nhập / đăng xuất ====================

    public function login(array $credentials): array
    {
        $loginInput = trim($credentials['login'] ?? $credentials['email'] ?? $credentials['username'] ?? '');
        $password   = $credentials['password'] ?? '';
        $remember   = ! empty($credentials['remember_me'] ?? $credentials['remember'] ?? false);

        if ($loginInput === '') {
            throw new \RuntimeException('Vui lòng nhập email hoặc tên tài khoản');
        }
        if ($password === '') {
            throw new \RuntimeException('Vui lòng nhập mật khẩu');
        }

        $user = null;

        if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
            $user = $this->findUserByEmail(strtolower($loginInput));
        } else {
            $user = $this->userProvider->where('username', $loginInput)->first();
        }

        if ($user === null) {
            throw new \RuntimeException('Tên tài khoản hoặc mật khẩu không chính xác');
        }

        // Tài khoản Google/Facebook chưa đặt mật khẩu lần đầu
        if ($this->needsPasswordSetup($user->id)) {
            throw new \RuntimeException(
                'Tài khoản này được tạo qua Google/Facebook. Hãy đăng nhập bằng Google/Facebook, hoặc dùng "Quên mật khẩu" để tạo mật khẩu.'
            );
        }

        $emailIdentity = $user->getEmailIdentity();
        if ($emailIdentity === null || empty($emailIdentity->secret2)) {
            throw new \RuntimeException('Tên tài khoản hoặc mật khẩu không chính xác');
        }

        if (! service('passwords')->verify($password, $emailIdentity->secret2)) {
            throw new \RuntimeException('Tên tài khoản hoặc mật khẩu không chính xác');
        }

        // Chặn hẳn tài khoản chưa kích hoạt (dữ liệu cũ từ luồng đăng ký trước đây)
        if (! $user->isActivated()) {
            throw new \RuntimeException(
                'Tài khoản chưa được xác minh email. Hãy dùng "Quên mật khẩu" để xác minh qua email, hoặc đăng ký lại.'
            );
        }

        $lifetime = $remember ? (30 * 86400) : (7 * 86400);
        $token    = $this->generateToken($user, $lifetime);

        return [
            'status' => 'success',
            'user'   => $this->formatUser($user),
            'token'  => $token,
        ];
    }

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

    public function validateToken(string $token): ?User
    {
        $identity = $this->identityProvider->where('type', 'access_token')
            ->where('secret', hash('sha256', $token))
            ->first();

        if ($identity === null) {
            return null;
        }

        if (! empty($identity->expires) && strtotime($identity->expires) < time()) {
            $this->identityProvider->where('id', $identity->id)->delete();
            return null;
        }

        $user = $this->userProvider->findById($identity->user_id);
        if ($user === null || ! $user->isActivated()) {
            return null;
        }

        $this->auth->setUser($user);
        return $user;
    }

    // ==================== Quên mật khẩu ====================

    /**
     * Luôn trả cùng một thông báo để không lộ email nào đã đăng ký.
     */
    public function forgotPassword(string $email): array
    {
        $email = strtolower(trim($email));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('Địa chỉ email không hợp lệ');
        }

        $result = [
            'message'     => 'Nếu email tồn tại trong hệ thống, mã xác nhận đã được gửi tới hộp thư của bạn.',
            'otp_preview' => null,
        ];

        // Email giả của Facebook (không có email thật) thì không gửi được
        if (str_ends_with($email, '@facebook.local') || ! $this->emailExists($email)) {
            return $result;
        }

        try {
            $otp = $this->otp->issue($email, OtpService::PURPOSE_RESET);
            if (ENVIRONMENT !== 'production') {
                $result['otp_preview'] = $otp;
            }
        } catch (\RuntimeException $e) {
            // Đang trong thời gian chờ gửi lại: vẫn trả thông báo chung
            log_message('info', 'forgotPassword: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Bước 2 của quên mật khẩu: chỉ kiểm tra mã, KHÔNG tiêu thụ mã.
     * Mã vẫn dùng được ở bước đặt mật khẩu (resetPassword).
     */
    public function verifyResetOtp(string $email, string $otp): void
    {
        $this->otp->verify(strtolower(trim($email)), OtpService::PURPOSE_RESET, $otp, false);
    }

    public function resetPassword(string $email, string $otp, string $newPassword): array
    {
        $email = strtolower(trim($email));

        // Kiểm tra mật khẩu TRƯỚC để không đốt OTP khi mật khẩu không hợp lệ
        $this->assertValidPassword($newPassword);

        $this->otp->verify($email, OtpService::PURPOSE_RESET, $otp);

        $user = $this->findUserByEmail($email);
        if ($user === null) {
            throw new \RuntimeException('Không tìm thấy tài khoản với email này');
        }

        $user->password = $newPassword;
        $user->active   = 1; // đã chứng minh sở hữu email qua OTP
        $this->userProvider->save($user);

        $this->markPasswordSet($user->id, true);

        // Đăng xuất mọi thiết bị
        $user->revokeAllAccessTokens();

        return ['message' => 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập bằng mật khẩu mới.'];
    }

    // ==================== Mật khẩu lần đầu (Google/Facebook) ====================

    public function setInitialPassword(int $userId, string $password): void
    {
        $this->assertValidPassword($password);

        if (! $this->needsPasswordSetup($userId)) {
            throw new \RuntimeException('Tài khoản đã có mật khẩu. Hãy dùng "Quên mật khẩu" nếu muốn đổi.');
        }

        $user = $this->userProvider->findById($userId);
        if ($user === null) {
            throw new \RuntimeException('Không tìm thấy người dùng');
        }

        $user->password = $password;
        $this->userProvider->save($user);

        $this->markPasswordSet($userId, true);
    }

    public function needsPasswordSetup(int $userId): bool
    {
        $row = db_connect()->table('user_profiles')
            ->select('password_set')
            ->where('user_id', $userId)
            ->get()->getRowArray();

        return $row !== null && (int) $row['password_set'] === 0;
    }

    public function markPasswordSet(int $userId, bool $isSet): void
    {
        $db  = db_connect();
        $now = date('Y-m-d H:i:s');

        $exists = $db->table('user_profiles')->where('user_id', $userId)->countAllResults() > 0;

        if ($exists) {
            $db->table('user_profiles')->where('user_id', $userId)
                ->update(['password_set' => $isSet ? 1 : 0, 'updated_at' => $now]);
        } else {
            $db->table('user_profiles')->insert([
                'user_id'      => $userId,
                'password_set' => $isSet ? 1 : 0,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }
    }

    // ==================== Helpers ====================

    protected function assertValidPassword(string $password): void
    {
        if (strlen($password) < 8) {
            throw new \RuntimeException('Mật khẩu phải có ít nhất 8 ký tự');
        }
        if (strlen($password) > 72) {
            throw new \RuntimeException('Mật khẩu tối đa 72 ký tự');
        }
    }

    protected function emailExists(string $email): bool
    {
        return $this->identityProvider
            ->where('type', 'email_password')
            ->where('secret', $email)
            ->first() !== null;
    }

    protected function usernameExists(string $username): bool
    {
        return $this->userProvider->where('username', $username)->first() !== null;
    }

    protected function findUserByEmail(string $email): ?User
    {
        $identity = $this->identityProvider
            ->where('type', 'email_password')
            ->where('secret', $email)
            ->first();

        return $identity === null ? null : $this->userProvider->findById($identity->user_id);
    }

    protected function generateToken(User $user, int $lifetime = 604800): string
    {
        $accessToken = $user->generateAccessToken('api-token');

        $this->identityProvider->update($accessToken->id, [
            'expires' => date('Y-m-d H:i:s', time() + $lifetime),
        ]);

        return $accessToken->raw_token;
    }

    /**
     * public: OAuthService dùng lại để trả cùng định dạng.
     */
    public function formatUser(User $user): array
    {
        return [
            'id'                   => $user->id,
            'email'                => $user->email,
            'username'             => $user->username ?? '',
            'groups'               => $user->getGroups(),
            'permissions'          => array_keys($user->getPermissions()),
            'created_at'           => $user->created_at?->format('Y-m-d H:i:s'),
            'is_active'            => $user->isActivated(),
            'needs_password_setup' => $this->needsPasswordSetup((int) $user->id),
        ];
    }

    // ==================== Profile ====================

    public function getUserProfile(int $userId): ?array
    {
        $profileModel = new UserProfileModel();
        $profile      = $profileModel->find($userId);

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

    public function updateProfile(int $userId, array $data): bool
    {
        $profileModel = new UserProfileModel();
        $profileData  = ['user_id' => $userId];

        foreach (['display_name', 'avatar', 'phone', 'bio'] as $field) {
            if (isset($data[$field])) {
                $profileData[$field] = $data[$field];
            }
        }

        $existing = $profileModel->find($userId);

        if ($existing === null) {
            return (bool) $profileModel->insert($profileData);
        }

        return $profileModel->update($userId, $profileData);
    }
}
