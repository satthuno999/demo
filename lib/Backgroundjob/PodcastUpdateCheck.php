<?php declare(strict_types=1);
namespace OCA\DEMO\Backgroundjob;

use OCA\DEMO\App\Music;
use OCA\DEMO\Utility\PodcastService;

class PodcastUpdateCheck {

	/**
	 * Check podcast updates on the background
	 */
	public static function run() {
		$app = \OC::$server->query(Music::class);

		$container = $app->getContainer();

		$logger = $container->query('Logger');
		$logger->log('Run ' . \get_class(), 'debug');

		$minInterval = (float)$container->query('Config')->getSystemValue('music.podcast_auto_update_interval', 24); // hours
		// negative interval values can be used to disable the auto-update
		if ($minInterval >= 0) {
			$users = $container->query('PodcastChannelBusinessLayer')->findAllUsers();
			$podcastService = $container->query('PodcastService');
			$channelsChecked = 0;

			foreach ($users as $userId) {
				$podcastService->updateAllChannels($userId, $minInterval, false, function (array $channelResult) use ($logger, $userId, &$channelsChecked) {
					$id = (isset($channelResult['channel'])) ? $channelResult['channel']->getId() : -1;

					if ($channelResult['updated']) {
						$logger->log("Channel $id of user $userId was updated", 'debug');
					} elseif ($channelResult['status'] === PodcastService::STATUS_OK) {
						$logger->log("Channel $id of user $userId had no changes", 'debug');
					} else {
						$logger->log("Channel $id of user $userId update failed", 'debug');
					}

					$channelsChecked++;
				});
			}

			if ($channelsChecked === 0) {
				$logger->log('No podcast channels were due to check for updates', 'debug');
			} else {
				$logger->log("$channelsChecked podcast channels in total were checked for updates", 'debug');
			}
		}
		else {
			$logger->log('Automatic podcast updating is disabled via config.php', 'debug');
		}

	}
}
