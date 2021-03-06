<?php
use Cake\Core\Plugin;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

Router::defaultRouteClass(DashedRoute::class);
Router::extensions(['json']);
Router::scope('/', function (\Cake\Routing\RouteBuilder $routes) {
	$routes->fallbacks(DashedRoute::class);
});
