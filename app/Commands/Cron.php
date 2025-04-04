<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\I18n\Time;
use App\Models\SessionBooking;

class Cron extends BaseCommand
{
    protected $group       = 'Cron';
    protected $name        = 'email:reminder';
    protected $description = 'Send email reminders for all upcoming appointments in the configured timespan';

    public function run(array $params)
    {
        helper(['email', 'setting']);
        $sessionBookingModel = new SessionBooking();
        $reminderEmailTimespan = setting('App.reminderEmailTimespan');

        // Get all session bookings in the reminderEmailTimespan timerange where a reminder has not been send yet
        $session_bookings_list = $sessionBookingModel->get_by_range_reminder_not_send(Time::now(), Time::now()->addSeconds($reminderEmailTimespan));
        // Get email template
        $langContext = 'lang:' . service('request')->getLocale();
        $subjectTemplate = setting()->get('Email.sessionBookingReminderTemplateSubject', $langContext);
        $bodyTemplate = setting()->get('Email.sessionBookingReminderTemplateHtml', $langContext);
        
        foreach ($session_bookings_list as $booking) {
            // Get user object from booking
            $user = auth()->getProvider()->findById($booking['user_id']);
            if (!$user) continue;
            // Get email template variables
            $templateVariables = [
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'title' => $booking['title'],
                'title_is_public' => $booking['title_is_public'],
                'description' => $booking['description'],
                'description_is_public' => $booking['description_is_public'],
                'start_time' => $booking['start_time'],
                'date' => $booking['start_time']->toLocalizedString('d. MMMM yyyy'),
                'time' => $booking['start_time']->toLocalizedString('HH:mm'),
            ];
            
            // Fill email subject
            $subject = enrichEmailTempate($subjectTemplate, $templateVariables);
            // Fill email body
            $body = enrichEmailTempate($bodyTemplate, $templateVariables);
            
            // Send reminder email
            $email = emailer(['mailType' => 'html'])->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
            $email->setTo($user->email);
            $email->setSubject($subject);
            $email->setMessage($body);
    
            if ($email->send(false) === false) {
                log_message('error', $email->printDebugger(['headers']));
            }
            else {
                log_message('info', 'Reminder email send', ['user' => $user->id, 'booking' => $booking['id']]);
                // Set reminder_send_at for booking
                $sessionBookingModel->update($booking['id'], ['reminder_send_at' => Time::now()]);
            }
            
            // Clear the email
            $email->clear();
        }
    }
}
