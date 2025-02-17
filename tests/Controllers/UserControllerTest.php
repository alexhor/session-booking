<?php

namespace App\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\Seeds\UserSeeder;
use App\Models\User;

class UserControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    public function testGetAllUsers(): void
    {
        $this->seed(UserSeeder::class);
        $response = $this->get('users');
        $response->assertNotOk();

        service('session')->set('admin_logged_in_user_id', 946638323423);
        $response = $this->withSession()->get('users');
        $response->assertOk();
        $this->assertCount(4, json_decode($response->getJson(), true));
    }

    public function testGetUserNotFound(): void
    {
        $user_authentication_controller = new UserAuthenticationController();
        $set_user_logged_in = $this->getPrivateMethodInvoker($user_authentication_controller, 'set_user_logged_in');

        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->get('users/nothing');

        $response = $this->get('users/123');
        $response->assertNotOk();
        $response->assertStatus(401);

        // Login admin
        service('session')->set('admin_logged_in_user_id', 1234);
        $set_user_logged_in(1234);

        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->withSession()->get('users/nothing');

        $response = $this->withSession()->get('users/123');
        $response->assertNotOk();
        $response->assertStatus(404);
        $this->withSession([])->get('/');

        // Login user
        $set_user_logged_in(1234);

        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->withSession()->get('users/nothing');

        $response = $this->withSession()->get('users/123');
        $response->assertNotOk();
        $response->assertStatus(401);
    }
    public function testGetUser(): void
    {
        $user_authentication_controller = new UserAuthenticationController();
        $set_user_logged_in = $this->getPrivateMethodInvoker($user_authentication_controller, 'set_user_logged_in');
        $user_model = new User();
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
        foreach ($user_data as $key => $_) {
            $this->assertEquals($response_data[$key], $user_data[$key]);
        }

        $this->assertCount(1, $user_model->findAll());
        // No user logged in
        $response = $this->get('users/' . $response_data['id']);
        $response->assertNotOk();
        $response->assertStatus(401);

        // Admin logged in
        service('session')->set('admin_logged_in_user_id', 1234);
        $set_user_logged_in(1234);
        $response = $this->withSession()->get('users/123');
        $response->assertNotOk();
        $response->assertStatus(404);
        $response = $this->withSession()->get('users/' . $response_data['id']);
        $response->assertOk();
        $this->withSession([])->get('/');

        // User logged in
        $set_user_logged_in($response_data['id']);
        $response = $this->withSession()->get('users/123');
        $response->assertNotOk();
        $response->assertStatus(401);

        $response = $this->withSession()->get('users/' . $response_data['id']);
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        foreach ($user_data as $key => $_) {
            $this->assertEquals($response_data[$key], $user_data[$key]);
        }
    }

    public function testCreateUser(): void
    {
        $user_model = new User();
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
            'firstname' => 'Test2',
            'lastname' => 'Testo2',
            'email' => 'test.testo2@example.com',
        ];
        $response = $this->withBodyFormat('json')->post('users', $user_data);
        $response->assertOk();
        $this->assertCount(2, $user_model->findAll());
    }

    public function testDeleteUser(): void
    {
        $user_authentication_controller = new UserAuthenticationController();
        $set_user_logged_in = $this->getPrivateMethodInvoker($user_authentication_controller, 'set_user_logged_in');
        $user_model = new User();
        $this->assertCount(0, $user_model->findAll());

        $user_data = [
            'firstname' => 'Test',
            'lastname' => 'Testo',
            'email' => 'test.testo@example.com',
        ];
        $response_data = json_decode($this->withBodyFormat('json')->post('users', $user_data)->getJson(), true);
        $this->assertArrayHasKey('message', $response_data);
        $this->assertIsString($response_data['message']);
        $this->assertArrayHasKey('data', $response_data);
        $this->assertIsArray($response_data['data']);
        $response_data = $response_data['data'];

        $this->assertCount(1, $user_model->findAll());
        $this->assertArrayHasKey('id', $response_data);
        $this->assertArrayHasKey('firstname', $response_data);
        $this->assertArrayHasKey('lastname', $response_data);
        $this->assertArrayHasKey('email', $response_data);

        // No user logged in
        $response = $this->delete('users/' . $response_data['id']);
        $response = $this->delete('users/123');
        $response->assertNotOk();
        $response->assertStatus(401);
        $this->assertCount(1, $user_model->findAll());
        $response = $this->delete('users/' . $response_data['id']);
        $response->assertNotOk();
        $response->assertStatus(401);
        $this->assertCount(1, $user_model->findAll());

        // Admin logged in
        service('session')->set('admin_logged_in_user_id', 1234);
        $set_user_logged_in(1234);
        $response = $this->withSession()->delete('users/123');
        $response->assertNotOk();
        $response->assertStatus(404);
        $this->assertCount(1, $user_model->findAll());
        $response = $this->withSession()->delete('users/' . $response_data['id']);
        $response->assertOk();
        $this->assertCount(0, $user_model->findAll());
        $this->withSession([])->get('/');

        $response_data = json_decode($this->withSession([])->withBodyFormat('json')->post('users', $user_data)->getJson(), true);
        $this->assertArrayHasKey('message', $response_data);
        $this->assertIsString($response_data['message']);
        $this->assertArrayHasKey('data', $response_data);
        $this->assertIsArray($response_data['data']);
        $response_data = $response_data['data'];
        $this->assertCount(1, $user_model->findAll());
        // User logged in
        $set_user_logged_in($response_data['id']);
        $response = $this->withSession()->delete('users/123');
        $response->assertNotOk();
        $response->assertStatus(401);
        $this->assertCount(1, $user_model->findAll());
        $response = $this->withSession()->delete('users/' . $response_data['id']);
        $response->assertOk();
        $this->assertCount(0, $user_model->findAll());
    }

    public function testUniqueEmail(): void
    {
        $user_model = new User();
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
        $user_model = new User();
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
        $user_model = new User();
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
        $user_authentication_controller = new UserAuthenticationController();
        $set_user_logged_in = $this->getPrivateMethodInvoker($user_authentication_controller, 'set_user_logged_in');
        $user_model = new User();
        $this->assertCount(0, $user_model->findAll());

        $user_data = [
            'firstname' => 'Test',
            'lastname' => 'Testo',
            'email' => 'test.testo@example.com',
        ];
        $response_data = json_decode($this->withBodyFormat('json')->post('users', $user_data)->getJson(), true);
        $this->assertArrayHasKey('message', $response_data);
        $this->assertIsString($response_data['message']);
        $this->assertArrayHasKey('data', $response_data);
        $this->assertIsArray($response_data['data']);
        $response_data = $response_data['data'];
        $this->assertCount(1, $user_model->findAll());

        // No user logged in, only change some values
        $user_data = [
            'firstname' => 'Test2',
            'lastname' => 'Testo2',
            'email' => 'test.testo@example.com',
        ];
        $response = $this->withBodyFormat('json')->put('users/' . $response_data['id'], $user_data);
        $response->assertNotOk();
        $response->assertStatus(401);
        $this->assertCount(1, $user_model->findAll());

        // No user logged in, change all values
        $user_data = [
            'firstname' => 'Tessdfkk kkd',
            'lastname' => 'Te2',
            'email' => 'hello@world.de',
        ];
        $response = $this->withBodyFormat('json')->put('users/' . $response_data['id'], $user_data);
        $response->assertNotOk();
        $response->assertStatus(401);
        $this->assertCount(1, $user_model->findAll());

        // Admin logged in, only change some values
        service('session')->set('admin_logged_in_user_id', 1234);
        $set_user_logged_in(1234);
        $user_data = [
            'firstname' => 'Test2',
            'lastname' => 'Testo2',
            'email' => 'test.testo@example.com',
        ];
        $response = $this->withSession()->withBodyFormat('json')->put('users/' . $response_data['id'], $user_data);
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayHasKey('message', $response_data);
        $this->assertIsString($response_data['message']);
        $this->assertArrayHasKey('data', $response_data);
        $this->assertIsArray($response_data['data']);
        $response_data = $response_data['data'];

        $this->assertCount(1, $user_model->findAll());
        foreach ($user_data as $key => $_) {
            $this->assertEquals($user_data[$key], $response_data[$key]);
        }

        // Admin logged in, change all values
        $user_data = [
            'firstname' => 'Tessdfkk kkd',
            'lastname' => 'Te2',
            'email' => 'hello@world.de',
        ];
        $response = $this->withSession()->withBodyFormat('json')->put('users/' . $response_data['id'], $user_data);
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayHasKey('message', $response_data);
        $this->assertIsString($response_data['message']);
        $this->assertArrayHasKey('data', $response_data);
        $this->assertIsArray($response_data['data']);
        $response_data = $response_data['data'];

        $this->assertCount(1, $user_model->findAll());
        foreach ($user_data as $key => $_) {
            $this->assertEquals($user_data[$key], $response_data[$key]);
        }

        // Admin logged in, non existing user
        $user_data = [
            'firstname' => 'Test2',
            'lastname' => 'Testo2',
            'email' => 'test.testo15@example.com',
        ];
        $response = $this->withSession()->withBodyFormat('json')->put('users/123');
        $response->assertNotOk();
        $response->assertStatus(404);
        $this->withSession([])->get('/');

        // User logged in, change all values
        $set_user_logged_in($response_data['id']);
        $user_data = [
            'firstname' => 'Test',
            'lastname' => 'Testo',
            'email' => 'test.testo@example.com',
        ];
        $response = $this->withSession()->withBodyFormat('json')->put('users/' . $response_data['id'], $user_data);
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayHasKey('message', $response_data);
        $this->assertIsString($response_data['message']);
        $this->assertArrayHasKey('data', $response_data);
        $this->assertIsArray($response_data['data']);
        $response_data = $response_data['data'];

        $this->assertCount(1, $user_model->findAll());
        foreach ($user_data as $key => $_) {
            $this->assertEquals($user_data[$key], $response_data[$key]);
        }

        // User logged in, only change some values
        $user_data = [
            'firstname' => 'Test2',
            'lastname' => 'Testo2',
            'email' => 'test.testo@example.com',
        ];
        $response = $this->withSession()->withBodyFormat('json')->put('users/' . $response_data['id'], $user_data);
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayHasKey('message', $response_data);
        $this->assertIsString($response_data['message']);
        $this->assertArrayHasKey('data', $response_data);
        $this->assertIsArray($response_data['data']);
        $response_data = $response_data['data'];

        $this->assertCount(1, $user_model->findAll());
        foreach ($user_data as $key => $_) {
            $this->assertEquals($user_data[$key], $response_data[$key]);
        }

        // User logged in, non existing user
        $user_data = [
            'firstname' => 'Test2',
            'lastname' => 'Testo2',
            'email' => 'test.testo15@example.com',
        ];
        $response = $this->withSession()->withBodyFormat('json')->put('users/123');
        $response->assertNotOk();
        $response->assertStatus(401);
    }
}