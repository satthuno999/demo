<?php declare(strict_types=1);

namespace OCA\DEMO\Command;

use OCA\DEMO\Db\Maintenance;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResetDatabase extends BaseCommand {

	/** @var Maintenance */
	private $maintenance;

	public function __construct(\OCP\IUserManager $userManager,
			\OCP\IGroupManager $groupManager, Maintenance $maintenance) {
		$this->maintenance = $maintenance;
		parent::__construct($userManager, $groupManager);
	}

	protected function doConfigure() : void {
		$this
			->setName('music:reset-database')
			->setDescription('drop metadata indexed by the music app (artists, albums, tracks, playlists)');
	}

	protected function doExecute(InputInterface $input, OutputInterface $output, array $users) : void {
		if ($input->getOption('all')) {
			$output->writeln("Drop tables for <info>all users</info>");
			$this->maintenance->resetLibrary(null, true);
		} else {
			foreach ($users as $user) {
				$output->writeln("Drop tables for <info>$user</info>");
				$this->maintenance->resetLibrary($user);
			}
		}
	}
}
