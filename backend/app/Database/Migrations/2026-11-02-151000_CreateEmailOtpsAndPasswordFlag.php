<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmailOtpsAndPasswordFlag extends Migration
{
    public function up(): void
    {
        // Bảng lưu OTP + dữ liệu đăng ký tạm (chưa tạo user thật cho tới khi OTP đúng)
        $this->forge->addField([
            'id'           => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'email'        => ['type' => 'varchar', 'constraint' => 254],
            'purpose'      => ['type' => 'varchar', 'constraint' => 16], // register | reset
            'code_hash'    => ['type' => 'varchar', 'constraint' => 255],
            'payload'      => ['type' => 'text', 'null' => true],        // JSON: username, password_hash
            'attempts'     => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'default' => 0],
            'expires_at'   => ['type' => 'datetime'],
            'last_sent_at' => ['type' => 'datetime'],
            'created_at'   => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['email', 'purpose'], 'uq_email_otps_email_purpose');
        $this->forge->addKey('expires_at');
        $this->forge->createTable('email_otps', false, ['ENGINE' => 'InnoDB']);

        // Cờ: tài khoản Google/Facebook mới chưa đặt mật khẩu (0 = chưa đặt)
        $this->forge->addColumn('user_profiles', [
            'password_set' => [
                'type'       => 'tinyint',
                'constraint' => 1,
                'unsigned'   => true,
                'null'       => false,
                'default'    => 1,
                'after'      => 'bio',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('user_profiles', 'password_set');
        $this->forge->dropTable('email_otps');
    }
}
