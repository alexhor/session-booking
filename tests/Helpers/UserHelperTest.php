<?php

use CodeIgniter\Test\CIUnitTestCase;
use App\Controllers\UserAuthenticationController;
use App\Helpers\UserHelper;

class UserHelperTest extends CIUnitTestCase
{
    public function testGetLoggedInUser()
    {
        $user_authentication_controller = new UserAuthenticationController();
        $set_user_logged_in = $this->getPrivateMethodInvoker($user_authentication_controller, 'set_user_logged_in');

        $user_id = 12345;
        $set_user_logged_in($user_id);
        $this->assertEquals($user_id, UserHelper::get_logged_in_user());
    }

    public function testGetNoLoggedInUser()
    {
        $this->assertFalse(UserHelper::get_logged_in_user());
    }

    public function testIsLoggedInUser()
    {
        $user_authentication_controller = new UserAuthenticationController();
        $set_user_logged_in = $this->getPrivateMethodInvoker($user_authentication_controller, 'set_user_logged_in');

        $user_id = 12345;
        $set_user_logged_in($user_id);
        $this->assertTrue(UserHelper::is_logged_in_user($user_id));
        $this->assertFalse(UserHelper::is_logged_in_user(123));
    }

    public function testIsNoLoggedInUser()
    {
        $this->assertFalse(UserHelper::get_logged_in_user(123));
    }

    public function testInvalidLoggedInUser()
    {
        $this->assertFalse(UserHelper::get_logged_in_user('invalid'));
    }
}
