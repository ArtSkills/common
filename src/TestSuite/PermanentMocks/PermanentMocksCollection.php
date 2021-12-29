<?php
declare(strict_types=1);

namespace ArtSkills\TestSuite\PermanentMocks;

use ArtSkills\Error\InternalException;
use ArtSkills\Filesystem\Folder;
use ArtSkills\Lib\Env;
use ArtSkills\Lib\Misc;
use ArtSkills\TestSuite\ClassMockEntity;

/**
 * Класс для подмены методов, необходимых только в тестовом окружении
 */
class PermanentMocksCollection
{
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
     * Инициалилзируем подмену методов
     *
     * @return void
     * @throws InternalException
     */
    public static function init(): void
    {
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

        // $permanentMocks
        // /var/www/dev-alex.common/src/TestSuite/PermanentMocks = "ArtSkills\TestSuite\PermanentMocks"
        // /var/www/dev-alex.common/tests/Suite/Mock/ = "App\Test\Suite\Mock"

        // $dir
        // _virtualPath = "/var/www/dev-alex.common/src/TestSuite/PermanentMocks"
        // path = "/var/www/dev-alex.common/src/TestSuite/PermanentMocks"

        // $files (3 файла):
        // MockConsoleOutput.php
        // MockLog.php
        // MockFileLog.php
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
}
