

angular.module('Music').directive('navigationItem', function() {
	return {
		scope: {
			text: '=',
			icon: '=',
			destination: '=',
			playlist: '='
		},
		templateUrl: 'navigationitem.html',
		replace: true
	};
});
