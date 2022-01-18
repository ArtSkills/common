<?php
declare(strict_types=1);

namespace ArtSkills\Test\TestCase\Lib\SerializerTest;

use ArtSkills\Error\InternalException;
use ArtSkills\Error\UserException;
use ArtSkills\Lib\Arrays;
use ArtSkills\TestSuite\AppTestCase;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class SerializerTest extends AppTestCase
{
    /**
     * Создание объекта из json-а - ошибки
     *
     * @group simple
     * @throws InternalException
     * @throws UserException
     */
    public function testCreateFromJsonException(): void
    {
        $this->expectExceptionMessage('Не указан fieldInt');
        $data = [
            'fieldString' => 'testString',
            'fieldObject' => [
                'fieldInt' => 9,
                'fieldString' => 'exampleString',
                'fieldObject' => null,
            ],
        ];
        RequestTest::createFromJson(Arrays::encode($data));
    }

    /**
     * Создание объекта из json-а
     *
     * @group simple
     * @throws ExceptionInterface
     * @throws InternalException
     * @throws UserException
     */
    public function testCreateFromJson(): void
    {
        $data = [
            'fieldInt' => 5,
            'fieldString' => 'testString',
            'fieldObject' => [
                'fieldInt' => '9',
                'fieldString' => 'exampleString',
                'fieldObject' => null,
                'objects' => [],
            ],
            'objects' => [],
        ];
        $result = RequestTest::createFromJson(Arrays::encode($data));
        $data['fieldObject']['fieldInt'] = 9;
        self::assertEquals($data, $result->toArray());
    }

    /**
     * Создание объекта из массива - ошибки
     *
     * @group simple
     * @throws InternalException
     * @throws UserException
     */
    public function testCreateFromArrayException(): void
    {
        $this->expectExceptionMessage('Не указан fieldInt');
        $data = [
            'fieldString' => 'testString',
            'fieldObject' => [
                'fieldInt' => 9,
                'fieldString' => 'exampleString',
                'fieldObject' => null,
            ],
        ];
        RequestTest::createFromArray($data);
    }

    /**
     * Создание объекта из массива
     *
     * @group simple
     * @throws ExceptionInterface
     * @throws InternalException
     * @throws UserException
     */
    public function testCreateFromArray(): void
    {
        $data = [
            'fieldInt' => 5,
            'fieldString' => 'testString',
            'fieldObject' => [
                'fieldInt' => '9',
                'fieldString' => 'exampleString',
                'fieldObject' => null,
                'objects' => [],
            ],
            'objects' => [
                [
                    'fieldInt' => 19,
                    'fieldString' => 'exampleString',
                    'testF' => 123,
                    'fieldObject' => null,
                    'objects' => [
                        [
                            'fieldInt' => 19,
                            'fieldString' => 'exampleString',
                            'fieldObject' => null,
                            'objects' => [
                                [
                                    'fieldInt' => 19,
                                    'fieldString' => 'exampleString',
                                    'fieldObject' => null,
                                    'objects' => [],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $result = RequestTest::createFromJson(Arrays::encode($data));
        $data['fieldObject']['fieldInt'] = 9;
        unset($data['objects'][0]['testF']);

        self::assertEquals($data, $result->toArray());
    }

    /**
     * @testdox Проверим конвертацию в массив
     *
     * @group simple
     * @throws ExceptionInterface
     * @throws InternalException
     * @throws UserException
     */
    public function testToArray(): void
    {
        $data = ['fieldInt' => 123, 'fieldString' => null, 'fieldObject' => null, 'objects' => []];
        $result = RequestTest::createFromArray($data);

        self::assertEquals($data, $result->toArray());

        unset($data['fieldString'], $data['fieldObject']);
        self::assertEquals(
            $data,
            $result->toArray(false, [AbstractObjectNormalizer::SKIP_NULL_VALUES => true])
        );
        self::assertEquals(
            ['field_int' => 123, 'objects' => []],
            $result->toArray(true, [AbstractObjectNormalizer::SKIP_NULL_VALUES => true])
        );
    }
}
