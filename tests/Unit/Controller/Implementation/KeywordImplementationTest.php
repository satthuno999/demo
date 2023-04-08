<?php

namespace OCA\demo\tests\Unit\Controller\Implementation;

use OCA\demo\Controller\Implementation\KeywordImplementation;
use PHPUnit\Framework\TestCase;
use OCA\demo\Service\RecipeService;
use OCA\demo\Service\DbCacheService;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \OCA\demo\Controller\Implementation\KeywordImplementation
 */
class KeywordImplementationTest extends TestCase {
	/**
	 * @var MockObject|RecipeService
	 */
	private $recipeService;
	/**
	 * @var DbCacheService|MockObject
	 */
	private $dbCacheService;

	/**
	 * @var KeywordImplementation
	 */
	private $sut;

	public function setUp(): void {
		parent::setUp();

		$this->recipeService = $this->createMock(RecipeService::class);
		$this->dbCacheService = $this->createMock(DbCacheService::class);

		$this->sut = new KeywordImplementation(
			$this->recipeService,
			$this->dbCacheService
		);
	}

	private function ensureCacheCheckTriggered(): void {
		$this->dbCacheService->expects($this->once())->method('triggerCheck');
	}



	public function testGetKeywords(): void {
		$this->ensureCacheCheckTriggered();

		$kw = ['Foo', 'Bar', 'Baz'];
		$this->recipeService->method('getAllKeywordsInSearchIndex')->willReturn($kw);

		$ret = $this->sut->index();

		$this->assertEquals(200, $ret->getStatus());
		$this->assertEquals($kw, $ret->getData());
	}
}
