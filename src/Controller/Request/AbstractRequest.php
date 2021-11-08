<?php
declare(strict_types=1);

namespace ArtSkills\Controller\Request;

use ArtSkills\Traits\ConverterTrait;

/**
 * @SuppressWarnings(PHPMD.MethodMix)
 */
abstract class AbstractRequest implements ValidationInterface
{
    use ConverterTrait;
}
