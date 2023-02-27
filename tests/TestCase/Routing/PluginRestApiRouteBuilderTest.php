<?php
declare(strict_types=1);

namespace ArtSkills\Test\TestCase\Routing;

use ArtSkills\Routing\PluginRestApiRouteBuilder;
use ArtSkills\TestSuite\AppTestCase;
use Cake\Routing\RouteBuilder;
use Cake\Routing\RouteCollection;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Eggheads\Mocks\PropertyAccess;
use TestApp\Controller\RestApi\Success\SuccessController;
use TestPlugin\Plugin as TestPluginClass;

class PluginRestApiRouteBuilderTest extends AppTestCase
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
        $collection = new RouteCollection();
        $builder = new RouteBuilder($collection, '');

        $openApi = new PluginRestApiRouteBuilder($builder, TestPluginClass::PLUGIN_NAME);
        $openApi->build('');

        self::assertEquals([
            'pass' => [],
            'controller' => 'PluginSuccess',
            'action' => 'index',
            'plugin' => null,
            '_method' => [
                0 => 'GET',
            ],
            '_matchedRoute' => '/plugin/success',
        ], $collection->parse('/plugin/success', 'GET'));
    }
}
