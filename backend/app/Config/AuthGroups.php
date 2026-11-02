<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    public string $defaultGroup = 'user';

    public array $groups = [
        'admin' => [
            'title'       => 'Admin',
            'description' => 'Quản trị viên hệ thống.',
        ],
        'user' => [
            'title'       => 'User',
            'description' => 'Người dùng thông thường.',
        ],
    ];

    public array $permissions = [];

    public array $matrix = [
        'admin' => ['*'],
        'user'  => [],
    ];
}