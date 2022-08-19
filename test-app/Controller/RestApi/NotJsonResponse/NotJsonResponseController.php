<?php
declare(strict_types=1);

namespace TestApp\Controller\RestApi\NotJsonResponse;

class NotJsonResponseController
{
    /**
     * @OA\Get(
     *  path = "/notJsonResponse",
     *  @OA\Response(
     *    response = 200,
     *    description = "Результат запроса",
     *    @OA\XmlContent()
     *  )
     * )
     */
    public function index()
    {
        // noop
    }
}
