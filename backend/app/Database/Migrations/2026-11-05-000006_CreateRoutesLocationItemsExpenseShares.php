<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * routes         : "Route 1: sáng ngày một" trong màn hình plan (ER: ROUTE)
 * location_items : địa điểm trong route, có "Mốc", chi phí dự tính (mỗi người) và thực tế (cả nhóm) (ER: LOCATION_ITEM)
 * expense_shares : bảng chi phí thực tế thành viên x địa điểm với các trạng thái
 *                  Bao / Ứng trước / Đã đóng / Thiếu / Miễn phí / Được bao / Chưa đóng (ER: EXPENSE_SHARE)
 *
 * `plan_locations`, `itinerary_activities`, `expenses`, `expense_allocations` (bản làm trước đó)
 * được giữ nguyên, không xóa; xem README-database.md.
 */
class CreateRoutesLocationItemsExpenseShares extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                     => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'plan_id'                => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'name'                   => ['type' => 'varchar', 'constraint' => 255],
            'day_number'             => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true, 'null' => true], // ngày thứ mấy của chuyến đi
            'order_index'            => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
            'total_distance'         => ['type' => 'decimal', 'constraint' => '8,2', 'unsigned' => true, 'default' => 0], // km
            'total_duration_minutes' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'default' => 0],
            'created_at'             => ['type' => 'datetime', 'null' => true],
            'updated_at'             => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['plan_id', 'order_index'], false, false, 'idx_routes_plan_order');
        $this->forge->addForeignKey('plan_id', 'plans', 'id', 'CASCADE', 'CASCADE', 'fk_routes_plan');
        $this->forge->createTable('routes', false, ['ENGINE' => 'InnoDB']);
        $this->db->query('ALTER TABLE routes ADD CONSTRAINT chk_routes_day CHECK (day_number IS NULL OR day_number > 0)');

        $this->forge->addField([
            'id'                       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'route_id'                 => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'location_id'              => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'category'                 => ['type' => 'varchar', 'constraint' => 16],
            'order_index'              => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
            'is_anchor'                => ['type' => 'tinyint', 'constraint' => 1, 'default' => 0], // "Mốc"; mỗi route tối đa 1 (ứng dụng kiểm soát)
            'estimated_cost'           => ['type' => 'bigint', 'constraint' => 20, 'unsigned' => true, 'default' => 0],     // VND / người
            'actual_cost'              => ['type' => 'bigint', 'constraint' => 20, 'unsigned' => true, 'null' => true],     // VND cả nhóm
            'scheduled_time'           => ['type' => 'datetime', 'null' => true],
            'note'                     => ['type' => 'text', 'null' => true],
            'travel_mode'              => ['type' => 'varchar', 'constraint' => 16, 'default' => 'motorbike'],
            'distance_from_anchor'     => ['type' => 'decimal', 'constraint' => '8,2', 'unsigned' => true, 'null' => true], // km, "2 km tính từ mốc"
            'duration_from_anchor_minutes' => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true],     // "5 phút đi xe máy"
            'created_at'               => ['type' => 'datetime', 'null' => true],
            'updated_at'               => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['route_id', 'order_index'], false, false, 'idx_location_items_route_order');
        $this->forge->addKey('location_id');
        $this->forge->addForeignKey('route_id', 'routes', 'id', 'CASCADE', 'CASCADE', 'fk_location_items_route');
        $this->forge->addForeignKey('location_id', 'locations', 'id', 'CASCADE', 'RESTRICT', 'fk_location_items_location');
        $this->forge->createTable('location_items', false, ['ENGINE' => 'InnoDB']);
        $this->db->query(
            'ALTER TABLE location_items
             ADD CONSTRAINT chk_location_items_category CHECK (
                 category IN (\'di_chuyen\', \'noi_o\', \'an_uong\', \'ca_phe\', \'vui_choi\', \'mua_sam\')
             ),
             ADD CONSTRAINT chk_location_items_travel_mode CHECK (travel_mode IN (\'motorbike\', \'car\', \'walking\'))'
        );

        $this->forge->addField([
            'id'                          => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'location_item_id'            => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'plan_member_id'              => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'amount_owed'                 => ['type' => 'bigint', 'constraint' => 20, 'unsigned' => true, 'default' => 0],
            'amount_paid'                 => ['type' => 'bigint', 'constraint' => 20, 'unsigned' => true, 'default' => 0],
            'is_paid'                     => ['type' => 'tinyint', 'constraint' => 1, 'default' => 0],
            // Trạng thái hiển thị trong ô bảng chi phí, trưởng nhóm bấm để đổi.
            'payment_status'              => ['type' => 'varchar', 'constraint' => 16, 'default' => 'unpaid'],
            'covered_by_plan_member_id'   => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true], // "Được bao" bởi ai
            'paid_at'                     => ['type' => 'datetime', 'null' => true],
            'created_at'                  => ['type' => 'datetime', 'null' => true],
            'updated_at'                  => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['location_item_id', 'plan_member_id'], 'uq_expense_shares_item_member');
        $this->forge->addKey('plan_member_id');
        $this->forge->addKey('covered_by_plan_member_id');
        $this->forge->addForeignKey('location_item_id', 'location_items', 'id', 'CASCADE', 'CASCADE', 'fk_expense_shares_item');
        $this->forge->addForeignKey('plan_member_id', 'plan_members', 'id', 'CASCADE', 'CASCADE', 'fk_expense_shares_member');
        $this->forge->addForeignKey('covered_by_plan_member_id', 'plan_members', 'id', 'CASCADE', 'SET NULL', 'fk_expense_shares_covered_by');
        $this->forge->createTable('expense_shares', false, ['ENGINE' => 'InnoDB']);
        $this->db->query(
            'ALTER TABLE expense_shares
             ADD CONSTRAINT chk_expense_shares_status CHECK (
                 payment_status IN (\'unpaid\', \'paid\', \'partial\', \'pays_for_all\', \'advanced\', \'covered\', \'free\')
             )'
        );
    }

    public function down(): void
    {
        $this->forge->dropTable('expense_shares');
        $this->forge->dropTable('location_items');
        $this->forge->dropTable('routes');
    }
}
