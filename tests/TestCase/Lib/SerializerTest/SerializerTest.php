<?php
declare(strict_types=1);

namespace ArtSkills\Test\TestCase\Lib\SerializerTest;

use ArtSkills\Error\InternalException;
use ArtSkills\Error\UserException;
use ArtSkills\Lib\Arrays;
use ArtSkills\TestSuite\AppTestCase;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class SerializerTest extends AppTestCase
{
    /**
     * Создание объекта из json-а - ошибки
     *
     * @throws InternalException
     */
    public function testCreateFromJsonException(): void
    {
        $this->expectExceptionMessage('Не указан fieldInt');
        $data = [
            'fieldString' => 'hjk,mn',
            'fieldObject' => [
                'fieldInt' => 9,
                'fieldString' => 'hkljlkln',
                'fieldObject' => null,
            ],
        ];
        RequestTest::createFromJson(Arrays::encode($data));
    }

    /**
     * Создание объекта из json-а
     *
     * @throws ExceptionInterface
     * @throws InternalException
     */
    public function testCreateFromJson(): void
    {
        $data = [
            'fieldInt' => 5,
            'fieldString' => 'hjk,mn',
            'fieldObject' => [
                'fieldInt' => 9,
                'fieldString' => 'hkljlkln',
                'fieldObject' => null,
            ],
        ];
        self::assertEquals($data, RequestTest::createFromJson(Arrays::encode($data))->toArray());
    }

    /**
     * Создание объекта из массива - ошибки
     *
     * @throws InternalException
     * @throws UserException
     */
    public function testCreateFromArrayException(): void
    {
        $this->expectExceptionMessage('Не указан fieldInt');
        $data = [
            'fieldString' => 'hjk,mn',
            'fieldObject' => [
                'fieldInt' => 9,
                'fieldString' => 'hkljlkln',
                'fieldObject' => null,
            ],
        ];
        RequestTest::createFromArray($data);
    }

    /**
     * Создание объекта из массива
     *
     * @throws ExceptionInterface
     * @throws InternalException
     * @throws UserException
     */
    public function testCreateFromArray(): void
    {
        $data = [
            'fieldInt' => 5,
            'fieldString' => 'hjk,mn',
            'fieldObject' => [
                'fieldInt' => 9,
                'fieldString' => 'hkljlkln',
                'fieldObject' => null,
            ],
        ];
        self::assertEquals($data, RequestTest::createFromArray($data)->toArray());
    }
}
