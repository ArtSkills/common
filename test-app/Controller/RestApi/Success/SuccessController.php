<?php
declare(strict_types=1);

namespace TestApp\Controller\RestApi\Success;

class SuccessController
{
    /**
     * @OA\Get(
     *  path = "/success",
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

    /**
     * @OA\Get(
     *  path = "/success/{wbConfigId}",
     *  @OA\Parameter(
     *      in = "path",
     *      name = "wbConfigId",
     *      description = "Идентификатор кабинета",
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

    /**
     * @OA\Post(
     *  path = "/success",
     *  @OA\Response(
     *    response = 200,
     *    description = "Результат запроса",
     *    @OA\JsonContent(ref = "#/components/schemas/ApiResponse")
     *  )
     * )
     */
    public function add()
    {
        // noop
    }

    /**
     * @OA\Put(
     *  path = "/success/{wbConfigId}",
     *  @OA\Parameter(
     *      in = "path",
     *      name = "wbConfigId",
     *      description = "Идентификатор кабинета",
     *      required = true,
     *      @OA\Schema(type="string")
     *  ),
     *  @OA\Response(
     *    response = 200,
     *    description = "Результат запроса",
     *    @OA\JsonContent(ref = "#/components/schemas/ApiResponse")
     *  )
     * )
     *
     * @param string $wbConfigId
     */
    public function edit(string $wbConfigId)
    {
        // noop
    }

    /**
     * @OA\Delete(
     *  path = "/success/{wbConfigId}",
     *  @OA\Parameter(
     *      in = "path",
     *      name = "wbConfigId",
     *      description = "Идентификатор кабинета",
     *      required = true,
     *      @OA\Schema(type="integer")
     *  ),
     *  @OA\Response(
     *    response = 200,
     *    description = "Результат запроса",
     *    @OA\JsonContent(ref = "#/components/schemas/ApiResponse")
     *  )
     * )
     *
     * @param int $wbConfigId
     */
    public function delete(int $wbConfigId)
    {
        // noop
    }

    /**
     * @OA\Delete(
     *  path = "/successDelete/{wbConfigId}",
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
     *
     * @param int $wbConfigId
     */
    public function successDelete(int $wbConfigId)
    {
        // noop
    }

    /**
     * @OA\Get(
     *  path = "/success",
     *  @OA\Response(
     *    response = 200,
     *    description = "Результат запроса",
     *    @OA\JsonContent(ref = "#/components/schemas/TestValueObjectWithTrait")
     *  )
     * )
     */
    public function successTraitedObject()
    {
        //noop
    }
}
