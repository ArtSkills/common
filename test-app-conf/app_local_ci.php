<?php

use Cake\Database\Connection;
use Cake\Database\Driver\Mysql;

return [
    'Datasources' => [
        'default' => [
            'className' => Connection::class,
            'driver' => Mysql::class,
            'persistent' => false,
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => 'root',
            'database' => 'cakephp',
            'encoding' => 'utf8',
            'timezone' => 'SYSTEM',
            'cacheMetadata' => true,
            'quoteIdentifiers' => true,
        ],
        'test' => [
            'className' => Connection::class,
            'driver' => Mysql::class,
            'persistent' => false,
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => 'root',
            'database' => 'cakephp_test',
            'encoding' => 'utf8',
            'timezone' => 'SYSTEM',
            'cacheMetadata' => true,
            'quoteIdentifiers' => true,
            'init' => ['SET FOREIGN_KEY_CHECKS=0'],
        ],
    ],
];