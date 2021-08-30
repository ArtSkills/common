<?php
declare(strict_types=1);

namespace ArtSkills\Lib\Serializer;

use Cake\I18n\Date;
use Cake\I18n\Time;

/** TODO: Нужно вынести в common */
use App\Lib\Time as AppTime;
use App\Lib\Date as AppDate;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class DateNormalizer extends DateTimeNormalizer
{
    /**
     * @var array<string,bool> Поддерживаемые типы объектов
     */
    private const SUPPORTED_TYPES = [
        Date::class => true,
        Time::class => true,
        AppTime::class => true,
        AppDate::class => true,
    ];

    /**
     * {@inheritdoc}
     *
     * @param mixed $data
     * @param string $type
     * @param null|string $format
     * @return bool
     */
    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return isset(self::SUPPORTED_TYPES[$type]);
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $data
     * @param string $type
     * @param null|string $format
     * @param array $context
     * @return Date|Time|AppTime|AppDate
     *
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @phpstan-ignore-next-line
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        if ('' === $data || null === $data) {
            throw new NotNormalizableValueException('The data is either an empty string or null, you should pass a string that can be parsed with the passed format or a valid DateTime string.');
        }

        /** @phpstan-ignore-next-line */
        switch ($type) {
            case Time::class:
                return Time::parse($data);
            case Date::class:
                return Date::parse($data);
            case AppTime::class:
                return AppTime::parse($data);
            case AppDate::class:
                return AppDate::parse($data);
        }
    }

    /**
     * @inheritDoc
     * @phpstan-ignore-next-line
     */
    public function normalize($object, $format = null, array $context = []): string
    {
        return $object->toDateString();
    }
}
