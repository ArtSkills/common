<?php
declare(strict_types=1);

namespace TestApp\Controller;

use ArtSkills\Controller\Response\ApiResponse;

class TestApiResponse extends ApiResponse
{
    /** @var string Тестовое свойство */
    public string $testProperty = 'testData';
}
