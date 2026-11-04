<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** plan_likes, plan_saves, plan_comments, plan_shares (ER) */
class CreatePlanSocialTables extends Migration
{
    public function up(): void
    {
        foreach (['plan_likes', 'plan_saves'] as $table) {
            $this->forge->addField([
                'id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'user_id'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
                'plan_id'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
                'created_at' => ['type' => 'datetime', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['user_id', 'plan_id'], 'uq_' . $table . '_user_plan');
            $this->forge->addKey(['plan_id', 'created_at'], false, false, 'idx_' . $table . '_plan');
            $this->forge->addForeignKey('user_id', 'users', 'id', 'RESTRICT', 'RESTRICT', 'fk_' . $table . '_user');
            $this->forge->addForeignKey('plan_id', 'plans', 'id', 'CASCADE', 'CASCADE', 'fk_' . $table . '_plan');
            $this->forge->createTable($table, false, ['ENGINE' => 'InnoDB']);
        }

        $this->forge->addField([
            'id'                => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'plan_id'           => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'user_id'           => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'parent_comment_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'content'           => ['type' => 'text'],
            'status'            => ['type' => 'varchar', 'constraint' => 16, 'default' => 'visible'],
            'created_at'        => ['type' => 'datetime', 'null' => true],
            'updated_at'        => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        // Cho phép FK ghép (parent_comment_id, plan_id) để trả lời luôn thuộc cùng plan với bình luận gốc.
        $this->forge->addUniqueKey(['id', 'plan_id'], 'uq_plan_comments_id_plan');
        $this->forge->addKey(['plan_id', 'parent_comment_id', 'created_at'], false, false, 'idx_plan_comments_thread');
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('plan_id', 'plans', 'id', 'CASCADE', 'CASCADE', 'fk_plan_comments_plan');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'RESTRICT', 'RESTRICT', 'fk_plan_comments_user');
        $this->forge->addForeignKey(
            ['parent_comment_id', 'plan_id'],
            'plan_comments',
            ['id', 'plan_id'],
            'CASCADE',
            'CASCADE',
            'fk_plan_comments_parent'
        );
        $this->forge->createTable('plan_comments', false, ['ENGINE' => 'InnoDB']);
        $this->db->query(
            'ALTER TABLE plan_comments
             ADD CONSTRAINT chk_plan_comments_status CHECK (status IN (\'visible\', \'hidden\', \'deleted\'))'
        );

        $this->forge->addField([
            'id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'plan_id'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'user_id'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true], // NULL = khách
            'channel'    => ['type' => 'varchar', 'constraint' => 16],
            'created_at' => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['plan_id', 'created_at'], false, false, 'idx_plan_shares_plan');
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('plan_id', 'plans', 'id', 'CASCADE', 'CASCADE', 'fk_plan_shares_plan');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'RESTRICT', 'RESTRICT', 'fk_plan_shares_user');
        $this->forge->createTable('plan_shares', false, ['ENGINE' => 'InnoDB']);
        $this->db->query(
            'ALTER TABLE plan_shares
             ADD CONSTRAINT chk_plan_shares_channel CHECK (channel IN (\'copy_link\', \'facebook\', \'zalo\', \'other\'))'
        );
    }

    public function down(): void
    {
        $this->forge->dropTable('plan_shares');
        $this->forge->dropTable('plan_comments');
        $this->forge->dropTable('plan_saves');
        $this->forge->dropTable('plan_likes');
    }
}
