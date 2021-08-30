<?php
declare(strict_types=1);

namespace ArtSkills\ORM;

use ArtSkills\Lib\Serializer\ArraySerializerInterface;
use ArtSkills\Lib\Serializer\SerializerFactory;

/**
 * Для описания JSON в БД в виде объекта
 */
abstract class AbstractJsonField implements ArraySerializerInterface
{
    /**
     * @inheritDoc
     * @phpstan-ignore-next-line
     */
    public static function createFromArray(array $data, array $context = [])
    {
        $type = static::class . (!empty($data[0]) ? '[]' : '');

        return SerializerFactory::create()->denormalize($data, $type, null, $context);
    }
}
