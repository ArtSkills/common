<?php
declare(strict_types=1);

namespace ArtSkills\Lib\Serializer;

interface JsonSerializerInterface
{
    /**
     * Преобразование строки json в объект
     *
     * @param string $json
     * @param array $context
     * @return static
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @phpstan-ignore-next-line
     */
    public static function createFromJson(string $json, array $context = []): JsonSerializerInterface;
}
