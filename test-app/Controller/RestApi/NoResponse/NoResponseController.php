<?php
declare(strict_types=1);

namespace TestApp\Controller\RestApi\NoResponse;

class NoResponseController
{
    /**
     * @OA\Get(
     *  path = "noResponse"
     * )
     */
    public function index()
    {
        // noop
    }
}
