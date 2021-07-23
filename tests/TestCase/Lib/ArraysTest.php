<?php
declare(strict_types=1);

namespace ArtSkills\Test\TestCase\Lib;

use ArtSkills\Lib\Arrays;
use ArtSkills\TestSuite\AppTestCase;
use stdClass;

class ArraysTest extends AppTestCase
{

    /** decode JSON */
    public function testDecode(): void
    {
        // тестовые данные
        $wrongJson = '{"state":0,"data":{"products":[{"id":13012188,"root":8691178},{"id":13012189,"root":8691179';
        $jsonVsWrongSymbols = '{"state":"active' . chr(2) . '","data":{"products":[{"id":13012188,"root":8691178},{"id":13012189,"root":8691179}]}}';
        $correctJson = '{"state":"active","data":{"products":[{"id":13012188,"root":8691178},{"id":13012189,"root":8691179}]}}';

        // данные для сравнения
        $product1 = new stdClass();
        $product1->id = 13012188;
        $product1->root = 8691178;

        $product2 = new stdClass();
        $product2->id = 13012189;
        $product2->root = 8691179;

        $object = new stdClass();
        $object->state = 'active';
        $object->data = new stdClass();
        $object->data->products = [
            $product1,
            $product2,
        ];

        self::assertEquals($object, Arrays::decode($correctJson, false));
        self::assertEquals($object, Arrays::decode($jsonVsWrongSymbols, false));
        self::assertNull(Arrays::decode($wrongJson, false));
    }

    /** фильтр части ключей */
    public function testFilterKeys(): void
    {
        $array = [
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'd' => 4,
        ];
        $expectedResult = [
            'a' => 1,
            'c' => 3,
        ];
        self::assertEquals($expectedResult, Arrays::filterKeys($array, ['a', 'c']));
    }

    /** значения в ключи */
    public function testKeysFromValues(): void
    {
        $values = ['a', 'b', 'c'];
        $expectedResult = [
            'a' => 'a',
            'b' => 'b',
            'c' => 'c',
        ];
        self::assertEquals($expectedResult, Arrays::keysFromValues($values));
    }

    /**
     * смена ключей массива
     */
    public function testRemap(): void
    {
        $array = [
            'before_1' => 'asd',
            'before_2' => 'qwe',
            'before_4' => 'zxc',
        ];
        $map = [
            'before_1' => 'after1',
            'before_2' => 'after2',
            'before_3' => 'after3',
        ];
        $expectedWithNull = [
            'after1' => 'asd',
            'after2' => 'qwe',
            'after3' => null,
        ];
        $expectedStrict = [
            'after1' => 'asd',
            'after2' => 'qwe',
        ];
        self::assertEquals($expectedWithNull, Arrays::remap($array, $map, true));
        self::assertEquals($expectedStrict, Arrays::remap($array, $map, false));
    }

    /** получение */
    public function testGet(): void
    {
        $default = 'default';
        $hasKey = 'hasKey';
        $hasNotKey = 'hasNotKey';
        $value = 'value';

        $array = [$hasKey => $value];
        self::assertEquals($value, Arrays::get($array, $hasKey));
        self::assertEquals($default, Arrays::get($array, $hasNotKey, $default));
        self::assertEquals(null, Arrays::get($array, $hasNotKey));
        self::assertEquals(null, Arrays::get(null, $hasNotKey));
    }

    /**
     * инициализация значения
     *
     * @throws \ArtSkills\Error\InternalException
     */
    public function testInitPath(): void
    {
        $array = [];
        $key = 'key1';
        $value = 'test';
        $newValue = 'asd';

        // ключа нет - запишется
        Arrays::initPath($array, $key, $value);
        self::assertEquals($value, $array[$key]);

        // ключ уже есть - не запишется
        Arrays::initPath($array, $key, $newValue);
        self::assertEquals($value, $array[$key]);

        // вложенность
        $keyNestedFirst = 'nest1';
        $keyNestedSecond = 'nest2';
        $keyNestedThird = 'nest3';
        $value = 'test1';

        // ключа нет - запишется
        Arrays::initPath($array, [$keyNestedFirst, $keyNestedSecond, $keyNestedThird], $value);
        self::assertEquals($value, $array[$keyNestedFirst][$keyNestedSecond][$keyNestedThird]);

        // ключ уже есть - не запишется
        Arrays::initPath($array, [$keyNestedFirst, $keyNestedSecond, $keyNestedThird], $newValue);
        self::assertEquals($value, $array[$keyNestedFirst][$keyNestedSecond][$keyNestedThird]);

        // часть пути уже есть
        $keyNestedThird = 'nest3.1';
        $value = 'test2';
        Arrays::initPath($array, [$keyNestedFirst, $keyNestedSecond, $keyNestedThird], $value);
        self::assertEquals($value, $array[$keyNestedFirst][$keyNestedSecond][$keyNestedThird]);
    }

    /**
     * на пути есть немассив
     *
     * @throws \ArtSkills\Error\InternalException
     */
    public function testInitPathFail(): void
    {
        $this->expectExceptionMessage("По ключу nest2 находится не массив");
        $this->expectException(\Exception::class);
        $keyNestedFirst = 'nest1';
        $keyNestedSecond = 'nest2';
        $keyNestedThird = 'nest3';
        $value = 'test';
        $array = [
            $keyNestedFirst => [
                $keyNestedSecond => 'asd',
            ],
        ];

        Arrays::initPath($array, [$keyNestedFirst, $keyNestedSecond, $keyNestedThird], $value);
    }

    /** Сравнение */
    public function testEquals(): void
    {
        $keyEquals = '_key';
        $keyNotEquals = '_notEq';
        $keyNotExists = '_notExists';
        $keyNumString = '_numString';
        $number = 123;
        $value = '_val';
        $badValue = 'asdfg';
        $arr = [
            $keyEquals => $value,
            $keyNotEquals => $badValue,
            $keyNumString => (string)$number,
        ];
        self::assertTrue(Arrays::equals($arr, $keyEquals, $value));
        self::assertTrue(Arrays::equalsAny($arr, $keyEquals, [$value]));
        self::assertTrue(Arrays::equalsAny($arr, $keyEquals, [$value, $badValue]));
        self::assertTrue(Arrays::equalsAny($arr, $keyEquals, [$badValue, $value]));
        self::assertFalse(Arrays::equalsAny($arr, $keyEquals, [$badValue]));

        self::assertFalse(Arrays::equals($arr, $keyNotEquals, $value));
        self::assertFalse(Arrays::equalsAny($arr, $keyNotEquals, [$value]));

        self::assertFalse(Arrays::equals($arr, $keyNotExists, $value));
        self::assertFalse(Arrays::equalsAny($arr, $keyNotExists, [$value]));


        self::assertFalse(Arrays::equals($arr, $keyNumString, $number));
        self::assertTrue(Arrays::equals($arr, $keyNumString, $number, false));
    }
}
