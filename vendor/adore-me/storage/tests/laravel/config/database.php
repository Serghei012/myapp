<?php
return [
    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [
        'sqlite' => [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ],

        'mysql' => [
            'read'      => [
                'host' => env('MYSQL_HOST_READ', 'tests.mysql.read'),
            ],
            'write'     => [
                'host' => env('MYSQL_HOST_WRITE', 'tests.mysql.write'),
            ],
            'timezone'  => 'UTC',
            'driver'    => 'mysql',
            'port'      => env('MYSQL_PORT', 3306),
            'database'  => env('MYSQL_DATABASE', 'tests'),
            'username'  => env('MYSQL_USERNAME', 'tests'),
            'password'  => env('MYSQL_PASSWORD', 'tests'),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => null,
        ],
    ],

    'migrations' => 'migrations',

    'redis' => [
        'client' => 'predis',
        'default' => [
            'host'     => env('REDIS_HOST', 'tests.redis'),
            'password' => env('REDIS_PASSWORD', null),
            'port'     => env('REDIS_PORT', 6379),
            'database' => 0,
        ],
    ],
];
