<?php
declare(strict_types=1);

namespace ArtSkills\TestSuite;

use ArtSkills\Error\InternalException;
use ArtSkills\Filesystem\Folder;
use ArtSkills\Lib\Env;
use ArtSkills\Lib\Misc;
use ArtSkills\TestSuite\PermanentMocks\MockFileLog;
use ArtSkills\Traits\Library;
use PHPUnit\Framework\Warning;

/**
 * Класс для подмены методов, необходимых только в тестовом окружении
 */
class PermanentMocksCollection
{
    use Library;

    /**
     * Набор постоянных моков
     *
     * @var ClassMockEntity[]
     */
    private static array $_permanentMocksList = [];

    /**
     * Отключённые постоянные моки
     *
     * @var array<string, bool> className => true
     */
    private static array $_disabledMocks = [];

    /**
     * Индикатор ошибки если не был замокан класс
     *
     * @var bool
     */
    private static bool $_hasWarning = false;

    /**
     * Сообщение ошибки
     *
     * @var string
     */
    private static string $_warningMessage = '';

    /**
     * Инициализируем подмену методов
     *
     * @return void
     * @throws InternalException
     */
    public static function init(): void
    {
        self::clearWarnings();
        $permanentMocks = [
            // folder => namespace
            __DIR__ . '/PermanentMocks' => Misc::namespaceSplit(MockFileLog::class)[0],
        ];

        $projectMockFolder = Env::getMockFolder();
        if (!empty($projectMockFolder)) {
            if (Env::hasMockNamespace()) {
                $projectMockNs = Env::getMockNamespace();
            } else {
                throw new InternalException('Не задан неймспейс для классов-моков');
            }
            $permanentMocks[$projectMockFolder] = $projectMockNs;
        }

        foreach ($permanentMocks as $folder => $mockNamespace) {
            $dir = new Folder($folder);
            $files = $dir->find('.*\.php');
            foreach ($files as $mockFile) {
                $mockClass = $mockNamespace . '\\' . str_replace('.php', '', $mockFile);
                if (empty(self::$_disabledMocks[$mockClass])) {
                    /** @var ClassMockEntity $mockClass */
                    $mockClass::init();
                    self::$_permanentMocksList[] = $mockClass;
                }
            }
        }
    }

    /**
     * Уничтожаем моки
     *
     * @return void
     */
    public static function destroy(): void
    {
        foreach (self::$_permanentMocksList as $mockClass) {
            $mockClass::destroy();
        }

        self::$_permanentMocksList = [];
        self::$_disabledMocks = [];

        if (self::hasWarning()) {
            throw new Warning(self::getWarningMessage());
        }
    }

    /**
     * Отключение постоянного мока
     *
     * @param string $mockClass Путь к классу который мокаем
     * @return void
     */
    public static function disableMock(string $mockClass): void
    {
        self::$_disabledMocks[$mockClass] = true;
    }

    /**
     * Записать статус ошибки
     *
     * @param bool $isWarning
     * @return void
     */
    public static function setHasWarning(bool $isWarning): void
    {
        self::$_hasWarning = $isWarning;
    }

    /**
     * Узнать статус ошибки
     *
     * @return bool
     */
    public static function hasWarning(): bool
    {
        return self::$_hasWarning;
    }

    /**
     * Записать причину ошибки
     *
     * @param string $warningMessage
     * @return void
     */
    public static function setWarningMessage(string $warningMessage): void
    {
        self::$_warningMessage = $warningMessage;
    }

    /**
     * Получить сообщение об ошибке
     *
     * @return string
     */
    public static function getWarningMessage(): string
    {
        return self::$_warningMessage;
    }

    /**
     * Сбрасываем ошибки
     *
     * @return void
     */
    public static function clearWarnings(): void
    {
        self::$_hasWarning = false;
        self::$_warningMessage = '';
    }
}
