<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UserAuthenticationMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'unique' => true,
            ],
            'token_hash' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'created_at' => [
                'type' => 'datetime',
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id');
        $this->forge->createTable('user_authentication');
    }

    public function down()
    {
        $this->forge->dropTable('user_authentication');
    }
}
