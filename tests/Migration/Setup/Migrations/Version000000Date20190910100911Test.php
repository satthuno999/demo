<?php

namespace OCA\demo\tests\Migration\Setup\Migrations;

include_once __DIR__ . '/AbstractMigrationTestCase.php';

class Version000000Date20190910100911Test extends AbstractMigrationTestCase {
	/**
	 * @runInSeparateProcess
	 * @covers \OCA\demo\Migration\Version000000Date20190910100911
	 */
	public function testAddedUserIdToTables() {
		$this->preTestAsserts('demo_recipes');
		$this->preTestAsserts('demo_keywords');

		// Run the migration under test
		$this->migrationService->migrate('000000Date20190910100911');
		$this->renewSchema();

		$this->postTestAsserts('demo_recipes');
		$this->postTestAsserts('demo_keywords');
	}

	private function preTestAsserts(string $tableName): void {
		$this->assertTrue($this->schema->hasTable($tableName));
		$table = $this->schema->getTable($tableName);
		$this->assertFalse($table->hasColumn('user_id'));
	}

	private function postTestAsserts(string $tableName): void {
		$this->assertTrue($this->schema->hasTable($tableName));
		$table = $this->schema->getTable($tableName);
		$this->assertTrue($table->hasColumn('user_id'));
		$column = $table->getColumn('user_id');
		$this->assertTrue($column->getNotnull());
	}

	protected function getPreviousMigrationName(): string {
		return '000000Date20190312140601';
	}
}
