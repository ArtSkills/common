<?php
declare(strict_types=1);

namespace ArtSkills\TestSuite\PermanentMocks;

use ArtSkills\TestSuite\ClassMockEntity;
use ArtSkills\TestSuite\Mock\MethodMocker;
use Cake\Error\Debugger;
use Cake\Log\Log;

class MockLog extends ClassMockEntity
{
    /**
     * @inheritdoc
     */
    public static function init()
    {
        MethodMocker::mock(Log::class, 'write', 'return ' . self::class . '::write(...func_get_args());');
    }

    /**
     * Вывод ошибка вместо файла в консоль
     *
     * @param string|int $level
     * @param string $message
     */
    public static function write($level, $message): bool
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
        self::_writeToConsole("test: $test \n Write to '$level' log from $file: $message\n\n");
        return true;
    }
}
