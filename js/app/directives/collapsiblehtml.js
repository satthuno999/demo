

angular.module('Music').directive('collapsibleHtml', ['gettextCatalog', function(gettextCatalog) {
	const clickToExpandText = gettextCatalog.getString('Click to expand');

	return {
		scope: {
			collapsibleHtml: '=',
			onExpand: '='
		},
		template: function(element, _attrs) {
			// Note: The original attributes of the element are not preserved; modify if those are needed.
			const tag = element[0].nodeName;
			return `<${tag} ng-init="mayTruncate = truncated = (collapsibleHtml.length > 400)"
					ng-class="{clickable: truncated, truncated: truncated}"
					ng-bind-html="collapsibleHtml | limitTo:(truncated ? 365 : undefined)"
					ng-click="truncated = false; onExpand()"
					title="{{ truncated ? '${clickToExpandText}' : '' }}">
					</${tag}>`;
		},
		replace: true
	};
}]);
