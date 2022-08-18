<?php
declare(strict_types=1);

namespace TestApp\Controller\RestApi\IncorrectType;

class IncorrectTypeController
{
    /**
     * @OA\Get(
     *  path = "/incorrectType/{wbConfigId}",
     *  @OA\Parameter(
     *      in = "path",
     *      name = "wbConfigId",
     *      description = "Идентификатор кабинета",
     *      required = false,
     *      @OA\Schema(type="float")
     *  ),
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
