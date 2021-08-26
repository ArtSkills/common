<?php
declare(strict_types=1);

namespace ArtSkills\Lib\Serializer;

use Symfony\Component\Serializer\Exception\ExceptionInterface;

interface ArraySerializerInterface
{
    /**
     * Преобразование массива в объект
     *
     * @param array $data
     * @param array $context
     * @return static|static[]
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @phpstan-ignore-next-line
     */
    public static function createFromArray(array $data, array $context = []);
}
