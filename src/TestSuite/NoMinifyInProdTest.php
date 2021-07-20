<?php
declare(strict_types=1);

namespace ArtSkills\TestSuite;

use ArtSkills\Filesystem\Folder;
use Webmozart\Assert\Assert;

/**
 * Тест мониторинга на попадание min.js и css.map файлов в прод
 */
class NoMinifyInProdTest
{
    /** Тест на наличие стилей/JS */
    public function test(): void
    {
        $jsFolder = new Folder(WWW_ROOT . 'js');
        $jsList = $jsFolder->findRecursive('.*\.min\.js');
        Assert::isEmpty($jsList, 'В проект включены минифицированные скрипты в папке js');

        $cssFolder = new Folder(WWW_ROOT . 'css');
        $cssList = $cssFolder->findRecursive('.*\.map');
        Assert::isEmpty($cssList, 'В проект включены минифицированные стили в папке css');
    }
}
