<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Shield\Config\Auth as ShieldAuth;

/**
 * Cấu hình CodeIgniter Shield cho dự án Cùng Đi.
 *
 * File này CHỈ ghi đè những gì cần thiết. Mọi giá trị còn lại (authenticators,
 * bảng auth_identities / auth_logins..., thuật toán băm mật khẩu...) lấy mặc định
 * từ CodeIgniter\Shield\Config\Auth.
 *
 * Nhóm (user/admin) và quyền nằm ở app/Config/AuthGroups.php,
 * cấu hình token (header Authorization: Bearer ...) nằm ở app/Config/AuthToken.php.
 */
class Auth extends ShieldAuth
{
    /**
     * Dự án dùng luồng đăng ký riêng qua API (AuthService + OTP),
     * không dùng trang đăng ký có sẵn của Shield.
     */
    public bool $allowRegistration = false;

    /**
     * Không dùng hành động kích hoạt/2FA của Shield:
     * việc xác minh email đã xử lý bằng OTP trong OtpService.
     */
    public array $actions = [
        'register' => null,
        'login'    => null,
    ];
}
