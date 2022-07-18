<?php
declare(strict_types=1);

namespace ArtSkills\Routing\Route;

use ArtSkills\Lib\Arrays;
use Cake\Routing\Exception\MissingRouteException;
use Cake\Routing\Route\DashedRoute;

class RestApiRoute extends DashedRoute
{
    /**
     * Меняем префикс пути для поиска контроллера
     *
     * @param string $prefix
     * @return self
     */
    public function setControllerPathPrefix(string $prefix): self
    {
        $this->defaults['prefix'] = $prefix;
        return $this;
    }

    /**
     * Проверка на все описанные параметры в пути и заполненные типы для них
     *
     * @return void
     */
    public function validatePassedArgs(): void
    {
        $route = $this->template;
        if (!preg_match_all('/:([a-z0-9-_]+(?<![-_]))/i', $route, $namedElements)) {
            return;
        }

        $routeParameters = $namedElements[1] ?? [];
        $describedParameters = $this->options['pass'] ?? [];

        $notDescribed = array_diff($routeParameters, $describedParameters);
        if (!empty($notDescribed)) {
            throw new MissingRouteException("Для маршрута $route не описаны параметры: " . Arrays::encode(array_values($notDescribed)));
        }

        $excessDescribed = array_diff($describedParameters, $routeParameters);
        if (!empty($excessDescribed)) {
            throw new MissingRouteException("Для маршрута $route описаны лишние параметры: " . Arrays::encode(array_values($excessDescribed)));
        }
    }
}
