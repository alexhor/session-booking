<?php

namespace Tests\Support\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Email settings
            [
                'class' => 'Config\Email',
                'key' => 'fromEmail',
                'value' => 'test@example.com',
                'type' => 'string',
                'context' => '',
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ],
            [
                'class' => 'Config\Email',
                'key' => 'fromName',
                'value' => 'Example',
                'type' => 'string',
                'context' => '',
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ],
            [
                'class' => 'Config\Email',
                'key' => 'protocol',
                'value' => 'smtp',
                'type' => 'string',
                'context' => '',
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ],
            [
                'class' => 'Config\Email',
                'key' => 'SMTPHost',
                'value' => 'mail.example.com',
                'type' => 'string',
                'context' => '',
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ],
            [
                'class' => 'Config\Email',
                'key' => 'SMTPUser',
                'value' => 'test@example.com',
                'type' => 'string',
                'context' => '',
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ],
            [
                'class' => 'Config\Email',
                'key' => 'SMTPPass',
                'value' => 'supersecurepassword',
                'type' => 'string',
                'context' => '',
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ],
            [
                'class' => 'Config\Email',
                'key' => 'SMTPPort',
                'value' => 465,
                'type' => 'integer',
                'context' => '',
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ],
            [
                'class' => 'Config\Email',
                'key' => 'SMTPTimeout',
                'value' => 5,
                'type' => 'integer',
                'context' => '',
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ],
            [
                'class' => 'Config\Email',
                'key' => 'SMTPCrypto',
                'value' => '',
                'type' => 'string',
                'context' => '',
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ],

            // App settings
            [
                'class' => 'Config\App',
                'key' => 'baseURL',
                'value' => 'https://example.com',
                'type' => 'string',
                'context' => '',
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ],
            [
                'class' => 'Config\App',
                'key' => 'defaultLocale',
                'value' => 'en',
                'type' => 'string',
                'context' => '',
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ],
            [
                'class' => 'Config\App',
                'key' => 'appTimezone',
                'value' => 'Europe/Berlin',
                'type' => 'string',
                'context' => '',
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ],
            [
                'class' => 'Config\App',
                'key' => 'title',
                'value' => 'Session Booking',
                'type' => 'string',
                'context' => '',
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ],
            [
                'class' => 'Config\App',
                'key' => 'daysInAWeek',
                'value' => 7,
                'type' => 'integer',
                'context' => '',
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ],
            [
                'class' => 'Config\App',
                'key' => 'weekStartTimestamp',
                'value' => 'now',
                'type' => 'string',
                'context' => '',
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ],
        ];

        $builder = $this->db->table('settings');

        foreach ($settings as $setting) {
            $builder->insert($setting);
        }
    }
}
