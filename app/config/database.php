<?php

return [
    'fetch' => PDO::FETCH_CLASS,
    'default' => 'bible',
    'connections' => [
        'bible' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'bible',
            'username' => getenv("MYSQL_SZENTIRAS_USER"),
            'password' => getenv("MYSQL_SZENTIRAS_PASSWORD"),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => 'kar_',
        ],
    ],

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'cluster' => false,

        'default' => [
            'host' => '127.0.0.1',
            'port' => 6379,
            'database' => 0,
        ],

    ],
];
