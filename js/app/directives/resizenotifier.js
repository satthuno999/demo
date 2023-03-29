
angular.module('Music').directive('resizeNotifier', ['$rootScope', '$timeout', function($rootScope, $timeout) {
	return function(_scope, element, _attrs, _ctrl) {
		element.resize(function() {
			$timeout(() => $rootScope.$emit('resize', element));
		});
	};
}]);
