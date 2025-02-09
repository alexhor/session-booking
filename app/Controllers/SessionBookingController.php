<?php

namespace App\Controllers;

use App\Models\SessionBooking;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\I18n\Time;
use App\Helpers\UserHelper;

class SessionBookingController extends ResourceController
{
    private $session_booking;
	private $session;

    public function __construct()
    {
        helper(['form', 'url', 'session']);
		$this->session_booking = new SessionBooking();
        $this->session = service('session');
    }

    /**
     * Return an array of resource objects, themselves in array format.
     *
     * @return ResponseInterface
     */
    public function get_by_range($date_from, $date_to)
    {
        $date_from = Time::createFromTimestamp($date_from, Time::now()->timezone);
        $date_to = Time::createFromTimestamp($date_to, Time::now()->timezone);
        $session_bookings = $this->session_booking->get_by_range($date_from, $date_to);
        foreach ($session_bookings as &$session_booking) {
            $session_booking['start_time'] = $session_booking['start_time']->getTimestamp();
            // Show full information only to the user who booked the session
            if (!UserHelper::is_logged_in_user($session_booking['user_id'])) {
                $session_booking = [
                    'start_time' => $session_booking['start_time'],
                ];
            }
        }
        return $this->respond($session_bookings);
    }

    /**
     * Return the properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function show($id = null)
    {
        $id = intval($id);
        $session_booking = $this->session_booking->find($id);
        if ($session_booking) {
            $session_booking['start_time'] = $session_booking['start_time']->getTimestamp();
            if (!UserHelper::is_logged_in_user($session_booking['user_id'])) {
                $session_booking = [
                    'start_time' => $session_booking['start_time'],
                ];
            }
            return $this->respond($session_booking);
        }
        return $this->failNotFound('Sorry! no session booking found');
    }

    /**
     * Create a new resource object, from "posted" parameters.
     *
     * @return ResponseInterface
     */
    public function create()
    {
        // Validate data
        $validation = $this->validate([
            'user_id' => 'required|integer|is_not_unique[users.id]',
            'start_time' => 'required|integer',
        ]);
        if (!$validation) {
            return $this->failValidationErrors($this->validator->getErrors());
        }
        $session_bookingData = [
            'user_id' => $this->request->getVar('user_id'),
            'start_time' => Time::createFromTimestamp($this->request->getVar('start_time'), Time::now()->getTimezone()),
        ];
        
        // Check correct user is logged in
        if (!UserHelper::is_logged_in_user($session_bookingData['user_id'])) {
            return $this->failUnauthorized();
        }

        // Check session isn't booked yet
        $existing_session_booking = $this->session_booking->where('start_time', $session_bookingData['start_time'])->findAll();
        if (0 != count($existing_session_booking)) {
            return $this->failResourceExists('Session already booked');
        }


        // Create booking
        $session_bookingId = $this->session_booking->insert($session_bookingData);
        if ($session_bookingId) {
            $session_booking = $this->session_booking->find($session_bookingId);
            $session_booking['start_time'] = $session_booking['start_time']->getTimestamp();
            return $this->respondCreated($session_booking);
        }
        return $this->fail('Sorry! no session_booking created');
    }

    /**
     * Delete the designated resource object from the model.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function delete($id = null)
    {
        $id = intval($id);
        
        $session_booking = $this->session_booking->find($id);
        if ($session_booking) {
            if (!UserHelper::is_logged_in_user($session_booking['user_id'])) {
                return $this->failUnauthorized();
            }
            
            $response = $this->session_booking->where('id', $id)->delete();
            if ($response) {
                return $this->respond($session_booking);
            }
            return $this->fail('Sorry! not deleted');
        }
        return $this->failNotFound('Sorry! no session_booking found');
    }
}
