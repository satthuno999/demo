<?php

namespace OCA\demo\Controller\Implementation;

use OCP\AppFramework\Http;
use OCA\demo\Service\RecipeService;
use OCP\AppFramework\Http\JSONResponse;
use OCA\demo\Service\DbCacheService;
use OCA\demo\Helper\RestParameterParser;

class CategoryImplementation {
	/** @var RecipeService */
	private $service;
	/** @var DbCacheService */
	private $dbCacheService;
	/** @var RestParameterParser */
	private $restParser;

	public function __construct(
		RecipeService $service,
		DbCacheService $dbCacheService,
		RestParameterParser $restParameterParser
	) {
		$this->service = $service;
		$this->dbCacheService = $dbCacheService;
		$this->restParser = $restParameterParser;
	}

	/**
	 * List all available categories.
	 *
	 * @return JSONResponse
	 */
	public function index() {
		$this->dbCacheService->triggerCheck();

		$categories = $this->service->getAllCategoriesInSearchIndex();
		return new JSONResponse($categories, 200);
	}

	/**
	 * Rename a category.
	 *
	 * @param string $category
	 * @return JSONResponse
	 */
	public function rename($category) {
		$this->dbCacheService->triggerCheck();

		$json = $this->restParser->getParameters();
		if (!$json || !isset($json['name']) || !$json['name']) {
			return new JSONResponse('New category name not found in data', 400);
		}

		$category = urldecode($category);
		try {
			$recipes = $this->service->getRecipesByCategory($category);
			foreach ($recipes as $recipe) {
				$r = $this->service->getRecipeById($recipe['recipe_id']);
				$r['recipeCategory'] = $json['name'];
				$this->service->addRecipe($r);
			}
			// Update cache
			$this->dbCacheService->updateCache();

			return new JSONResponse($json['name'], Http::STATUS_OK);
		} catch (\Exception $e) {
			return new JSONResponse($e->getMessage(), 500);
		}
	}
}
