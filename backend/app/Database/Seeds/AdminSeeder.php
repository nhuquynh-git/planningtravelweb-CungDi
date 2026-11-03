<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;

/**
 * Tạo tài khoản admin mặc định (idempotent: chạy nhiều lần không tạo trùng).
 *
 * Thông tin lấy từ .env, nếu không có thì dùng giá trị dev bên dưới:
 *   ADMIN_EMAIL, ADMIN_USERNAME, ADMIN_PASSWORD
 *
 * Chạy tay:  php spark db:seed AdminSeeder
 * (cũng được gọi tự động bởi migration SeedDefaultAdmin)
 */
class AdminSeeder extends Seeder
{
    private const DEFAULT_EMAIL    = 'admin@cungdi.local';
    private const DEFAULT_USERNAME = 'admin';
    private const DEFAULT_PASSWORD = 'admin'; // CHỈ để dev. Production phải đặt ADMIN_PASSWORD trong .env

    public function run(): void
    {
        $email    = strtolower(trim((string) env('ADMIN_EMAIL', self::DEFAULT_EMAIL)));
        $username = trim((string) env('ADMIN_USERNAME', self::DEFAULT_USERNAME));
        $password = (string) env('ADMIN_PASSWORD', self::DEFAULT_PASSWORD);

        if (! array_key_exists('admin', config('AuthGroups')->groups)) {
            throw new \RuntimeException("Nhóm 'admin' chưa được khai báo trong app/Config/AuthGroups.php");
        }

        $users      = model(\CodeIgniter\Shield\Models\UserModel::class);
        $identities = model(\CodeIgniter\Shield\Models\UserIdentityModel::class);

        // Đã có tài khoản với email này -> chỉ đảm bảo đã kích hoạt + có quyền admin
        $existing = $identities->where('type', 'email_password')->where('secret', $email)->first();

        if ($existing !== null) {
            $user = $users->findById($existing->user_id);
            if ($user !== null) {
                if (! $user->isActivated()) {
                    $user->active = 1;
                    $users->save($user);
                }
                if (! $user->inGroup('admin')) {
                    $user->addGroup('admin');
                }
                echo "Admin đã tồn tại ({$email}), đã đảm bảo quyền admin.\n";
                return;
            }
        }

        if ($users->where('username', $username)->first() !== null) {
            throw new \RuntimeException("Username '{$username}' đã được dùng bởi tài khoản khác. Đặt ADMIN_USERNAME khác trong .env");
        }

        $user = new User([
            'email'    => $email,
            'username' => $username,
            'password' => $password,
            'active'   => 1,
        ]);
        $users->save($user);

        $user = $users->findById($users->getInsertID());
        if ($user === null) {
            throw new \RuntimeException('Không thể tạo tài khoản admin');
        }

        $user->addGroup('admin');

        echo "Đã tạo admin: {$username} / {$email}\n";
    }
}
