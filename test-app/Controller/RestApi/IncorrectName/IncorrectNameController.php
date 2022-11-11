<?php
declare(strict_types=1);

namespace TestApp\Controller\RestApi\IncorrectName;

class IncorrectNameController
{
    /**
     * @OA\Get(
     *  path = "/incorrectName/{wbConfigId}",
     *  @OA\Parameter(
     *      in = "path",
     *      name = "wbConfigId",
     *      description = "Идентификатор кабинета",
     *      required = false,
     *      @OA\Schema(type="integer")
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
