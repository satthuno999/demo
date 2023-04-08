<?php

namespace OCA\demo\tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use OCA\demo\Helper\HTMLParser\HttpJsonLdParser;
use OCA\demo\Helper\HTMLParser\HttpMicrodataParser;
use OCA\demo\Exception\HtmlParsingException;
use OCP\IL10N;
use OCA\demo\Service\RecipeExtractionService;
use PHPUnit\Framework\MockObject\MockObject;

class RecipeExtractionServiceTest extends TestCase {
	/**
	 * @var IL10N
	 */
	private $l;

	protected function setUp(): void {
		$this->l = $this->createStub(IL10N::class);
	}

	/**
	 * @dataProvider dataProvider
	 * @param bool $jsonSuccess
	 * @param bool $microdataSuccess
	 * @param bool $exceptionExpected
	 */
	public function testParsingDelegation($jsonSuccess, $microdataSuccess, $exceptionExpected): void {
		/** @var HttpJsonLdParser|MockObject $jsonParser */
		$jsonParser = $this->createMock(HttpJsonLdParser::class);
		/** @var HttpMicrodataParser|MockObject $microdataParser */
		$microdataParser = $this->createMock(HttpMicrodataParser::class);

		$document = $this->createStub(\DOMDocument::class);
		$url = 'http://example.com';
		$expectedObject = [new \stdClass()];

		if ($jsonSuccess) {
			$jsonParser->expects($this->once())
				->method('parse')
				->with($document, $url)
				->willReturn($expectedObject);

			$microdataParser->expects($this->never())->method('parse');
		} else {
			$jsonParser->expects($this->once())
				->method('parse')
				->with($document, $url)
				->willThrowException(new HtmlParsingException());

			if ($microdataSuccess) {
				$microdataParser->expects($this->once())
					->method('parse')
					->with($document, $url)
					->willReturn($expectedObject);
			} else {
				$microdataParser->expects($this->once())
					->method('parse')
					->with($document, $url)
					->willThrowException(new HtmlParsingException());
			}
		}

		$sut = new RecipeExtractionService($jsonParser, $microdataParser, $this->l);

		try {
			$ret = $sut->parse($document, $url);

			$this->assertEquals($expectedObject, $ret);
		} catch (HtmlParsingException $ex) {
			$this->assertTrue($exceptionExpected);
		}
	}

	public function dataProvider() {
		return [
			[true, false, false],
			[false, true, false],
			[false, false, true],
		];
	}
}
