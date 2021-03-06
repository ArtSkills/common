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
 * @property int $fieldAlias blabla (алиас поля table_one_fk)
 * @property string $virtualField
 */
class TestTableTwo extends Entity
{
	/** @inheritdoc */
	protected $_aliases = [
		'fieldAlias' => 'table_one_fk',
	];

	/**
	 * @return string whoa!!!
	 */
	protected function _getVirtualField()
	{
		return 'whoa, virtual fields!';
	}
}