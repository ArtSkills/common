<?php
declare(strict_types=1);

use Cake\Database\Connection;
use Cake\Database\Driver\Mysql;

return [
    'Datasources' => [
        'default' => [
            'className' => Connection::class,
            'driver' => Mysql::class,
            'persistent' => false,
            'host' => 'mysql',
            //'port' => '3307',
            'username' => 'root',
            'password' => 'secret',
            'database' => 'common',
            'encoding' => 'utf8',
            'timezone' => 'SYSTEM',
            'cacheMetadata' => true,
            'quoteIdentifiers' => true,
        ],
        'test' => [
            'className' => Connection::class,
            'driver' => Mysql::class,
            'persistent' => false,
            'host' => 'mysql',
            //'port' => '3307',
            'username' => 'root',
            'password' => 'secret',
            'database' => 'common_test',
            'encoding' => 'utf8',
            'timezone' => 'SYSTEM',
            'cacheMetadata' => true,
            'quoteIdentifiers' => true,
            'init' => ['SET FOREIGN_KEY_CHECKS=0'],
        ],
    ],
];
