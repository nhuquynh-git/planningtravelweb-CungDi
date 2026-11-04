<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * - auth_identities: đăng nhập Google/Facebook (ER: AUTH_IDENTITY)
 * - subscriptions : gói Premium tháng/năm/trọn đời (ER: SUBSCRIPTION, màn hình Premium)
 *
 * PASSWORD_RESET_CODE của ER được thay bằng bảng `email_otps` đã có (purpose = reset),
 * vì bảng đó còn phục vụ đăng ký khi user chưa được tạo.
 */
class CreateAuthIdentitiesAndSubscriptions extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'               => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'          => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'provider'         => ['type' => 'varchar', 'constraint' => 16],
            'provider_user_id' => ['type' => 'varchar', 'constraint' => 191],
            'created_at'       => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['provider', 'provider_user_id'], 'uq_auth_identities_provider_user');
        $this->forge->addUniqueKey(['user_id', 'provider'], 'uq_auth_identities_user_provider');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'RESTRICT', 'RESTRICT', 'fk_auth_identities_user');
        $this->forge->createTable('auth_identities', false, ['ENGINE' => 'InnoDB']);
        $this->db->query(
            'ALTER TABLE auth_identities
             ADD CONSTRAINT chk_auth_identities_provider CHECK (provider IN (\'google\', \'facebook\'))'
        );

        $this->forge->addField([
            'id'              => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'plan_type'       => ['type' => 'varchar', 'constraint' => 16],
            'amount_paid'     => ['type' => 'bigint', 'constraint' => 20, 'unsigned' => true], // VND
            'payment_method'  => ['type' => 'varchar', 'constraint' => 16],
            'transaction_ref' => ['type' => 'varchar', 'constraint' => 100],
            'status'          => ['type' => 'varchar', 'constraint' => 16, 'default' => 'pending'],
            'started_at'      => ['type' => 'datetime', 'null' => true],
            'expires_at'      => ['type' => 'datetime', 'null' => true], // NULL nếu lifetime
            'created_at'      => ['type' => 'datetime', 'null' => true],
            'updated_at'      => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('transaction_ref', 'uq_subscriptions_transaction_ref');
        // Tra "gói còn hạn của user" và đếm suất trọn đời (còn 143/200).
        $this->forge->addKey(['user_id', 'status', 'expires_at'], false, false, 'idx_subscriptions_user_active');
        $this->forge->addKey(['plan_type', 'status'], false, false, 'idx_subscriptions_plan_status');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'RESTRICT', 'RESTRICT', 'fk_subscriptions_user');
        $this->forge->createTable('subscriptions', false, ['ENGINE' => 'InnoDB']);
        $this->db->query(
            'ALTER TABLE subscriptions
             ADD CONSTRAINT chk_subscriptions_plan_type CHECK (plan_type IN (\'monthly\', \'yearly\', \'lifetime\')),
             ADD CONSTRAINT chk_subscriptions_method CHECK (payment_method IN (\'momo\', \'bank\', \'zalopay\')),
             ADD CONSTRAINT chk_subscriptions_status CHECK (status IN (\'pending\', \'success\', \'failed\', \'refunded\')),
             ADD CONSTRAINT chk_subscriptions_period CHECK (
                 status <> \'success\'
                 OR (plan_type = \'lifetime\' AND started_at IS NOT NULL AND expires_at IS NULL)
                 OR (plan_type <> \'lifetime\' AND started_at IS NOT NULL AND expires_at IS NOT NULL AND expires_at > started_at)
             )'
        );
    }

    public function down(): void
    {
        $this->forge->dropTable('subscriptions');
        $this->forge->dropTable('auth_identities');
    }
}
