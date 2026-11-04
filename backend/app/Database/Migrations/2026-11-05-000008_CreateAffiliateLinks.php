<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Link đối tác: "Xem giá phòng/So sánh giá" ở trang địa điểm và "Mua ngay" ở mini game.
 *
 * Quy tắc "đúng một trong location_id / mini_game_id" do ứng dụng kiểm soát: MySQL không cho đặt CHECK
 * lên cột đang có FK với ON DELETE CASCADE.
 */
class CreateAffiliateLinks extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'location_id'  => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'mini_game_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'provider'     => ['type' => 'varchar', 'constraint' => 50],
            'link_type'    => ['type' => 'varchar', 'constraint' => 16],
            'title'        => ['type' => 'varchar', 'constraint' => 255],
            'image_url'    => ['type' => 'varchar', 'constraint' => 500, 'null' => true],
            'price'        => ['type' => 'bigint', 'constraint' => 20, 'unsigned' => true, 'null' => true], // VND
            'url'          => ['type' => 'varchar', 'constraint' => 1000],
            'active'       => ['type' => 'tinyint', 'constraint' => 1, 'default' => 1],
            'created_at'   => ['type' => 'datetime', 'null' => true],
            'updated_at'   => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['location_id', 'active'], false, false, 'idx_affiliate_links_location');
        $this->forge->addKey(['mini_game_id', 'active'], false, false, 'idx_affiliate_links_mini_game');
        $this->forge->addForeignKey('location_id', 'locations', 'id', 'CASCADE', 'CASCADE', 'fk_affiliate_links_location');
        $this->forge->addForeignKey('mini_game_id', 'mini_games', 'id', 'CASCADE', 'CASCADE', 'fk_affiliate_links_mini_game');
        $this->forge->createTable('affiliate_links', false, ['ENGINE' => 'InnoDB']);
        $this->db->query(
            'ALTER TABLE affiliate_links
             ADD CONSTRAINT chk_affiliate_links_type CHECK (link_type IN (\'hotel\', \'ticket\', \'transport\', \'product\'))'
        );
    }

    public function down(): void
    {
        $this->forge->dropTable('affiliate_links');
    }
}
