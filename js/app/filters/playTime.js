

angular.module('Music').filter('playTime', function() {
	return OCA.Music.Utils.formatPlayTime;
});