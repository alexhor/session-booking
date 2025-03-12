<?php

namespace Tests\Support\Database\Migrations;

require_once(HOMEPATH . 'app/Database/Migrations/2025-01-26-125907_User.php');
use App\Database\Migrations\UserMigration;


class TestUserMigration extends UserMigration
{
    protected $DBGroup = 'tests';

    public function down() {
        $this->db->transStart();
        $this->db->query('CREATE TEMPORARY TABLE users_backup(id, username, status, status_message, active, last_active, created_at, updated_at, deleted_at);');
        $this->db->query('INSERT INTO users_backup SELECT id, username, status, status_message, active, last_active, created_at, updated_at, deleted_at FROM users;');
        $this->db->query('DROP TABLE users;');
        
        $this->forge->addField([
            'id'             => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'username'       => ['type' => 'varchar', 'constraint' => 30, 'null' => true],
            'status'         => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'status_message' => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'active'         => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            'last_active'    => ['type' => 'datetime', 'null' => true],
            'created_at'     => ['type' => 'datetime', 'null' => true],
            'updated_at'     => ['type' => 'datetime', 'null' => true],
            'deleted_at'     => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('username');
        $this->forge->createTable($this->tables['users']);

        $this->db->query('INSERT INTO users SELECT id, username, status, status_message, active, last_active, created_at, updated_at, deleted_at FROM users_backup;');
        $this->db->query('DROP TABLE users_backup;');
        $this->db->transComplete();
    }
}
