<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdminAuditEvents extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                  => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'actor_admin_user_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'action'              => ['type' => 'varchar', 'constraint' => 16],
            'target_type'         => ['type' => 'varchar', 'constraint' => 16],
            'target_id'           => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'occurred_at'         => ['type' => 'datetime'],
            'metadata'            => ['type' => 'json', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['actor_admin_user_id', 'occurred_at']);
        $this->forge->addKey(['target_type', 'target_id']);
        $this->forge->addKey('occurred_at');
        $this->forge->addForeignKey(
            'actor_admin_user_id',
            'users',
            'id',
            'RESTRICT',
            'RESTRICT',
            'fk_admin_audit_events_actor'
        );
        $this->forge->createTable('admin_audit_events', false, ['ENGINE' => 'InnoDB']);
        $this->db->query(
            'ALTER TABLE admin_audit_events
             ADD CONSTRAINT chk_admin_audit_events_action
                 CHECK (action IN (\'create\', \'update\', \'delete\')),
             ADD CONSTRAINT chk_admin_audit_events_target
                 CHECK (target_type IN (\'plan\', \'expense\'))'
        );
    }

    public function down(): void
    {
        $this->forge->dropTable('admin_audit_events');
    }
}
