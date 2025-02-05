<?php

namespace Tests\Support\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class SessionBookingSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(UserSeeder::class);

        $session_bookings = [
            [
                'id' => 1,
                'user_id' => 2306585,
                'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(1),
            ],
            [
                'id' => 2,
                'user_id' => 2306585,
                'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(2),
            ],
            [
                'id' => 3,
                'user_id' => 2306585,
                'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(3),
            ],
            [
                'id' => 4,
                'user_id' => 772843,
                'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(8),
            ]
        ];

        $builder = $this->db->table('session_bookings');

        foreach ($session_bookings as $session_booking) {
            $builder->insert($session_booking);
        }
    }
}
