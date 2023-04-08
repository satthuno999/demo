<?php

namespace OCA\demo\tests\Unit\Helper\Filter;

use PHPUnit\Framework\TestCase;
use OCA\demo\Helper\Filter\RecipeJSONOutputFilter;
use OCA\demo\Helper\Filter\Output\EnsureNutritionPresentFilter;

class RecipeJSONOutputFilterTest extends TestCase {
	/** @var RecipeJSONOutputFilter */
	private $dut;

	private $ensureNutritionPresentFilter;

	protected function setUp(): void {
		$this->ensureNutritionPresentFilter = $this->createStub(EnsureNutritionPresentFilter::class);

		$this->dut = new RecipeJSONOutputFilter(
			$this->ensureNutritionPresentFilter
		);
		//
	}

	public function testSequence() {
		$idx = 0;
		$counter = 0;
		$closure = function () use (&$idx, &$counter) {
			$callback = function ($x) use ($idx, &$counter) {
				$this->assertEquals($idx, $counter);
				$counter += 1;
				return false;
			};
			$idx += 1;
			return $callback;
		};

		$this->ensureNutritionPresentFilter->method('apply')->willReturnCallback($closure());
		//
		$this->dut->filter([]);
	}
}
