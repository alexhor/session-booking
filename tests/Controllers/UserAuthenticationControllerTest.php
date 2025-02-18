<?php

namespace App\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\Seeds\UserSeeder;
use App\Models\UserAuthentication;
use CodeIgniter\Events\Events;
use CodeIgniter\I18n\Time;

class UserAuthenticationControllerTest extends CIUnitTestCase
{
    protected $seed = UserSeeder::class;

    use DatabaseTestTrait;
    use FeatureTestTrait;

    public function testCreateToken(): void
    {
        Time::setTestNow(Time::now());
        $user_data = [
            'email' => 'test.testo@example.com',
        ];
        $previous_token = null;
        $email;
        $token;
        
        Events::on('email', function($archive) use (&$email, &$token) {
            preg_match('#/login\?email=([^&]+)&token=(.{72})#', $archive['body'], $matches);
            $email = urldecode($matches[1]);
            $token = urldecode($matches[2]);
        });

        for ($i=0; $i<5; $i++) {
            Time::setTestNow(Time::now()->addMinutes(1));
            $response = $this->withBodyFormat('json')->post('users/authentication', $user_data);
            $response->assertOk();
            $this->assertIsString(json_decode($response->getJson(), true));
            $this->assertEventTriggered('email');
            
            $this->assertEquals($user_data['email'], $email);
            $this->assertNotEquals($previous_token, $token);
            $previous_token = $token;
        }
    }

    public function testCreateTokenNonexistingUser(): void
    {
        $user_data = [
            'email' => 'nobody@invalid.com',
        ];
        $email_send = false;
        Events::on('email', function($archive) use (&$email_send) {
            $email_send = true;
        });

        $response = $this->withBodyFormat('json')->post('users/authentication', $user_data);
        $response->assertNotOk();
        $this->assertFalse($email_send);


        $user_data = [
            'email' => 'test.testo@example.com',
        ];
        $response = $this->withBodyFormat('json')->post('users/authentication', $user_data);
        $response->assertOk();
        $this->assertTrue($email_send);
    }

    public function testCreateTokenInvalidEmail(): void
    {
        $user_data = [
            'email' => 'invalid',
        ];
        $email_send = false;
        Events::on('email', function($archive) use (&$email_send) {
            $email_send = true;
        });

        $response = $this->withBodyFormat('json')->post('users/authentication', $user_data);
        $response->assertNotOk();
        $this->assertFalse($email_send);
    }

    public function testCreateTokenMissingEmail(): void
    {
        $user_data = [];
        $email_send = false;
        Events::on('email', function($archive) use (&$email_send) {
            $email_send = true;
        });

        $response = $this->withBodyFormat('json')->post('users/authentication', $user_data);
        $response->assertNotOk();
        $this->assertFalse($email_send);
    }

    private function testLoginUser($user_id=772843, $user_email='test.testo@example.com'): void
    {
        $user_data = [
            'email' => $user_email,
        ];
        $email;
        $token;
        
        Events::on('email', function($archive) use ($user_data, &$email, &$token) {
            preg_match('#/login\?email=([^&]+)&token=(.{72})#', $archive['body'], $matches);
            $email = urldecode($matches[1]);
            $token = urldecode($matches[2]);
        });
        
        $response = $this->withBodyFormat('json')->post('users/authentication', $user_data);
        $response->assertOk();
        $this->assertIsString(json_decode($response->getJson(), true));
        $this->assertEventTriggered('email');
            
        $this->assertEquals($user_data['email'], $email);
        $response = $this->post('users/authentication/login', ['email' => $email, 'token' => $token]);
        $response->assertOk();
        $response->assertSessionHas('logged_in_user_id', $user_id);

        $response = $this->withSession()->get('/');
        $response->assertSessionHas('logged_in_user_id', $user_id);
    }

    public function testLoginUserInvalidToken(): void
    {
        Time::setTestNow(Time::now()->addMinutes(1));
        $user_id = 772843;
        $user_data = [
            'email' => 'test.testo@example.com',
        ];
        
        $response = $this->withBodyFormat('json')->post('users/authentication', $user_data);
        $response->assertOk();
        $this->assertIsString(json_decode($response->getJson(), true));
        $this->assertEventTriggered('email');
            
        $user_data['token'] = 'tooshort';
        $response = $this->post('users/authentication/login', $user_data);
        $response->assertNotOk();
        $response->assertSessionMissing('logged_in_user_id');
        $response = $this->withSession()->get('/');
        $response->assertSessionMissing('logged_in_user_id');

        $user_data['token'] = 123;
        $response = $this->post('users/authentication/login', $user_data);
        $response->assertNotOk();
        $response->assertSessionMissing('logged_in_user_id');
        $response = $this->withSession()->get('/');
        $response->assertSessionMissing('logged_in_user_id');
    }

    public function testLoginUserMissingValues(): void
    {
        // Missing token
        $user_data = [
            'email' => 'test.testo@example.com',
        ];
        $response = $this->post('users/authentication/login', $user_data);
        $response->assertNotOk();
        $response->assertSessionMissing('logged_in_user_id');

        // Missing email
        $user_data = [
            'token' => '123456789012345678901234567890123456789012345678901234567890123456789012',
        ];
        $response = $this->post('users/authentication/login', $user_data);
        $response->assertNotOk();
        $response->assertSessionMissing('logged_in_user_id');

        // Missing all
        $user_data = [];
        $response = $this->post('users/authentication/login', $user_data);
        $response->assertNotOk();
        $response->assertSessionMissing('logged_in_user_id');
    }

    public function testLogoutUser(): void
    {
        $this->testLoginUser(772843, 'test.testo@example.com');

        $response = $this->withSession()->post('users/authentication/logout');
        $response->assertOk();
        $response->assertIsString(json_decode($response->getJson(), true));
        $response->assertSessionMissing('logged_in_user_id');
        $response->assertSessionMissing('admin_logged_in_user_id');

        $response = $this->withSession()->get('/');
        $response->assertSessionMissing('logged_in_user_id');
        $response->assertSessionMissing('admin_logged_in_user_id');
    }

    public function testLogoutUserNoUserLoggedIn(): void
    {
        $response = $this->post('users/authentication/logout');
        $response->assertOk();
        $response->assertIsString(json_decode($response->getJson(), true));
        $response->assertSessionMissing('logged_in_user_id');
        $response->assertSessionMissing('admin_logged_in_user_id');
    }

    public function testGetLogedInUser(): void
    {
        $user_authentication_controller = new UserAuthenticationController();
        $set_user_logged_in = $this->getPrivateMethodInvoker($user_authentication_controller, 'set_user_logged_in');
        $user_id = 772843;
        
        $response = $this->get('users/authentication/login');
        $response->assertOk();
        $this->assertFalse(json_decode($response->getJson(), true));

        $set_user_logged_in($user_id);
        $response = $this->withSession()->get('users/authentication/login');
        $response->assertOk();
        $this->assertEquals($user_id, json_decode($response->getJson(), true));

        $this->withSession()->post('users/authentication/logout');
        $response = $this->withSession()->get('users/authentication/login');
        $response->assertOk();
        $this->assertFalse(json_decode($response->getJson(), true));
    }
}