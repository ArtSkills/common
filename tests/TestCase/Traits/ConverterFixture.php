<?php
declare(strict_types=1);

namespace ArtSkills\Test\TestCase\Traits;

use ArtSkills\Traits\Converter;

class ConverterFixture
{
    use Converter;

    public int $intField;

    public string $stringField;

    public bool $boolField;
}