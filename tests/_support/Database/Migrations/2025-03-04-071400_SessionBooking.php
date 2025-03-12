<?php

namespace Tests\Support\Database\Migrations;

require_once(HOMEPATH . 'app/Database/Migrations/2025-03-04-071400_SessionBookingAddTitleDescription.php');
use App\Database\Migrations\SessionBookingAddTitleDescriptionMigration;


class TestSessionBookingAddTitleDescriptionMigration extends SessionBookingAddTitleDescriptionMigration
{
    protected $DBGroup = 'tests';

    public function down() {
        $this->db->transStart();
        $this->db->query('CREATE TEMPORARY TABLE session_bookings_backup(id, user_id, start_time, created_at);');
        $this->db->query('INSERT INTO session_bookings_backup SELECT id, user_id, start_time, created_at FROM session_bookings;');
        $this->db->query('DROP TABLE session_bookings;');
        
        //$this->db->query('CREATE TABLE session_bookings(id, user_id, start_time, created_at);');
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

        $this->db->query('INSERT INTO session_bookings SELECT id, user_id, start_time, created_at FROM session_bookings_backup;');
        $this->db->query('DROP TABLE session_bookings_backup;');
        $this->db->transComplete();
    }
}
