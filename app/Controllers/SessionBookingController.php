<?php

namespace App\Controllers;

use App\Models\SessionBooking;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\I18n\Time;

class SessionBookingController extends ResourceController
{
    private $session_booking;
	private $session;

    public function __construct()
    {
        helper(['form', 'url', 'session']);
		$this->session_booking = new SessionBooking();
    }

    /**
     * Filter out all session booking data the user isn't allowed to see
     */
    protected function filter_session_booking_outgoing_data($session_booking) {
        // Show full information only to the user who booked the session and users with permission
        if (auth()->user() != null && (auth()->user()->can('session-bookings.show-details') || user_id() == $session_booking['user_id'])) return $session_booking;
        else {
            // Always add start time
            $new_session_booking = [
                'start_time' => $session_booking['start_time'],
            ];
            // Add public title if given
            if ($session_booking['title_is_public']) {
                $new_session_booking['title'] = $session_booking['title'];
                $new_session_booking['title_is_public'] = $session_booking['title_is_public'];
            }
            // Add public description if given
            if ($session_booking['description_is_public']) {
                $new_session_booking['description'] = $session_booking['description'];
                $new_session_booking['description_is_public'] = $session_booking['description_is_public'];
            }

            return $new_session_booking;
        }
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
            $session_booking = $this->filter_session_booking_outgoing_data($session_booking);
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
            $session_booking = $this->filter_session_booking_outgoing_data($session_booking);
            return $this->respond($session_booking);
        }
        return $this->failNotFound(lang('Validation.session_booking.not_found'));
    }

    /**
     * Create a new resource object, from "posted" parameters.
     *
     * @return ResponseInterface
     */
    public function create()
    {
        // Check correct user is logged in
        if (null == auth()->user() || !auth()->user()->can('session-bookings.create')) {
            return $this->failUnauthorized();
        }

        // Validate data
        $validation = $this->validate([
            'user_id' => [
                'label' => 'Validation.user.id.label',
                'rules' => 'required|integer|is_not_unique[users.id]',
                'errors' => [
                    'required' => 'Validation.user.id.required',
                    'integer' => 'Validation.user.id.integer',
                    'is_not_unique' => 'Validation.user.id.not_found',
                ],
            ],
            'start_time' => [
                'label' => 'Validation.session_booking.start_time.label',
                'rules' => 'required|integer',
                'errors' => [
                    'required' => 'Validation.session_booking.start_time.required',
                    'integer' => 'Validation.session_booking.start_time.integer',
                ],
            ],
            'title_is_public' => 'if_exist|in_list[true,false,1,0]',
            'description_is_public' => 'if_exist|in_list[true,false,1,0]',
        ]);
        if (!$validation) {
            return $this->failValidationErrors($this->validator->getErrors());
        }
        $session_bookingData = [
            'user_id' => $this->request->getVar('user_id'),
            'start_time' => Time::createFromTimestamp($this->request->getVar('start_time'), Time::now()->getTimezone()),
        ];

        // Check session isn't booked yet
        $existing_session_booking = $this->session_booking->where('start_time', $session_bookingData['start_time'])->findAll();
        if (0 != count($existing_session_booking)) {
            return $this->failResourceExists(lang('Validation.session_booking.taken'));
        }
        
        // Add title if given
        if (null != $this->request->getVar('title')) {
            $session_bookingData['title'] = $this->request->getVar('title');
            if (auth()->user()->can('session-bookings.set-details-public') && null != $this->request->getVar('title_is_public')) {
                $session_bookingData['title_is_public'] = $this->request->getVar('title_is_public');
            }
            else {
                $session_bookingData['title_is_public'] = false;
            }
        }
        // Add description if given
        if (null != $this->request->getVar('description')) {
            $session_bookingData['description'] = $this->request->getVar('description');
            if (auth()->user()->can('session-bookings.set-details-public') && null != $this->request->getVar('description_is_public')) {
                $session_bookingData['description_is_public'] = $this->request->getVar('description_is_public');
            }
            else {
                $session_bookingData['description_is_public'] = false;
            }
        }

        // Create booking
        $session_bookingId = $this->session_booking->insert($session_bookingData);
        if ($session_bookingId) {
            $session_booking = $this->session_booking->find($session_bookingId);
            $session_booking['start_time'] = $session_booking['start_time']->getTimestamp();
            return $this->respondCreated([
                'message' => lang('Validation.session_booking.created'),
                'data' => $session_booking
            ]);
        }
        return $this->fail(lang('Validation.session_booking.creating_failed'));
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
            if (auth()->user() == null || (!auth()->user()->can('session-bookings.delete') && user_id() != $session_booking['user_id'])) {
                return $this->failUnauthorized();
            }
            
            $response = $this->session_booking->where('id', $id)->delete();
            if ($response) {
                return $this->respond(lang('Validation.session_booking.deleted'));
            }
            return $this->fail(lang('Validation.session_booking.deleting_failed'));
        }
        return $this->failNotFound(lang('Validation.session_booking.not_found'));
    }
}
