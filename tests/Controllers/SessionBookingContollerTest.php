<?php

namespace App\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\I18n\Time;
use App\Models\SessionBooking;
use App\Controllers\UserAuthenticationController;
use Tests\Support\Database\Seeds\SessionBookingSeeder;
use CodeIgniter\Shield\Test\AuthenticationTesting;

class SessionBookingContollerTest extends CIUnitTestCase
{
    protected $seed = SessionBookingSeeder::class;

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
        $session_booking_model = new SessionBooking();
        $user = auth()->getProvider()->findById(946638323423);

        $session_booking_data = [
            'user_id' => $user->id,
            'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp(),
        ];
        $response = $this->withBodyFormat('json')->post('sessions/bookings', $session_booking_data);
        $response->assertNotOk();
        $this->assertCount(7, $session_booking_model->findAll());

        $response = $this->actingAs($user)->withBodyFormat('json')->post('sessions/bookings', $session_booking_data);
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayHasKey('message', $response_data);
        $this->assertIsString($response_data['message']);
        $this->assertArrayHasKey('data', $response_data);
        $this->assertIsArray($response_data['data']);
        $response_data = $response_data['data'];

        $this->assertArrayHasKey('id', $response_data);
        $this->assertIsInt($response_data['id']);
        $session_booking_data['id'] = $response_data['id'];
        $this->assertArrayHasKey('user_id', $response_data);
        $this->assertArrayHasKey('start_time', $response_data);
        foreach ($session_booking_data as $key => $_) {
            $this->assertEquals($response_data[$key], $session_booking_data[$key]);
        }
        $this->assertCount(8, $session_booking_model->findAll());

        $session_booking_data = [
            'user_id' => $user->id,
            'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(10)->getTimestamp(),
        ];
        $response = $this->actingAs($user)->withBodyFormat('json')->post('sessions/bookings', $session_booking_data);
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayHasKey('message', $response_data);
        $this->assertIsString($response_data['message']);
        $this->assertArrayHasKey('data', $response_data);
        $this->assertIsArray($response_data['data']);
        $response_data = $response_data['data'];
        
        $this->assertArrayHasKey('id', $response_data);
        $this->assertIsInt($response_data['id']);
        $session_booking_data['id'] = $response_data['id'];
        $this->assertArrayHasKey('user_id', $response_data);
        $this->assertArrayHasKey('start_time', $response_data);
        foreach ($session_booking_data as $key => $_) {
            $this->assertEquals($response_data[$key], $session_booking_data[$key]);
        }
        $this->assertCount(9, $session_booking_model->findAll());
    }

    public function testGetSessionBooking(): void
    {
        $session_booking_model = new SessionBooking();
        // Get booking logged in
        $user = auth()->getProvider()->findById(2306585);
        $response = $this->actingAs($user)->get('sessions/bookings/1');
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayHasKey('title', $response_data);
        $this->assertArrayHasKey('title_is_public', $response_data);
        $this->assertArrayHasKey('description', $response_data);
        $this->assertArrayHasKey('description_is_public', $response_data);
        $this->assertArrayHasKey('user_id', $response_data);
        $this->assertArrayHasKey('start_time', $response_data);

        $response = $this->actingAs($user)->get('sessions/bookings/3');
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertEquals($response_data['title'], '');
        $this->assertFalse($response_data['title_is_public']);
        $this->assertEquals($response_data['description'], '');
        $this->assertFalse($response_data['description_is_public']);
        $this->assertArrayHasKey('user_id', $response_data);
        $this->assertArrayHasKey('start_time', $response_data);

        $response = $this->actingAs($user)->get('sessions/bookings/4');
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayHasKey('title', $response_data);
        $this->assertArrayHasKey('title_is_public', $response_data);
        $this->assertArrayHasKey('description', $response_data);
        $this->assertArrayHasKey('description_is_public', $response_data);
        $this->assertArrayNotHasKey('user_id', $response_data);
        $this->assertArrayHasKey('start_time', $response_data);
        
        // Get booking admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $session_booking_model = new SessionBooking();
        $response = $this->actingAs($adminUser)->get('sessions/bookings/1');
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayHasKey('title', $response_data);
        $this->assertArrayHasKey('title_is_public', $response_data);
        $this->assertArrayHasKey('description', $response_data);
        $this->assertArrayHasKey('description_is_public', $response_data);
        $this->assertArrayHasKey('user_id', $response_data);
        $this->assertArrayHasKey('id', $response_data);
        $this->assertArrayHasKey('start_time', $response_data);

        $response = $this->actingAs($adminUser)->get('sessions/bookings/4');
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayHasKey('title', $response_data);
        $this->assertArrayHasKey('title_is_public', $response_data);
        $this->assertArrayHasKey('description', $response_data);
        $this->assertArrayHasKey('description_is_public', $response_data);
        $this->assertArrayHasKey('user_id', $response_data);
        $this->assertArrayHasKey('id', $response_data);
        $this->assertArrayHasKey('start_time', $response_data);

        $response = $this->actingAs($adminUser)->get('sessions/bookings/3');
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayHasKey('title', $response_data);
        $this->assertEquals($response_data['title'], '');
        $this->assertFalse($response_data['title_is_public']);
        $this->assertEquals($response_data['description'], '');
        $this->assertFalse($response_data['description_is_public']);
        $this->assertArrayHasKey('user_id', $response_data);
        $this->assertArrayHasKey('id', $response_data);
        $this->assertArrayHasKey('start_time', $response_data);

        // Get booking not logged in
        $this->clearAuth();
        $response = $this->get('sessions/bookings/1');
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayNotHasKey('title', $response_data);
        $this->assertArrayNotHasKey('title_is_public', $response_data);
        $this->assertArrayNotHasKey('description', $response_data);
        $this->assertArrayNotHasKey('description_is_public', $response_data);
        $this->assertArrayNotHasKey('user_id', $response_data);
        $this->assertArrayNotHasKey('id', $response_data);
        $this->assertArrayHasKey('start_time', $response_data);

        $response = $this->get('sessions/bookings/4');
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertArrayHasKey('title', $response_data);
        $this->assertArrayHasKey('title_is_public', $response_data);
        $this->assertArrayHasKey('description', $response_data);
        $this->assertArrayHasKey('description_is_public', $response_data);
        $this->assertArrayNotHasKey('user_id', $response_data);
        $this->assertArrayNotHasKey('id', $response_data);
        $this->assertArrayHasKey('start_time', $response_data);
    }

    public function testSessionBookingUpdateBlocked(): void
    {
        $user = auth()->getProvider()->findById(2306585);
        $session_booking_data = [
            'id' => '3',
            'user_id' => $user->id,
            'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(14),
        ];
        
        // Try update with no logged in user
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->put('sessions/bookings/' . $session_booking_data['id'], $session_booking_data);

        // Try update with logged in user
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->actingAs($user)->put('sessions/bookings/' . $session_booking_data['id'], $session_booking_data);

        // Try update with logged in admin
        $adminUser = auth()->getProvider()->findById(772843);
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->actingAs($adminUser)->put('sessions/bookings/' . $session_booking_data['id'], $session_booking_data);
    }

    public function testDoubleSessionBookingBlocked(): void
    {
        $user = auth()->getProvider()->findById(946638323423);
        $session_booking_data = [
            'user_id' => $user->id,
            'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp(),
        ];

        // Initial creating succeeds
        $response = $this->actingAs($user)->withBodyFormat('json')->post('sessions/bookings', $session_booking_data);
        $response->assertOk();
        // Try double booking with same user
        $response = $this->actingAs($user)->withBodyFormat('json')->post('sessions/bookings', $session_booking_data);
        $response->assertNotOk();

        // Try double booking with other user
        $user = auth()->getProvider()->findById(2306585);
        $response = $this->actingAs($user)->withBodyFormat('json')->post('sessions/bookings', $session_booking_data);
        $response->assertNotOk();
    }

    public function testDeleteSessionBooking(): void
    {
        $user = auth()->getProvider()->findById(2306585);
        $session_booking_model = new SessionBooking();
        $this->assertCount(7, $session_booking_model->findAll());

        // Delete with not logged in user
        $response = $this->delete('sessions/bookings/2');
        $response->assertNotOk();
        $this->assertCount(7, $session_booking_model->findAll());

        // Delete with logged in user
        $response = $this->actingAs($user)->delete('sessions/bookings/2');
        $response->assertOk();
        $this->assertCount(6, $session_booking_model->findAll());


        $this->regressDatabase();
        $this->migrateDatabase();
        $this->seed(SessionBookingSeeder::class);
        // Delete with logged in admin
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->delete('sessions/bookings/2');
        $response->assertOk();
        $this->assertCount(6, $session_booking_model->findAll());
    }

    public function testInvalidUser(): void
    {
        $user = auth()->getProvider()->findById(2306585);
        $session_booking_model = new SessionBooking();
        $this->assertCount(7, $session_booking_model->findAll());

        $session_booking_data = [
            'user_id' => '1234',
            'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp(),
        ];
        $response = $this->actingAs($user)->withBodyFormat('json')->post('sessions/bookings', $session_booking_data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertCount(7, $session_booking_model->findAll());

        $session_booking_data = [
            'user_id' => 'invalid',
            'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp(),
        ];
        $response = $this->actingAs($user)->withBodyFormat('json')->post('sessions/bookings', $session_booking_data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertCount(7, $session_booking_model->findAll());
    }

    public function testInvalidTime(): void
    {
        $user = auth()->getProvider()->findById(2306585);
        $session_booking_model = new SessionBooking();
        $this->assertCount(7, $session_booking_model->findAll());

        $session_booking_data = [
            'user_id' => $user->id,
            'start_time' => 'invalid',
        ];
        $response = $this->actingAs($user)->withBodyFormat('json')->post('sessions/bookings', $session_booking_data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertCount(7, $session_booking_model->findAll());

        $session_booking_data = [
            'user_id' => $user->id,
            'start_time' => 1.23,
        ];
        $response = $this->actingAs($user)->withBodyFormat('json')->post('sessions/bookings', $session_booking_data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertCount(7, $session_booking_model->findAll());
    }

    public function testMissingValues(): void
    {
        $user = auth()->getProvider()->findById(2306585);
        $session_booking_model = new SessionBooking();
        $this->assertCount(7, $session_booking_model->findAll());

        // start_time missing
        $session_booking_data = [
            'user_id' => $user->id,
        ];
        $response = $this->actingAs($user)->withBodyFormat('json')->post('sessions/bookings', $session_booking_data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertCount(7, $session_booking_model->findAll());

        // user_id missing
        $session_booking_data = [
            'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp(),
        ];
        $response = $this->actingAs($user)->withBodyFormat('json')->post('sessions/bookings', $session_booking_data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertCount(7, $session_booking_model->findAll());
        
        // All missing
        $session_booking_data = [];
        $response = $this->actingAs($user)->withBodyFormat('json')->post('sessions/bookings', $session_booking_data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertCount(7, $session_booking_model->findAll());
    }

    public function testGetByRange(): void
    {
        Time::setTestNow(Time::now());
        $this->regressDatabase();
        $this->migrateDatabase();
        $this->seed(SessionBookingSeeder::class);
        $session_booking_model = new SessionBooking();
        $this->assertCount(7, $session_booking_model->findAll());

        $range_start = Time::now()->setMinute(0)->setSecond(0)->addHours(2)->getTimestamp();
        $range_end = Time::now()->setMinute(0)->setSecond(0)->addHours(101)->getTimestamp();

        // Get with no user logged in
        $response = $this->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertCount(3, $response_data);
        for ($i=0; $i<3; $i++) {
            $session_booking = $response_data[$i];
            $this->assertArrayNotHasKey('id', $session_booking);
            $this->assertArrayNotHasKey('user_id', $session_booking);
            $this->assertArrayHasKey('start_time', $session_booking);
            if (2 == $i) {
                $this->assertArrayHasKey('title', $session_booking);
                $this->assertArrayHasKey('title_is_public', $session_booking);
                $this->assertArrayHasKey('description', $session_booking);
                $this->assertArrayHasKey('description_is_public', $session_booking);
            }
            else {
                $this->assertArrayNotHasKey('title', $session_booking);
                $this->assertArrayNotHasKey('title_is_public', $session_booking);
                $this->assertArrayNotHasKey('description', $session_booking);
                $this->assertArrayNotHasKey('description_is_public', $session_booking);
            }
        }

        // Get with user logged in
        $user = auth()->getProvider()->findById(2306585);
        $response = $this->actingAs($user)->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertCount(3, $response_data);
        for ($i=0; $i<3; $i++) {
            $session_booking = $response_data[$i];
            if (2 == $i) {
                $this->assertArrayNotHasKey('id', $session_booking);
                $this->assertArrayNotHasKey('user_id', $session_booking);
                
            }
            else {
                $this->assertArrayHasKey('id', $session_booking);
                $this->assertArrayHasKey('user_id', $session_booking);
            }

            $this->assertArrayHasKey('start_time', $session_booking);
            $this->assertArrayHasKey('title', $session_booking);
            $this->assertArrayHasKey('title_is_public', $session_booking);
            $this->assertArrayHasKey('description', $session_booking);
            $this->assertArrayHasKey('description_is_public', $session_booking);
        }

        // Get with adming logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertCount(3, $response_data);
        for ($i=0; $i<3; $i++) {
            $session_booking = $response_data[$i];
            $this->assertArrayHasKey('id', $session_booking);
            $this->assertArrayHasKey('user_id', $session_booking);
            $this->assertArrayHasKey('start_time', $session_booking);
            $this->assertArrayHasKey('title', $session_booking);
            $this->assertArrayHasKey('title_is_public', $session_booking);
            $this->assertArrayHasKey('description', $session_booking);
            $this->assertArrayHasKey('description_is_public', $session_booking);
        }
    }

    public function testGetByInvalidRange(): void
    {
        Time::setTestNow(Time::now());
        $this->regressDatabase();
        $this->migrateDatabase();
        $this->seed(SessionBookingSeeder::class);
        $session_booking_model = new SessionBooking();
        $this->assertCount(7, $session_booking_model->findAll());

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
        $user = auth()->getProvider()->findById(772843);
        $session_booking_model = new SessionBooking();
        $this->assertCount(7, $session_booking_model->findAll());

        $range_end = Time::now()->setMinute(0)->setSecond(0)->getTimestamp();
        $range_start = Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp();
        $response = $this->actingAs($user)->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertCount(0, $response_data);

        // Negative values
        $range_end = -100;
        $range_start = Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp();
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $response = $this->actingAs($user)->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));

        $range_end = Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp();
        $range_start = -100;
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $response = $this->actingAs($user)->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));

        $range_end = -100;
        $range_start = -39934;
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $response = $this->actingAs($user)->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));

        // Decimal values
        $range_end = 1.23;
        $range_start = Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp();
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $response = $this->actingAs($user)->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));

        $range_end = Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp();
        $range_start = 1.23;
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $response = $this->actingAs($user)->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));

        $range_end = 1.23;
        $range_start = 39.934;
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $response = $this->actingAs($user)->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));

        // String values
        $range_end = 'invalid';
        $range_start = Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp();
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $response = $this->actingAs($user)->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));

        $range_end = Time::now()->setMinute(0)->setSecond(0)->addHours(4)->getTimestamp();
        $range_start = 'invalid';
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $response = $this->actingAs($user)->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));

        $range_end = 'invalid';
        $range_start = 'nothing';
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $response = $this->actingAs($user)->withBodyFormat('json')->get('sessions/bookings/' . strval($range_start) . '/' . strval($range_end));
    }
}