<?php

namespace OCA\demo\tests\Unit\Controller;

require_once(__DIR__ . '/AbstractControllerTestCase.php');

namespace OCA\demo\tests\Unit\Controller;

use OCA\demo\Controller\Implementation\KeywordImplementation;
use OCA\demo\Controller\KeywordController;

/**
 * @covers \OCA\demo\Controller\KeywordController
 */
class KeywordControllerTest extends AbstractControllerTestCase {
	protected function getClassName(): string {
		return KeywordController::class;
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
