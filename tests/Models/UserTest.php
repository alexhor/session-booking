<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\User;
use Tests\Support\Database\Seeds\UserSeeder;


/**
 * @internal
 */
final class UserTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    public function testCreateUser(): void
    {
        $user_model = new User();

        $this->assertCount(0, $user_model->findAll());

        $user_data = [
            'firstname' => 'Test',
            'lastname' => 'Testo',
            'email' => 'test.testo@example.com',
        ];
        $user_model->insert($user_data);
        $this->assertCount(1, $user_model->findAll());

        $user_data = [
            'firstname' => 'Test2',
            'lastname' => 'Testo2',
            'email' => 'test.testo2@example.com',
        ];
        $user_model->insert($user_data);
        $this->assertCount(2, $user_model->findAll());
    }

    public function testDeleteUser(): void
    {
        $user_model = new User();

        $this->assertCount(0, $user_model->findAll());

        $user_data = [
            'firstname' => 'Test',
            'lastname' => 'Testo',
            'email' => 'test.testo@example.com',
        ];
        $user_id = $user_model->insert($user_data);
        $this->assertCount(1, $user_model->findAll());

        $user_model->delete($user_id);
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
        $user_id = $user_model->insert($user_data);
        $this->assertCount(1, $user_model->findAll());

        $user_data = [
            'firstname' => 'asdasfdas',
            'lastname' => 'dfgdfgdfg',
            'email' => 'test.testo@example.com',
        ];
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $this->assertEquals($user_model->insert($user_data), false);
        $this->assertCount(1, $user_model->findAll());
    }

    public function testInvalidEmail(): void
    {
        $user_model = new User();

        $this->assertCount(0, $user_model->findAll());

        $user_data = [
            'firstname' => 'Test',
            'lastname' => 'Testo',
            'email' => 'test.testo',
        ];
        $this->assertEquals($user_model->insert($user_data), false);
        $this->assertCount(0, $user_model->findAll());

        $user_data = [
            'firstname' => 'asdasfdas',
            'lastname' => 'dfgdfgdfg',
            'email' => 'test.testo@example',
        ];
        $this->assertEquals($user_model->insert($user_data), false);
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
        $this->expectException(CodeIgniter\Database\Exceptions\DatabaseException::class);
        $user_model->insert($user_data);
        $this->assertCount(0, $user_model->findAll());

        // Firstname missing
        $user_data = [
            'lastname' => 'Testo',
            'email' => 'test.testo@example.com',
        ];
        $this->expectException(CodeIgniter\Database\Exceptions\DatabaseException::class);
        $user_model->insert($user_data);
        $this->assertCount(0, $user_model->findAll());
        
        // Lastname missing
        $user_data = [
            'firstname' => 'Test',
            'email' => 'test.testo@example.com',
        ];
        $this->expectException(CodeIgniter\Database\Exceptions\DatabaseException::class);
        $user_model->insert($user_data);
        $this->assertCount(0, $user_model->findAll());

        // All missing
        $user_data = [];
        $this->expectException(CodeIgniter\Database\Exceptions\DatabaseException::class);
        $user_model->insert($user_data);
        $this->assertCount(0, $user_model->findAll());
    }

    public function testUpdateUser(): void
    {
        $user_model = new User();

        $this->assertCount(0, $user_model->findAll());

        $user_data = [
            'firstname' => 'Test',
            'lastname' => 'Testo',
            'email' => 'test.testo@example.com',
        ];
        $user_id = $user_model->insert($user_data);
        $this->assertCount(1, $user_model->findAll());

        // Only change some values
        $user_data = [
            'id' => $user_id,
            'firstname' => 'Test2',
            'lastname' => 'Testo2',
            'email' => 'test.testo@example.com',
        ];
        $user_model->save($user_data);
        $this->assertCount(1, $user_model->findAll());
        foreach ($user_data as $key => $_) {
            $this->assertEquals($user_data[$key], $user_model->find($user_id)[$key]);
        }

        // Change all values
        $user_data = [
            'id' => $user_id,
            'firstname' => 'Tessdfkk kkd',
            'lastname' => 'Te2',
            'email' => 'hello@world.de',
        ];
        $user_model->save($user_data);
        $this->assertCount(1, $user_model->findAll());
        foreach ($user_data as $key => $_) {
            $this->assertEquals($user_data[$key], $user_model->find($user_id)[$key]);
        }
    }

    public function testGetUser(): void
    {
        $this->seed(UserSeeder::class);
        $user_model = new User();
        $user_id = 946638323423;
        $user_data = $user_model->find($user_id);

        $this->assertArrayHasKey('id', $user_data);
        $this->assertArrayHasKey('firstname', $user_data);
        $this->assertArrayHasKey('lastname', $user_data);
        $this->assertArrayHasKey('email', $user_data);

        $this->assertEquals(null, $user_model->find(123));
    }
}
