<?php
declare(strict_types=1);

namespace App\Test\TestCase\Shell\ValueObjectDocumentationShellTest\Entities;

/**
 * @OA\Schema()
 */
class ObjectResponse extends ObjectParentResponse
{
    /**
     * @OA\Property
     * @var float[]
     */
    public array $arrNumberProp;

    /**
     * @OA\Property
     * @var Object1[]
     */
    public array $arrObjectProp;

    /**
     * @OA\Property(type="object", ref="#/components/schemas/Object1", nullable=true)
     * @var Object1|null
     */
    public ?Object1 $objectPropOrNull = null;

    /**
     * @OA\Property()
     * @var Object1|null
     */
    public $objectPropEmptyTypeOrNull = null;
}
