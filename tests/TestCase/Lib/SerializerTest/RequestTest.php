<?php
declare(strict_types=1);

namespace ArtSkills\Test\TestCase\Lib\SerializerTest;

use ArtSkills\Controller\Request\AbstractRequest;
use Cake\Validation\Validator;

class RequestTest extends AbstractRequest
{
    /** @var int Числовое поле */
    public int $fieldInt;

    /** @var string|null Строковое поле */
    public ?string $fieldString = null;

    /** @var RequestTest|null Объект */
    public ?RequestTest $fieldObject = null;

    /** @var RequestTest[] Объекты */
    public array $objects = [];

    /** @inheritDoc */
    public function addValidation(Validator $validator): Validator
    {
        $validator->requirePresence('fieldInt', true, 'Не указан fieldInt');
        return $validator;
    }
}
