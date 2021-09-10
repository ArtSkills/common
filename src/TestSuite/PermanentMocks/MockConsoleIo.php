<?php
declare(strict_types=1);

namespace ArtSkills\TestSuite\PermanentMocks;

use ArtSkills\TestSuite\ClassMockEntity;
use ArtSkills\TestSuite\Mock\MethodMocker;
use ArtSkills\TestSuite\Mock\MethodMockerEntity;
use Cake\Console\ConsoleIo;
use Cake\Error\Debugger;

class MockConsoleIo extends ClassMockEntity
{
    /**
     * @var MethodMockerEntity $_mockOut Мок метод Out
     */
    private static MethodMockerEntity $_mockOut;

    /**
     * @inheritdoc
     */
    public static function init()
    {
        static::$_mockOut = MethodMocker::mock(ConsoleIo::class, 'out', 'return ' . self::class . '::out(...func_get_args());');
    }

    /**
     * Вывод ошибка вместо вывода данных
     *
     * @param string $message
     * @param int $level
     * @return bool
     */
    public static function out(string $message = '', int $level = ConsoleIo::NORMAL): bool
    {
        $trace = Debugger::trace();
        $trace = explode("\n", $trace);
        $test = '';
        foreach ($trace as $line) {
            // последняя строчка трейса в которой есть слово тест и нет пхпюнит - это строка теста, вызвавшего запись в лог
            if (stristr($line, 'test') && !stristr($line, 'phpunit')) {
                $test = $line;
            }
        }
        $file = $trace[4];
        file_put_contents('php://stderr', "test: $test \n Write to '$level' out from $file: $message\n\n");
        return true;
    }

    /** @inheritDoc */
    public static function destroy()
    {
        /** @phpstan-ignore-next-line */
        if (self::$_mockOut && !self::$_mockOut->isRestored()) {
            self::$_mockOut->restore();
        }
    }
}
