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
}
