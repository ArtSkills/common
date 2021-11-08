<?php
declare(strict_types=1);

namespace ArtSkills\Test\TestCase\Traits;

use ArtSkills\Traits\ConverterTrait;

class ConverterFixture
{
    use ConverterTrait;

    /** @var int Числовое поле */
    public int $intField;

    /** @var string Строковое поле */
    public string $stringField;

    /** @var bool Булевое поле */
    public bool $boolField;
}
