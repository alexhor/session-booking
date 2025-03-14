<?php

namespace App\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\Seeds\UserSeeder;
use CodeIgniter\Shield\Test\AuthenticationTesting;

use CodeIgniter\Shield\Authorization\Groups;

class UserControllerTest extends CIUnitTestCase
{
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

    public function testGetAllUsers(): void
    {
        $this->seed(UserSeeder::class);
        $response = $this->get('users');
        $response->assertNotOk();
        
        // Non admin user
        $user = auth()->getProvider()->findById(946638323423);
        $response = $this->actingAs($user)->get('users');
        $response->assertNotOk();
        
        // Admin user
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->get('users');
        $response->assertOk();
        $this->assertCount(4, json_decode($response->getJson(), true));
    }

    public function testGetUserNotFound(): void
    {
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->get('users/nothing');

        // Anonymous
        $response = $this->get('users/123');
        $response->assertNotOk();
        $response->assertStatus(401);

        // Login admin
        $adminUser = auth()->getProvider()->findById(772843);
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->actingAs($adminUser)->get('users/nothing');

        $response = $this->actingAs($adminUser)->get('users/123');
        $response->assertNotOk();
        $response->assertStatus(404);
        $this->get('/');

        // Login user
        $user = auth()->getProvider()->findById(946638323423);
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->actingAs($user)->get('users/nothing');

        $response = $this->actingAs($user)->get('users/123');
        $response->assertNotOk();
        $response->assertStatus(401);
    }

    public function testGetUser(): void
    {
        $this->seed(UserSeeder::class);

        // No user logged in
        $response = $this->get('users/123');
        $response->assertNotOk();
        $response->assertStatus(401);
        $response = $this->get('users/946638323423');
        $response->assertNotOk();
        $response->assertStatus(401);
        $response = $this->get('users/772843');
        $response->assertNotOk();
        $response->assertStatus(401);

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->get('users/123');
        $response->assertNotOk();
        $response->assertStatus(404);

        $response = $this->actingAs($adminUser)->get('users/946638323423');
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayHasKey('id', $response_data);
        $this->assertArrayHasKey('firstname', $response_data);
        $this->assertArrayHasKey('lastname', $response_data);
        $this->assertArrayHasKey('email', $response_data);
        $this->assertArrayHasKey('groups', $response_data);
        $this->assertEqualsCanonicalizing(['user'], $response_data['groups']);

        $response = $this->actingAs($adminUser)->get('users/772843');
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayHasKey('id', $response_data);
        $this->assertArrayHasKey('firstname', $response_data);
        $this->assertArrayHasKey('lastname', $response_data);
        $this->assertArrayHasKey('email', $response_data);
        $this->assertArrayHasKey('groups', $response_data);
        $this->assertEqualsCanonicalizing(['admin'], $response_data['groups']);

        // User logged in
        $user = auth()->getProvider()->findById(946638323423);
        $response = $this->actingAs($user)->get('users/123');
        $response->assertNotOk();
        $response->assertStatus(401);
        $response = $this->actingAs($user)->get('users/' . $user->id);
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayHasKey('id', $response_data);
        $this->assertArrayHasKey('firstname', $response_data);
        $this->assertArrayHasKey('lastname', $response_data);
        $this->assertArrayHasKey('email', $response_data);
        $this->assertArrayHasKey('groups', $response_data);
        $this->assertEqualsCanonicalizing(['user'], $response_data['groups']);
    }

    public function testCreateUser(): void
    {
        $user_model = auth()->getProvider();
        $this->assertCount(0, $user_model->findAll());

        $user_data = [
            'firstname' => 'Test',
            'lastname' => 'Testo',
            'email' => 'test.testo@example.com',
        ];
        $response = $this->withBodyFormat('json')->post('users', $user_data);
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayHasKey('message', $response_data);
        $this->assertIsString($response_data['message']);
        $this->assertArrayHasKey('data', $response_data);
        $this->assertIsArray($response_data['data']);
        $response_data = $response_data['data'];

        $this->assertArrayHasKey('id', $response_data);
        $this->assertArrayHasKey('firstname', $response_data);
        $this->assertArrayHasKey('lastname', $response_data);
        $this->assertArrayHasKey('email', $response_data);
        $this->assertArrayHasKey('groups', $response_data);
        $this->assertEqualsCanonicalizing(['user', 'admin'], $response_data['groups']);
        foreach ($user_data as $key => $_) {
            $this->assertEquals($response_data[$key], $user_data[$key]);
        }
        $this->assertCount(1, $user_model->findAll());
        $user = $user_model->findById($response_data['id']);
        $this->assertTrue($user->inGroup('user'));
        $this->assertTrue($user->inGroup('admin'));

        $user_data = [
            'firstname' => 'Test2',
            'lastname' => 'Testo2',
            'email' => 'test.testo2@example.com',
        ];
        $response = $this->withBodyFormat('json')->post('users', $user_data);
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayHasKey('message', $response_data);
        $this->assertIsString($response_data['message']);
        $this->assertArrayHasKey('data', $response_data);
        $this->assertIsArray($response_data['data']);
        $response_data = $response_data['data'];

        $this->assertArrayHasKey('id', $response_data);
        $this->assertArrayHasKey('firstname', $response_data);
        $this->assertArrayHasKey('lastname', $response_data);
        $this->assertArrayHasKey('email', $response_data);
        $this->assertArrayHasKey('groups', $response_data);
        $this->assertEqualsCanonicalizing(['user'], $response_data['groups']);
        foreach ($user_data as $key => $_) {
            $this->assertEquals($response_data[$key], $user_data[$key]);
        }
        $this->assertCount(2, $user_model->findAll());
        $user = $user_model->findById($response_data['id']);
        $this->assertTrue($user->inGroup('user'));
        $this->assertFalse($user->inGroup('admin'));
    }

    public function testDeleteUser(): void
    {
        $this->seed(UserSeeder::class);
        $user_model = auth()->getProvider();
        $this->assertCount(4, $user_model->findAll());

        // No user logged in
        $response = $this->delete('users/772843');
        $response = $this->delete('users/123');
        $response->assertNotOk();
        $response->assertStatus(401);
        $this->assertCount(4, $user_model->findAll());
        $response = $this->delete('users/946638323423');
        $response->assertNotOk();
        $response->assertStatus(401);
        $this->assertCount(4, $user_model->findAll());

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->delete('users/123');
        $response->assertNotOk();
        $response->assertStatus(404);
        $this->assertCount(4, $user_model->findAll());
        $response = $this->actingAs($adminUser)->delete('users/946638323423');
        $response->assertOk();
        $this->assertCount(3, $user_model->findAll());
        $response = $this->actingAs($adminUser)->delete('users/772843');
        $response->assertOk();
        $this->assertCount(2, $user_model->findAll());
        $this->assertNull(auth()->user());

        $this->regressDatabase();
        $this->migrateDatabase();
        $this->seed(UserSeeder::class);
        // User logged in
        $user = auth()->getProvider()->findById(946638323423);
        $response = $this->actingAs($user)->delete('users/123');
        $response->assertNotOk();
        $response->assertStatus(401);
        $this->assertCount(4, $user_model->findAll());
        $response = $this->actingAs($user)->delete('users/772843');
        $response->assertNotOk();
        $response->assertStatus(401);
        $this->assertCount(4, $user_model->findAll());
        $response = $this->actingAs($user)->delete('users/946638323423');
        $response->assertOk();
        $this->assertCount(3, $user_model->findAll());
        $this->assertNull(auth()->user());
    }

    public function testUniqueEmail(): void
    {
        $user_model = auth()->getProvider();
        $this->assertCount(0, $user_model->findAll());

        $user_data = [
            'firstname' => 'Test',
            'lastname' => 'Testo',
            'email' => 'test.testo@example.com',
        ];
        $response = $this->withBodyFormat('json')->post('users', $user_data);
        $response->assertOk();
        $this->assertCount(1, $user_model->findAll());

        $user_data = [
            'firstname' => 'asdasfdas',
            'lastname' => 'dfgdfgdfg',
            'email' => 'test.testo@example.com',
        ];
        $response = $this->withBodyFormat('json')->post('users', $user_data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertCount(1, $user_model->findAll());
    }

    public function testInvalidEmail(): void
    {
        $user_model = auth()->getProvider();
        $this->assertCount(0, $user_model->findAll());

        $user_data = [
            'firstname' => 'asdasfdas',
            'lastname' => 'dfgdfgdfg',
            'email' => 'test.testo@example',
        ];
        $response = $this->withBodyFormat('json')->post('users', $user_data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertCount(0, $user_model->findAll());
    }

    public function testMissingValues(): void
    {
        $user_model = auth()->getProvider();
        $this->assertCount(0, $user_model->findAll());

        // Email missing
        $user_data = [
            'firstname' => 'Test',
            'lastname' => 'Testo',
        ];
        $response = $this->withBodyFormat('json')->post('users', $user_data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertCount(0, $user_model->findAll());

        // Firstname missing
        $user_data = [
            'lastname' => 'Testo',
            'email' => 'test.testo@example.com',
        ];
        $response = $this->withBodyFormat('json')->post('users', $user_data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertCount(0, $user_model->findAll());
        
        // Lastname missing
        $user_data = [
            'firstname' => 'Test',
            'email' => 'test.testo@example.com',
        ];
        $response = $this->withBodyFormat('json')->post('users', $user_data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertCount(0, $user_model->findAll());

        // All missing
        $user_data = [];
        $response = $this->withBodyFormat('json')->post('users', $user_data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertCount(0, $user_model->findAll());
    }

    public function testUpdateUser(): void
    {
        $this->seed(UserSeeder::class);
        $user_model = auth()->getProvider();
        $this->assertCount(4, $user_model->findAll());

        $user_data = [
            'firstname' => 'Test',
            'lastname' => 'Testo',
            'email' => 'test.testo55432@example.com',
        ];
        $response_data = json_decode($this->withBodyFormat('json')->post('users', $user_data)->getJson(), true);
        $this->assertArrayHasKey('message', $response_data);
        $this->assertIsString($response_data['message']);
        $this->assertArrayHasKey('data', $response_data);
        $this->assertIsArray($response_data['data']);
        $response_data = $response_data['data'];
        $this->assertCount(5, $user_model->findAll());

        // No user logged in, only change some values
        $user_data = [
            'firstname' => 'Test2',
            'lastname' => 'Testo2',
            'email' => 'test.testo55432@example.com',
        ];
        $response = $this->withBodyFormat('json')->put('users/' . $response_data['id'], $user_data);
        $response->assertNotOk();
        $response->assertStatus(401);
        $this->assertCount(5, $user_model->findAll());

        // No user logged in, change all values
        $user_data = [
            'firstname' => 'Tessdfkk kkd',
            'lastname' => 'Te2',
            'email' => 'hello@world.de',
        ];
        $response = $this->withBodyFormat('json')->put('users/' . $response_data['id'], $user_data);
        $response->assertNotOk();
        $response->assertStatus(401);
        $this->assertCount(5, $user_model->findAll());

        // Admin logged in, only change some values
        $adminUser = auth()->getProvider()->findById(772843);
        $user_data = [
            'firstname' => 'Test2',
            'lastname' => 'Testo2',
            'email' => 'test.testo55432@example.com',
        ];
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->put('users/' . $response_data['id'], $user_data);
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayHasKey('message', $response_data);
        $this->assertIsString($response_data['message']);
        $this->assertArrayHasKey('data', $response_data);
        $this->assertIsArray($response_data['data']);
        $response_data = $response_data['data'];

        $this->assertCount(5, $user_model->findAll());
        foreach ($user_data as $key => $_) {
            $this->assertEquals($user_data[$key], $response_data[$key]);
        }

        // Admin logged in, change all values
        $user_data = [
            'firstname' => 'Tessdfkk kkd',
            'lastname' => 'Te2',
            'email' => 'hello@world.de',
        ];
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->put('users/' . $response_data['id'], $user_data);
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayHasKey('message', $response_data);
        $this->assertIsString($response_data['message']);
        $this->assertArrayHasKey('data', $response_data);
        $this->assertIsArray($response_data['data']);
        $response_data = $response_data['data'];

        $this->assertCount(5, $user_model->findAll());
        foreach ($user_data as $key => $_) {
            $this->assertEquals($user_data[$key], $response_data[$key]);
        }

        // Admin logged in, non existing user
        $user_data = [
            'firstname' => 'Test2',
            'lastname' => 'Testo2',
            'email' => 'test.testo15@example.com',
        ];
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->put('users/123');
        $response->assertNotOk();
        $response->assertStatus(404);
        $this->withSession([])->get('/');

        // User logged in, unauthorised
        $user = auth()->getProvider()->findById(946638323423);
        $user_data = [
            'firstname' => 'Test',
            'lastname' => 'Testo',
            'email' => 'test.testo55432@example.com',
        ];
        $response = $this->actingAs($user)->withBodyFormat('json')->put('users/' . $response_data['id'], $user_data);
        $response->assertNotOk();

        // User logged in, change all values
        $user_data = [
            'firstname' => 'Test',
            'lastname' => 'Testo',
            'email' => 'test.testo55432@example.com',
        ];
        $response = $this->actingAs($user)->withBodyFormat('json')->put('users/' . $user->id, $user_data);
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayHasKey('message', $response_data);
        $this->assertIsString($response_data['message']);
        $this->assertArrayHasKey('data', $response_data);
        $this->assertIsArray($response_data['data']);
        $response_data = $response_data['data'];

        $this->assertCount(5, $user_model->findAll());
        foreach ($user_data as $key => $_) {
            $this->assertEquals($user_data[$key], $response_data[$key]);
        }

        // User logged in, only change some values
        $user_data = [
            'firstname' => 'Test2',
            'lastname' => 'Testo2',
            'email' => 'test.testo55432@example.com',
        ];
        $response = $this->actingAs($user)->withBodyFormat('json')->put('users/' . $user->id, $user_data);
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayHasKey('message', $response_data);
        $this->assertIsString($response_data['message']);
        $this->assertArrayHasKey('data', $response_data);
        $this->assertIsArray($response_data['data']);
        $response_data = $response_data['data'];

        $this->assertCount(5, $user_model->findAll());
        foreach ($user_data as $key => $_) {
            $this->assertEquals($user_data[$key], $response_data[$key]);
        }

        // User logged in, non existing user
        $user_data = [
            'firstname' => 'Test2',
            'lastname' => 'Testo2',
            'email' => 'test.testo15@example.com',
        ];
        $response = $this->actingAs($user)->withBodyFormat('json')->put('users/123', $user_data);
        $response->assertNotOk();
        $response->assertStatus(401);
    }
}