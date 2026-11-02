<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePlanFoundation extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'owner_user_id'              => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'title'                      => ['type' => 'varchar', 'constraint' => 255],
            'description'                => ['type' => 'text', 'null' => true],
            'start_date'                 => ['type' => 'date'],
            'end_date'                   => ['type' => 'date'],
            'destination_source_kind'    => ['type' => 'varchar', 'constraint' => 16],
            'destination_source_key'     => ['type' => 'varchar', 'constraint' => 100, 'null' => true],
            'destination_source_reference' => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'destination_name'           => ['type' => 'varchar', 'constraint' => 255],
            'destination_address'        => ['type' => 'varchar', 'constraint' => 500, 'null' => true],
            'destination_latitude'       => ['type' => 'decimal', 'constraint' => '10,7', 'null' => true],
            'destination_longitude'      => ['type' => 'decimal', 'constraint' => '10,7', 'null' => true],
            'created_at'                 => ['type' => 'datetime', 'null' => true],
            'updated_at'                 => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('owner_user_id');
        $this->forge->addForeignKey(
            'owner_user_id',
            'users',
            'id',
            'CASCADE',
            'RESTRICT',
            'fk_plans_owner_user'
        );
        $this->forge->createTable('plans', false, ['ENGINE' => 'InnoDB']);

        $this->db->query(
            'ALTER TABLE plans
             ADD CONSTRAINT chk_plans_date_range CHECK (end_date >= start_date),
             ADD CONSTRAINT chk_plans_destination_source CHECK (
                 (destination_source_kind = \'manual\'
                     AND destination_source_key IS NULL
                     AND destination_source_reference IS NULL)
                 OR
                 (destination_source_kind = \'provider\'
                     AND destination_source_key IS NOT NULL
                     AND destination_source_reference IS NOT NULL)
             ),
             ADD CONSTRAINT chk_plans_destination_latitude CHECK (
                 destination_latitude IS NULL
                 OR destination_latitude BETWEEN -90 AND 90
             ),
             ADD CONSTRAINT chk_plans_destination_longitude CHECK (
                 destination_longitude IS NULL
                 OR destination_longitude BETWEEN -180 AND 180
             )'
        );

        $this->forge->addField([
            'id'              => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'plan_id'          => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'user_id'          => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'added_by_user_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'joined_at'        => ['type' => 'datetime'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['plan_id', 'user_id'], 'uq_plan_members_plan_user');
        $this->forge->addKey(['plan_id', 'joined_at', 'id']);
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('plan_id', 'plans', 'id', 'CASCADE', 'CASCADE', 'fk_plan_members_plan');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'RESTRICT', 'fk_plan_members_user');
        $this->forge->addForeignKey(
            'added_by_user_id',
            'users',
            'id',
            'CASCADE',
            'RESTRICT',
            'fk_plan_members_added_by'
        );
        $this->forge->createTable('plan_members', false, ['ENGINE' => 'InnoDB']);

        $this->forge->addField([
            'id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'plan_id'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'name'       => ['type' => 'varchar', 'constraint' => 255],
            'created_at' => ['type' => 'datetime', 'null' => true],
            'updated_at' => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('plan_id');
        $this->forge->addForeignKey('plan_id', 'plans', 'id', 'CASCADE', 'CASCADE', 'fk_plan_locations_plan');
        $this->forge->createTable('plan_locations', false, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('plan_locations');
        $this->forge->dropTable('plan_members');
        $this->forge->dropTable('plans');
    }
}
