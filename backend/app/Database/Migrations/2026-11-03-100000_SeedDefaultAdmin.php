<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Seeds\AdminSeeder;
use CodeIgniter\Database\Migration;

/**
 * Tạo admin mặc định ngay khi chạy `php spark migrate`,
 * để thành viên mới pull code về chỉ cần migrate là đăng nhập được.
 */
class SeedDefaultAdmin extends Migration
{
    public function up(): void
    {
        \Config\Database::seeder()->call(AdminSeeder::class);
    }

    public function down(): void
    {
        // Không xoá tài khoản khi rollback để tránh mất dữ liệu.
    }
}
