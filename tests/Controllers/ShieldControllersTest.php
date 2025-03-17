<?php

namespace App\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\Seeds\UserSeeder;
use App\Models\UserAuthentication;
use CodeIgniter\Events\Events;
use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Test\AuthenticationTesting;

class ShieldControllersTest extends CIUnitTestCase
{
    protected $seed = UserSeeder::class;

    use DatabaseTestTrait;
    use FeatureTestTrait;
    use AuthenticationTesting;

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->clearAuth();
    }

    protected function clearAuth(): void
    {
        auth()->logout();
        session()->destroy();
        $this->assertNull(auth()->user());
    }

    public function testGetLoginToken(): void
    {
        $email_send = false;
        Events::on('email', function($archive) use (&$email_send) { $email_send = true; });

        // Invalid user
        $response = $this->post('users/authentication', [ 'email' => 'invalid@example.com' ]);
        $response->assertOk();
        $response->assertRedirectTo('magic-link');
        $this->assertFalse($email_send);

        // User
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->post('users/authentication', [ 'email' => $user->email ]);
        $response->assertOk();
        $response->assertNotRedirect();
        $this->assertTrue($email_send);
        $this->assertEventTriggered('email');

        // Admin
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->post('users/authentication', [ 'email' => $adminUser->email ]);
        $response->assertOk();
        $response->assertNotRedirect();
        $this->assertEventTriggered('email');
    }
}
