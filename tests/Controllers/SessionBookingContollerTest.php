<?php

namespace App\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\I18n\Time;
use App\Models\SessionBooking;
use App\Controllers\UserAuthenticationController;
use Tests\Support\Database\Seeds\UserSeeder;
use Tests\Support\Database\Seeds\SessionBookingSeeder;

class SessionBookingContollerTest extends CIUnitTestCase
{
    protected $seed = UserSeeder::class;

    use DatabaseTestTrait;
    use FeatureTestTrait;

    public function testGetSessionBookingNotFound(): void
    {
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->get('sessions/bookings/nothing');

        $response = $this->get('sessions/bookings/123');
        $response->assertNotOk();
        $response->assertStatus(404);
    }

    public function testCreateSessionBooking(): void
    {
        $user_authentication_controller = new UserAuthenticationController();
        $set_user_logged_in = $this->getPrivateMethodInvoker($user_authentication_controller, 'set_user_logged_in');
        $session_booking_model = new SessionBooking();
        $user_id = 946638323423;

        $session_booking_data = [
            'user_id' => $user_id,
            'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp(),
        ];
        $response = $this->withBodyFormat('json')->post('sessions/bookings', $session_booking_data);
        $response->assertNotOk();
        $this->assertCount(0, $session_booking_model->findAll());

        $set_user_logged_in($user_id);
        $response = $this->withSession()->withBodyFormat('json')->post('sessions/bookings', $session_booking_data);
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayHasKey('id', $response_data);
        $this->assertIsInt($response_data['id']);
        $session_booking_data['id'] = $response_data['id'];
        $this->assertArrayHasKey('user_id', $response_data);
        $this->assertArrayHasKey('start_time', $response_data);
        foreach ($session_booking_data as $key => $_) {
            $this->assertEquals($response_data[$key], $session_booking_data[$key]);
        }
        $this->assertCount(1, $session_booking_model->findAll());

        $session_booking_data = [
            'user_id' => $user_id,
            'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(10)->getTimestamp(),
        ];
        $response = $this->withSession()->withBodyFormat('json')->post('sessions/bookings', $session_booking_data);
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayHasKey('id', $response_data);
        $this->assertIsInt($response_data['id']);
        $session_booking_data['id'] = $response_data['id'];
        $this->assertArrayHasKey('user_id', $response_data);
        $this->assertArrayHasKey('start_time', $response_data);
        foreach ($session_booking_data as $key => $_) {
            $this->assertEquals($response_data[$key], $session_booking_data[$key]);
        }
        $this->assertCount(2, $session_booking_model->findAll());
    }

    public function testGetSessionBooking(): void
    {
        $user_authentication_controller = new UserAuthenticationController();
        $set_user_logged_in = $this->getPrivateMethodInvoker($user_authentication_controller, 'set_user_logged_in');
        $session_booking_model = new SessionBooking();
        $user_id = 946638323423;
        $session_booking_data = [
            'user_id' => $user_id,
            'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp(),
        ];

        // Create booking
        $set_user_logged_in($user_id);
        $response = $this->withSession()->withBodyFormat('json')->post('sessions/bookings', $session_booking_data);
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayHasKey('id', $response_data);
        $this->assertIsInt($response_data['id']);
        $session_booking_data['id'] = $response_data['id'];
        $this->assertArrayHasKey('user_id', $response_data);
        $this->assertArrayHasKey('start_time', $response_data);
        foreach ($session_booking_data as $key => $_) {
            $this->assertEquals($response_data[$key], $session_booking_data[$key]);
        }
        
        // Get booking logged in
        $this->assertCount(1, $session_booking_model->findAll());
        $response = $this->withSession()->get('sessions/bookings/' . $session_booking_data['id']);
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayHasKey('user_id', $response_data);
        $this->assertArrayHasKey('start_time', $response_data);
        foreach ($session_booking_data as $key => $_) {
            $this->assertEquals($response_data[$key], $session_booking_data[$key]);
        }

        // Get booking not logged in
        $request = $this->withSession()->post('users/authentication/logout');
        $request->assertOk();
        $response = $this->withSession()->get('sessions/bookings/' . $session_booking_data['id']);
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayNotHasKey('user_id', $response_data);
        $this->assertArrayHasKey('start_time', $response_data);
    }

    public function testSessionBookingUpdateBlocked(): void
    {
        $user_authentication_controller = new UserAuthenticationController();
        $set_user_logged_in = $this->getPrivateMethodInvoker($user_authentication_controller, 'set_user_logged_in');
        $session_booking_model = new SessionBooking();
        $user_id = 946638323423;
        $session_booking_data = [
            'user_id' => $user_id,
            'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(4),
        ];
        $session_booking_id = $session_booking_model->insert($session_booking_data);

        $session_booking_data['id'] = $session_booking_id;
        $session_booking_data['start_time'] = $session_booking_data['start_time']->addHours(1);

        // Try update with no logged in user
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->put('sessions/bookings/' . $session_booking_id, $session_booking_data);

        $response = $this->get('sessions/bookings/' . $session_booking_id);
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);

        foreach ($session_booking_data as $key => $_) {
            $this->assertEquals($response_data[$key], $session_booking_data[$key]);
        }

        // Try update with logged in user
        $set_user_logged_in($user_id);
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->withSession()->put('sessions/bookings/' . $session_booking_id, $session_booking_data);

        $response = $this->get('sessions/bookings/' . $session_booking_id);
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);

        foreach ($session_booking_data as $key => $_) {
            $this->assertEquals($response_data[$key], $session_booking_data[$key]);
        }
    }

    public function testDoubleSessionBookingBlocked(): void
    {
        $user_authentication_controller = new UserAuthenticationController();
        $set_user_logged_in = $this->getPrivateMethodInvoker($user_authentication_controller, 'set_user_logged_in');
        $user_id = 946638323423;
        $session_booking_data = [
            'user_id' => $user_id,
            'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp(),
        ];

        $set_user_logged_in($session_booking_data['user_id']);
        $response = $this->withSession()->withBodyFormat('json')->post('sessions/bookings', $session_booking_data);
        $response->assertOk();

        $session_booking_data['user_id'] = 772843;
        $set_user_logged_in($session_booking_data['user_id']);
        $response = $this->withSession()->withBodyFormat('json')->post('sessions/bookings', $session_booking_data);
        $response->assertNotOk();
    }

    public function testDeleteSessionBooking(): void
    {
        $user_authentication_controller = new UserAuthenticationController();
        $set_user_logged_in = $this->getPrivateMethodInvoker($user_authentication_controller, 'set_user_logged_in');
        $session_booking_model = new SessionBooking();
        $user_id = 946638323423;
        $session_booking_data = [
            'user_id' => $user_id,
            'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(4),
        ];

        // Create booking
        $session_booking_id = $session_booking_model->insert($session_booking_data);
        $this->assertCount(1, $session_booking_model->findAll());

        // Delete with not logged in user
        $response = $this->delete('sessions/bookings/' . $session_booking_id);
        $response->assertNotOk();
        $this->assertCount(1, $session_booking_model->findAll());

        // Delete with logged in user
        $set_user_logged_in($user_id);
        $response = $this->withSession()->delete('sessions/bookings/' . $session_booking_id);
        $response->assertOk();
        $this->assertCount(0, $session_booking_model->findAll());
    }

    public function testInvalidUser(): void
    {
        $user_authentication_controller = new UserAuthenticationController();
        $set_user_logged_in = $this->getPrivateMethodInvoker($user_authentication_controller, 'set_user_logged_in');
        $session_booking_model = new SessionBooking();
        $this->assertCount(0, $session_booking_model->findAll());

        $session_booking_data = [
            'user_id' => 1234,
            'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp(),
        ];
        $set_user_logged_in($session_booking_data['user_id']);
        $response = $this->withSession()->withBodyFormat('json')->post('sessions/bookings', $session_booking_data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertCount(0, $session_booking_model->findAll());

        $session_booking_data = [
            'user_id' => 'invalid',
            'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp(),
        ];
        $set_user_logged_in($session_booking_data['user_id']);
        $response = $this->withSession()->withBodyFormat('json')->post('sessions/bookings', $session_booking_data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertCount(0, $session_booking_model->findAll());
    }

    public function testInvalidTime(): void
    {
        $user_authentication_controller = new UserAuthenticationController();
        $set_user_logged_in = $this->getPrivateMethodInvoker($user_authentication_controller, 'set_user_logged_in');
        $session_booking_model = new SessionBooking();
        $user_id = 946638323423;
        $this->assertCount(0, $session_booking_model->findAll());
        $set_user_logged_in($user_id);

        $session_booking_data = [
            'user_id' => $user_id,
            'start_time' => 'invalid',
        ];
        $response = $this->withSession()->withBodyFormat('json')->post('sessions/bookings', $session_booking_data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertCount(0, $session_booking_model->findAll());

        $session_booking_data = [
            'user_id' => $user_id,
            'start_time' => 1.23,
        ];
        $response = $this->withSession()->withBodyFormat('json')->post('sessions/bookings', $session_booking_data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertCount(0, $session_booking_model->findAll());
    }

    public function testMissingValues(): void
    {
        $user_authentication_controller = new UserAuthenticationController();
        $set_user_logged_in = $this->getPrivateMethodInvoker($user_authentication_controller, 'set_user_logged_in');
        $session_booking_model = new SessionBooking();
        $user_id = 946638323423;
        $this->assertCount(0, $session_booking_model->findAll());
        $set_user_logged_in($user_id);

        // start_time missing
        $session_booking_data = [
            'user_id' => $user_id,
        ];
        $response = $this->withSession()->withBodyFormat('json')->post('sessions/bookings', $session_booking_data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertCount(0, $session_booking_model->findAll());

        // user_id missing
        $session_booking_data = [
            'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp(),
        ];
        $response = $this->withSession()->withBodyFormat('json')->post('sessions/bookings', $session_booking_data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertCount(0, $session_booking_model->findAll());
        
        // All missing
        $session_booking_data = [];
        $response = $this->withSession()->withBodyFormat('json')->post('sessions/bookings', $session_booking_data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertCount(0, $session_booking_model->findAll());
    }

    public function testGetByRange(): void
    {
        Time::setTestNow(Time::now());
        $this->regressDatabase();
        $this->migrateDatabase();
        $this->seed(SessionBookingSeeder::class);
        $user_authentication_controller = new UserAuthenticationController();
        $set_user_logged_in = $this->getPrivateMethodInvoker($user_authentication_controller, 'set_user_logged_in');
        $session_booking_model = new SessionBooking();
        $this->assertCount(4, $session_booking_model->findAll());

        $range_start = Time::now()->setMinute(0)->setSecond(0)->addHours(2)->getTimestamp();
        $range_end = Time::now()->setMinute(0)->setSecond(0)->addHours(14)->getTimestamp();

        // Get with no user logged in
        $response = $this->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertCount(3, $response_data);
        foreach ($response_data as $session_booking) {
            $this->assertArrayNotHasKey('id', $session_booking);
            $this->assertArrayNotHasKey('user_id', $session_booking);
            $this->assertArrayHasKey('start_time', $session_booking);
        }

        // Get with user logged in
        $set_user_logged_in(2306585);
        $response = $this->withSession()->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertCount(3, $response_data);
        $user2_session_booking = array_pop($response_data);
        $this->assertArrayNotHasKey('id', $user2_session_booking);
        $this->assertArrayNotHasKey('user_id', $user2_session_booking);
        $this->assertArrayHasKey('start_time', $user2_session_booking);
        foreach ($response_data as $session_booking) {
            $this->assertArrayHasKey('id', $session_booking);
            $this->assertArrayHasKey('user_id', $session_booking);
            $this->assertArrayHasKey('start_time', $session_booking);
        }
    }

    public function testGetByInvalidRange(): void
    {
        Time::setTestNow(Time::now());
        $this->regressDatabase();
        $this->migrateDatabase();
        $this->seed(SessionBookingSeeder::class);
        $session_booking_model = new SessionBooking();
        $this->assertCount(4, $session_booking_model->findAll());

        $range_end = Time::now()->setMinute(0)->setSecond(0)->getTimestamp();
        $range_start = Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp();
        $response = $this->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertCount(0, $response_data);

        // Negative values
        $range_end = -100;
        $range_start = Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp();
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $response = $this->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));

        $range_end = Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp();
        $range_start = -100;
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $response = $this->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));

        $range_end = -100;
        $range_start = -39934;
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $response = $this->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));

        // Decimal values
        $range_end = 1.23;
        $range_start = Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp();
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $response = $this->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));

        $range_end = Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp();
        $range_start = 1.23;
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $response = $this->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));

        $range_end = 1.23;
        $range_start = 39.934;
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $response = $this->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));

        // String values
        $range_end = 'invalid';
        $range_start = Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp();
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $response = $this->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));

        $range_end = Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp();
        $range_start = 'invalid';
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $response = $this->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));

        $range_end = 'invalid';
        $range_start = 'nothing';
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $response = $this->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));
    }

    public function testGetByInvalidRangeLoggedInUser(): void
    {
        Time::setTestNow(Time::now());
        $this->regressDatabase();
        $this->migrateDatabase();
        $this->seed(SessionBookingSeeder::class);
        $user_authentication_controller = new UserAuthenticationController();
        $set_user_logged_in = $this->getPrivateMethodInvoker($user_authentication_controller, 'set_user_logged_in');
        $session_booking_model = new SessionBooking();
        $this->assertCount(4, $session_booking_model->findAll());
        $set_user_logged_in(772843);

        $range_end = Time::now()->setMinute(0)->setSecond(0)->getTimestamp();
        $range_start = Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp();
        $response = $this->withSession()->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertCount(0, $response_data);

        // Negative values
        $range_end = -100;
        $range_start = Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp();
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $response = $this->withSession()->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));

        $range_end = Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp();
        $range_start = -100;
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $response = $this->withSession()->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));

        $range_end = -100;
        $range_start = -39934;
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $response = $this->withSession()->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));

        // Decimal values
        $range_end = 1.23;
        $range_start = Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp();
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $response = $this->withSession()->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));

        $range_end = Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp();
        $range_start = 1.23;
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $response = $this->withSession()->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));

        $range_end = 1.23;
        $range_start = 39.934;
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $response = $this->withSession()->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));

        // String values
        $range_end = 'invalid';
        $range_start = Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp();
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $response = $this->withSession()->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));

        $range_end = Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp();
        $range_start = 'invalid';
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $response = $this->withSession()->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));

        $range_end = 'invalid';
        $range_start = 'nothing';
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $response = $this->withSession()->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));
    }
}