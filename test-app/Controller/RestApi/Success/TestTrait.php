<?php

declare(strict_types=1);

namespace TestApp\Controller;

trait TestTrait
{
    /**
     * @OA\Property()
     * @var string
     */
    public string $propertyFromTrait = 'testTrait';
}
