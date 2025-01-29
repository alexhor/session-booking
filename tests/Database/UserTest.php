<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\User;


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
}
