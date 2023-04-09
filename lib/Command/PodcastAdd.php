<?php declare(strict_types=1);
namespace OCA\Demo\Command;

use OCA\Demo\BusinessLayer\PodcastChannelBusinessLayer;
use OCA\Demo\BusinessLayer\PodcastEpisodeBusinessLayer;
use OCA\Demo\Utility\HttpUtil;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Instantiator\Exception\InvalidArgumentException;

class PodcastAdd extends BaseCommand {

	/** @var PodcastChannelBusinessLayer */
	private $channelBusinessLayer;
	/** @var PodcastEpisodeBusinessLayer */
	private $episodeBusinessLayer;

	public function __construct(
			\OCP\IUserManager $userManager,
			\OCP\IGroupManager $groupManager,
			PodcastChannelBusinessLayer $channelBusinessLayer,
			PodcastEpisodeBusinessLayer $episodeBusinessLayer) {
		$this->channelBusinessLayer = $channelBusinessLayer;
		$this->episodeBusinessLayer = $episodeBusinessLayer;
		parent::__construct($userManager, $groupManager);
	}

	protected function doConfigure() : void {
		$this
			->setName('demo:podcast-add')
			->setDescription('add a podcast channel from an RSS feed')
			->addOption(
					'rss',
					null,
					InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
					'URL to an RSS feed'
			)
		;
	}

	protected function doExecute(InputInterface $input, OutputInterface $output, array $users) : void {
		$rssUrls = $input->getOption('rss');

		if (!$rssUrls) {
			throw new \InvalidArgumentException("The named argument <error>rss</error> must be given!");
		}
		\assert(\is_array($rssUrls));

		foreach ($rssUrls as $rss) {
			$this->addPodcast($rss, $input, $output, $users);
		}
	}

	private function addPodcast(string $rss, InputInterface $input, OutputInterface $output, array $users) : void {
		$rssData = HttpUtil::loadFromUrl($rss);
		$content = $rssData['content'];
		if ($content === false) {
			throw new \InvalidArgumentException("Invalid URL <error>$rss</error>! {$rssData['status_code']} {$rssData['message']}");
		}

		$xmlTree = \simplexml_load_string($content, \SimpleXMLElement::class, LIBXML_NOCDATA);
		if ($xmlTree === false || !$xmlTree->channel) {
			throw new \InvalidArgumentException("The document at URL <error>$rss</error> is not a valid podcast RSS feed!");
		}

		if ($input->getOption('all')) {
			$this->userManager->callForAllUsers(function($user) use ($output, $rss, $content, $xmlTree) {
				$this->addPodcastForUser($user->getUID(), $rss, $content, $xmlTree->channel, $output);
			});
		} else {
			foreach ($users as $userId) {
				$this->addPodcastForUser($userId, $rss, $content, $xmlTree->channel, $output);
			}
		}
	}

	private function addPodcastForUser(string $userId, string $rss, string $content, \SimpleXMLElement $xmlNode, OutputInterface $output) : void {
		$output->writeln("Adding podcast channel <info>{$xmlNode->title}</info> for user <info>$userId</info>");
		try {
			$channel = $this->channelBusinessLayer->create($userId, $rss, $content, $xmlNode);

			// loop the episodes from XML in reverse order to get chronological order
			$items = $xmlNode->item;
			for ($count = \count($items), $i = $count-1; $i >= 0; --$i) {
				if ($items[$i] !== null) {
					$this->episodeBusinessLayer->addOrUpdate($userId, $channel->getId(), $items[$i]);
				}
			}
		} catch (\OCA\Demo\AppFramework\Db\UniqueConstraintViolationException $ex) {
			$output->writeln('User already has this podcast channel, skipping');
		}
	}
}