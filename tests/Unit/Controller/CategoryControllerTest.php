<?php

namespace OCA\demo\tests\Unit\Controller;

require_once(__DIR__ . '/AbstractControllerTestCase.php');

namespace OCA\demo\tests\Unit\Controller;

use OCA\demo\Controller\CategoryController;
use OCA\demo\Controller\Implementation\CategoryImplementation;

/**
 * @covers \OCA\demo\Controller\CategoryController
 */
class CategoryControllerTest extends AbstractControllerTestCase {
	protected function getClassName(): string {
		return CategoryController::class;
	}

	protected function getImplementationClassName(): string {
		return CategoryImplementation::class;
	}

	protected function getMethodsAndParameters(): array {
		return [
			['name' => 'categories', 'implName' => 'index'],
			['name' => 'rename', 'args' => [['my category']], 'once' => true],
		];
	}
}
