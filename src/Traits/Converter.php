<?php
declare(strict_types=1);

namespace ArtSkills\Traits;

use ArtSkills\Error\InternalException;
use ArtSkills\Lib\Serializer\SerializerFactory;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use TypeError;

trait Converter
{
    /**
     * Создание объекта из json
     *
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @param string $json
     * @param array $context
     * @param bool $isConvertCamelCaseKeyToSnakeCase
     * @return Object
     * @throws InternalException
     */
    public static function createFromJson(string $json, array $context = [], bool $isConvertCamelCaseKeyToSnakeCase = false): object
    {
        try {
            /** @var static $dto */
            $dto = SerializerFactory::create($isConvertCamelCaseKeyToSnakeCase)->deserialize(
                $json,
                static::class,
                'json',
                $context + [AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true]
            );
        } catch (NotNormalizableValueException | ExceptionInterface $e) {
            throw new InternalException($e->getMessage());
        }

        return $dto;
    }

    /**
     * Создание объекта из массива
     *
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @param array $data
     * @param array $context
     * @param bool $isConvertCamelCaseKeyToSnakeCase
     * @return Object
     * @throws InternalException
     */
    public static function createFromArray(array $data, array $context = [], bool $isConvertCamelCaseKeyToSnakeCase = false): object
    {
        try {
            /** @var static $dto */
            $dto = SerializerFactory::create($isConvertCamelCaseKeyToSnakeCase)->denormalize(
                $data,
                static::class,
                'array',
                $context + [AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true]
            );
        } catch (NotNormalizableValueException | ExceptionInterface | TypeError $e) {
            throw new InternalException($e->getMessage());
        }

        return $dto;
    }

    /**
     * Конвертация объекта в массив
     *
     * @param bool $isConvertCamelCaseKeyToSnakeCase Конвертировать CamelCase ключи в snake_case
     * @param array $context
     * @return array
     * @throws ExceptionInterface
     * @SuppressWarnings(PHPMD.MethodArgs)
     */
    public function toArray(bool $isConvertCamelCaseKeyToSnakeCase = false, array $context = []): array
    {
        return SerializerFactory::create($isConvertCamelCaseKeyToSnakeCase)->normalize($this, 'array', $context);
    }

    /**
     * Преобразование строки в массив объектов
     *
     * @param string $json
     * @param bool $useCameToSnakeConverter
     * @return static[]
     */
    public static function createArrayFromJson(string $json, bool $useCameToSnakeConverter = false): array
    {
        return SerializerFactory::create($useCameToSnakeConverter)->deserialize($json, static::class . '[]', 'json');
    }
}