<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use ArtSkills\ORM\Entity;

/**
 * @property int $id
 * @property int $table_one_fk blabla
 * @property string $col_text = NULL
 * @property TestTableOne $TestTableOne `table_one_fk` => `id`
 * @tableComment description qweqwe
 * @property string $virtualField
 * @property string|null $virtualFieldOrNull
 */
class TestTableTwo extends Entity
{
    /**
     * @return string whoa!!!
     */
    protected function _getVirtualField()
    {
        return 'whoa, virtual fields!';
    }

    /**
     * @return string|null
     */
    protected function _getVirtualFieldOrNull(): ?string
    {
        return null;
    }
}
