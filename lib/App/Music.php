<?php declare(strict_types=1);

namespace OCA\Demo\App;

use OCP\AppFramework\App;
use OCP\AppFramework\IAppContainer;

use OCA\Demo\AppFramework\Core\Logger;

use OCA\Demo\BusinessLayer\AlbumBusinessLayer;
use OCA\Demo\BusinessLayer\ArtistBusinessLayer;
use OCA\Demo\BusinessLayer\BookmarkBusinessLayer;
use OCA\Demo\BusinessLayer\GenreBusinessLayer;
use OCA\Demo\BusinessLayer\Library;
use OCA\Demo\BusinessLayer\PlaylistBusinessLayer;
use OCA\Demo\BusinessLayer\PodcastChannelBusinessLayer;
use OCA\Demo\BusinessLayer\PodcastEpisodeBusinessLayer;
use OCA\Demo\BusinessLayer\RadioStationBusinessLayer;
use OCA\Demo\BusinessLayer\TrackBusinessLayer;

use OCA\Demo\Controller\AmpacheController;
use OCA\Demo\Controller\ApiController;
use OCA\Demo\Controller\LogController;
use OCA\Demo\Controller\PageController;
use OCA\Demo\Controller\PlaylistApiController;
use OCA\Demo\Controller\PodcastApiController;
use OCA\Demo\Controller\RadioApiController;
use OCA\Demo\Controller\SettingController;
use OCA\Demo\Controller\ShareController;
use OCA\Demo\Controller\ShivaApiController;
use OCA\Demo\Controller\SubsonicController;

use OCA\Demo\Db\AlbumMapper;
use OCA\Demo\Db\AmpacheSessionMapper;
use OCA\Demo\Db\AmpacheUserMapper;
use OCA\Demo\Db\ArtistMapper;
use OCA\Demo\Db\BookmarkMapper;
use OCA\Demo\Db\Cache;
use OCA\Demo\Db\GenreMapper;
use OCA\Demo\Db\Maintenance;
use OCA\Demo\Db\PlaylistMapper;
use OCA\Demo\Db\PodcastChannelMapper;
use OCA\Demo\Db\PodcastEpisodeMapper;
use OCA\Demo\Db\RadioStationMapper;
use OCA\Demo\Db\TrackMapper;

use OCA\Demo\Hooks\FileHooks;
use OCA\Demo\Hooks\ShareHooks;
use OCA\Demo\Hooks\UserHooks;

use OCA\Demo\Middleware\AmpacheMiddleware;
use OCA\Demo\Middleware\SubsonicMiddleware;

use OCA\Demo\Utility\AmpacheUser;
use OCA\Demo\Utility\CollectionHelper;
use OCA\Demo\Utility\CoverHelper;
use OCA\Demo\Utility\DetailsHelper;
use OCA\Demo\Utility\ExtractorGetID3;
use OCA\Demo\Utility\LastfmService;
use OCA\Demo\Utility\PlaylistFileService;
use OCA\Demo\Utility\PodcastService;
use OCA\Demo\Utility\RadioService;
use OCA\Demo\Utility\Random;
use OCA\Demo\Utility\Scanner;
use OCA\Demo\Utility\LibrarySettings;

class Music extends App {
	public function __construct(array $urlParams=[]) {
		parent::__construct('demo', $urlParams);

		\mb_internal_encoding('UTF-8');

		$container = $this->getContainer();

		/**
		 * Controllers
		 */
		$container->registerService('AmpacheController', function (IAppContainer $c) {
			return new AmpacheController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('L10N'),
				$c->query('URLGenerator'),
				$c->query('AmpacheUserMapper'),
				$c->query('AmpacheSessionMapper'),
				$c->query('AlbumBusinessLayer'),
				$c->query('ArtistBusinessLayer'),
				$c->query('GenreBusinessLayer'),
				$c->query('PlaylistBusinessLayer'),
				$c->query('PodcastChannelBusinessLayer'),
				$c->query('PodcastEpisodeBusinessLayer'),
				$c->query('TrackBusinessLayer'),
				$c->query('Library'),
				$c->query('PodcastService'),
				$c->query('AmpacheUser'),
				$c->query('RootFolder'),
				$c->query('CoverHelper'),
				$c->query('Random'),
				$c->query('Logger')
			);
		});

		$container->registerService('ApiController', function (IAppContainer $c) {
			return new ApiController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('URLGenerator'),
				$c->query('TrackBusinessLayer'),
				$c->query('ArtistBusinessLayer'),
				$c->query('AlbumBusinessLayer'),
				$c->query('GenreBusinessLayer'),
				$c->query('Scanner'),
				$c->query('CollectionHelper'),
				$c->query('CoverHelper'),
				$c->query('DetailsHelper'),
				$c->query('LastfmService'),
				$c->query('Maintenance'),
				$c->query('LibrarySettings'),
				$c->query('UserId'),
				$c->query('L10N'),
				$c->query('UserFolder'),
				$c->query('Logger')
			);
		});

		$container->registerService('PageController', function (IAppContainer $c) {
			return new PageController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('L10N')
			);
		});

		$container->registerService('PlaylistApiController', function (IAppContainer $c) {
			return new PlaylistApiController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('URLGenerator'),
				$c->query('PlaylistBusinessLayer'),
				$c->query('AlbumBusinessLayer'),
				$c->query('TrackBusinessLayer'),
				$c->query('CoverHelper'),
				$c->query('PlaylistFileService'),
				$c->query('UserId'),
				$c->query('UserFolder'),
				$c->query('Logger')
			);
		});

		$container->registerService('PodcastApiController', function (IAppContainer $c) {
			return new PodcastApiController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('PodcastService'),
				$c->query('UserId'),
				$c->query('Logger')
			);
		});

		$container->registerService('LogController', function (IAppContainer $c) {
			return new LogController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('Logger')
			);
		});

		$container->registerService('RadioApiController', function (IAppContainer $c) {
			return new RadioApiController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('Config'),
				$c->query('RadioStationBusinessLayer'),
				$c->query('RadioService'),
				$c->query('PlaylistFileService'),
				$c->query('UserId'),
				$c->query('UserFolder'),
				$c->query('Logger')
			);
		});

		$container->registerService('SettingController', function (IAppContainer $c) {
			return new SettingController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('AmpacheUserMapper'),
				$c->query('Scanner'),
				$c->query('UserId'),
				$c->query('LibrarySettings'),
				$c->query('SecureRandom'),
				$c->query('URLGenerator'),
				$c->query('Logger')
			);
		});

		$container->registerService('ShareController', function (IAppContainer $c) {
			return new ShareController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('Scanner'),
				$c->query('PlaylistFileService'),
				$c->query('Logger'),
				$c->query('ShareManager')
			);
		});

		$container->registerService('ShivaApiController', function (IAppContainer $c) {
			return new ShivaApiController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('URLGenerator'),
				$c->query('TrackBusinessLayer'),
				$c->query('ArtistBusinessLayer'),
				$c->query('AlbumBusinessLayer'),
				$c->query('UserId'),
				$c->query('L10N'),
				$c->query('Logger')
			);
		});

		$container->registerService('SubsonicController', function (IAppContainer $c) {
			return new SubsonicController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('L10N'),
				$c->query('URLGenerator'),
				$c->query('UserManager'),
				$c->query('AlbumBusinessLayer'),
				$c->query('ArtistBusinessLayer'),
				$c->query('BookmarkBusinessLayer'),
				$c->query('GenreBusinessLayer'),
				$c->query('PlaylistBusinessLayer'),
				$c->query('PodcastChannelBusinessLayer'),
				$c->query('PodcastEpisodeBusinessLayer'),
				$c->query('RadioStationBusinessLayer'),
				$c->query('TrackBusinessLayer'),
				$c->query('LibrarySettings'),
				$c->query('CoverHelper'),
				$c->query('DetailsHelper'),
				$c->query('LastfmService'),
				$c->query('PodcastService'),
				$c->query('Random'),
				$c->query('Logger')
			);
		});

		/**
		 * Business Layer
		 */

		$container->registerService('TrackBusinessLayer', function (IAppContainer $c) {
			return new TrackBusinessLayer(
				$c->query('TrackMapper'),
				$c->query('Logger')
			);
		});

		$container->registerService('ArtistBusinessLayer', function (IAppContainer $c) {
			return new ArtistBusinessLayer(
				$c->query('ArtistMapper'),
				$c->query('Logger')
			);
		});

		$container->registerService('GenreBusinessLayer', function (IAppContainer $c) {
			return new GenreBusinessLayer(
				$c->query('GenreMapper'),
				$c->query('TrackMapper'),
				$c->query('Logger')
			);
		});

		$container->registerService('AlbumBusinessLayer', function (IAppContainer $c) {
			return new AlbumBusinessLayer(
				$c->query('AlbumMapper'),
				$c->query('Logger')
			);
		});

		$container->registerService('PlaylistBusinessLayer', function (IAppContainer $c) {
			return new PlaylistBusinessLayer(
				$c->query('PlaylistMapper'),
				$c->query('TrackMapper'),
				$c->query('Logger')
			);
		});

		$container->registerService('PodcastChannelBusinessLayer', function (IAppContainer $c) {
			return new PodcastChannelBusinessLayer(
				$c->query('PodcastChannelMapper'),
				$c->query('Logger')
			);
		});

		$container->registerService('PodcastEpisodeBusinessLayer', function (IAppContainer $c) {
			return new PodcastEpisodeBusinessLayer(
				$c->query('PodcastEpisodeMapper'),
				$c->query('Logger')
			);
		});

		$container->registerService('BookmarkBusinessLayer', function (IAppContainer $c) {
			return new BookmarkBusinessLayer(
				$c->query('BookmarkMapper'),
				$c->query('Logger')
			);
		});

		$container->registerService('RadioStationBusinessLayer', function ($c) {
			return new RadioStationBusinessLayer(
				$c->query('RadioStationMapper'),
				$c->query('Logger')
			);
		});

		$container->registerService('Library', function (IAppContainer $c) {
			return new Library(
				$c->query('AlbumBusinessLayer'),
				$c->query('ArtistBusinessLayer'),
				$c->query('TrackBusinessLayer'),
				$c->query('CoverHelper'),
				$c->query('URLGenerator'),
				$c->query('L10N'),
				$c->query('Logger')
			);
		});

		/**
		 * Mappers
		 */

		$container->registerService('AlbumMapper', function (IAppContainer $c) {
			return new AlbumMapper(
				$c->getServer()->getDatabaseConnection()
			);
		});

		$container->registerService('AmpacheSessionMapper', function (IAppContainer $c) {
			return new AmpacheSessionMapper(
				$c->getServer()->getDatabaseConnection()
			);
		});

		$container->registerService('AmpacheUserMapper', function (IAppContainer $c) {
			return new AmpacheUserMapper(
				$c->getServer()->getDatabaseConnection()
			);
		});

		$container->registerService('ArtistMapper', function (IAppContainer $c) {
			return new ArtistMapper(
				$c->getServer()->getDatabaseConnection()
			);
		});

		$container->registerService('DbCache', function (IAppContainer $c) {
			return new Cache(
				$c->getServer()->getDatabaseConnection()
			);
		});

		$container->registerService('GenreMapper', function (IAppContainer $c) {
			return new GenreMapper(
				$c->getServer()->getDatabaseConnection()
			);
		});

		$container->registerService('PlaylistMapper', function (IAppContainer $c) {
			return new PlaylistMapper(
				$c->getServer()->getDatabaseConnection()
			);
		});

		$container->registerService('PodcastChannelMapper', function (IAppContainer $c) {
			return new PodcastChannelMapper(
				$c->getServer()->getDatabaseConnection()
			);
		});

		$container->registerService('PodcastEpisodeMapper', function (IAppContainer $c) {
			return new PodcastEpisodeMapper(
				$c->getServer()->getDatabaseConnection()
			);
		});

		$container->registerService('TrackMapper', function (IAppContainer $c) {
			return new TrackMapper(
				$c->getServer()->getDatabaseConnection()
			);
		});

		$container->registerService('BookmarkMapper', function (IAppContainer $c) {
			return new BookmarkMapper(
				$c->getServer()->getDatabaseConnection()
			);
		});

		$container->registerService('RadioStationMapper', function (IAppContainer $c) {
			return new RadioStationMapper(
				$c->getServer()->getDatabaseConnection()
			);
		});

		/**
		 * Core
		 */

		$container->registerService('Config', function (IAppContainer $c) {
			return $c->getServer()->getConfig();
		});

		$container->registerService('Db', function (IAppContainer $c) {
			return $c->getServer()->getDatabaseConnection();
		});

		$container->registerService('FileCache', function (IAppContainer $c) {
			return $c->getServer()->getCache();
		});

		$container->registerService('L10N', function (IAppContainer $c) {
			return $c->getServer()->getL10N($c->query('AppName'));
		});

		$container->registerService('L10NFactory', function (IAppContainer $c) {
			return $c->getServer()->getL10NFactory();
		});

		$container->registerService('Logger', function (IAppContainer $c) {
			return new Logger(
				$c->query('AppName'),
				$c->getServer()->getLogger()
			);
		});

		$container->registerService('MimeTypeLoader', function (IappContainer $c) {
			return $c->getServer()->getMimeTypeLoader();
		});

		$container->registerService('URLGenerator', function (IAppContainer $c) {
			return $c->getServer()->getURLGenerator();
		});

		$container->registerService('UserFolder', function (IAppContainer $c) {
			return $c->getServer()->getUserFolder();
		});

		$container->registerService('RootFolder', function (IAppContainer $c) {
			return $c->getServer()->getRootFolder();
		});

		$container->registerService('UserId', function (IAppContainer $c) {
			$user = $c->getServer()->getUserSession()->getUser();
			return $user ? $user->getUID() : null;
		});

		$container->registerService('SecureRandom', function (IAppContainer $c) {
			return $c->getServer()->getSecureRandom();
		});

		$container->registerService('UserManager', function (IAppContainer $c) {
			return $c->getServer()->getUserManager();
		});

		$container->registerService('GroupManager', function (IAppContainer $c) {
			return $c->getServer()->getGroupManager();
		});

		$container->registerService('ShareManager', function (IAppContainer $c) {
			return $c->getServer()->getShareManager();
		});

		/**
		 * Utility
		 */

		$container->registerService('AmpacheUser', function () {
			return new AmpacheUser();
		});

		$container->registerService('CollectionHelper', function (IAppContainer $c) {
			return new CollectionHelper(
				$c->query('Library'),
				$c->query('FileCache'),
				$c->query('DbCache'),
				$c->query('Logger'),
				$c->query('UserId')
			);
		});

		$container->registerService('CoverHelper', function (IAppContainer $c) {
			return new CoverHelper(
				$c->query('ExtractorGetID3'),
				$c->query('DbCache'),
				$c->query('AlbumBusinessLayer'),
				$c->query('Config'),
				$c->query('Logger')
			);
		});

		$container->registerService('DetailsHelper', function (IAppContainer $c) {
			return new DetailsHelper(
				$c->query('ExtractorGetID3'),
				$c->query('Logger')
			);
		});

		$container->registerService('ExtractorGetID3', function (IAppContainer $c) {
			return new ExtractorGetID3(
				$c->query('Logger')
			);
		});

		$container->registerService('LastfmService', function (IAppContainer $c) {
			return new LastfmService(
				$c->query('AlbumBusinessLayer'),
				$c->query('ArtistBusinessLayer'),
				$c->query('TrackBusinessLayer'),
				$c->query('Config'),
				$c->query('Logger')
			);
		});

		$container->registerService('Maintenance', function (IAppContainer $c) {
			return new Maintenance(
				$c->query('Db'),
				$c->query('Logger')
			);
		});

		$container->registerService('PlaylistFileService', function (IAppContainer $c) {
			return new PlaylistFileService(
				$c->query('PlaylistBusinessLayer'),
				$c->query('RadioStationBusinessLayer'),
				$c->query('TrackBusinessLayer'),
				$c->query('Logger')
			);
		});

		$container->registerService('PodcastService', function (IAppContainer $c) {
			return new PodcastService(
				$c->query('PodcastChannelBusinessLayer'),
				$c->query('PodcastEpisodeBusinessLayer'),
				$c->query('Logger')
			);
		});

		$container->registerService('RadioService', function (IAppContainer $c) {
			return new RadioService(
				$c->query('URLGenerator'),
				$c->query('Logger')
			);
		});

		$container->registerService('Random', function (IAppContainer $c) {
			return new Random(
				$c->query('DbCache'),
				$c->query('Logger')
			);
		});

		$container->registerService('Scanner', function (IAppContainer $c) {
			return new Scanner(
				$c->query('ExtractorGetID3'),
				$c->query('ArtistBusinessLayer'),
				$c->query('AlbumBusinessLayer'),
				$c->query('TrackBusinessLayer'),
				$c->query('PlaylistBusinessLayer'),
				$c->query('GenreBusinessLayer'),
				$c->query('DbCache'),
				$c->query('CoverHelper'),
				$c->query('Logger'),
				$c->query('Maintenance'),
				$c->query('LibrarySettings'),
				$c->query('RootFolder'),
				$c->query('Config'),
				$c->query('L10NFactory')
			);
		});

		$container->registerService('LibrarySettings', function (IAppContainer $c) {
			return new LibrarySettings(
				$c->query('AppName'),
				$c->query('Config'),
				$c->query('RootFolder'),
				$c->query('Logger')
			);
		});

		/**
		 * Middleware
		 */

		$container->registerService('AmpacheMiddleware', function (IAppContainer $c) {
			return new AmpacheMiddleware(
				$c->query('Request'),
				$c->query('AmpacheSessionMapper'),
				$c->query('AmpacheUser'),
				$c->query('Logger')
			);
		});
		$container->registerMiddleWare('AmpacheMiddleware');

		$container->registerService('SubsonicMiddleware', function (IAppContainer $c) {
			return new SubsonicMiddleware(
				$c->query('Request'),
				$c->query('AmpacheUserMapper'), /* not a mistake, the mapper is shared between the APIs */
				$c->query('Logger')
			);
		});
		$container->registerMiddleWare('SubsonicMiddleware');

		/**
		 * Hooks
		 */
		$container->registerService('FileHooks', function (IAppContainer $c) {
			return new FileHooks(
				$c->getServer()->getRootFolder()
			);
		});

		$container->registerService('ShareHooks', function (/** @scrutinizer ignore-unused */ IAppContainer $c) {
			return new ShareHooks();
		});

		$container->registerService('UserHooks', function (IAppContainer $c) {
			return new UserHooks(
				$c->query('ServerContainer')->getUserManager(),
				$c->query('Maintenance')
			);
		});
	}
}
