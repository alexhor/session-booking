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
        'logout' => 'Logout successful',
        'token' => [
            'label' => 'Authentication Token',
            'required' => 'No authentication token provided',
            'alpha_numeric' => 'Invalid token format',
            'exact_length' => 'Invalid token length',
            'invalid_or_expired' => 'Invalid or expired token',
        ],
    ],
];
