<?php

namespace App\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class UserControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    public function testGetUserNotFound(): void
    {
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->get('users/nothing');

        $response = $this->get('users/123');
        $response->assertNotOk();
        $response->assertStatus(404);
    }
    public function testGetUser(): void
    {
        $user_data = [
            'firstname' => 'Test',
            'lastname' => 'Testo',
            'email' => 'test.testo@example.com',
        ];
        $response = $this->withBodyFormat('json')->post('users', $user_data);
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayHasKey('id', $response_data);
        $this->assertArrayHasKey('firstname', $response_data);
        $this->assertArrayHasKey('lastname', $response_data);
        $this->assertArrayHasKey('email', $response_data);
        foreach ($user_data as $key => $_) {
            $this->assertEquals($response_data[$key], $user_data[$key]);
        }

        $this->assertCount(1, json_decode($this->get('users')->getJson(), true));
        $response = $this->get('users/' . $response_data['id']);
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        foreach ($user_data as $key => $_) {
            $this->assertEquals($response_data[$key], $user_data[$key]);
        }
    }

    public function testCreateUser(): void
    {
        $this->assertCount(0, json_decode($this->get('users')->getJson(), true));

        $user_data = [
            'firstname' => 'Test',
            'lastname' => 'Testo',
            'email' => 'test.testo@example.com',
        ];
        $response = $this->withBodyFormat('json')->post('users', $user_data);
        $response->assertOk();
        $this->assertCount(1, json_decode($this->get('users')->getJson(), true));

        $user_data = [
            'firstname' => 'Test2',
            'lastname' => 'Testo2',
            'email' => 'test.testo2@example.com',
        ];
        $response = $this->withBodyFormat('json')->post('users', $user_data);
        $response->assertOk();
        $this->assertCount(2, json_decode($this->get('users')->getJson(), true));
    }

    public function testDeleteUser(): void
    {
        $this->assertCount(0, json_decode($this->get('users')->getJson(), true));

        $user_data = [
            'firstname' => 'Test',
            'lastname' => 'Testo',
            'email' => 'test.testo@example.com',
        ];
        $response_data = json_decode($this->withBodyFormat('json')->post('users', $user_data)->getJson(), true);
        $this->assertCount(1, json_decode($this->get('users')->getJson(), true));

        $this->assertArrayHasKey('id', $response_data);
        $this->assertArrayHasKey('firstname', $response_data);
        $this->assertArrayHasKey('lastname', $response_data);
        $this->assertArrayHasKey('email', $response_data);
        $response = $this->delete('users/' . $response_data['id']);
        $response->assertOk();
        $this->assertCount(0, json_decode($this->get('users')->getJson(), true));
    }

    public function testUniqueEmail(): void
    {
        $this->assertCount(0, json_decode($this->get('users')->getJson(), true));

        $user_data = [
            'firstname' => 'Test',
            'lastname' => 'Testo',
            'email' => 'test.testo@example.com',
        ];
        $response = $this->withBodyFormat('json')->post('users', $user_data);
        $response->assertOk();
        $this->assertCount(1, json_decode($this->get('users')->getJson(), true));

        $user_data = [
            'firstname' => 'asdasfdas',
            'lastname' => 'dfgdfgdfg',
            'email' => 'test.testo@example.com',
        ];
        $response = $this->withBodyFormat('json')->post('users', $user_data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertCount(1, json_decode($this->get('users')->getJson(), true));
    }

    public function testInvalidEmail(): void
    {
        $this->assertCount(0, json_decode($this->get('users')->getJson(), true));

        $user_data = [
            'firstname' => 'asdasfdas',
            'lastname' => 'dfgdfgdfg',
            'email' => 'test.testo@example',
        ];
        $response = $this->withBodyFormat('json')->post('users', $user_data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertCount(0, json_decode($this->get('users')->getJson(), true));
    }

    public function testMissingValues(): void
    {
        $this->assertCount(0, json_decode($this->get('users')->getJson(), true));

        // Email missing
        $user_data = [
            'firstname' => 'Test',
            'lastname' => 'Testo',
        ];
        $response = $this->withBodyFormat('json')->post('users', $user_data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertCount(0, json_decode($this->get('users')->getJson(), true));

        // Firstname missing
        $user_data = [
            'lastname' => 'Testo',
            'email' => 'test.testo@example.com',
        ];
        $response = $this->withBodyFormat('json')->post('users', $user_data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertCount(0, json_decode($this->get('users')->getJson(), true));
        
        // Lastname missing
        $user_data = [
            'firstname' => 'Test',
            'email' => 'test.testo@example.com',
        ];
        $response = $this->withBodyFormat('json')->post('users', $user_data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertCount(0, json_decode($this->get('users')->getJson(), true));

        // All missing
        $user_data = [];
        $response = $this->withBodyFormat('json')->post('users', $user_data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertCount(0, json_decode($this->get('users')->getJson(), true));
    }

    public function testUpdateUser(): void
    {
        $this->assertCount(0, json_decode($this->get('users')->getJson(), true));

        $user_data = [
            'firstname' => 'Test',
            'lastname' => 'Testo',
            'email' => 'test.testo@example.com',
        ];
        $response_data = json_decode($this->withBodyFormat('json')->post('users', $user_data)->getJson(), true);
        $this->assertCount(1, json_decode($this->get('users')->getJson(), true));

        // Only change some values
        $user_data = [
            'firstname' => 'Test2',
            'lastname' => 'Testo2',
            'email' => 'test.testo@example.com',
        ];
        $response = $this->withBodyFormat('json')->put('users/' . $response_data['id'], $user_data);
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertCount(1, json_decode($this->get('users')->getJson(), true));
        foreach ($user_data as $key => $_) {
            $this->assertEquals($user_data[$key], $response_data[$key]);
        }

        // Change all values
        $user_data = [
            'firstname' => 'Tessdfkk kkd',
            'lastname' => 'Te2',
            'email' => 'hello@world.de',
        ];
        $response = $this->withBodyFormat('json')->put('users/' . $response_data['id'], $user_data);
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertCount(1, json_decode($this->get('users')->getJson(), true));
        foreach ($user_data as $key => $_) {
            $this->assertEquals($user_data[$key], $response_data[$key]);
        }
    }
}