
angular.module('Music').factory('Audio', [function () {
	return new OCA.Music.GaplessPlayer();
}]);
