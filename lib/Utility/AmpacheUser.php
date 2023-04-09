<?php declare(strict_types=1);

namespace OCA\Demo\Utility;

/**
 * This class is used to share data about the user between the AmpacheMiddleware and
 * the AmpacheController
 */
class AmpacheUser {
	private $userId;

	public function getUserId() {
		return $this->userId;
	}

	public function setUserId($id) {
		$this->userId = $id;
	}
}
