<?php
declare(strict_types=1);

namespace ArtSkills\TestSuite;

use ArtSkills\TestSuite\Mock\PropertyAccess;
use ArtSkills\Traits\Library;

class SingletonCollection
{
    use Library;

    /** @var string[] Массив одиночек */
    private static array $_collection = [];

    /**
     * Добавление одиночки
     *
     * @param string $item
     */
    public static function append(string $item): void
    {
        array_push(self::$_collection, $item);
    }

    /**
     * Список одиночек
     *
     * @return string[]
     */
    public static function getCollection(): array
    {
        return self::$_collection;
    }

    /**
     * Очищаем одиночек
     */
    public static function clearCollection(): void
    {
        $singletons = self::getCollection();
        foreach ($singletons as $className) {
            PropertyAccess::setStatic($className, '_instance', null);
        }
        self::$_collection = [];
    }
}
