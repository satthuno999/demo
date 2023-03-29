<?php declare(strict_types=1);
namespace OCA\DEMO\Command;

use OCA\DEMO\Db\Maintenance;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Cleanup extends Command {

	/** @var Maintenance */
	private $maintenance;

	public function __construct($maintenance) {
		$this->maintenance = $maintenance;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setName('music:cleanup')
			->setDescription('clean up orphaned DB entries (this happens also periodically on the background)')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$output->writeln('Running cleanup task...');
		$removedEtries = $this->maintenance->cleanUp();
		$output->writeln("Removed entries: " . \json_encode($removedEtries));
		return 0;
	}
}
