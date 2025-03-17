<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/admin', 'Home::admin');
$routes->get('/admin/users', 'Home::adminUsers');
$routes->get('/admin/settings', 'Home::adminSettings');

// TODO: add email reminder 24h before a booked session

// TODO: add email confirmation for (un)booking a session
// TODO: add ical as attachment to email with booked session
// TOOD: validate session length and offset

// TODO: add test for unauthorized response for user self delete




# Some (validation) error occured during email sending or token validation
$routes->get('magic-link', 'Home::index');
$routes->get('login', 'Home::index');


# Send token via email and inform user what's happening now
$routes->post('users/authentication', '\CodeIgniter\Shield\Controllers\MagicLinkController::loginAction');
# Link in the email points here
$routes->get('verify-magic-link', 'UserAuthenticationController::verify');
# Logout user
$routes->get('users/authentication/logout', '\CodeIgniter\Shield\Controllers\LoginController::logoutAction');
# Get data of logged in user
$routes->get('users/authentication/login', 'UserAuthenticationController::get_logged_in_user');
# Check if the logged in user is an admin
$routes->get('users/admin', 'UserAuthenticationController::is_admin');

# User actions
$routes->get('users', 'UserController::index');
$routes->get('users/(:num)', 'UserController::show/$1');
$routes->post('users', 'UserController::create');
$routes->put('users/(:num)', 'UserController::update/$1');
$routes->delete('users/(:num)', 'UserController::delete/$1');
## User group actions
$routes->put('users/(:num)/groups/(:alpha)', 'UserAuthenticationController::addToGroup/$1/$2');
$routes->delete('users/(:num)/groups/(:alpha)', 'UserAuthenticationController::removeFromGroup/$1/$2');

# Session booking actions
$routes->get('sessions/bookings/(:num)', 'SessionBookingController::show/$1');
$routes->get('sessions/bookings/(:num)/(:num)', 'SessionBookingController::get_by_range/$1/$2');
$routes->delete('sessions/bookings/(:num)', 'SessionBookingController::delete/$1');
$routes->post('sessions/bookings', 'SessionBookingController::create');

# Settings
$routes->get('settings/(:segment)', 'SettingController::get/$1');
$routes->put('settings/(:segment)', 'SettingController::set/$1');
$routes->delete('settings/(:segment)', 'SettingController::delete/$1');
