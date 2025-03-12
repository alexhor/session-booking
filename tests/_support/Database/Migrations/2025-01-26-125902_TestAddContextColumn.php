<?php

namespace Tests\Support\Database\Migrations;

require_once(HOMEPATH . 'vendor/codeigniter4/settings/src/Database/Migrations/2021-11-14-143905_AddContextColumn.php');
use CodeIgniter\Settings\Database\Migrations\AddContextColumn;


class TestAddContextColumn extends AddContextColumn
{
    protected $DBGroup = 'tests';

    public function down() {
        $table = config('Settings')->database['table'];
        $this->db->transStart();
        $this->db->query('CREATE TEMPORARY TABLE ' . $table . '_backup(id, user_id, start_time, created_at);');
        $this->db->query('INSERT INTO ' . $table . '_backup SELECT id, user_id, start_time, created_at FROM ' . $table . ';');
        $this->db->query('DROP TABLE ' . $table . ';');
        
        $this->forge->addField('id');
        $this->forge->addField([
            'class' => [
                'type'       => 'varchar',
                'constraint' => 255,
            ],
            'key' => [
                'type'       => 'varchar',
                'constraint' => 255,
            ],
            'value' => [
                'type' => 'text',
                'null' => true,
            ],
            'type' => [
                'type'       => 'varchar',
                'constraint' => 31,
                'default'    => 'string',
            ],
            'created_at' => [
                'type' => 'datetime',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'datetime',
                'null' => false,
            ],
        ]);
        $this->forge->createTable($table, true);

        $this->db->query('INSERT INTO ' . $table . ' SELECT id, user_id, start_time, created_at FROM ' . $table . '_backup;');
        $this->db->query('DROP TABLE ' . $table . '_backup;');
        $this->db->transComplete();
    }
}
