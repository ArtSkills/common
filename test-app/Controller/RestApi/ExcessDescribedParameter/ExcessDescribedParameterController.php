<?php
declare(strict_types=1);

namespace TestApp\Controller\RestApi\ExcessDescribedParameter;

class ExcessDescribedParameterController
{
    /**
     * @OA\Get(
     *  path = "excessDescribedParameter/{wbConfigId}",
     *  @OA\Parameter(
     *      in = "path",
     *      name = "wbConfigId",
     *      description = "Идентификатор кабинета",
     *      required = false,
     *      @OA\Schema(type="string")
     *  ),
     *  @OA\Parameter(
     *      in = "path",
     *      name = "excess",
     *      description = "Лишний параметр",
     *      required = false,
     *      @OA\Schema(type="string")
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
