<?php

namespace OCA\demo\tests\Unit\Helper\Filter\JSON;

use OCA\demo\Helper\Filter\JSON\FixUrlFilter;
use OCA\demo\Helper\TextCleanupHelper;
use OCP\IL10N;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class FixUrlFilterTest extends TestCase {
	/** @var FixUrlFilter */
	private $dut;

	/** @var TextCleanupHelper|MockObject */
	private $textCleanupHelper;

	/** @var array */
	private $stub;

	protected function setUp(): void {
		/** @var Stub|IL10N */
		$l = $this->createStub(IL10N::class);
		$l->method('t')->willReturnArgument(0);
		$logger = $this->createStub(LoggerInterface::class);

		$this->dut = new FixUrlFilter($l, $logger);

		$this->stub = [
			'name' => 'The name of the recipe',
			'id' => 1234,
		];
	}

	public function testNonExisting() {
		$recipe = $this->stub;

		$this->assertTrue($this->dut->apply($recipe));

		$this->stub['url'] = '';
		$this->assertEquals($this->stub, $recipe);
	}

	public function dpApply() {
		// ["abc\n\n", "abc", true],
		yield ['', '', false];
		yield ['http://example.com', 'http://example.com', false];
		yield ['Just some arbitrary text', '', true];
		$longUrl = 'https://user:pwd@domain.com/path/name?parameters=abc&other=foo';
		yield [$longUrl, $longUrl, false];
		// yield ['http://example.com/<>', 'http://example.com/', true];
	}

	/** @dataProvider dpApply */
	public function testApply($oldValue, $expectedValue, $shouldChange) {
		$recipe = $this->stub;
		$recipe['url'] = $oldValue;

		$ret = $this->dut->apply($recipe);

		$this->stub['url'] = $expectedValue;
		$this->assertEquals($this->stub, $recipe);
		$this->assertEquals($shouldChange, $ret);
	}
}
