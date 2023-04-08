<?php

namespace OCA\demo\Controller\Implementation;

use OCA\demo\Service\RecipeService;
use OCP\AppFramework\Http\JSONResponse;
use OCA\demo\Service\DbCacheService;

class KeywordImplementation {
	/** @var RecipeService */
	private $service;
	/** @var DbCacheService */
	private $dbCacheService;

	public function __construct(
		RecipeService $recipeService,
		DbCacheService $dbCacheService
	) {
		$this->service = $recipeService;
		$this->dbCacheService = $dbCacheService;
	}
	/**
	 * List all available keywords.
	 *
	 * @return JSONResponse
	 */
	public function index() {
		$this->dbCacheService->triggerCheck();

		$keywords = $this->service->getAllKeywordsInSearchIndex();
		return new JSONResponse($keywords, 200);
	}
}
