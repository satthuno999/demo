<?php declare(strict_types=1);
namespace OCA\Demo\Backgroundjob;

use OCA\Demo\App\Music;

class Cleanup {

	/**
	 * Run background cleanup task
	 */
	public static function run() {
		$app = \OC::$server->query(Music::class);

		$container = $app->getContainer();

		// remove orphaned entities
		$container->query('Maintenance')->cleanUp();

		// remove expired sessions
		$container->query('AmpacheSessionMapper')->cleanUp();

		// find covers - TODO performance stuff - maybe just call this once in an hour
		$container->query('Scanner')->findAlbumCovers();
	}
}
