<?php
declare(strict_types=1);

namespace ArtSkills\Lib\Serializer;

use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SerializerFactory
{
    /**
     * Создание сериализатора
     *
     * @param bool $useCamelToSnakeConverter
     * @return Serializer
     */
    public static function create(bool $useCamelToSnakeConverter = false): Serializer
    {
        $convertor = $useCamelToSnakeConverter ? new CamelCaseToSnakeCaseNameConverter() : null;

        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();

        $listExtractors = [$reflectionExtractor];
        $typeExtractors = [$phpDocExtractor, $reflectionExtractor];
        $descriptionExtractors = [$phpDocExtractor];
        $accessExtractors = [$reflectionExtractor];
        $propertyInitializableExtractors = [$reflectionExtractor];

        $propertyInfo = new PropertyInfoExtractor(
            $listExtractors,
            $typeExtractors,
            $descriptionExtractors,
            $accessExtractors,
            $propertyInitializableExtractors
        );

        return new Serializer(
            [
                new DateNormalizer(),
                new ObjectNormalizer(null, $convertor, null, $propertyInfo),
                new ArrayDenormalizer(),
            ],
            [new JsonEncoder()]
        );
    }
}
