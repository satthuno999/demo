<?php

namespace OCA\demo\tests\Unit\Controller;

require_once(__DIR__ . '/AbstractControllerTestCase.php');

namespace OCA\demo\tests\Unit\Controller;

use OCA\demo\Controller\Implementation\RecipeImplementation;
use OCA\demo\Controller\RecipeController;

/**
 * @covers \OCA\demo\Controller\RecipeController
 * @covers \OCA\demo\Exception\NoRecipeNameGivenException
 */
class RecipeControllerTest extends AbstractControllerTestCase {
	protected function getClassName(): string {
		return RecipeController::class;
	}

	protected function getImplementationClassName(): string {
		return RecipeImplementation::class;
	}

	protected function getMethodsAndParameters(): array {
		return [
			['name' => 'index'],
			['name' => 'show', 'args' => [[123], ['123']]],
			['name' => 'update', 'args' => [[123], ['123']]],
			['name' => 'create'],
			['name' => 'destroy', 'args' => [[123], ['123']]],
			['name' => 'image', 'args' => [[123], ['123']]],
			['name' => 'import'],
			['name' => 'search', 'args' => [['The search']]],
			['name' => 'category', 'args' => [['The category']], 'implName' => 'getAllInCategory'],
			['name' => 'tags', 'args' => [['one keyword'], ['one keyword,another one']], 'implName' => 'getAllWithTags'],
		];
	}
}
