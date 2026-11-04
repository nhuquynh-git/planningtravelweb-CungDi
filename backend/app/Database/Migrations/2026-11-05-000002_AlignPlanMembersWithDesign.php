<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * `plan_members` thiếu role/status của ER. UI cần:
 *  - Trưởng nhóm / Thành viên
 *  - pending  = "Đang chờ duyệt" (xin tham gia bằng mã mời, có nút Duyệt/Từ chối, Hủy yêu cầu)
 *  - inactive = đã rời plan / bị xóa khỏi plan (giữ dòng để không mất dữ liệu chia tiền)
 */
class AlignPlanMembersWithDesign extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('plan_members', [
            'role'         => ['type' => 'varchar', 'constraint' => 16, 'null' => false, 'default' => 'thanh_vien'],
            'status'       => ['type' => 'varchar', 'constraint' => 16, 'null' => false, 'default' => 'active'],
            'requested_at' => ['type' => 'datetime', 'null' => true],
            'left_at'      => ['type' => 'datetime', 'null' => true],
        ]);

        // Người đang xin tham gia chưa có joined_at.
        $this->db->query('ALTER TABLE plan_members MODIFY joined_at DATETIME NULL');

        // Chủ plan luôn là trưởng nhóm.
        $this->db->query(
            'UPDATE plan_members pm
             JOIN plans p ON p.id = pm.plan_id AND p.owner_user_id = pm.user_id
             SET pm.role = \'truong_nhom\''
        );
        // Plan cũ chưa có dòng thành viên cho chủ plan thì bổ sung.
        $this->db->query(
            'INSERT INTO plan_members (plan_id, user_id, added_by_user_id, joined_at, role, status)
             SELECT p.id, p.owner_user_id, p.owner_user_id, COALESCE(p.created_at, NOW()), \'truong_nhom\', \'active\'
             FROM plans p
             LEFT JOIN plan_members pm ON pm.plan_id = p.id AND pm.user_id = p.owner_user_id
             WHERE pm.id IS NULL'
        );

        $this->db->query(
            'ALTER TABLE plan_members
             ADD KEY idx_plan_members_plan_status (plan_id, status),
             ADD CONSTRAINT chk_plan_members_role CHECK (role IN (\'truong_nhom\', \'thanh_vien\')),
             ADD CONSTRAINT chk_plan_members_status CHECK (status IN (\'active\', \'inactive\', \'pending\')),
             ADD CONSTRAINT chk_plan_members_joined CHECK (
                 (status = \'pending\' AND joined_at IS NULL)
                 OR (status <> \'pending\' AND joined_at IS NOT NULL)
             )'
        );
    }

    public function down(): void
    {
        $this->db->query(
            'ALTER TABLE plan_members
             DROP CHECK chk_plan_members_joined,
             DROP CHECK chk_plan_members_status,
             DROP CHECK chk_plan_members_role,
             DROP INDEX idx_plan_members_plan_status'
        );
        $this->db->query('DELETE FROM plan_members WHERE status = \'pending\'');
        $this->db->query('ALTER TABLE plan_members MODIFY joined_at DATETIME NOT NULL');
        $this->forge->dropColumn('plan_members', ['role', 'status', 'requested_at', 'left_at']);
    }
}
