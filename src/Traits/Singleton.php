<?php // phpcs:ignore

namespace ArtSkills\Traits;

use ArtSkills\Lib\Env;
use ArtSkills\TestSuite\SingletonCollection;

/**
 * Трейт-одиночка.
 * strict_types специально не объявлено, ибо не работает с ним
 */
trait Singleton
{
    /**
     * Объект-одиночка
     *
     * @var ?static
     */
    private static $_instance;

    /**
     * Защищаем от создания через new Singleton
     */
    private function __construct()
    {
    }

    /**
     * Защищаем от создания через клонирование
     */
    private function __clone()
    {
    }

    /**
     * Защищаем от создания через unserialize
     */
    private function __wakeup()
    {
    }

    /**
     * Возвращает объект-одиночку
     *
     * @return static
     */
    public static function getInstance()
    {
        if (empty(static::$_instance)) {
            static::$_instance = new static(); // @phpstan-ignore-line
        }

        if (Env::isUnitTest()) {
            (new SingletonCollection())->append(static::class);
        }

        return static::$_instance;
    }

    /**
     * Подчищаем инстанс, если объект уничтожили
     */
    public function __destruct()
    {
        static::$_instance = null;
    }
}
