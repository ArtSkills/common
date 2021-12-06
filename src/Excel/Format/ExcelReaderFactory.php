<?php
declare(strict_types=1);

namespace ArtSkills\Excel\Format;

use ArtSkills\Error\InternalException;
use Exception;
use Throwable;

class ExcelReaderFactory
{
    /**
     * Получаем экземпляр класса исходя из типа файла
     *
     * @param string $filename
     * @return AbstractReaderFormat
     * @throws Exception
     */
    public static function createFromFile(string $filename): AbstractReaderFormat
    {
        if (!file_exists($filename)) {
            throw new InternalException("Файл $filename не существует!");
        }

        try {
            return new SpoutReaderFormat($filename);
        } catch (Throwable $exception) {
            return new DefaultReaderFormat($filename);
        }
    }
}
