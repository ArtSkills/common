<?php
declare(strict_types=1);

namespace TestApp\Controller\RestApi\NoResponse;

class NoResponseController
{
    /**
     * @OA\Get(
     *  path = "/noResponse",
     *  @OA\Response(
     *    response = 403,
     *    description = "Результат запроса",
     *    @OA\JsonContent(ref = "#/components/schemas/ApiResponse")
     *  )
     * )
     */
    public function index()
    {
        // noop
    }
}
