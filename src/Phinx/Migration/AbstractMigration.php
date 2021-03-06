<?php

namespace ArtSkills\Phinx\Migration;

use ArtSkills\Phinx\Db\Table;

abstract class AbstractMigration extends \Phinx\Migration\AbstractMigration
{
	/**
	 * @inheritdoc
	 */
	public function table($tableName, $options = [])
	{
		return new Table($tableName, $options, $this->getAdapter());
	}

}
