<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SessionBookingMigration extends Migration
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
            ],
            'start_time' => [
                'type' => 'datetime',
                'unique' => true,
            ],
            'created_at datetime default current_timestamp',
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id');
        $this->forge->createTable('session_bookings');
    }

    public function down()
    {
        $this->forge->dropTable('session_bookings');
    }
}
