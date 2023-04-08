<?php

namespace OCA\demo\Controller;

use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCA\demo\Controller\Implementation\KeywordImplementation;

class KeywordController extends Controller {
	/** @var KeywordImplementation */
	private $impl;

	public function __construct(
		string $AppName,
		IRequest $request,
		KeywordImplementation $keywordImplementation
	) {
		parent::__construct($AppName, $request);

		$this->impl = $keywordImplementation;
	}
	/**
	 * @NoAdminRequired
	 *
	 * @return JSONResponse
	 */
	public function keywords() {
		return $this->impl->index();
	}
}
