
<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

// Public Routes
$routes->get('/login', 'Home::index');
$routes->get('/register', 'Home::index');

// API Routes
$routes->group('api', ['filter' => 'cors'], function ($routes) {

    // Public API
    $routes->get('health', 'Api\Health::index');

    // Auth Routes (public)
    $routes->group('auth', function ($routes) {
        $routes->post('register', 'Api\Auth::register');
        $routes->post('login', 'Api\Auth::login');
        $routes->post('logout', 'Api\Auth::logout');
        $routes->options('(:any)', 'Api\Auth::options');
    });

    // Protected API Routes (require authentication)
    $routes->group('', ['filter' => 'token'], function ($routes) {
        $routes->get('auth/me', 'Api\Auth::me');
        $routes->put('auth/profile', 'Api\Auth::profile');
        $routes->post('auth/refresh', 'Api\Auth::refresh');
    });

    // CORS Preflight
    $routes->options('(:any)', function () {
        return service('response')->setStatusCode(204);
    });

});
