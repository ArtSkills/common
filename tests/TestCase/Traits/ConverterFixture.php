<?php
declare(strict_types=1);

namespace ArtSkills\Test\TestCase\Traits;

use ArtSkills\Traits\Converter;

class ConverterFixture
{
    use Converter;

    /** @var int hghjgj */
    public int $intField;

    /** @var string hghjgj */
    public string $stringField;

    /** @var bool hghjgj */
    public bool $boolField;
}
