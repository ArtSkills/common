<?php
declare(strict_types=1);

namespace ArtSkills\Test\TestCase\Traits;

use ArtSkills\Error\InternalException;
use ArtSkills\Error\UserException;
use ArtSkills\Lib\Arrays;
use ArtSkills\TestSuite\AppTestCase;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class ConverterTraitTest extends AppTestCase
{
    /**
     * Создание объекта из json, массива, конвертация в массив
     *
     * @throws InternalException
     * @throws UserException
     * @throws ExceptionInterface
     */
    public function test(): void
    {
        $fixture = new ConverterFixture();
        $data = [
            'intField' => 3,
            'stringField' => 'hkjhjkhkj',
            'boolField' => true,
        ];
        $resJson = $fixture::createFromJson(Arrays::encode($data));
        $resArray = $fixture::createFromArray($data);
        $resArrayFromJson = $fixture::createArrayFromJson(Arrays::encode([
            'test1' => $data,
            'test2' => $data,
        ]));

        self::assertEquals($resArray->toArray(), $resJson->toArray());
        self::assertEquals($data, $resJson->toArray());
        self::assertEquals([
            'test1' => $fixture::createFromArray($data),
            'test2' => $fixture::createFromArray($data),
        ], $resArrayFromJson);
    }
}
