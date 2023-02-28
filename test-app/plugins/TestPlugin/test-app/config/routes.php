<?php

use ArtSkills\Routing\PluginRestApiRouteBuilder;
use Cake\Routing\Router;
use TestPlugin\Plugin;

Router::plugin(
    Plugin::PLUGIN_NAME,
    ['path' => '/'],
    static function ($routes) {
        $openApi = new PluginRestApiRouteBuilder($routes, Plugin::PLUGIN_NAME);
        $openApi->build('');
    }
);
