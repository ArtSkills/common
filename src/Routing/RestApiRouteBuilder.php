<?php
declare(strict_types=1);

namespace ArtSkills\Routing;

use ArtSkills\Filesystem\Folder;
use ArtSkills\Lib\Strings;
use ArtSkills\Routing\Route\RestApiRoute;
use Cake\Routing\Exception\MissingRouteException;
use Cake\Routing\RouteBuilder;
use OpenApi\Analysers\TokenAnalyser;
use OpenApi\Annotations\AbstractAnnotation;
use OpenApi\Annotations\Delete;
use OpenApi\Annotations\Get;
use OpenApi\Annotations\JsonContent;
use OpenApi\Annotations\Operation;
use OpenApi\Annotations\Post;
use OpenApi\Annotations\Put;
use OpenApi\Context;
use OpenApi\Generator;

/**
 * Конструктор маршрутов для routes.php на основе OpenApi документации в phpDoc
 */
class RestApiRouteBuilder
{
    /**
     * Окончание имён файлов контроллеров
     */
    private const CONTROLLER_POSTFIX = 'Controller.php';

    /**
     * Окончания старого формата JSON вызова
     */
    private const JSON_EXTENSION = 'json';

    /**
     * Конструктор маршрутов
     *
     * @var RouteBuilder
     */
    private RouteBuilder $_routes;

    /**
     * Полный путь к корневой папки контроллеров
     *
     * @var string
     */
    private string $_controllersDir;

    /**
     * OpenApi анализатор класса
     *
     * @var TokenAnalyser
     */
    private TokenAnalyser $_analyser;

    /**
     * Префикс URL вызова всего проекта
     *
     * @var string
     */
    private string $_projectPathPrefix = '';

    /**
     * Конструктор
     *
     * @param RouteBuilder $routes
     */
    public function __construct(RouteBuilder $routes)
    {
        $this->_routes = $routes;
        $this->_controllersDir = APP . 'Controller' . DS;
        $this->_analyser = new TokenAnalyser();
    }

    /**
     * Прописываем маршруты в RouteBuilder из контроллеров подпапки $subFolder
     *
     * @param string $subFolder
     * @return void
     */
    public function build(string $subFolder): void
    {
        $folder = new Folder($this->_controllersDir . $subFolder);
        $controllers = $folder->findRecursive('.*' . str_replace('.', '\.', self::CONTROLLER_POSTFIX));

        foreach ($controllers as $controller) {
            if (!Strings::endsWith($controller, self::CONTROLLER_POSTFIX)) {
                continue;
            }
            $this->_addControllerRoutes($controller);
        }
    }

    /**
     * Добавляем из контроллера маршруты
     *
     * @param string $controllerFile
     * @return void
     */
    private function _addControllerRoutes(string $controllerFile): void
    {
        $generator = new Generator();

        $rootContext = new Context([
            'version' => $generator->getVersion(),
            'logger' => $generator->getLogger(),
        ]);

        $result = $this->_analyser->fromFile($controllerFile, $rootContext);

        foreach ($result->annotations as $annotation) {
            $this->_addMethodRoute($annotation);
        }
    }

    /**
     * Добавляем маршрут для метода
     *
     * Формат результирующего маршрута:
     * ```
     * $routes->connect('/predict/wbConfig/:id/clearAll', ['controller' => 'WbConfig', 'action' => 'clearAll', '_method' => 'DELETE'], ['routeClass' => OpenApiRoute::class]);
     *   ->setPass(['id'])
     *   ->setPatterns(['id' => '[0-9]+'])
     * ```
     *
     * @param AbstractAnnotation|Get|Post|Put|Delete $annotation
     * @return void
     */
    private function _addMethodRoute(AbstractAnnotation $annotation): void
    {
        if (!$annotation instanceof Operation) {
            return;
        }

        $fileUri = Strings::replacePostfix(Strings::replacePrefix($annotation->_context->filename, $this->_controllersDir), self::CONTROLLER_POSTFIX);

        $controller = Strings::lastPart('/', $fileUri);
        $controllerPathPrefix = Strings::replacePostfix($fileUri, '/' . $controller);
        $annotationRoute = $annotation->path;

        if (!Strings::startsWith($annotationRoute, '/')) {
            throw new MissingRouteException('В маршруте ' . $annotationRoute . ' путь должен быть абсолютным, т.е. начинаться с "/"' .
                ' (' . $annotation->_context->filename . ', метод ' . $annotation->_context->method . ')');
        }


        $route = $this->_projectPathPrefix . str_replace(['{', '}'], [':', ''], $annotationRoute);
        if (Strings::endsWith($route, '.' . self::JSON_EXTENSION)) {
            throw new MissingRouteException('В маршруте ' . $annotationRoute . ' .' . self::JSON_EXTENSION . ' постфикс запрещён' .
                ' (' . $annotation->_context->filename . ', метод ' . $annotation->_context->method . ')');
        }


        /** @var RestApiRoute $newRoute */
        $newRoute = $this->_routes->connect(
            $route,
            [
                'controller' => $controller,
                'action' => $annotation->_context->method,
            ],
            [
                'routeClass' => RestApiRoute::class,
            ]
        )
            ->setExtensions([self::JSON_EXTENSION])
            ->setMethods([$annotation->method]);

        if (!empty($controllerPathPrefix)) {
            $newRoute->setControllerPathPrefix($controllerPathPrefix);
        }

        $parameters = $this->_getRouteParameters($annotation);
        if (!empty($parameters)) {
            $newRoute->setPass($parameters['parameters'])
                ->setPatterns($parameters['patterns']);
        }

        $this->_validateMethodResponse($annotation);
        $newRoute->validatePassedArgs();
    }

    /**
     * Получаем список параметров, которые передаётся в пути
     *
     * @param Operation|Get|Post|Put|Delete $annotation
     * @return array{parameters: string[], patterns: array<string, string>}|null
     */
    private function _getRouteParameters(Operation $annotation): ?array
    {
        if ($annotation->parameters === Generator::UNDEFINED) { // @phpstan-ignore-line
            return null;
        }

        $passArgs = [];
        $passPatterns = [];
        foreach ($annotation->parameters as $parameter) {
            if ($parameter->in !== 'path') {
                continue;
            }

            if ($parameter->name === Generator::UNDEFINED) {
                throw new MissingRouteException('Для маршрута ' . $annotation->path . ' не задано имя параметра (' . $annotation->_context->filename . ', метод ' . $annotation->_context->method . ')');
            }
            if ($parameter->schema === Generator::UNDEFINED) { // @phpstan-ignore-line
                throw new MissingRouteException('Для маршрута ' . $annotation->path . ' не задан тип параметра ' . $parameter->name . ' (' . $annotation->_context->filename . ', метод ' . $annotation->_context->method . ')');
            }

            switch ($parameter->schema->type) {
                case 'string':
                    if ($parameter->required === true) {
                        $passPatterns[$parameter->name] = '[a-zA-Z0-9а-яА-Я \.\-\_]+';
                    } else {
                        $passPatterns[$parameter->name] = '[a-zA-Z0-9а-яА-Я .\-\_]*'; // \w* не работает
                    }
                    break;

                case 'integer':
                    if ($parameter->required === true) {
                        $passPatterns[$parameter->name] = '[0-9]+';
                    } else {
                        $passPatterns[$parameter->name] = '[0-9]*'; // \d* не работает
                    }

                    break;

                default:
                    throw new MissingRouteException('Для маршрута ' . $annotation->path . ' и параметра ' .
                        $parameter->name . ' задан некорректный тип "' . $parameter->schema->type . '", поддерживается только "string" и "integer" (' .
                        $annotation->_context->filename . ', метод ' . $annotation->_context->method . ')');
            }

            $passArgs[] = $parameter->name;
        }

        return ['parameters' => $passArgs, 'patterns' => $passPatterns];
    }

    /**
     * Проверяем результат выполнения метода
     *
     * @param Operation|Get|Post|Put|Delete $annotation
     * @return void
     */
    private function _validateMethodResponse(Operation $annotation): void
    {
        if ($annotation->responses === Generator::UNDEFINED) { // @phpstan-ignore-line
            throw new MissingRouteException('Для маршрута ' . $annotation->path . ' не описан ответ (' .
                $annotation->_context->filename . ', метод ' . $annotation->_context->method . ')');
        }

        $hasSuccessResponse = false;
        foreach ($annotation->responses as $response) {
            if ($response->response != '200') { // тут нельзя строго, ибо бага либы
                continue;
            }
            $hasSuccessResponse = true;
            foreach ($response->_unmerged as $item) {
                if (!$item instanceof JsonContent) {
                    throw new MissingRouteException('Маршрут ' . $annotation->path . ' должен возвращать только JSON ответ (' .
                        $annotation->_context->filename . ', метод ' . $annotation->_context->method . ')');
                }
            }
        }
        if (!$hasSuccessResponse) {
            throw new MissingRouteException('Для маршрута ' . $annotation->path . ' не описан успешный (200) ответ (' .
                $annotation->_context->filename . ', метод ' . $annotation->_context->method . ')');
        }
    }
}
