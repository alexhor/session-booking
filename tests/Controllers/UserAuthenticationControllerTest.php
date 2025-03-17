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

class UserAuthenticationControllerTest extends CIUnitTestCase
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

    public function testGetLogedInUser(): void
    {
        // No user logged in
        $response = $this->get('users/authentication/login');
        $response->assertOk();
        $this->assertFalse(json_decode($response->getJson(), true));
        
        // User logged in
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->actingAs($user)->get('users/authentication/login');
        $response->assertOk();
        $this->assertEquals($user->id, json_decode($response->getJson(), true));

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->get('users/authentication/login');
        $response->assertOk();
        $this->assertEquals($adminUser->id, json_decode($response->getJson(), true));
    }

    public function testIsAdmin(): void
    {
        // No user logged in
        $response = $this->get('users/admin');
        $response->assertOk();
        $this->assertFalse(json_decode($response->getJson(), true));
        
        // User logged in
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->actingAs($user)->get('users/admin');
        $response->assertOk();
        $this->assertFalse(json_decode($response->getJson(), true));

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->get('users/admin');
        $response->assertOk();
        $this->assertTrue(json_decode($response->getJson(), true));
    }

    public function testAddUserToGroup(): void
    {
        $user = auth()->getProvider()->findById(2299488734);
        $this->assertEqualsCanonicalizing(['user'], $user->getGroups());

        // No user logged in
        $response = $this->put('users/' . $user->id . '/groups/admin');
        $response->assertNotOk();
        $response->assertStatus(401);
        $user = auth()->getProvider()->findById($user->id);
        $this->assertEqualsCanonicalizing(['user'], $user->getGroups());
        
        // User logged in
        $response = $this->actingAs($user)->put('users/' . $user->id . '/groups/admin');
        $response->assertNotOk();
        $response->assertStatus(401);
        $user = auth()->getProvider()->findById($user->id);
        $this->assertEqualsCanonicalizing(['user'], $user->getGroups());

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->put('users/' . $user->id . '/groups/admin');
        $response->assertOk();
        $user = auth()->getProvider()->findById($user->id);
        $this->assertEqualsCanonicalizing(['user', 'admin'], $user->getGroups());
    }

    public function testRemoveUserFromGroup(): void
    {
        $user = auth()->getProvider()->findById(2299488734);
        $this->assertEqualsCanonicalizing(['user'], $user->getGroups());

        // No user logged in
        $response = $this->delete('users/' . $user->id . '/groups/user');
        $response->assertNotOk();
        $response->assertStatus(401);
        $user = auth()->getProvider()->findById($user->id);
        $this->assertEqualsCanonicalizing(['user'], $user->getGroups());
        
        // User logged in
        $response = $this->actingAs($user)->delete('users/' . $user->id . '/groups/user');
        $response->assertNotOk();
        $response->assertStatus(401);
        $user = auth()->getProvider()->findById($user->id);
        $this->assertEqualsCanonicalizing(['user'], $user->getGroups());

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->delete('users/' . $user->id . '/groups/user');
        $response->assertOk();
        $user = auth()->getProvider()->findById($user->id);
        $this->assertEmpty($user->getGroups());
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

    public function testLoginWithUserAlreadyLoggedIn() 
    {
        $token = null;
        
        $events_triggered = [];
        Events::on('email', function($archive) use (&$events_triggered, &$token) {
            $events_triggered[] = 'email';

            preg_match('#/verify-magic-link\?token=([^"]+)#', $archive['body'], $matches);
            $token = urldecode($matches[1]);
        });
        Events::on('magicLogin', function() use (&$events_triggered) { $events_triggered[] = 'magicLogin'; });
        Events::on('failedLogin', function($credentials) use (&$events_triggered) { $events_triggered[] = 'failedLogin'; });

        // Get other user token
        $otherUser = auth()->getProvider()->findById(946638323423);
        $response = $this->post('users/authentication', [ 'email' => $otherUser->email ]);
        $response->assertOk();
        $this->assertEventTriggered('email');
        $this->assertNotNull($token);

        // Login other user
        $events_triggered = [];
        $response = $this->withSession()->get('verify-magic-link', ['token' => $token]);
        $response->assertOk();
        $response->assertRedirect();
        $response->assertEquals(site_url('/'), $response->getRedirectUrl());
        $this->assertContains('magicLogin', $events_triggered);
        $this->assertNotContains('failedLogin', $events_triggered);
        $response->assertEquals($otherUser, auth()->user());


        
        // Get other user token
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->post('users/authentication', [ 'email' => $user->email ]);
        $response->assertOk();
        $this->assertEventTriggered('email');
        $this->assertNotNull($token);

        // Login other user
        $events_triggered = [];
        $response = $this->withSession()->get('verify-magic-link', ['token' => $token]);
        $response->assertOk();
        $response->assertRedirect();
        $response->assertEquals(site_url('/'), $response->getRedirectUrl());
        $this->assertContains('magicLogin', $events_triggered);
        $this->assertNotContains('failedLogin', $events_triggered);
        $response->assertEquals($user, auth()->user());
    }
}
