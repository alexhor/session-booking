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

    public function testVerifyMagicLink(): void
    {
        Time::setTestNow(Time::now());
        $token = null;
        $events_triggered = [];
        Events::on('email', function($archive) use (&$events_triggered, &$token) {
            $events_triggered[] = 'email';

            preg_match('#/verify-magic-link\?token=([^"]+)#', $archive['body'], $matches);
            $token = urldecode($matches[1]);
        });
        Events::on('magicLogin', function() use (&$events_triggered) { $events_triggered[] = 'magicLogin'; });
        Events::on('failedLogin', function($credentials) use (&$events_triggered) { $events_triggered[] = 'failedLogin'; });

        /** User normal login **/
        $events_triggered = [];
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->post('users/authentication', [ 'email' => $user->email ]);
        $response->assertOk();
        $this->assertEventTriggered('email');
        $this->assertNotNull($token);
        // First login successful
        $events_triggered = [];
        $response = $this->get('verify-magic-link', ['token' => $token]);
        $response->assertOk();
        $response->assertRedirect();
        $response->assertEquals(site_url('/'), $response->getRedirectUrl());
        $this->assertContains('magicLogin', $events_triggered);
        $this->assertNotContains('failedLogin', $events_triggered);
        // Second login fails
        $this->clearAuth();
        $events_triggered = [];
        $response = $this->get('verify-magic-link', ['token' => $token]);
        $response->assertOk();
        $response->assertRedirectTo('magic-link');
        $this->assertNotContains('magicLogin', $events_triggered);
        $this->assertContains('failedLogin', $events_triggered);

        /** User just expired token **/
        $this->clearAuth();
        $events_triggered = [];
        $response = $this->post('users/authentication', [ 'email' => $user->email ]);
        $response->assertOk();
        $this->assertEventTriggered('email');
        $this->assertNotNull($token);
        // Expire token
        Time::setTestNow(Time::now()->addSeconds(setting('Auth.magicLinkLifetime')));
        $events_triggered = [];
        $response = $this->get('verify-magic-link', ['token' => $token]);
        $response->assertOk();
        $response->assertRedirectTo('magic-link');
        $this->assertNotContains('magicLogin', $events_triggered);
        $this->assertContains('failedLogin', $events_triggered);

        /** User very expired token **/
        $this->clearAuth();
        $events_triggered = [];
        $response = $this->post('users/authentication', [ 'email' => $user->email ]);
        $response->assertOk();
        $this->assertEventTriggered('email');
        $this->assertNotNull($token);
        // Expire token
        Time::setTestNow(Time::now()->addSeconds(setting('Auth.magicLinkLifetime') + DAY));
        $events_triggered = [];
        $response = $this->get('verify-magic-link', ['token' => $token]);
        $response->assertOk();
        $response->assertRedirectTo('magic-link');
        $this->assertNotContains('magicLogin', $events_triggered);
        $this->assertContains('failedLogin', $events_triggered);

        /** User just not expired token **/
        $this->clearAuth();
        $events_triggered = [];
        $response = $this->post('users/authentication', [ 'email' => $user->email ]);
        $response->assertOk();
        $this->assertEventTriggered('email');
        $this->assertNotNull($token);
        // Just not expired token
        Time::setTestNow(Time::now()->addSeconds(setting('Auth.magicLinkLifetime')-1));
        $events_triggered = [];
        $response = $this->get('verify-magic-link', ['token' => $token]);
        $response->assertOk();
        $response->assertRedirect();
        $response->assertEquals(site_url('/'), $response->getRedirectUrl());
        $this->assertContains('magicLogin', $events_triggered);
        $this->assertNotContains('failedLogin', $events_triggered);
    }

    public function testVerifyInvalidMagicLink(): void
    {
        // Create token
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->post('users/authentication', [ 'email' => $user->email ]);
        $response->assertOk();

        $events_triggered = [];
        Events::on('magicLogin', function() use (&$events_triggered) { $events_triggered[] = 'magicLogin'; });
        Events::on('failedLogin', function($credentials) use (&$events_triggered) { $events_triggered[] = 'failedLogin'; });
        
        // Invalid token
        $response = $this->get('verify-magic-link', ['token' => 'invalid']);
        $response->assertOk();
        $response->assertRedirectTo('magic-link');
        $this->assertNotContains('magicLogin', $events_triggered);
        $this->assertContains('failedLogin', $events_triggered);
    }
}
