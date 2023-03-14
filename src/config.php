<?php

$defaultLifetime = env( 'DEFAULT_TTL',3600 );
return [
    'default' => 'file',
    'stores'  => [
        'array'     => [
            'driver'           => 'array',
            'default_lifetime' => $defaultLifetime,
            'max_lifetime'     => 0,
            'serialized'       => true,
            'max_items'        => 0
        ],
        'apc'       => [
            'driver'           => 'apc',
            'namespace'        => 'webman',
            'default_lifetime' => $defaultLifetime,
        ],
        'file'      => [
            'driver'           => 'file',
            'path'             => runtime_path().'/cache',
            'default_lifetime' => $defaultLifetime,
            'namespace'        => 'webman',
        ],
        'database'  => [
            'driver'     => 'database',
            'namespace'  => 'webman',
            'connection' => null,// https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url
            'options'    => [
                'table' => 'cache',
            ],
        ],
        'memcached' => [
            'driver'           => 'memcached',
            'default_lifetime' => $defaultLifetime,
            'namespace'        => 'webman',
            'options'          => [
                //'persistent_id' => 'MEMCACHED_PERSISTENT_ID',
            ],
            'servers'          => [
                'host'   => '127.0.0.1',
                'port'   => 11211,
                'weight' => 100,
            ],
        ],
        'redis'     => [
            'driver'           => 'redis',
            'connection'       => 'redis://localhost:6379',
            'default_lifetime' => $defaultLifetime,
            'namespace'        => 'webman',
        ],

    ],
];

