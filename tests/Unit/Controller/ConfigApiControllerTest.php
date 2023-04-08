<?php

namespace OCA\demo\tests\Unit\Controller;

require_once(__DIR__ . '/AbstractControllerTestCase.php');

namespace OCA\demo\tests\Unit\Controller;

use OCA\demo\Controller\ConfigApiController;
use OCA\demo\Controller\Implementation\ConfigImplementation;

/**
 * @covers OCA\demo\Controller\ConfigApiController
 */
class ConfigApiControllerTest extends AbstractControllerTestCase {
	protected function getClassName(): string {
		return ConfigApiController::class;
	}

	protected function getImplementationClassName(): string {
		return ConfigImplementation::class;
	}

	protected function getMethodsAndParameters(): array {
		return [
			['name' => 'list'],
			['name' => 'reindex'],
			['name' => 'config', 'once' => true],
		];
	}
}
