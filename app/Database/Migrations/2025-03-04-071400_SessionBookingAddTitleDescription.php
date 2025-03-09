<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SessionBookingAddTitleDescriptionMigration extends Migration
{
    public function up()
    {
        $this->forge->addColumn('session_bookings', [
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'default' => '',
            ],
            'title_is_public' => [
                'type' => 'BIT',
                'default' => 0,
            ],
            'description' => [
                'type' => 'TEXT',
                'default' => '',
            ],
            'description_is_public' => [
                'type' => 'BIT',
                'default' => 0,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('session_bookings', ['title', 'title_is_public', 'description', 'description_is_public']);
    }
}
