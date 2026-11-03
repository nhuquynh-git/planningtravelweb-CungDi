<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

// Public Routes
$routes->get('/login', 'Home::index');
$routes->get('/register', 'Home::index');
$routes->get('/forgot-password', 'Home::index');
$routes->get('/set-password', 'Home::index');

// API Routes
$routes->group('api', ['filter' => 'cors'], function ($routes) {

    // Public API
    $routes->get('health', 'Api\Health::index');

    // Auth Routes (public)
    $routes->group('auth', function ($routes) {
        $routes->post('register', 'Api\Auth::register');
        $routes->post('verify-otp', 'Api\Auth::verifyOtp');
        $routes->post('resend-otp', 'Api\Auth::resendOtp');
        $routes->post('login', 'Api\Auth::login');
        $routes->post('logout', 'Api\Auth::logout');

        // Quên mật khẩu (MỚI)
        $routes->post('forgot-password', 'Api\Auth::forgotPassword');
        $routes->post('reset-password', 'Api\Auth::resetPassword');

        // OAuth Routes - Google & Facebook
        $routes->get('oauth/google', 'Api\Auth::oauthGoogle');
        $routes->get('oauth/google/callback', 'Api\Auth::oauthGoogleCallback');

        $routes->get('oauth/facebook', 'Api\Auth::oauthFacebook');
        $routes->get('oauth/facebook/callback', 'Api\Auth::oauthFacebookCallback');

        $routes->options('(:any)', 'Api\Auth::options');
    });

    // Protected API Routes (require authentication)
    $routes->group('', ['filter' => 'token'], function ($routes) {
        $routes->get('auth/me', 'Api\Auth::me');
        $routes->put('auth/profile', 'Api\Auth::profile');
        $routes->post('auth/set-password', 'Api\Auth::setPassword'); // MỚI
    });

    // CORS Preflight
    $routes->options('(:any)', function () {
        return service('response')->setStatusCode(204);
    });

});
