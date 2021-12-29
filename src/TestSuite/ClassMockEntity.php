<?php
declare(strict_types=1);

namespace ArtSkills\TestSuite;

/**
 * Мок методов касса
 */
abstract class ClassMockEntity
{
    /**
     * Базовый метод, который и иницализирует все подмены
     *
     * @return void
     */
    public static function init()
    {
    }

    /**
     * Вызов после каждого теста
     *
     * @return void
     */
    public static function destroy()
    {
    }

    /**
     * @param string $str
     * @return void
     */
    protected static function _writeToConsole(string $str)
    {
        PermanentMocksCollection::setHasWarning(true);
        file_put_contents('php://stderr', $str);
    }
}
