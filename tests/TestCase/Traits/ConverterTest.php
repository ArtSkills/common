<?php
declare(strict_types=1);

namespace ArtSkills\Test\TestCase\Traits;

use ArtSkills\Error\InternalException;
use ArtSkills\Lib\Arrays;
use ArtSkills\TestSuite\AppTestCase;

class ConverterTest extends AppTestCase
{
    /**
     * Создание объекта из json, массива, конвертация в массив
     * @throws InternalException
     */
    public function test()
    {
        $fixture = new ConverterFixture();
        $data = [
            'intField' => 3,
            'stringField' => 'hkjhjkhkj',
            'boolField' => true,
        ];
        $resJson = $fixture->createFromJson(Arrays::encode($data));
        $resArray = $fixture->createFromArray($data);

        self::assertEquals($resArray->toArray(), $resJson->toArray());
        self::assertEquals($data, $resJson->toArray());
    }
}