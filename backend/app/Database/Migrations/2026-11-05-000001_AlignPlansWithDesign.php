<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Bổ sung cho bảng `plans` các cột còn thiếu so với ER + UI:
 * mã mời, loại hình, ảnh bìa, hiển thị, trạng thái, chi phí tổng, số ngày,
 * "sao chép từ", bộ đếm like/bình luận/chia sẻ/lưu.
 * Ngày bắt đầu/kết thúc chuyển thành tùy chọn (form "Tạo plan mới" ghi "Tùy chọn").
 */
class AlignPlansWithDesign extends Migration
{
    /** Khớp 8 chip "Loại hình du lịch" trong UI. */
    private const TYPES = [
        'resort', 'eco', 'adventure', 'entertainment',
        'food_shopping', 'mice', 'culture', 'sport',
    ];

    public function up(): void
    {
        $this->forge->addColumn('plans', [
            'invite_code'             => ['type' => 'varchar', 'constraint' => 16, 'null' => true],
            'type'                    => ['type' => 'varchar', 'constraint' => 24, 'null' => false, 'default' => 'resort'],
            'plan_photo_url'          => ['type' => 'varchar', 'constraint' => 500, 'null' => true],
            'status'                  => ['type' => 'varchar', 'constraint' => 16, 'null' => false, 'default' => 'active'],
            'visibility'              => ['type' => 'varchar', 'constraint' => 16, 'null' => false, 'default' => 'private'],
            'duration_days'           => ['type' => 'smallint', 'constraint' => 5, 'unsigned' => true, 'null' => true],
            // VND. estimated_total_cost = cả nhóm; est_cost_per_person_day dùng cho bộ lọc "Ngân sách mỗi người mỗi ngày".
            'estimated_total_cost'    => ['type' => 'bigint', 'constraint' => 20, 'unsigned' => true, 'null' => true],
            'actual_total_cost'       => ['type' => 'bigint', 'constraint' => 20, 'unsigned' => true, 'null' => true],
            'est_cost_per_person_day' => ['type' => 'bigint', 'constraint' => 20, 'unsigned' => true, 'null' => true],
            'copied_from_plan_id'     => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            // Bộ đếm phi chuẩn hóa cho thẻ plan; ứng dụng cập nhật cùng lúc với bảng gốc.
            'like_count'              => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => false, 'default' => 0],
            'comment_count'           => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => false, 'default' => 0],
            'share_count'             => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => false, 'default' => 0],
            'save_count'              => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => false, 'default' => 0],
        ]);

        $this->backfillInviteCodes();

        // Gỡ CHECK ngày cũ trước khi đổi cột sang NULL, rồi tạo lại chấp nhận NULL.
        $this->db->query('ALTER TABLE plans DROP CHECK chk_plans_date_range');
        $this->db->query(
            'ALTER TABLE plans
             MODIFY start_date DATE NULL,
             MODIFY end_date DATE NULL,
             MODIFY invite_code VARCHAR(16) NOT NULL,
             ADD UNIQUE KEY uq_plans_invite_code (invite_code),
             ADD KEY idx_plans_explore (visibility, status, created_at),
             ADD KEY idx_plans_type (type),
             ADD KEY idx_plans_destination (destination_name),
             ADD KEY idx_plans_budget (est_cost_per_person_day),
             ADD KEY idx_plans_copied_from (copied_from_plan_id)'
        );
        $this->db->query(
            'ALTER TABLE plans
             ADD CONSTRAINT chk_plans_date_range CHECK (
                 start_date IS NULL OR end_date IS NULL OR end_date >= start_date
             ),
             ADD CONSTRAINT chk_plans_type CHECK (type IN (' . $this->quotedList(self::TYPES) . ')),
             ADD CONSTRAINT chk_plans_status CHECK (status IN (\'active\', \'hidden_by_admin\')),
             ADD CONSTRAINT chk_plans_visibility CHECK (visibility IN (\'private\', \'public\')),
             ADD CONSTRAINT chk_plans_duration CHECK (duration_days IS NULL OR duration_days > 0)'
        );
        $this->db->query(
            'ALTER TABLE plans
             ADD CONSTRAINT fk_plans_copied_from FOREIGN KEY (copied_from_plan_id)
             REFERENCES plans (id) ON DELETE SET NULL ON UPDATE CASCADE'
        );
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE plans DROP FOREIGN KEY fk_plans_copied_from');
        foreach (['chk_plans_duration', 'chk_plans_visibility', 'chk_plans_status', 'chk_plans_type', 'chk_plans_date_range'] as $check) {
            $this->db->query('ALTER TABLE plans DROP CHECK ' . $check);
        }
        $this->db->query(
            'ALTER TABLE plans
             DROP INDEX idx_plans_copied_from,
             DROP INDEX idx_plans_budget,
             DROP INDEX idx_plans_destination,
             DROP INDEX idx_plans_type,
             DROP INDEX idx_plans_explore,
             DROP INDEX uq_plans_invite_code'
        );
        $this->forge->dropColumn('plans', [
            'invite_code', 'type', 'plan_photo_url', 'status', 'visibility', 'duration_days',
            'estimated_total_cost', 'actual_total_cost', 'est_cost_per_person_day',
            'copied_from_plan_id', 'like_count', 'comment_count', 'share_count', 'save_count',
        ]);
        $this->db->query('UPDATE plans SET start_date = COALESCE(start_date, CURDATE())');
        $this->db->query('UPDATE plans SET end_date = COALESCE(end_date, start_date)');
        $this->db->query('ALTER TABLE plans MODIFY start_date DATE NOT NULL, MODIFY end_date DATE NOT NULL');
        $this->db->query('ALTER TABLE plans ADD CONSTRAINT chk_plans_date_range CHECK (end_date >= start_date)');
    }

    /** Sinh mã mời dạng TRIP-9F2K cho các plan đã tồn tại. */
    private function backfillInviteCodes(): void
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $used     = [];
        $rows     = $this->db->query('SELECT id FROM plans WHERE invite_code IS NULL')->getResultArray();
        foreach ($rows as $row) {
            do {
                $code = 'TRIP-';
                for ($i = 0; $i < 4; $i++) {
                    $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
                }
            } while (isset($used[$code]));
            $used[$code] = true;
            $this->db->query('UPDATE plans SET invite_code = ? WHERE id = ?', [$code, $row['id']]);
        }
    }

    /** @param list<string> $values */
    private function quotedList(array $values): string
    {
        return implode(', ', array_map(static fn (string $v): string => "'" . $v . "'", $values));
    }
}
