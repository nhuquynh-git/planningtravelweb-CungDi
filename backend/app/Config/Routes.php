
<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

// API Routes
$routes->group('api', ['filter' => 'cors'], function ($routes) {

    // Public API
    $routes->get('health', 'Api\Health::index');

    // CORS Preflight
    $routes->options('(:any)', function () {
        return service('response')->setStatusCode(204);
    });

});
