<?php
declare(strict_types=1);
declare(ticks=1);

namespace ArtSkills\Command;

use ArtSkills\Lib\Env;
use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\I18n\FrozenTime;

/**
 * Абстрактный класс обработчика очереди. Выполняет задание определённое кол-во времени config['maxQueueRunSeconds'], либо
 * завершает выполнение при превышении 90% лимита отведённой памяти.
 * Также корректно отрабатывает сигналы остановки процесса.
 */
abstract class AbstractQueueCommand extends Command
{
    /** @var int Сколько секунд ждать между запросами */
    protected const SLEEP_SECONDS = 1;

    /** @var int Максимальный размер памяти команды по-умолчанию */
    protected const MAX_MEMORY_LIMIT_BYTES = 4294967296;

    /** @var int Время запуска по-умолчанию */
    protected const DEFAULT_QUEUE_RUN_SECONDS = 300;

    /**
     * @var bool Сигнал остановки
     */
    private bool $_suspend = false;

    /**
     * @var int UnixTime времени завершения процесса
     */
    private int $_endMeTime;

    /**
     * @var int Лимит памяти на процесс
     */
    private int $_memoryLimit;

    /**
     * inheritdoc
     * @SuppressWarnings(PHPMD)
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $this->_memoryLimit = $this->_getBytes((string)ini_get('memory_limit'));
        if (!$this->_memoryLimit || $this->_memoryLimit < 0) {
            $this->_memoryLimit = self::MAX_MEMORY_LIMIT_BYTES;
        } else {
            $this->_memoryLimit = (int)ceil($this->_memoryLimit * 0.9); // оставляем запас в 10%
        }

        $runSeconds = Env::getMaxQueueRunSeconds();
        if (!$runSeconds) {
            $runSeconds = static::DEFAULT_QUEUE_RUN_SECONDS;
        }

        $this->_endMeTime = (int)FrozenTime::now()->toUnixString() + $runSeconds;

        pcntl_signal(SIGTERM, [$this, "sigHandler"]);
        pcntl_signal(SIGINT, [$this, "sigHandler"]);

        return $this->_do($io);
    }

    /**
     * Запуск задания
     *
     * @param ConsoleIo $io
     * @return ?int Код завершения, если > 0, то выход
     */
    abstract protected function _runTask(ConsoleIo $io): ?int;

    /**
     * Завершение скрипта
     *
     * @param ConsoleIo $io
     * @return void
     */
    abstract protected function _tearDown(ConsoleIo $io): void;

    /**
     * Обработчик событий от системы
     *
     * @return void
     */
    public function sigHandler(): void
    {
        $this->_suspend = true;
    }

    /**
     * Выполняю операцию
     *
     * @param ConsoleIo $io
     * @return ?int
     */
    private function _do(ConsoleIo $io): ?int
    {
        do {
            $taskCode = $this->_runTask($io);
            if (Env::isUnitTest()) {
                $io->info('Unit test mode, exit');
                $this->_tearDown($io);
                return $taskCode;
            }
            if ($taskCode > 0) {
                $this->_tearDown($io);
                return $taskCode;
            }

            $usedMemory = memory_get_usage(true);
            if ($this->_suspend) {
                $io->info('Received exit command');
                $this->_tearDown($io);
                return null;
            } elseif ((int)FrozenTime::now()->toUnixString() > $this->_endMeTime) {
                $io->info('Script run timeout');
                $this->_tearDown($io);
                return null;
            } elseif ($usedMemory > $this->_memoryLimit) {
                $io->info('Script memory limit reached (' . $this->_memoryLimit . ')');
                $this->_tearDown($io);
                return null;
            }
            sleep(static::SLEEP_SECONDS);
        } while (true);
    }

    /**
     * Получаем размер в байтах из строки
     *
     * @see https://www.php.net/manual/ru/function.ini-get.php#126324
     *
     * @param string $size
     * @return int
     */
    private function _getBytes(string $size): int
    {
        $size = trim($size);

        #
        # Separate the value from the metric(i.e MB, GB, KB)
        #
        preg_match('/([0-9]+)[\s]*([a-zA-Z]+)/', $size, $matches);

        $value = (isset($matches[1])) ? $matches[1] : 0;
        $metric = (isset($matches[2])) ? strtolower($matches[2]) : 'b';

        #
        # Result of $value multiplied by the matched case
        # Note: (1024 ** 2) is same as (1024 * 1024) or pow(1024, 2)
        #
        switch ($metric) {
            case 'k':
            case 'kb':
                $value *= 1024;
                break;

            case 'm':
            case 'mb':
                $value *= (1024 ** 2);
                break;
            case 'g':
            case 'gb':
                $value *= (1024 ** 3);
                break;
            case 't':
            case 'tb':
                $value *= (1024 ** 4);
                break;
        }
        return (int)$value;
    }
}
