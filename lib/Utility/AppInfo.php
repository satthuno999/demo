<?php declare(strict_types=1);
namespace OCA\DEMO\Utility;

class AppInfo {

	public const APP_ID = 'music';

	public static function getVersion() {
		// Nextcloud 14 introduced a new API for this which is not available on ownCloud.
		// Nextcloud 25 removed the old API for good.
		$appManager = \OC::$server->getAppManager();
		if (\method_exists($appManager, 'getAppVersion')) {
			return $appManager->getAppVersion(self::APP_ID); // NC14+
		} else {
			return \OCP\App::getAppVersion(self::APP_ID); // OC or NC13
		}
	}
}