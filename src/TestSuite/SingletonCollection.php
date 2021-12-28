<?php
declare(strict_types=1);

namespace ArtSkills\TestSuite;

use ArtSkills\TestSuite\Mock\PropertyAccess;

class SingletonCollection
{
    /** @var string[] Массив одиночек */
    private static array $_collection = [];

    /** Добавление одиночки */
    public function append($item)
    {
        array_push(self::$_collection, $item);
        PropertyAccess::setStatic(self::class, '_collection', self::$_collection);
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
        PropertyAccess::setStatic(self::class, '_collection', []);
    }
}