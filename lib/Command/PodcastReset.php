<?php declare(strict_types=1);
namespace OCA\DEMO\Command;

use OCA\DEMO\Utility\PodcastService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PodcastReset extends BaseCommand {

	/** @var PodcastService */
	private $podcastService;

	public function __construct(
			\OCP\IUserManager $userManager,
			\OCP\IGroupManager $groupManager,
			PodcastService $podcastService) {
		$this->podcastService = $podcastService;
		parent::__construct($userManager, $groupManager);
	}

	protected function doConfigure() : void {
		$this
			->setName('music:podcast-reset')
			->setDescription('remove all podcast channels of one or more users')
		;
	}

	protected function doExecute(InputInterface $input, OutputInterface $output, array $users) : void {
		if ($input->getOption('all')) {
			$this->userManager->callForAllUsers(function($user) use ($output) {
				$this->resetPodcasts($user->getUID(), $output);
			});
		} else {
			foreach ($users as $userId) {
				$this->resetPodcasts($userId, $output);
			}
		}
	}

	private function resetPodcasts(string $userId, OutputInterface $output) : void {
		$output->writeln("Reset all podcasts of the user <info>$userId</info>");
		$this->podcastService->resetAll($userId);
	}
}
