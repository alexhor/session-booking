<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\I18n\Time;
use App\Models\UserAuthentication;
use Tests\Support\Database\Seeds\UserSeeder;


/**
 * @internal
 */
final class UserAuthenticationTest extends CIUnitTestCase
{
    protected $seed = UserSeeder::class;

    use DatabaseTestTrait;

    public function testCreateUserAuthentication(): void
    {
        $user_authentication_model = new UserAuthentication();
        $this->assertCount(0, $user_authentication_model->findAll());

        $user_data = [
            'user_id' => 2299488734,
        ];
        $token1 = $user_authentication_model->insert($user_data);
        $this->assertCount(1, $user_authentication_model->findAll());

        $user_data = [
            'user_id' => 946638323423,
        ];
        $user_authentication_model->insert($user_data);
        $this->assertCount(2, $user_authentication_model->findAll());

        $user_data = [
            'user_id' => 2299488734,
        ];
        $token2 = $user_authentication_model->insert($user_data);
        
        $this->assertCount(2, $user_authentication_model->findAll());
        $this->assertNotEquals(false, $token2);
        $this->assertNotEquals($token1, $token2);
    }

    public function testCreateInvalidUserAuthentication(): void
    {
        $user_authentication_model = new UserAuthentication();
        $this->assertCount(0, $user_authentication_model->findAll());

        $user_data = [
            'user_id' => 123,
        ];
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $user_authentication_model->insert($user_data);
        $this->assertCount(0, $user_authentication_model->findAll());

        $user_data = [
            'user_id' => 'invalid',
        ];
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $user_authentication_model->insert($user_data);
        $this->assertCount(0, $user_authentication_model->findAll());
    }
    
    public function testCreateUserAuthenticationMissingValue(): void
    {
        $user_authentication_model = new UserAuthentication();
        $this->assertCount(0, $user_authentication_model->findAll());
        
        $user_data = [];
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $token2 = $user_authentication_model->insert($user_data);
        $this->assertCount(0, $user_authentication_model->findAll());
    }

    public function testDeleteUserAuthentication(): void
    {
        $user_authentication_model = new UserAuthentication();
        $this->assertCount(0, $user_authentication_model->findAll());

        $user_data = [
            'user_id' => 2299488734,
        ];

        $user_authentication_model->insert($user_data);
        $this->assertCount(1, $user_authentication_model->findAll());
        $user_authentication_model->delete(1);
        $this->assertCount(0, $user_authentication_model->findAll());

        $user_authentication_model->insert($user_data);
        $this->assertCount(1, $user_authentication_model->findAll());
        $user_authentication_model->delete_by_user_id($user_data['user_id']);
        $this->assertCount(0, $user_authentication_model->findAll());

        $user_authentication_model->delete(123);
        $this->assertCount(0, $user_authentication_model->findAll());

        $user_authentication_model->delete_by_user_id($user_data['user_id']);
        $this->assertCount(0, $user_authentication_model->findAll());
    }

    public function testUpdateNotPossible(): void
    {
        $user_authentication_model = new UserAuthentication();
        $this->assertCount(0, $user_authentication_model->findAll());

        $user_data = [
            'user_id' => 2299488734,
        ];
        $user_authentication_model->insert($user_data);
        $this->assertCount(1, $user_authentication_model->findAll());

        $this->expectException(\BadMethodCallException::class);
        $user_authentication_model->update(1, $user_data);
    }

    public function testGenerateToken(): void
    {
        $user_authentication_model = new UserAuthentication();
        $generate_token = $this->getPrivateMethodInvoker($user_authentication_model, 'generate_token');

        $token = $generate_token();
        $this->assertIsstring($token);
        $this->assertEquals(72, strlen($token));

        $token2 = $generate_token();
        $this->assertIsstring($token2);
        $this->assertEquals(72, strlen($token2));
        $this->assertNotEquals($token, $token2);
    }

    public function testHashToken(): void
    {
        $user_authentication_model = new UserAuthentication();
        $generate_token = $this->getPrivateMethodInvoker($user_authentication_model, 'generate_token');
        $hash_token = $this->getPrivateMethodInvoker($user_authentication_model, 'hash_token');

        $token = $generate_token();
        $token_hash = $hash_token($token);
        $this->assertIsstring($token_hash);
        $this->assertNotEquals($token, $token_hash);

        $token_hash2 = $hash_token($token);
        $this->assertIsstring($token_hash2);
        $this->assertNotEquals($token_hash, $token_hash2);
    }

    public function testTokenValid(): void
    {
        $now = Time::now();
        Time::setTestNow($now);
        $user_authentication_model = new UserAuthentication();
        $user_data = [
            'user_id' => 2299488734,
        ];
        $token = $user_authentication_model->insert($user_data);
        
        $this->assertTrue($user_authentication_model->token_valid($user_data['user_id'], $token));
        $this->assertFalse($user_authentication_model->token_valid($user_data['user_id'], 'invalid'));
        $this->assertFalse($user_authentication_model->token_valid($user_data['user_id'], substr($token, 1)));
        $this->assertFalse($user_authentication_model->token_valid($user_data['user_id'], substr($token, 0, strlen($token)-1)));

        $token_expire_time_in_seconds = $this->getPrivateProperty($user_authentication_model, 'token_expire_time_in_seconds');
        Time::setTestNow($now->addSeconds($token_expire_time_in_seconds)->subSeconds(1));
        $this->assertTrue($user_authentication_model->token_valid($user_data['user_id'], $token));
        Time::setTestNow($now->addSeconds($token_expire_time_in_seconds));
        $this->assertFalse($user_authentication_model->token_valid($user_data['user_id'], $token));
        Time::setTestNow($now->addSeconds($token_expire_time_in_seconds)->addMinutes(6));
        $this->assertFalse($user_authentication_model->token_valid($user_data['user_id'], $token));
    }
}
