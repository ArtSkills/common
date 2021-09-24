<?php
declare(strict_types=1);

namespace ArtSkills\Controller\Request;

use ArtSkills\Lib\Serializer\ArraySerializerInterface;
use ArtSkills\Lib\Serializer\JsonSerializerInterface;
use ArtSkills\Lib\Serializer\SerializerFactory;
use ArtSkills\Error\InternalException;
use ArtSkills\Error\UserException;
use Cake\Validation\Validator;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use TypeError;

/**
 * @SuppressWarnings(PHPMD.MethodMix)
 */
abstract class AbstractRequest implements ValidationInterface, JsonSerializerInterface, ArraySerializerInterface
{
    /**
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @phpstan-ignore-next-line
     * @throws UserException
     * @throws InternalException
     */
    public static function createFromJson(string $json, array $context = []): AbstractRequest
    {
        try {
            /** @var static $dto */
            $dto = SerializerFactory::create()->deserialize(
                $json,
                static::class,
                'json',
                $context + [AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true]
            );
            $dto->_validate();
        } catch (NotNormalizableValueException | ExceptionInterface $e) {
            throw new InternalException($e->getMessage());
        }

        return $dto;
    }

    /**
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @phpstan-ignore-next-line
     * @throws UserException
     * @throws InternalException
     */
    public static function createFromArray(array $data, array $context = []): AbstractRequest
    {
        try {
            /** @var static $dto */
            $dto = SerializerFactory::create()->denormalize(
                $data,
                static::class,
                'array',
                $context + [AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true]
            );
            $dto->_validate();
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
     * @phpstan-ignore-next-line
     */
    public function toArray(bool $isConvertCamelCaseKeyToSnakeCase = false, array $context = []): array
    {
        return SerializerFactory::create($isConvertCamelCaseKeyToSnakeCase)->normalize($this, 'array', $context);
    }

    /**
     * Проверим входные данные
     *
     * @throws ExceptionInterface
     * @throws UserException
     * @return void
     */
    protected function _validate()
    {
        $errors = $this->addValidation(new Validator())->validate($this->toArray());

        if ($errors) {
            $messages = [];
            $this->_getErrorsMessage($messages, $errors);
            throw new UserException(implode(', ', $messages));
        }
    }

    /**
     * Преобразование древовидного списка ошибок в плоский список
     *
     * @param string[] $messages
     * @param array $errors
     * @return void
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @phpstan-ignore-next-line
     */
    private function _getErrorsMessage(array &$messages, array $errors)
    {
        foreach ($errors as $error) {
            if (is_array($error)) {
                $this->_getErrorsMessage($messages, $error);
            } else {
                $messages[] = $error;
            }
        }
    }
}
