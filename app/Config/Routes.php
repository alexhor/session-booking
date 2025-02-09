<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/login', 'Home::login');


$routes->post('users/authentication/login', 'UserAuthenticationController::login_user');
$routes->post('users/authentication/logout', 'UserAuthenticationController::logout_user');
$routes->post('users/authentication', 'UserAuthenticationController::create_token');

$routes->get('users', 'UserController::index'/*, ['filter' => 'admin']*/);
$routes->get('users/(:num)', 'UserController::show/$1'/*, ['filter' => 'auth']*/);
$routes->post('users', 'UserController::create');
$routes->put('users/(:num)', 'UserController::update/$1'/*, ['filter' => 'auth']*/);
$routes->delete('users/(:num)', 'UserController::delete/$1'/*, ['filter' => 'auth']*/);

$routes->get('sessions/bookings/(:num)', 'SessionBookingController::show/$1');
$routes->get('sessions/bookings/(:num)/(:num)', 'SessionBookingController::get_by_range/$1/$2');
$routes->delete('sessions/bookings/(:num)', 'SessionBookingController::delete/$1');
$routes->post('sessions/bookings', 'SessionBookingController::create');
