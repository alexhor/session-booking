<?php

namespace Tests\Support\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'id' => 772843,
                'firstname' => 'Test',
                'lastname' => 'Testo',
                'email' => 'test.testo@example.com',
            ],
            [
                'id' => 2306585,
                'firstname' => 'Test2',
                'lastname' => 'Testo2',
                'email' => 'test.testo2@example.com',
            ],
            [
                'id' => 946638323423,
                'firstname' => 'Test3',
                'lastname' => 'Testo3',
                'email' => 'test.testo@example.at',
            ],
            [
                'id' => 2299488734,
                'firstname' => 'Test4',
                'lastname' => 'Testo4',
                'email' => 'test.testo2@example.at',
            ]
        ];

        $builder = $this->db->table('users');

        foreach ($users as $user) {
            $builder->insert($user);
        }
    }
}
