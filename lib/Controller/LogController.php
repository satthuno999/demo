<?php declare(strict_types=1);
namespace OCA\DEMO\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

use OCA\DEMO\AppFramework\Core\Logger;

class LogController extends Controller {
	private $logger;

	public function __construct($appname,
								IRequest $request,
								Logger $logger) {
		parent::__construct($appname, $request);
		$this->logger = $logger;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function log($message) {
		$this->logger->log('JS: ' . $message, 'debug');
		return new JSONResponse(['success' => true]);
	}
}
