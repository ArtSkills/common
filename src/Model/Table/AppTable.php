<?php
declare(strict_types=1);

namespace App\Model\Table;

use ArtSkills\ORM\Table;
use Cake\Event\Event;
use Cake\ORM\Entity;

abstract class AppTable extends Table
{
    const CACHE_PROFILE = 'default';

    /**
     * Массив полей, которые необходимо приводить к NULL при сохранении пустых данных
     */
    const NULLABLE_EMPTY_FIELDS = [];

    /**
     * триггер
     *
     * @param Event $event
     * @param Entity $entity
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(Event $event, Entity $entity)
    {
        foreach (static::NULLABLE_EMPTY_FIELDS as $nullField) {
            if ($entity->isDirty($nullField) && empty($entity->{$nullField}) && $entity->{$nullField} !== null) {
                $entity->{$nullField} = null;
            }
        }
    }
}
