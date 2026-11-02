<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRemainingBusinessTables extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('plans', [
            'itinerary_sort_mode' => [
                'type'       => 'varchar',
                'constraint' => 16,
                'default'    => 'chronological',
            ],
        ]);
        $this->db->query(
            'ALTER TABLE plans
             ADD CONSTRAINT chk_plans_itinerary_sort_mode
             CHECK (itinerary_sort_mode IN (\'chronological\', \'manual\'))'
        );

        $this->db->query('ALTER TABLE plan_locations ADD UNIQUE KEY uq_plan_locations_plan_id_id (plan_id, id)');

        $this->forge->addField([
            'id'               => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'plan_id'          => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'activity_date'    => ['type' => 'date'],
            'name'             => ['type' => 'varchar', 'constraint' => 255],
            'start_time'       => ['type' => 'time', 'null' => true],
            'end_time'         => ['type' => 'time', 'null' => true],
            'location_name'    => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'instructions'     => ['type' => 'text', 'null' => true],
            'notes'            => ['type' => 'text', 'null' => true],
            'manual_order'     => ['type' => 'int', 'constraint' => 10, 'unsigned' => true],
            'created_at'       => ['type' => 'datetime', 'null' => true],
            'updated_at'       => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['plan_id', 'activity_date', 'start_time', 'id']);
        $this->forge->addKey(['plan_id', 'activity_date', 'manual_order']);
        $this->forge->addForeignKey(
            'plan_id',
            'plans',
            'id',
            'CASCADE',
            'RESTRICT',
            'fk_itinerary_activities_plan'
        );
        $this->forge->createTable('itinerary_activities', false, ['ENGINE' => 'InnoDB']);
        $this->db->query(
            'ALTER TABLE itinerary_activities
             ADD CONSTRAINT chk_itinerary_activity_time_order CHECK (
                 start_time IS NULL OR end_time IS NULL OR end_time > start_time
             )'
        );

        $this->forge->addField([
            'id'               => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'plan_id'          => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'payer_user_id'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'plan_location_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'amount_vnd'       => ['type' => 'bigint', 'constraint' => 20, 'unsigned' => true],
            'expense_date'     => ['type' => 'date'],
            'note'             => ['type' => 'text', 'null' => true],
            'category_label'   => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'split_method'     => ['type' => 'varchar', 'constraint' => 16],
            'created_at'       => ['type' => 'datetime', 'null' => true],
            'updated_at'       => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['id', 'plan_id'], 'uq_expenses_id_plan_id');
        $this->forge->addKey(['plan_id', 'expense_date']);
        $this->forge->addKey(['plan_id', 'category_label']);
        $this->forge->addKey(['plan_id', 'payer_user_id', 'expense_date', 'amount_vnd']);
        $this->forge->addForeignKey('plan_id', 'plans', 'id', 'CASCADE', 'RESTRICT', 'fk_expenses_plan');
        $this->forge->addForeignKey(
            ['plan_id', 'payer_user_id'],
            'plan_members',
            ['plan_id', 'user_id'],
            '',
            'RESTRICT',
            'fk_expenses_current_payer'
        );
        $this->forge->addForeignKey(
            ['plan_id', 'plan_location_id'],
            'plan_locations',
            ['plan_id', 'id'],
            '',
            'RESTRICT',
            'fk_expenses_plan_location'
        );
        $this->forge->createTable('expenses', false, ['ENGINE' => 'InnoDB']);
        $this->db->query(
            'ALTER TABLE expenses
             ADD CONSTRAINT chk_expenses_amount_positive CHECK (amount_vnd > 0),
             ADD CONSTRAINT chk_expenses_split_method CHECK (split_method IN (\'equal\', \'custom\'))'
        );

        $this->db->query('ALTER TABLE plan_members ADD UNIQUE KEY uq_plan_members_plan_id_id (plan_id, id)');

        $this->forge->addField([
            'id'              => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'expense_id'      => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'plan_id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'plan_member_id'  => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'amount_vnd'      => ['type' => 'bigint', 'constraint' => 20, 'unsigned' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['expense_id', 'plan_member_id'], 'uq_expense_allocations_expense_member');
        $this->forge->addKey(['plan_id', 'plan_member_id']);
        $this->forge->addForeignKey(
            ['expense_id', 'plan_id'],
            'expenses',
            ['id', 'plan_id'],
            'CASCADE',
            'RESTRICT',
            'fk_expense_allocations_expense'
        );
        $this->forge->addForeignKey(
            ['plan_id', 'plan_member_id'],
            'plan_members',
            ['plan_id', 'id'],
            '',
            'RESTRICT',
            'fk_expense_allocations_member'
        );
        $this->forge->createTable('expense_allocations', false, ['ENGINE' => 'InnoDB']);
        $this->db->query(
            'ALTER TABLE expense_allocations
             ADD CONSTRAINT chk_expense_allocations_amount_positive CHECK (amount_vnd > 0)'
        );

        $this->forge->addField([
            'id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'       => ['type' => 'varchar', 'constraint' => 100],
            'is_active'  => ['type' => 'tinyint', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'datetime', 'null' => true],
            'updated_at' => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('name', 'uq_minigame_genres_name');
        $this->forge->createTable('minigame_genres', false, ['ENGINE' => 'InnoDB']);

        $this->forge->addField([
            'id'                       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'author_user_id'           => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'title'                    => ['type' => 'varchar', 'constraint' => 255],
            'rules'                    => ['type' => 'text'],
            'description'              => ['type' => 'text', 'null' => true],
            'min_players'              => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'max_players'              => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'duration_minutes'         => ['type' => 'int', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'equipment'               => ['type' => 'text', 'null' => true],
            'cover_image_path'        => ['type' => 'varchar', 'constraint' => 500, 'null' => true],
            'status'                  => ['type' => 'varchar', 'constraint' => 16, 'default' => 'pending'],
            'moderated_by_user_id'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'moderated_at'            => ['type' => 'datetime', 'null' => true],
            'published_at'            => ['type' => 'datetime', 'null' => true],
            'deleted_by_user_id'      => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'deleted_at'              => ['type' => 'datetime', 'null' => true],
            'created_at'              => ['type' => 'datetime', 'null' => true],
            'updated_at'              => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['status', 'published_at']);
        $this->forge->addKey(['min_players', 'max_players']);
        $this->forge->addKey('duration_minutes');
        $this->forge->addKey('author_user_id');
        $this->forge->addForeignKey(
            'author_user_id',
            'users',
            'id',
            'CASCADE',
            'RESTRICT',
            'fk_minigame_posts_author'
        );
        $this->forge->addForeignKey(
            'moderated_by_user_id',
            'users',
            'id',
            'CASCADE',
            'RESTRICT',
            'fk_minigame_posts_moderator'
        );
        $this->forge->addForeignKey(
            'deleted_by_user_id',
            'users',
            'id',
            'CASCADE',
            'RESTRICT',
            'fk_minigame_posts_deleted_by'
        );
        $this->forge->createTable('minigame_posts', false, ['ENGINE' => 'InnoDB']);
        $this->db->query(
            'ALTER TABLE minigame_posts
             ADD CONSTRAINT chk_minigame_posts_status
                 CHECK (status IN (\'pending\', \'approved\', \'hidden\', \'deleted\')),
             ADD CONSTRAINT chk_minigame_posts_player_range CHECK (
                 (min_players IS NULL OR min_players > 0)
                 AND (max_players IS NULL OR max_players > 0)
                 AND (min_players IS NULL OR max_players IS NULL OR max_players >= min_players)
             ),
             ADD CONSTRAINT chk_minigame_posts_duration
                 CHECK (duration_minutes IS NULL OR duration_minutes > 0),
             ADD CONSTRAINT chk_minigame_posts_deleted_state CHECK (
                 (status = \'deleted\' AND deleted_at IS NOT NULL)
                 OR (status <> \'deleted\' AND deleted_at IS NULL)
             )'
        );

        $this->forge->addField([
            'id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'post_id'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'genre_id'   => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['post_id', 'genre_id'], 'uq_minigame_post_genres_post_genre');
        $this->forge->addKey('genre_id');
        $this->forge->addForeignKey(
            'post_id',
            'minigame_posts',
            'id',
            'CASCADE',
            'CASCADE',
            'fk_minigame_post_genres_post'
        );
        $this->forge->addForeignKey(
            'genre_id',
            'minigame_genres',
            'id',
            'CASCADE',
            'RESTRICT',
            'fk_minigame_post_genres_genre'
        );
        $this->forge->createTable('minigame_post_genres', false, ['ENGINE' => 'InnoDB']);

        $this->forge->addField([
            'id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'post_id'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'tag'        => ['type' => 'varchar', 'constraint' => 100],
            'created_at' => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['post_id', 'tag'], 'uq_minigame_post_tags_post_tag');
        $this->forge->addForeignKey(
            'post_id',
            'minigame_posts',
            'id',
            'CASCADE',
            'CASCADE',
            'fk_minigame_post_tags_post'
        );
        $this->forge->createTable('minigame_post_tags', false, ['ENGINE' => 'InnoDB']);

        $this->createPostInteractionTable('minigame_post_likes', 'fk_minigame_post_likes_post', 'fk_minigame_post_likes_user');
        $this->createPostInteractionTable(
            'minigame_post_favorites',
            'fk_minigame_post_favorites_post',
            'fk_minigame_post_favorites_user'
        );

        $this->forge->addField([
            'id'              => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'post_id'          => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'author_user_id'   => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'content'          => ['type' => 'text'],
            'created_at'       => ['type' => 'datetime', 'null' => true],
            'updated_at'       => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['post_id', 'created_at', 'id']);
        $this->forge->addKey('author_user_id');
        $this->forge->addForeignKey(
            'post_id',
            'minigame_posts',
            'id',
            'CASCADE',
            'CASCADE',
            'fk_minigame_post_comments_post'
        );
        $this->forge->addForeignKey(
            'author_user_id',
            'users',
            'id',
            'CASCADE',
            'RESTRICT',
            'fk_minigame_post_comments_author'
        );
        $this->forge->createTable('minigame_post_comments', false, ['ENGINE' => 'InnoDB']);

        $this->forge->addField([
            'id'                 => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'post_id'            => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'reporter_user_id'   => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'reason'             => ['type' => 'text'],
            'status'             => ['type' => 'varchar', 'constraint' => 16, 'default' => 'pending'],
            'resolution'         => ['type' => 'varchar', 'constraint' => 16, 'null' => true],
            'reviewed_by_user_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'reviewed_at'        => ['type' => 'datetime', 'null' => true],
            'created_at'         => ['type' => 'datetime', 'null' => true],
            'updated_at'         => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['post_id', 'reporter_user_id'], 'uq_minigame_post_reports_post_reporter');
        $this->forge->addKey(['status', 'created_at']);
        $this->forge->addForeignKey(
            'post_id',
            'minigame_posts',
            'id',
            '',
            'RESTRICT',
            'fk_minigame_post_reports_post'
        );
        $this->forge->addForeignKey(
            'reporter_user_id',
            'users',
            'id',
            'CASCADE',
            'RESTRICT',
            'fk_minigame_post_reports_reporter'
        );
        $this->forge->addForeignKey(
            'reviewed_by_user_id',
            'users',
            'id',
            '',
            'RESTRICT',
            'fk_minigame_post_reports_reviewer'
        );
        $this->forge->createTable('minigame_post_reports', false, ['ENGINE' => 'InnoDB']);
        $this->db->query(
            'ALTER TABLE minigame_post_reports
             ADD CONSTRAINT chk_minigame_post_reports_status
                 CHECK (status IN (\'pending\', \'resolved\')),
             ADD CONSTRAINT chk_minigame_post_reports_resolution
                 CHECK (resolution IS NULL OR resolution IN (\'keep\', \'hide\', \'delete\')),
             ADD CONSTRAINT chk_minigame_post_reports_review_state CHECK (
                 (status = \'pending\' AND resolution IS NULL AND reviewed_by_user_id IS NULL AND reviewed_at IS NULL)
                 OR
                 (status = \'resolved\' AND resolution IS NOT NULL AND reviewed_by_user_id IS NOT NULL AND reviewed_at IS NOT NULL)
             )'
        );
    }

    public function down(): void
    {
        $this->forge->dropTable('minigame_post_reports');
        $this->forge->dropTable('minigame_post_comments');
        $this->forge->dropTable('minigame_post_favorites');
        $this->forge->dropTable('minigame_post_likes');
        $this->forge->dropTable('minigame_post_tags');
        $this->forge->dropTable('minigame_post_genres');
        $this->forge->dropTable('minigame_posts');
        $this->forge->dropTable('minigame_genres');
        $this->forge->dropTable('expense_allocations');
        $this->forge->dropTable('expenses');
        $this->forge->dropTable('itinerary_activities');
        $this->db->query('ALTER TABLE plan_members DROP INDEX uq_plan_members_plan_id_id');
        $this->db->query('ALTER TABLE plan_locations DROP INDEX uq_plan_locations_plan_id_id');
        $this->db->query('ALTER TABLE plans DROP CHECK chk_plans_itinerary_sort_mode');
        $this->forge->dropColumn('plans', 'itinerary_sort_mode');
    }

    private function createPostInteractionTable(string $table, string $postForeignKey, string $userForeignKey): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'post_id'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'user_id'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['post_id', 'user_id'], 'uq_' . $table . '_post_user');
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('post_id', 'minigame_posts', 'id', 'CASCADE', 'CASCADE', $postForeignKey);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'RESTRICT', $userForeignKey);
        $this->forge->createTable($table, false, ['ENGINE' => 'InnoDB']);
    }
}
