<?php

namespace OCA\demo\tests\Unit\Controller;

require_once(__DIR__ . '/AbstractControllerTestCase.php');

namespace OCA\demo\tests\Unit\Controller;

use OCA\demo\Controller\Implementation\KeywordImplementation;
use OCA\demo\Controller\KeywordApiController;

/**
 * @covers \OCA\demo\Controller\KeywordApiController
 */
class KeywordApiControllerTest extends AbstractControllerTestCase {
	protected function getClassName(): string {
		return KeywordApiController::class;
	}

	protected function getImplementationClassName(): string {
		return KeywordImplementation::class;
	}

	protected function getMethodsAndParameters(): array {
		return [
			['name' => 'keywords', 'implName' => 'index'],
		];
	}
}
