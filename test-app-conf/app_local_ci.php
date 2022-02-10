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
    
  'EmailTransport' => ['test' => ['className' => 'ArtSkills.TestEmail']],
  'debug' => true,
  'Security' => ['salt' => '7c47f1e793a39c7f518efc6b909b920ed5ba7a7470efc0501f2960973b7954dd'],
  'Sentry' => ['dsn' => ''],
  'testServerName' => 'eggheads.solutions',
];