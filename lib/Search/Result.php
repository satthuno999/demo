<?php declare(strict_types=1);
namespace OCA\DEMO\Search;

/**
 * A found track/album/artist
 */
class Result extends \OCP\Search\Result {
	public function __construct($id, $name, $link, $type) {
		parent::__construct($id, $name, $link);
		$this->type = $type; // defined by the parent class
	}
}
