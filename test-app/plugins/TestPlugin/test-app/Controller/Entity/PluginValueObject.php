<?php
declare(strict_types=1);

namespace TestPlugin\Controller\Entity;

use ArtSkills\ValueObject\ValueObject;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema()
 */
class PluginValueObject extends ValueObject
{
    /**
     * Тестовое свойство
     *
     * @OA\Property
     * @var string
     */
    public string $pluginProperty = 'testData';
}
