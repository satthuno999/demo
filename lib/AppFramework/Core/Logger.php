<?php declare(strict_types=1);
namespace OCA\DEMO\AppFramework\Core;

use OCP\ILogger;

class Logger {
	protected $appName;
	protected $logger;

	public function __construct($appName, ILogger $logger) {
		$this->appName = $appName;
		$this->logger = $logger;
	}

	/**
	 * Writes a message to the log file
	 * @param string $msg the message to be logged
	 * @param string $level the severity of the logged event, defaults to 'error'
	 */
	public function log(string $msg, string $level=null) {
		$context = ['app' => $this->appName];
		switch ($level) {
			case 'debug':
				$this->logger->debug($msg, $context);
				break;
			case 'info':
				$this->logger->info($msg, $context);
				break;
			case 'warn':
				$this->logger->warning($msg, $context);
				break;
			case 'fatal':
				$this->logger->emergency($msg, $context);
				break;
			default:
				$this->logger->error($msg, $context);
				break;
		}
	}
}
