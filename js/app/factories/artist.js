

angular.module('Music').factory('ArtistFactory', ['Restangular', '$rootScope', function (Restangular, $rootScope) {
	return {
		getArtists: function() {
			return Restangular.all('prepare_collection').post().then(function(reply) {
				$rootScope.$emit('newCoverArtToken', reply.cover_token);
				$rootScope.$emit('updateIgnoredArticles', reply.ignored_articles);
				return Restangular.all('collection').getList({hash: reply.hash});
			});
		}
	};
}]);
