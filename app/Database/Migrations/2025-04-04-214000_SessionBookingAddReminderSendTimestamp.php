<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SessionBookingAddReminderSendTimestamp extends Migration
{
    public function up()
    {
        $this->forge->addColumn('session_bookings', [
            'reminder_send_at' => [
                'type' => 'datetime',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('session_bookings', ['reminder_send_at']);
    }
}
