<?php
declare(strict_types=1);

namespace ArtSkills\Test\TestCase\Routing;

use ArtSkills\Routing\RestApiRouteBuilder;
use ArtSkills\TestSuite\AppTestCase;
use Cake\Routing\Exception\MissingRouteException;
use Cake\Routing\RouteBuilder;
use Cake\Routing\RouteCollection;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Eggheads\Mocks\PropertyAccess;
use OpenApi\Generator;
use TestApp\Controller\RestApi\DeprecatedExtension\DeprecatedExtensionController;
use TestApp\Controller\RestApi\IncorrectSchema\IncorrectSchemaController;
use TestApp\Controller\RestApi\NoResponse\NoResponseController;
use TestApp\Controller\RestApi\NotDescribedParameter\NotDescribedParameterController;
use TestApp\Controller\RestApi\Success\SuccessController;

class RestApiRouteBuilderTest extends AppTestCase
{
    /** @inheritdoc */
    public function setUp()
    {
        PropertyAccess::setStatic(AnnotationRegistry::class, 'loaders', []);
        parent::setUp();
    }

    /**
     * Тест успешных запросов
     *
     * @return void
     * @see SuccessController
     *
     */
    public function testSuccess(): void
    {
        $collection = $this->_buildCollection('RestApi/Success');

        self::assertEquals([
            'pass' => [],
            'controller' => 'Success',
            'action' => 'index',
            'plugin' => null,
            '_method' => [
                0 => 'GET',
            ],
            'prefix' => 'RestApi/Success',
            '_matchedRoute' => '/success',
        ], $collection->parse('/success', 'GET'));

        self::assertEquals([
            'wbConfigId' => '123',
            'pass' => [
                0 => '123',
            ],
            'controller' => 'Success',
            'action' => 'view',
            'plugin' => null,
            '_method' => [
                0 => 'GET',
            ],
            'prefix' => 'RestApi/Success',
            '_matchedRoute' => '/success/:wbConfigId',
        ], $collection->parse('/success/123', 'GET'));

        self::assertEquals([
            'wbConfigId' => '3d очки',
            'pass' => [
                0 => '3d очки',
            ],
            'controller' => 'Success',
            'action' => 'view',
            'plugin' => null,
            '_method' => [
                0 => 'GET',
            ],
            'prefix' => 'RestApi/Success',
            '_matchedRoute' => '/success/:wbConfigId',
        ], $collection->parse('/success/3d очки', 'GET'));

        self::assertEquals([
            'wbConfigId' => '',
            'pass' => [
                0 => '',
            ],
            'controller' => 'Success',
            'action' => 'view',
            'plugin' => null,
            '_method' => [
                0 => 'GET',
            ],
            'prefix' => 'RestApi/Success',
            '_matchedRoute' => '/success/:wbConfigId',
        ], $collection->parse('/success/', 'GET'));

        self::assertEquals([
            'pass' => [],
            'controller' => 'Success',
            'action' => 'add',
            'plugin' => null,
            '_method' => [
                0 => 'POST',
            ],
            'prefix' => 'RestApi/Success',
            '_matchedRoute' => '/success',
        ], $collection->parse('/success', 'POST'));

        self::assertEquals([
            'wbConfigId' => 'string',
            'pass' => [
                0 => 'string',
            ],
            'controller' => 'Success',
            'action' => 'edit',
            'plugin' => null,
            '_method' => [
                0 => 'PUT',
            ],
            'prefix' => 'RestApi/Success',
            '_matchedRoute' => '/success/:wbConfigId',
        ], $collection->parse('/success/string', 'PUT'));

        self::assertEquals([
            'wbConfigId' => '123',
            'pass' => [
                0 => '123',
            ],
            'controller' => 'Success',
            'action' => 'delete',
            'plugin' => null,
            '_method' => [
                0 => 'DELETE',
            ],
            'prefix' => 'RestApi/Success',
            '_matchedRoute' => '/success/:wbConfigId',
        ], $collection->parse('/success/123', 'DELETE'));

        // Числовой необязательный параметр
        self::assertEquals([
            'wbConfigId' => '',
            'pass' => [
                0 => '',
            ],
            'controller' => 'Success',
            'action' => 'successDelete',
            'plugin' => null,
            '_method' => [
                0 => 'DELETE',
            ],
            'prefix' => 'RestApi/Success',
            '_matchedRoute' => '/successDelete/:wbConfigId',
        ], $collection->parse('/successDelete/', 'DELETE'));

        self::assertEquals([
            'wbConfigId' => '666',
            'pass' => [
                0 => '666',
            ],
            'controller' => 'Success',
            'action' => 'successDelete',
            'plugin' => null,
            '_method' => [
                0 => 'DELETE',
            ],
            'prefix' => 'RestApi/Success',
            '_matchedRoute' => '/successDelete/:wbConfigId',
        ], $collection->parse('/successDelete/666', 'DELETE'));
    }

    /**
     * Запрос с некорректным типом
     *
     * @return void
     * @see SuccessController
     */
    public function testBadType(): void
    {
        $collection = $this->_buildCollection('RestApi/Success');

        $this->expectException(MissingRouteException::class);
        $this->expectExceptionMessage('A "DELETE" route matching "/success/string" could not be found.');

        $collection->parse('/success/string', 'DELETE');
    }

    /**
     * Запрос с неописанным методом
     *
     * @return void
     * @see SuccessController
     */
    public function testBadMethod(): void
    {
        $collection = $this->_buildCollection('RestApi/Success');

        $this->expectException(MissingRouteException::class);
        $this->expectExceptionMessage('A "PATCH" route matching "/success" could not be found.');

        $collection->parse('/success', 'PATCH');
    }

    /**
     * Тест на некорректный тип параметра
     *
     * @return void
     * @see IncorrectTypeController
     */
    public function testIncorrectParameterType(): void
    {
        $this->expectException(MissingRouteException::class);
        $this->expectExceptionMessage('Для маршрута /incorrectType/{wbConfigId} и параметра wbConfigId задан некорректный тип "float", поддерживается только "string" и "integer"');

        $collection = $this->_buildCollection('RestApi/IncorrectType');
        unset($collection);
    }

    /**
     * Тест на пустое имя параметра
     *
     * @return void
     * @see IncorrectNameController
     */
    public function testIncorrectParameterName(): void
    {
        $this->expectException(MissingRouteException::class);
        $this->expectExceptionMessage('Для маршрута /incorrectName/{wbConfigId} не задано имя параметра');

        $collection = $this->_buildCollection('RestApi/IncorrectName');
        unset($collection);
    }

    /**
     * Тест на незаданный тип параметра
     *
     * @return void
     * @see IncorrectSchemaController
     */
    public function testIncorrectParameterSchema(): void
    {
        $this->expectException(MissingRouteException::class);
        $this->expectExceptionMessage('Для маршрута /incorrectSchema/{wbConfigId} не задан тип параметра');

        $collection = $this->_buildCollection('RestApi/IncorrectSchema');
        unset($collection);
    }

    /**
     * Тест на устаревший постфикс
     *
     * @return void
     * @see DeprecatedExtensionController
     */
    public function testDeprecatedExtension(): void
    {
        $this->expectException(MissingRouteException::class);
        $this->expectExceptionMessage('В маршруте /deprecatedExtension.json .json постфикс запрещён');

        $collection = $this->_buildCollection('RestApi/DeprecatedExtension');
        unset($collection);
    }

    /**
     * Тест на абсолютный путь
     *
     * @return void
     * @see BadPrefixController
     */
    public function testBadPrefix(): void
    {
        $this->expectException(MissingRouteException::class);
        $this->expectExceptionMessage('В маршруте badPrefix путь должен быть абсолютным, т.е. начинаться с "/"');

        $collection = $this->_buildCollection('RestApi/BadPrefix');
        unset($collection);
    }

    /**
     * Тест отсутствие описания ответа
     *
     * @return void
     * @see NoResponseController
     */
    public function testNoResponse(): void
    {
        $this->expectException(MissingRouteException::class);
        $this->expectExceptionMessage('Для маршрута /noResponse не описан ответ');

        $collection = $this->_buildCollection('RestApi/NoResponse');
        unset($collection);
    }

    /**
     * Тест на отсутствие успешного ответа
     *
     * @return void
     * @see NoSuccessResponseController
     */
    public function testNoSuccessResponse(): void
    {
        $this->expectException(MissingRouteException::class);
        $this->expectExceptionMessage('Для маршрута /noSuccessResponse не описан успешный (200) ответ');

        $collection = $this->_buildCollection('RestApi/NoSuccessResponse');
        unset($collection);
    }

    /**
     * Тест на некорректный ответ
     *
     * @return void
     * @see NoSuccessResponseController
     */
    public function testNotJsonResponse(): void
    {
        $this->expectException(MissingRouteException::class);
        $this->expectExceptionMessage('Маршрут /notJsonResponse должен возвращать только JSON ответ');

        $collection = $this->_buildCollection('RestApi/NotJsonResponse');
        unset($collection);
    }

    /**
     * Не описанный параметр
     *
     * @return void
     * @see NotDescribedParameterController
     */
    public function testNotDescribedParameter(): void
    {
        $this->expectException(MissingRouteException::class);
        $this->expectExceptionMessage('Для маршрута /notDescribedParameter/:wbConfigId не описаны параметры: ["wbConfigId"]');

        $collection = $this->_buildCollection('RestApi/NotDescribedParameter');
        unset($collection);
    }

    /**
     * Лишний параметр
     *
     * @return void
     * @see NotDescribedParameterController
     */
    public function testExcessDescribedParameter(): void
    {
        $this->expectException(MissingRouteException::class);
        $this->expectExceptionMessage('Для маршрута /excessDescribedParameter/:wbConfigId описаны лишние параметры: ["excess"]');

        $collection = $this->_buildCollection('RestApi/ExcessDescribedParameter');
        unset($collection);
    }

    /**
     * Формируем коллекцию маршрутов
     *
     * @param string $buildDir
     * @return RouteCollection
     */
    private function _buildCollection(string $buildDir): RouteCollection
    {
        $collection = new RouteCollection();
        $builder = new RouteBuilder($collection, '');

        $openApi = new RestApiRouteBuilder($builder);
        $openApi->build($buildDir);

        return $collection;
    }
}
