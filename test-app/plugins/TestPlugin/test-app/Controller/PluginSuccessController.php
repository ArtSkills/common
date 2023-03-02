<?php
declare(strict_types=1);

namespace TestPlugin\Controller;

use OpenApi\Annotations as OA;

class PluginSuccessController
{
    /**
     * @OA\Get(
     *  path = "/plugin/success",
     *  tags = {"test"},
     *  summary = "test",
     *  @OA\Response(
     *    response = 200,
     *    description = "Результат запроса",
     *    @OA\JsonContent(ref = "#/components/schemas/ApiResponse")
     *  )
     * )
     */
    public function index(): void
    {
        // noop
    }
}
