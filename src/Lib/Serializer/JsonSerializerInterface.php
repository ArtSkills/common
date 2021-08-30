<?php
declare(strict_types=1);

namespace ArtSkills\Lib\Serializer;

use ArtSkills\Error\InternalException;

interface JsonSerializerInterface
{
    /**
     * Преобразование строки json в объект
     *
     * @param string $json
     * @throws InternalException
     * @return static
     */
    public static function createFromJson(string $json): JsonSerializerInterface;
}
