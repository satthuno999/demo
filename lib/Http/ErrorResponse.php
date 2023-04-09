<?php declare(strict_types=1);
namespace OCA\Demo\Http;

use OCP\AppFramework\Http\JSONResponse;

/**
 * A renderer for files
 */
class ErrorResponse extends JSONResponse {

	/**
	 * @param int $statusCode the Http status code
	 * @param string $message Error message, defaults to empty
	 */
	public function __construct(int $statusCode, string $message=null) {
		parent::__construct(
				empty($message) ? [] : ['message' => $message],
				$statusCode
		);
	}
}
