<?php

namespace OCA\demo\tests\Unit\Controller;

use OCP\IRequest;
use PHPUnit\Framework\TestCase;
use OCA\demo\Service\DbCacheService;
use OCA\demo\Controller\MainController;
use PHPUnit\Framework\MockObject\MockObject;
use OCA\demo\Exception\UserFolderNotWritableException;
use OCA\demo\Helper\UserFolderHelper;
use OCP\Files\Folder;

/**
 * @covers \OCA\demo\Controller\MainController
 * @covers \OCA\demo\Exception\UserFolderNotWritableException
 */
class MainControllerTest extends TestCase {
	/**
	 * @var DbCacheService|MockObject
	 */
	private $dbCacheService;
	/**
	 * @var UserFolderHelper|MockObject
	 */
	private $userFolder;

	/**
	 * @var MainController
	 */
	private $sut;

	public function setUp(): void {
		parent::setUp();

		$request = $this->createStub(IRequest::class);
		$this->dbCacheService = $this->createMock(DbCacheService::class);
		$this->userFolder = $this->createMock(UserFolderHelper::class);

		$this->sut = new MainController(
			'demo',
			$request,
			$this->dbCacheService,
			$this->userFolder
		);
	}

	private function ensureCacheCheckTriggered(): void {
		$this->dbCacheService->expects($this->once())->method('triggerCheck');
	}

	public function testIndex(): void {
		$this->ensureCacheCheckTriggered();
		$userFolder = $this->createStub(Folder::class);
		$this->userFolder->method('getFolder')->willReturn($userFolder);

		$ret = $this->sut->index();

		$this->assertEquals(200, $ret->getStatus());
		$this->assertEquals('index', $ret->getTemplateName());
	}

	public function testIndexInvalidUser(): void {
		$this->userFolder->method('getFolder')->willThrowException(new UserFolderNotWritableException());
		$ret = $this->sut->index();
		$this->assertEquals(200, $ret->getStatus());
		$this->assertEquals('invalid_guest', $ret->getTemplateName());
	}
}
