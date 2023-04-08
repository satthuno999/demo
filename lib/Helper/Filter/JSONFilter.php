<?php

namespace OCA\demo\Helper\Filter;

use OCA\demo\Helper\Filter\JSON\CleanCategoryFilter;
use OCA\demo\Helper\Filter\JSON\ExtractImageUrlFilter;
use OCA\demo\Helper\Filter\JSON\FixDescriptionFilter;
use OCA\demo\Helper\Filter\JSON\FixDurationsFilter;
use OCA\demo\Helper\Filter\JSON\FixImageSchemeFilter;
use OCA\demo\Helper\Filter\JSON\FixIngredientsFilter;
use OCA\demo\Helper\Filter\JSON\FixInstructionsFilter;
use OCA\demo\Helper\Filter\JSON\FixKeywordsFilter;
use OCA\demo\Helper\Filter\JSON\FixNutritionFilter;
use OCA\demo\Helper\Filter\JSON\FixRecipeYieldFilter;
use OCA\demo\Helper\Filter\JSON\FixToolsFilter;
use OCA\demo\Helper\Filter\JSON\FixUrlFilter;
use OCA\demo\Helper\Filter\JSON\RecipeIdTypeFilter;
use OCA\demo\Helper\Filter\JSON\RecipeNameFilter;
use OCA\demo\Helper\Filter\JSON\SchemaConformityFilter;

class JSONFilter {
	/** @var AbstractJSONFilter[] */
	private $filters;

	public function __construct(
		SchemaConformityFilter $schemaConformityFilter,
		RecipeNameFilter $recipeNameFilter,
		RecipeIdTypeFilter $recipeIdTypeFilter,
		ExtractImageUrlFilter $extractImageUrlFilter,
		FixImageSchemeFilter $fixImageSchemeFilter,
		CleanCategoryFilter $cleanCategoryFilter,
		FixRecipeYieldFilter $fixRecipeYieldFilter,
		FixKeywordsFilter $fixKeywordsFilter,
		FixToolsFilter $fixToolsFilter,
		FixIngredientsFilter $fixIngredientsFilter,
		FixInstructionsFilter $fixInstructionsFilter,
		FixDescriptionFilter $fixDescriptionFilter,
		FixUrlFilter $fixUrlFilter,
		FixDurationsFilter $fixDurationsFilter,
		FixNutritionFilter $fixNutritionFilter
	) {
		$this->filters = [
			$schemaConformityFilter,
			$recipeNameFilter,
			$recipeIdTypeFilter,
			$extractImageUrlFilter,
			$fixImageSchemeFilter,
			$cleanCategoryFilter,
			$fixRecipeYieldFilter,
			$fixKeywordsFilter,
			$fixToolsFilter,
			$fixIngredientsFilter,
			$fixInstructionsFilter,
			$fixDescriptionFilter,
			$fixUrlFilter,
			$fixDurationsFilter,
			$fixNutritionFilter
		];
	}

	public function apply(array $json): array {
		foreach ($this->filters as $filter) {
			$filter->apply($json);
		}

		return $json;
	}
}
