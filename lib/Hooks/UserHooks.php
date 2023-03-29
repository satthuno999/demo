<?php declare(strict_types=1);
namespace OCA\DEMO\Hooks;

class UserHooks {
	private $userManager;
	private $maintenance;

	public function __construct($userManager, $maintenance) {
		$this->userManager = $userManager;
		$this->maintenance = $maintenance;
	}

	public function register() {
		$maintenance = $this->maintenance;
		$callback = function ($user) use ($maintenance) {
			$maintenance->resetAllData($user->getUID());
		};
		$this->userManager->listen('\OC\User', 'postDelete', $callback);
	}
}
