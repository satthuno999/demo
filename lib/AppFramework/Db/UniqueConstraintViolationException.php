<?php declare(strict_types=1);
namespace OCA\DEMO\AppFramework\Db;

/**
 * Own exception type for the Music app to be used on DB unique constraint violations.
 * The exceptions used for this by the core vary by the version of Nexcloud or ownCloud used.
 * Mapping these all to a common type enables unified handling.
 */
class UniqueConstraintViolationException extends \Exception {

	public function __construct(string $message = "", int $code = 0, \Throwable $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}
