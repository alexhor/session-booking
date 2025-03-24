<?php

namespace Tests\Support\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Authorization\Authorization;

class UserSeeder extends Seeder
{
    private $permissions = [
        'admin' => [
            'users.show',
            'users.update',
            'users.delete',
            'users.update-groups',
            'session-bookings.show-details',
            'session-bookings.create',
            'session-bookings.update',
            'session-bookings.delete',
        ],
        'user' => [
            'session-bookings.create',
        ],
    ];

    public function run(): void
    {
        $this->call(SettingSeeder::class);

        $users = [
            [
                'id' => 772843,
                'firstname' => 'Test',
                'lastname' => 'Testo',
                'email' => 'test.testo@example.com',
                'group' => 'admin',
            ],
            [
                'id' => 2306585,
                'firstname' => 'Test2',
                'lastname' => 'Testo2',
                'email' => 'test.testo2@example.com',
                'group' => 'user',
            ],
            [
                'id' => 946638323423,
                'firstname' => 'Test3',
                'lastname' => 'Testo3',
                'email' => 'test.testo@example.at',
                'group' => 'user',
            ],
            [
                'id' => 2299488734,
                'firstname' => 'Test4',
                'lastname' => 'Testo4',
                'email' => 'test.testo2@example.at',
                'group' => 'user',
            ]
        ];

        $builder = $this->db->table('users');
        $auth_identities_builder = $this->db->table('auth_identities');

        foreach ($users as $user) {
            $email = $user['email'];
            unset($user['email']);
            $group = $user['group'];
            unset($user['group']);

            $builder->insert($user);

            $auth_identities_builder->insert([
                'user_id' => $user['id'],
                'secret' => $email,
                'secret2' => 'dummyhash',
                'type' => 'email_password',
            ]);

            $this->userAddGroup($user['id'], $group);
        }
    }

    private function userAddGroup($user_id, $group): void
    {
        $this->db->table('auth_groups_users')->insert([
            'group' => $group,
            'user_id' => $user_id,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        foreach ($this->permissions[$group] as $permission) {
            $this->db->table('auth_permissions_users')->insert([
                'permission' => $permission,
                'user_id' => $user_id,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
