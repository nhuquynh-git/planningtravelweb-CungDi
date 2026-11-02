<?php

namespace App\Controllers\Api;

class Health extends BaseApiController
{
    public function index()
    {
        try {
            $db = \Config\Database::connect();
            $db->query('SELECT 1');

            return $this->success(
                [
                    'backend' => 'connected',
                    'database' => 'connected',
                ],
                'Travel Planner API is running'
            );

        } catch (\Throwable $e) {
            return $this->error(
                'Database connection failed',
                503
            );
        }
    }
}