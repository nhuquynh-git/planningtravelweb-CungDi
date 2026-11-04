<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Danh mục "Gợi ý mini game" do quản trị viên quản lý (ER: MINI_GAME, MINI_GAME_TAG).
 * UI chi tiết có luật chơi theo từng bước, mẹo chơi và bảng gợi ý chia vai nên bổ sung
 * mini_game_rule_steps + 2 cột JSON (tips, role_suggestions).
 *
 * Các bảng `minigame_posts`, `minigame_genres`... (bài đăng do người dùng gửi) không có trong ER lẫn UI,
 * được giữ nguyên, không xóa.
 */
class CreateMiniGameCatalog extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'               => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'             => ['type' => 'varchar', 'constraint' => 255],
            'description'      => ['type' => 'text', 'null' => true],
            'min_players'      => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
            'max_players'      => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
            'min_minutes'      => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
            'max_minutes'      => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
            'cover_url'        => ['type' => 'varchar', 'constraint' => 500, 'null' => true],
            // JSON: ["Người đã bị loại không được nói...", ...]
            'tips'             => ['type' => 'json', 'null' => true],
            // JSON: {"columns":["Sói","Tiên tri","Bảo vệ","Dân"],"rows":[{"players":5,"values":[1,1,0,3]}]}
            'role_suggestions' => ['type' => 'json', 'null' => true],
            'is_published'     => ['type' => 'tinyint', 'constraint' => 1, 'default' => 1],
            'created_at'       => ['type' => 'datetime', 'null' => true],
            'updated_at'       => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['is_published', 'min_players', 'max_players'], false, false, 'idx_mini_games_filter');
        $this->forge->createTable('mini_games', false, ['ENGINE' => 'InnoDB']);
        $this->db->query(
            'ALTER TABLE mini_games
             ADD CONSTRAINT chk_mini_games_players CHECK (min_players > 0 AND max_players >= min_players),
             ADD CONSTRAINT chk_mini_games_minutes CHECK (min_minutes > 0 AND max_minutes >= min_minutes)'
        );

        $this->forge->addField([
            'id'           => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'mini_game_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'tag_type'     => ['type' => 'varchar', 'constraint' => 16],
            'value'        => ['type' => 'varchar', 'constraint' => 16],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['mini_game_id', 'tag_type', 'value'], 'uq_mini_game_tags_game_tag_value');
        $this->forge->addKey(['tag_type', 'value'], false, false, 'idx_mini_game_tags_filter');
        $this->forge->addForeignKey('mini_game_id', 'mini_games', 'id', 'CASCADE', 'CASCADE', 'fk_mini_game_tags_game');
        $this->forge->createTable('mini_game_tags', false, ['ENGINE' => 'InnoDB']);
        $this->db->query(
            'ALTER TABLE mini_game_tags
             ADD CONSTRAINT chk_mini_game_tags_value CHECK (
                 (tag_type = \'equipment\' AND value IN (\'none\', \'paper\', \'cards\', \'phone\', \'ball\'))
                 OR (tag_type = \'setting\' AND value IN (\'car\', \'table\', \'room\', \'outdoor\'))
             )'
        );

        $this->forge->addField([
            'id'           => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'mini_game_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'order_index'  => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
            'title'        => ['type' => 'varchar', 'constraint' => 255],
            'content'      => ['type' => 'text'], // mỗi dòng là một gạch đầu dòng
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['mini_game_id', 'order_index'], false, false, 'idx_mini_game_rule_steps_order');
        $this->forge->addForeignKey('mini_game_id', 'mini_games', 'id', 'CASCADE', 'CASCADE', 'fk_mini_game_rule_steps_game');
        $this->forge->createTable('mini_game_rule_steps', false, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('mini_game_rule_steps');
        $this->forge->dropTable('mini_game_tags');
        $this->forge->dropTable('mini_games');
    }
}
