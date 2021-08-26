<?php
declare(strict_types=1);

namespace ArtSkills\Test\TestCase\Lib\SerializerTest;

use ArtSkills\Controller\Request\AbstractRequest;
use Cake\Validation\Validator;

class RequestTest extends AbstractRequest
{
    public int $fieldInt;

    public ?string $fieldString = null;

    public ?RequestTest $fieldObject = null;

    /** @inheritDoc */
    public function addValidation(Validator $validator): Validator
    {
        $validator->requirePresence('fieldInt', true, 'Не указан fieldInt');
        return $validator;
    }
}
