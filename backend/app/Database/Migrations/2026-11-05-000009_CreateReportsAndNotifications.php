<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * reports       : báo cáo plan / bình luận / đánh giá địa điểm (ER: REPORT)
 * notifications : màn hình "Thông báo" (ER: NOTIFICATION)
 *
 * Quy tắc "đúng một đối tượng bị báo cáo" do ứng dụng kiểm soát (MySQL không cho CHECK lên cột FK có CASCADE).
 */
class CreateReportsAndNotifications extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                  => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'reporter_id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'reported_plan_id'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'reported_comment_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'reported_review_id'  => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'reason'              => ['type' => 'varchar', 'constraint' => 24],
            'description'         => ['type' => 'text', 'null' => true],
            'status'              => ['type' => 'varchar', 'constraint' => 16, 'default' => 'pending'],
            'handled_by'          => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'resolution_note'     => ['type' => 'text', 'null' => true],
            'handled_at'          => ['type' => 'datetime', 'null' => true],
            'created_at'          => ['type' => 'datetime', 'null' => true],
            'updated_at'          => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        // Mỗi người chỉ báo cáo một đối tượng một lần.
        $this->forge->addUniqueKey(['reporter_id', 'reported_plan_id'], 'uq_reports_reporter_plan');
        $this->forge->addUniqueKey(['reporter_id', 'reported_comment_id'], 'uq_reports_reporter_comment');
        $this->forge->addUniqueKey(['reporter_id', 'reported_review_id'], 'uq_reports_reporter_review');
        $this->forge->addKey(['status', 'created_at'], false, false, 'idx_reports_queue');
        $this->forge->addKey('reported_plan_id');
        $this->forge->addKey('reported_comment_id');
        $this->forge->addKey('reported_review_id');
        $this->forge->addKey('handled_by');
        $this->forge->addForeignKey('reporter_id', 'users', 'id', 'RESTRICT', 'RESTRICT', 'fk_reports_reporter');
        $this->forge->addForeignKey('reported_plan_id', 'plans', 'id', 'CASCADE', 'CASCADE', 'fk_reports_plan');
        $this->forge->addForeignKey('reported_comment_id', 'plan_comments', 'id', 'CASCADE', 'CASCADE', 'fk_reports_comment');
        $this->forge->addForeignKey('reported_review_id', 'location_reviews', 'id', 'CASCADE', 'CASCADE', 'fk_reports_review');
        $this->forge->addForeignKey('handled_by', 'users', 'id', 'RESTRICT', 'RESTRICT', 'fk_reports_handler');
        $this->forge->createTable('reports', false, ['ENGINE' => 'InnoDB']);
        $this->db->query(
            'ALTER TABLE reports
             ADD CONSTRAINT chk_reports_reason CHECK (
                 reason IN (\'spam\', \'offensive\', \'inappropriate_photo\', \'other\')
             ),
             ADD CONSTRAINT chk_reports_status CHECK (status IN (\'pending\', \'resolved\', \'dismissed\')),
             ADD CONSTRAINT chk_reports_handled CHECK (
                 (status = \'pending\' AND handled_by IS NULL AND handled_at IS NULL)
                 OR (status <> \'pending\' AND handled_by IS NOT NULL AND handled_at IS NOT NULL)
             )'
        );

        $this->forge->addField([
            'id'           => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'recipient_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'actor_id'     => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true], // NULL = hệ thống
            'type'         => ['type' => 'varchar', 'constraint' => 32],
            'plan_id'      => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'comment_id'   => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            // JSON: tên plan lúc gửi, số tiền, số ngày còn lại... (giữ được thông báo cả khi plan đã bị xóa)
            'data'         => ['type' => 'json', 'null' => true],
            'group_key'    => ['type' => 'varchar', 'constraint' => 100, 'null' => true],
            'count'        => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'default' => 1],
            'is_read'      => ['type' => 'tinyint', 'constraint' => 1, 'default' => 0],
            'created_at'   => ['type' => 'datetime', 'null' => true],
            'updated_at'   => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        // Danh sách theo người nhận + đếm chưa đọc.
        $this->forge->addKey(['recipient_id', 'is_read', 'created_at'], false, false, 'idx_notifications_inbox');
        $this->forge->addKey(['recipient_id', 'group_key'], false, false, 'idx_notifications_group');
        $this->forge->addKey('actor_id');
        $this->forge->addKey('plan_id');
        $this->forge->addKey('comment_id');
        $this->forge->addForeignKey('recipient_id', 'users', 'id', 'RESTRICT', 'RESTRICT', 'fk_notifications_recipient');
        $this->forge->addForeignKey('actor_id', 'users', 'id', 'RESTRICT', 'RESTRICT', 'fk_notifications_actor');
        $this->forge->addForeignKey('plan_id', 'plans', 'id', 'CASCADE', 'SET NULL', 'fk_notifications_plan');
        $this->forge->addForeignKey('comment_id', 'plan_comments', 'id', 'CASCADE', 'SET NULL', 'fk_notifications_comment');
        $this->forge->createTable('notifications', false, ['ENGINE' => 'InnoDB']);
        $this->db->query(
            'ALTER TABLE notifications
             ADD CONSTRAINT chk_notifications_type CHECK (type IN (
                 \'plan_like\', \'plan_comment\', \'comment_reply\', \'join_request\', \'join_accepted\',
                 \'join_rejected\', \'member_removed\', \'member_left\', \'cost_updated\',
                 \'saved_plan_unavailable\', \'premium_expiring\'
             )),
             ADD CONSTRAINT chk_notifications_count CHECK (count >= 1)'
        );
    }

    public function down(): void
    {
        $this->forge->dropTable('notifications');
        $this->forge->dropTable('reports');
    }
}
