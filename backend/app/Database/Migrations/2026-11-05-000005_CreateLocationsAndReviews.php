<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * locations        : cache địa điểm lấy từ nhà cung cấp bản đồ (ER: LOCATION)
 * location_reviews : đánh giá địa điểm, mỗi người 1 đánh giá/địa điểm, có sửa lại (ER: LOCATION_REVIEW)
 */
class CreateLocationsAndReviews extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'               => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'provider'         => ['type' => 'varchar', 'constraint' => 16],
            'provider_place_id' => ['type' => 'varchar', 'constraint' => 191],
            'name'             => ['type' => 'varchar', 'constraint' => 255],
            'address'          => ['type' => 'varchar', 'constraint' => 500, 'null' => true],
            'description'      => ['type' => 'text', 'null' => true],
            'lat'              => ['type' => 'decimal', 'constraint' => '10,7', 'null' => true],
            'lng'              => ['type' => 'decimal', 'constraint' => '10,7', 'null' => true],
            'coords_cached_at' => ['type' => 'datetime', 'null' => true],
            // Điểm đánh giá của cộng đồng Cùng Đi (không phải của nhà cung cấp); ứng dụng cập nhật khi có review.
            'rating_avg'       => ['type' => 'decimal', 'constraint' => '3,2', 'unsigned' => true, 'default' => 0],
            'rating_count'     => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'default' => 0],
            'created_at'       => ['type' => 'datetime', 'null' => true],
            'updated_at'       => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['provider', 'provider_place_id'], 'uq_locations_provider_place');
        $this->forge->addKey('name');
        $this->forge->createTable('locations', false, ['ENGINE' => 'InnoDB']);
        $this->db->query(
            'ALTER TABLE locations
             ADD CONSTRAINT chk_locations_lat CHECK (lat IS NULL OR lat BETWEEN -90 AND 90),
             ADD CONSTRAINT chk_locations_lng CHECK (lng IS NULL OR lng BETWEEN -180 AND 180),
             ADD CONSTRAINT chk_locations_rating CHECK (rating_avg BETWEEN 0 AND 5)'
        );

        $this->forge->addField([
            'id'          => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'     => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'location_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'rating'      => ['type' => 'tinyint', 'constraint' => 3, 'unsigned' => true],
            'comment'     => ['type' => 'text', 'null' => true],
            'photo_url'   => ['type' => 'varchar', 'constraint' => 500, 'null' => true], // tối đa 1 ảnh/review
            'status'      => ['type' => 'varchar', 'constraint' => 16, 'default' => 'visible'],
            'created_at'  => ['type' => 'datetime', 'null' => true],
            'updated_at'  => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'location_id'], 'uq_location_reviews_user_location');
        $this->forge->addKey(['location_id', 'status', 'created_at'], false, false, 'idx_location_reviews_list');
        $this->forge->addKey(['location_id', 'rating'], false, false, 'idx_location_reviews_rating');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'RESTRICT', 'RESTRICT', 'fk_location_reviews_user');
        $this->forge->addForeignKey('location_id', 'locations', 'id', 'CASCADE', 'CASCADE', 'fk_location_reviews_location');
        $this->forge->createTable('location_reviews', false, ['ENGINE' => 'InnoDB']);
        $this->db->query(
            'ALTER TABLE location_reviews
             ADD CONSTRAINT chk_location_reviews_rating CHECK (rating BETWEEN 1 AND 5),
             ADD CONSTRAINT chk_location_reviews_status CHECK (status IN (\'visible\', \'hidden\', \'deleted\'))'
        );
    }

    public function down(): void
    {
        $this->forge->dropTable('location_reviews');
        $this->forge->dropTable('locations');
    }
}
