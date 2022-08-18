<?php
declare(strict_types=1);

namespace TestApp\Controller\RestApi\BadPrefix;

class BadPrefixController
{
    /**
     * @OA\Get(
     *  path = "badPrefix",
     *  @OA\Response(
     *    response = 200,
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
