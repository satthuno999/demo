<?php

namespace OCA\demo\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version000000Date20190312140601 extends SimpleMigrationStep {
	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('demo_recipes')) {
			$table = $schema->createTable('demo_recipes');
			$table->addColumn('recipe_id', 'integer', [
				'notnull' => true,
			]);
			$table->addColumn('name', 'string', [
				'notnull' => true,
				'length' => 128,
			]);

			$table->setPrimaryKey(['recipe_id']);
		}

		if (!$schema->hasTable('demo_keywords')) {
			$table = $schema->createTable('demo_keywords');
			$table->addColumn('recipe_id', 'integer', [
				'notnull' => true,
			]);
			$table->addColumn('name', 'string', [
				'notnull' => true,
				'length' => 64,
			]);
		}

		return $schema;
	}
}
