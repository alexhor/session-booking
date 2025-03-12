<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Forge;
use CodeIgniter\Database\Migration;

class UserMigration extends Migration
{
    /**
     * @var string[]
     */
    protected array $tables;

    public function __construct(?Forge $forge = null)
    {
        parent::__construct($forge);

        /** @var \Config\Auth $authConfig */
        $authConfig   = config('Auth');
        $this->tables = $authConfig->tables;
    }

    public function up()
    {
        $fields = [
            'firstname' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
            ],
            'lastname' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
            ],
        ];
        $this->forge->addColumn($this->tables['users'], $fields);
    }

    public function down()
    {
        $this->forge->dropColumn($this->tables['users'], ['firstname', 'lastname']);
    }
}
