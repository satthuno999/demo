<?php declare(strict_types=1);

namespace OCA\DEMO\AppFramework\BusinessLayer;

class BusinessLayerException extends \Exception {

	/**
	 * Constructor
	 * @param string $msg the error message
	 */
	public function __construct(string $msg) {
		parent::__construct($msg);
	}
}
