<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\I18n\Time;
use Tests\Support\Database\Seeds\UserSeeder;
use App\Models\SessionBooking;
use App\Models\User;


/**
 * @internal
 */
final class SessionBookingTest extends CIUnitTestCase
{
    protected $seed = UserSeeder::class;

    use DatabaseTestTrait;

    public function testCreateSessionBooking(): void
    {
        $session_booking_model = new SessionBooking();
        $user_id = 946638323423;

        $this->assertCount(0, $session_booking_model->findAll());

        $session_booking_data = [
            'user_id' => $user_id,
            'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(2),
        ];
        $session_booking_model->insert($session_booking_data);
        $this->assertCount(1, $session_booking_model->findAll());

        $session_booking_data = [
            'user_id' => $user_id,
            'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(4),
        ];
        $session_booking_model->insert($session_booking_data);
        $this->assertCount(2, $session_booking_model->findAll());
    }

    public function testDeleteSessionBooking(): void
    {
        $session_booking_model = new SessionBooking();
        $user_id = 946638323423;

        $this->assertCount(0, $session_booking_model->findAll());

        $session_booking_data = [
            'user_id' => $user_id,
            'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(4),
        ];
        $session_booking_id = $session_booking_model->insert($session_booking_data);
        $this->assertCount(1, $session_booking_model->findAll());

        $session_booking_model->delete($session_booking_id);
        $this->assertCount(0, $session_booking_model->findAll());
    }

    public function testUniqueStartTime(): void
    {
        $session_booking_model = new SessionBooking();
        $user_id = 946638323423;

        $this->assertCount(0, $session_booking_model->findAll());

        $session_booking_data = [
            'user_id' => $user_id,
            'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(4),
        ];
        $session_booking_id = $session_booking_model->insert($session_booking_data);
        $this->assertCount(1, $session_booking_model->findAll());

        $this->expectException(CodeIgniter\Database\Exceptions\DatabaseException::class);
        $session_booking_model->insert($session_booking_data);
        $this->assertCount(1, $session_booking_model->findAll());

        $session_booking_data['user_id'] = 772843;
        $this->expectException(CodeIgniter\Database\Exceptions\DatabaseException::class);
        $session_booking_model->insert($session_booking_data);
        $this->assertCount(1, $session_booking_model->findAll());
    }

    public function testMissingValues(): void
    {
        $session_booking_model = new SessionBooking();
        $user_id = 946638323423;

        $this->assertCount(0, $session_booking_model->findAll());

        // user_id missing
        $session_booking_data = [
            'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(4),
        ];
        $this->expectException(CodeIgniter\Database\Exceptions\DatabaseException::class);
        $session_booking_model->insert($session_booking_data);
        $this->assertCount(0, $session_booking_model->findAll());

        // start_time missing
        $session_booking_data = [
            'user_id' => $user_id,
        ];
        $this->expectException(CodeIgniter\Database\Exceptions\DatabaseException::class);
        $session_booking_model->insert($session_booking_data);
        $this->assertCount(0, $session_booking_model->findAll());
        
        // All missing
        $session_booking_data = [];
        $this->expectException(CodeIgniter\Database\Exceptions\DatabaseException::class);
        $session_booking_model->insert($session_booking_data);
        $this->assertCount(0, $session_booking_model->findAll());
    }

    public function testUpdateNotPossible(): void
    {
        $session_booking_model = new SessionBooking();
        $user_id = 946638323423;

        $this->assertCount(0, $session_booking_model->findAll());

        $session_booking_data = [
            'user_id' => $user_id,
            'start_time' => Time::now()->setMinute(0)->setSecond(0)->addHours(4),
        ];
        $session_booking_id = $session_booking_model->insert($session_booking_data);
        $this->assertCount(1, $session_booking_model->findAll());

        $this->expectException(\BadMethodCallException::class);
        $session_booking_model->update($session_booking_id, $session_booking_data);
        $this->assertCount(1, $session_booking_model->findAll());

        $session_booking_data['start_time'] = $session_booking_data['start_time']->addHours(1);
        $this->expectException(\BadMethodCallException::class);
        $session_booking_model->update($session_booking_id, $session_booking_data);
        $this->assertCount(1, $session_booking_model->findAll());
    }
}
