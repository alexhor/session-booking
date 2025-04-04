<?php

// override core en language system validation or define your own en language validation message
return [
    'session_booking' => [
        'not_found' => 'No session booking found',
        'deleting_failed' => 'Deleting session booking failed',
        'taken' => 'Session is already booked by someone else',
        'created' => 'Session booked',
        'creating_failed' => "Session couldn't be booked",
        'deleted' => 'Session booking deleted',

        'start_time' => [
            'label' => 'start time',
            'required' => 'Missing session booking time',
            'integer' => 'Session booking time is invalid',
        ]
    ],
    'user' => [
        'not_found' => 'No user could be found',
        'created' => 'User created',
        'creating_failed' => 'Creating user failed',
        'updated' => 'User data updated',
        'updating_failed' => 'Updating user data failed',
        'deleted' => 'User deleted',
        'deleting_failed' => 'Deleting user failed',
        'add_to_group' => 'User was added to group {group}',
        'add_to_group_failed' => 'Adding user to the group {group} failed',
        'remove_from_group' => 'User was removed from group {group}',
        'remove_from_group_failed' => 'Removing user from the group {group} failed',
        'id' => [
            'label' => 'User ID',
            'required' => 'No user id given',
            'integer' => 'Invalid user id',
            'not_found' => 'No such user found',
        ],
        'email' => [
            'label' => 'E-Mail',
            'required' => 'Missing email adress',
            'valid' => 'Invalid email adress',
            'not_found' => 'No user could be found with this email adress',
            'taken' => 'This email adress is already in use by another user account',
            'too_long' => 'E-Mail adress ist too long',
        ],
        'firstname' => [
            'label' => 'Firstname',
            'required' => 'Missing firstname',
        ],
        'lastname' => [
            'label' => 'Lastname',
            'required' => 'Missing lastname',
        ],
    ],
    'user_authentication' => [
        'login' => 'Login successful',
        'logout' => 'Logout successful',
        'token' => [
            'label' => 'Authentication Token',
            'required' => 'No authentication token provided',
            'alpha_numeric' => 'Invalid token format',
            'exact_length' => 'Invalid token length',
            'invalid_or_expired' => 'Invalid or expired token',
            'send_by_email' => 'E-Mail with token was send',
            'too_may_requests' => 'Token was requested too often. Please try again in one minute',
        ],
    ],
    'email_templates' => [
        'json' => [
            'label' => 'json',
            'required' => 'JSON version of template required',
            'valid_json' => 'JSON version of template is invalid',
        ],
        'html' => [
            'label' => 'html',
            'required' => 'HTML version of template required',
            'valid_json' => 'HTML version of template is invalid',
        ],
        'subject' => [
            'label' => 'Subject',
            'required' => 'Subject of email template required',
            'string' => 'Subject of email template is invalid',
        ],
        'success' => 'Email template has been saved',
    ],
    'setting_invalid_value' => 'An invalid value has been given for the setting',
    'setting_saving_failed' => 'Saving the setting failed',
    'failed_to_save_email_template' => 'Failed to save email tempate',
];
