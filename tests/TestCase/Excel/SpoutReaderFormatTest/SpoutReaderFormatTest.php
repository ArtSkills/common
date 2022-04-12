<?php
declare(strict_types=1);

namespace ArtSkills\Test\TestCase\Excel\SpoutReaderFormatTest;

use ArtSkills\Excel\Format\SpoutReaderFormat;
use ArtSkills\TestSuite\AppTestCase;

class SpoutReaderFormatTest extends AppTestCase
{
    /**
     * @testdox Проверим чтение пустых строк
     */
    public function testSkipEmptyRows(): void
    {
        $testFile = __DIR__ . DS . 'test_xls.xlsx';

        // Выводим пустые строки
        $reader = new SpoutReaderFormat($testFile);
        $result = $reader->getRows(1, 34, false);
        self::assertEquals([
            0 => [
                0 => null,
                1 => null,
                2 => null,
                3 => null,
                4 => null,
                5 => null,
                6 => null,
                7 => null,
                8 => null,
                9 => null,
                10 => null,
                11 => null,
                12 => null,
                13 => null,
                14 => null,
                15 => null,
                16 => null,
                17 => null,
                18 => null,
                19 => null,
                20 => null,
            ],
        ], $result);

        // Не выводим пустые строки
        $reader = new SpoutReaderFormat($testFile);
        self::assertCount(5, $reader->getRows(1, 1));
        $result = $reader->getRows(1, 34);
        self::assertEquals([], $result);
    }
}
