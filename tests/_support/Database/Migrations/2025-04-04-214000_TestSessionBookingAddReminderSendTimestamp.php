<?php

namespace Tests\Support\Database\Migrations;

require_once(HOMEPATH . 'app/Database/Migrations/2025-04-04-214000_SessionBookingAddReminderSendTimestamp.php');
use App\Database\Migrations\SessionBookingAddReminderSendTimestamp;


class TestSessionBookingAddReminderSendTimestamp extends SessionBookingAddReminderSendTimestamp
{
    protected $DBGroup = 'tests';

    public function down() {
        $this->db->transStart();
        $this->db->query('CREATE TEMPORARY TABLE session_bookings_backup(id, user_id, start_time, created_at, title, title_is_public, description, description_is_public);');
        $this->db->query('INSERT INTO session_bookings_backup SELECT id, user_id, start_time, created_at, title, title_is_public, description, description_is_public FROM session_bookings;');
        $this->db->query('DROP TABLE session_bookings;');
        
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

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id');
        $this->forge->createTable('session_bookings');

        $this->db->query('INSERT INTO session_bookings SELECT id, user_id, start_time, created_at, title, title_is_public, description, description_is_public FROM session_bookings_backup;');
        $this->db->query('DROP TABLE session_bookings_backup;');
        $this->db->transComplete();
    }
}
