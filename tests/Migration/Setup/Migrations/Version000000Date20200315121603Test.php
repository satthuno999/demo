<?php

namespace OCA\demo\tests\Migration\Setup\Migrations;

include_once __DIR__ . '/AbstractMigrationTestCase.php';

class Version000000Date20200315121603Test extends AbstractMigrationTestCase {
	/**
	 * @runInSeparateProcess
	 * @covers \OCA\demo\Migration\Version000000Date20200315121603
	 */
	public function testAddCategoriesTable() {
		$this->assertFalse($this->schema->hasTable('demo_categories'));

		// Run the migration under test
		$this->migrationService->migrate('000000Date20210427082010');
		$this->renewSchema();

		$this->assertTrue($this->schema->hasTable('demo_categories'));

		$table = $this->schema->getTable('demo_categories');

		$this->assertTrue($table->hasColumn('recipe_id'));
		$this->assertTrue($table->hasColumn('name'));
		$this->assertTrue($table->hasColumn('user_id'));

		$this->assertTrue($table->getColumn('recipe_id')->getNotnull());
		$this->assertTrue($table->getColumn('name')     ->getNotnull());
		$this->assertTrue($table->getColumn('user_id')  ->getNotnull());
	}

	protected function getPreviousMigrationName(): string {
		return '000000Date20190910223344';
	}
}
