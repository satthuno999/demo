<?php

namespace OCA\demo\tests\Unit\Controller;

use OCP\IRequest;
use PHPUnit\Framework\TestCase;
use OCA\demo\Controller\UtilApiController;

/**
 * @covers \OCA\demo\Controller\UtilApiController
 * @covers \OCA\demo\Exception\UserFolderNotWritableException
 */
class UtilApiControllerTest extends TestCase {
	/**
	 * @var UtilApiController
	 */
	private $sut;

	public function setUp(): void {
		parent::setUp();

		$request = $this->createStub(IRequest::class);

		$this->sut = new UtilApiController(
			'demo',
			$request
		);
	}

	public function testGetAPIVersion(): void {
		$ret = $this->sut->getApiVersion();

		$this->assertEquals(200, $ret->getStatus());

		$retData = $ret->getData();
		$this->assertArrayHasKey('demo_version', $retData);
		$this->assertGreaterThanOrEqual(3, count($retData['demo_version']));
		$this->assertLessThanOrEqual(4, count($retData['demo_version']));
		$this->assertIsInt($retData['demo_version'][0]);
		$this->assertIsInt($retData['demo_version'][1]);
		$this->assertIsInt($retData['demo_version'][2]);
		if (count($retData['demo_version']) === 4) {
			$this->assertIsString($retData['demo_version'][3]);
		}

		$this->assertArrayHasKey('api_version', $retData);
		$this->assertArrayHasKey('epoch', $retData['api_version']);
		$this->assertArrayHasKey('major', $retData['api_version']);
		$this->assertArrayHasKey('minor', $retData['api_version']);
	}
}
