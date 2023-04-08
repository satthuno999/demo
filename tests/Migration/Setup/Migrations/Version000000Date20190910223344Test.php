<?php

namespace OCA\demo\tests\Migration\Setup\Migrations;

include_once __DIR__ . '/AbstractMigrationTestCase.php';

class Version000000Date20190910223344Test extends AbstractMigrationTestCase {
	/**
	 * @runInSeparateProcess
	 * @covers \OCA\demo\Migration\Version000000Date20190910223344
	 */
	public function testDropPrimaryKeyFromTables() {
		$this->assertTrue($this->schema->hasTable('demo_recipes'));
		$this->assertFalse($this->schema->hasTable('demo_names'));

		// Run the migration under test
		$this->migrationService->migrate('000000Date20190910223344');
		$this->renewSchema();

		$this->assertFalse($this->schema->hasTable('demo_recipes'));
		$this->assertTrue($this->schema->hasTable('demo_names'));

		$table = $this->schema->getTable('demo_names');

		$this->assertTrue($table->hasColumn('recipe_id'));
		$this->assertTrue($table->hasColumn('name'));
		$this->assertTrue($table->hasColumn('user_id'));

		$this->assertTrue($table->getColumn('recipe_id')->getNotnull());
		$this->assertTrue($table->getColumn('name')     ->getNotnull());
		$this->assertTrue($table->getColumn('user_id')  ->getNotnull());
	}

	protected function getPreviousMigrationName(): string {
		return '000000Date20190910100911';
	}
}
