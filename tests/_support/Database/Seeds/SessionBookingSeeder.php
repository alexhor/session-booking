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
                'title' => 'Lorem ipsum',
                'title_is_public' => false,
                'description' => 'Lorem ipsum',
                'description_is_public' => false,
            ],
            [
                'id' => 2,
                'user_id' => 2306585,
                'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(2),
                'description' => 'Lorem ipsum',
                'description_is_public' => false,
            ],
            [
                'id' => 3,
                'user_id' => 2306585,
                'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(3),
            ],
            [
                'id' => 4,
                'user_id' => 772843,
                'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(101),
                'title' => 'Lorem ipsum',
                'title_is_public' => true,
                'description' => 'Lorem ipsum',
                'description_is_public' => true,
            ],
            [
                'id' => 5,
                'user_id' => 772843,
                'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(102),
                'title' => 'Lorem ipsum',
                'title_is_public' => false,
            ],
            [
                'id' => 6,
                'user_id' => 772843,
                'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(103),
                'description' => 'Lorem ipsum',
                'description_is_public' => true,
            ],
            [
                'id' => 7,
                'user_id' => 772843,
                'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(104),
                'description' => 'Lorem ipsum',
                'description_is_public' => false,
            ],
        ];

        $builder = $this->db->table('session_bookings');

        foreach ($session_bookings as $session_booking) {
            $builder->insert($session_booking);
        }
    }
}
