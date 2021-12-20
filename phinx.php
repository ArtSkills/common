<?php
declare(strict_types=1);

use ArtSkills\Phinx\Migration\AbstractMigration;

const PATHS_FILE = __DIR__ . '/test-app-conf/paths.php';
require_once PATHS_FILE;
require __DIR__ . DS . 'src' . DS . 'config' . DS . 'phinx.php';
return getPhinxConfig(__DIR__ . '/test-app-conf/app_local.php', PATHS_FILE, AbstractMigration::class);
