<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->resource('users', ['controller' => 'UserController', 'except' => ['new', 'edit']]);
$routes->get('sessions/bookings/(:num)', 'SessionBookingController::show/$1');
$routes->get('sessions/bookings/(:num)/(:num)', 'SessionBookingController::get_by_range/$1/$2');
$routes->delete('sessions/bookings/(:num)', 'SessionBookingController::delete/$1');
$routes->post('sessions/bookings', 'SessionBookingController::create');
