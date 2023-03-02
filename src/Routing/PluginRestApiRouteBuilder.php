<?php
declare(strict_types=1);

namespace ArtSkills\Routing;

use Cake\Routing\RouteBuilder;

/**
 * Конструктор маршрутов для Плагина CakePHP на основе OpenApi документации в phpDoc
 */
class PluginRestApiRouteBuilder extends RestApiRouteBuilder
{
    /**
     * Конструктор
     *
     * @param RouteBuilder $routes
     * @param string $pluginName Название плагина, которе должно быть равно папке
     */
    public function __construct(RouteBuilder $routes, string $pluginName)
    {
        parent::__construct($routes);
        $this->_controllersDir = PLUGINS . $pluginName . DS . APP_DIR . DS . 'Controller' . DS;
    }
}
