<?php
declare(strict_types=1);

namespace TestApp\Controller\RestApi\DeprecatedExtension;

class DeprecatedExtensionController
{
    /**
     * @OA\Get(
     *  path = "deprecatedExtension.json",
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
