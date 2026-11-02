<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RestrictBusinessUserDeletion extends Migration
{
    private const USER_FOREIGN_KEYS = [
        ['plans', 'owner_user_id', 'fk_plans_owner_user'],
        ['plan_members', 'user_id', 'fk_plan_members_user'],
        ['plan_members', 'added_by_user_id', 'fk_plan_members_added_by'],
        ['minigame_posts', 'author_user_id', 'fk_minigame_posts_author'],
        ['minigame_posts', 'moderated_by_user_id', 'fk_minigame_posts_moderator'],
        ['minigame_posts', 'deleted_by_user_id', 'fk_minigame_posts_deleted_by'],
        ['minigame_post_comments', 'author_user_id', 'fk_minigame_post_comments_author'],
        ['minigame_post_likes', 'user_id', 'fk_minigame_post_likes_user'],
        ['minigame_post_favorites', 'user_id', 'fk_minigame_post_favorites_user'],
        ['minigame_post_reports', 'reporter_user_id', 'fk_minigame_post_reports_reporter'],
    ];

    public function up(): void
    {
        foreach (self::USER_FOREIGN_KEYS as [$table, $column, $constraint]) {
            $this->replaceDeleteRule($table, $column, $constraint, 'RESTRICT');
        }
    }

    public function down(): void
    {
        foreach (self::USER_FOREIGN_KEYS as [$table, $column, $constraint]) {
            $this->replaceDeleteRule($table, $column, $constraint, 'CASCADE');
        }
    }

    private function replaceDeleteRule(
        string $table,
        string $column,
        string $constraint,
        string $onDelete
    ): void     {
        $existing = $this->db->query(
            'SELECT COUNT(*) AS found FROM information_schema.table_constraints
             WHERE constraint_schema = DATABASE() AND table_name = ? AND constraint_name = ?
               AND constraint_type = \'FOREIGN KEY\'',
            [$table, $constraint]
        )->getRowArray();

        if ((int) $existing['found'] > 0) {
            $this->db->query('ALTER TABLE `' . $table . '` DROP FOREIGN KEY `' . $constraint . '`');
        }

        $this->db->query(
            'ALTER TABLE `' . $table . '` ADD CONSTRAINT `' . $constraint . '` FOREIGN KEY (`' . $column . '`)
             REFERENCES `users` (`id`) ON DELETE ' . $onDelete . ' ON UPDATE RESTRICT'
        );
    }
}
