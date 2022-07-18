<?php
declare(strict_types=1);

namespace TestApp\Controller\RestApi\NotDescribedParameter;

class NotDescribedParameterController
{
    /**
     * @OA\Get(
     *  path = "notDescribedParameter/{wbConfigId}",
     *  @OA\Response(
     *    response = 200,
     *    description = "Результат запроса",
     *    @OA\JsonContent(ref = "#/components/schemas/ApiResponse")
     *  )
     * )
     */
    public function view(string $id)
    {
        // noop
    }
}
