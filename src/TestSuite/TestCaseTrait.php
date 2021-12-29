<?php
declare(strict_types=1);

namespace ArtSkills\TestSuite;

use ArtSkills\Error\InternalException;
use ArtSkills\Lib\AppCache;
use ArtSkills\Lib\Strings;
use ArtSkills\Mailer\Transport\TestEmailTransport;
use ArtSkills\ORM\Entity;
use ArtSkills\TestSuite\HttpClientMock\HttpClientAdapter;
use ArtSkills\TestSuite\HttpClientMock\HttpClientMocker;
use ArtSkills\TestSuite\Mock\ConstantMocker;
use ArtSkills\TestSuite\Mock\MethodMocker;
use ArtSkills\TestSuite\Mock\PropertyAccess;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Inflector;

/**
 * Тестовое окружение
 *
 * @package App\Test
 */
trait TestCaseTrait
{
    /**
     * Список правильно проинициализированных таблиц
     *
     * @var string[]
     */
    private static array $_tableRegistry = [];

    /** Вызывать в реальном setUpBeforeClass */
    protected static function _setUpBeforeClass(): void
    {
        // noop
    }

    /**
     * Инициализация тестового окружения
     *
     * @throws InternalException
     */
    protected function _setUp(): void
    {
        $this->_clearCache();
        PermanentMocksCollection::init();
        $this->_loadFixtureModels();

        HttpClientAdapter::enableDebug();
        $this->_setUpLocal();
    }

    /**
     * Чистка тестового окружения
     */
    protected function _tearDown(): void
    {
        /** @var TestCase $this */
        ConstantMocker::restore();
        PropertyAccess::restoreStaticAll();
        Time::setTestNow(null); // сбрасываем тестовое время
        TestEmailTransport::clearMessages();
        $this->_tearDownLocal(); // @phpstan-ignore-line
        SingletonCollection::clearCollection();

        try {
            MethodMocker::restore($this->hasFailed());
        } finally {
            PermanentMocksCollection::destroy();
            HttpClientMocker::clean($this->hasFailed());
        }
    }

    /**
     * Для локальных действий на setUp
     *
     * @return void
     */
    protected function _setUpLocal()
    {
        // noop
    }

    /** Для локальных действий на tearDown */
    protected function _tearDownLocal(): void
    {
        // noop
    }

    /**
     * Отключение постоянного мока; вызывать перед parent::setUp();
     *
     * @param string $mockClass
     */
    protected function _disablePermanentMock(string $mockClass): void
    {
        PermanentMocksCollection::disableMock($mockClass);
    }

    /**
     * Чистка кеша
     */
    protected function _clearCache(): void
    {
        AppCache::flushExcept(['_cake_core_', '_cake_model_']);
    }

    /**
     * loadModel на все таблицы фикстур
     */
    protected function _loadFixtureModels(): void
    {
        if (empty($this->fixtures)) {
            return;
        }
        foreach ($this->fixtures as $fixtureName) {
            $modelAlias = Inflector::camelize(Strings::lastPart('.', $fixtureName));
            if (TableRegistry::getTableLocator()->exists($modelAlias)) {
                TableRegistry::getTableLocator()->remove($modelAlias);
            }
            $this->{$modelAlias} = TableRegistry::getTableLocator()->get($modelAlias, [
                'className' => $modelAlias,
                'testInit' => true,
            ]);
        }
    }

    /**
     * Задать тестовое время
     * Чтоб можно было передавать строку
     *
     * @param Time|string|null $time
     * @param bool $clearMicroseconds убрать из времени микросекунды (PHP7).
     *                                Полезно тем, что в базу микросекунды всё равно не сохранятся
     * @return Time
     */
    protected function _setTestNow($time = null, bool $clearMicroseconds = true): Time
    {
        if (!($time instanceof Time)) {
            $time = new Time($time);
        }
        if ($clearMicroseconds) {
            $time->setTime($time->hour, $time->minute, $time->second, 0);
        }
        Time::setTestNow($time);
        return $time;
    }

    /**
     * Проверка совпадения части массива
     * Замена нативного assertArraySubset, который не показывает красивые диффы
     *
     * @param array $expected
     * @param array $actual
     * @param string $message
     * @param float $delta
     * @param int $maxDepth
     * @param bool $canonicalize
     * @param bool $ignoreCase
     * @phpstan-ignore-next-line
     * @SuppressWarnings(PHPMD.MethodArgs)
     */
    public function assertArraySubsetEquals(
        array  $expected,
        array  $actual,
        string $message = '',
        float  $delta = 0.0,
        int    $maxDepth = 10,
        bool   $canonicalize = false,
        bool   $ignoreCase = false
    ): void {
        $actual = array_intersect_key($actual, $expected);
        self::assertEquals($expected, $actual, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
    }

    /**
     * Проверка части полей сущности
     *
     * @param array $expectedSubset
     * @param Entity $entity
     * @param string $message
     * @param float $delta
     * @param int $maxDepth
     * @phpstan-ignore-next-line
     * @SuppressWarnings(PHPMD.MethodArgs)
     */
    public function assertEntitySubset(
        array  $expectedSubset,
        Entity $entity,
        string $message = '',
        float  $delta = 0.0,
        int    $maxDepth = 10
    ): void {
        $this->assertArraySubsetEquals($expectedSubset, $entity->toArray(), $message, $delta, $maxDepth);
    }

    /**
     * Сравнение двух сущностей
     *
     * @param Entity $expectedEntity
     * @param Entity $actualEntity
     * @param string $message
     * @param float $delta
     * @param int $maxDepth
     */
    public function assertEntityEqualsEntity(
        Entity $expectedEntity,
        Entity $actualEntity,
        string $message = '',
        float  $delta = 0.0,
        int    $maxDepth = 10
    ): void {
        self::assertEquals($expectedEntity->toArray(), $actualEntity->toArray(), $message, $delta, $maxDepth);
    }

    /**
     * Сравнение двух сущностей
     *
     * @param array $expectedArray
     * @param Entity $actualEntity
     * @param string $message
     * @param float $delta
     * @param int $maxDepth
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @phpstan-ignore-next-line
     */
    public function assertEntityEqualsArray(
        array  $expectedArray,
        Entity $actualEntity,
        string $message = '',
        float  $delta = 0.0,
        int    $maxDepth = 10
    ): void {
        self::assertEquals($expectedArray, $actualEntity->toArray(), $message, $delta, $maxDepth);
    }

    /**
     * Содержимое файла соответствует ожидаемой строке
     *
     * @param string $expectedString
     * @param string $actualFile
     * @param string $message
     * @param bool $canonicalize
     * @param bool $ignoreCase
     */
    public function assertFileEqualsString(
        string $expectedString,
        string $actualFile,
        string $message = '',
        bool   $canonicalize = false,
        bool   $ignoreCase = false
    ): void {
        self::assertFileExists($actualFile, $message);
        self::assertEquals(
            $expectedString,
            file_get_contents($actualFile),
            $message,
            0,
            10,
            $canonicalize,
            $ignoreCase
        );
    }
}
