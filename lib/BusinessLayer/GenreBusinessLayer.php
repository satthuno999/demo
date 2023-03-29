<?php declare(strict_types=1);

namespace OCA\DEMO\BusinessLayer;

use OCA\DEMO\AppFramework\BusinessLayer\BusinessLayer;
use OCA\DEMO\AppFramework\Core\Logger;

use OCA\DEMO\Db\Genre;
use OCA\DEMO\Db\GenreMapper;
use OCA\DEMO\Db\SortBy;
use OCA\DEMO\Db\TrackMapper;

use OCA\DEMO\Utility\Util;

/**
 * Base class functions with the actually used inherited types to help IDE and Scrutinizer:
 * @method Genre find(int $genreId, string $userId)
 * @method Genre[] findAll(string $userId, int $sortBy=SortBy::None, int $limit=null, int $offset=null)
 * @method Genre[] findAllByName(string $name, string $userId, int $matchMode=MatchMode::Exact, int $limit=null, int $offset=null)
 * @phpstan-extends BusinessLayer<Genre>
 */
class GenreBusinessLayer extends BusinessLayer {
	protected $mapper; // eclipse the definition from the base class, to help IDE and Scrutinizer to know the actual type
	private $trackMapper;
	private $logger;

	public function __construct(GenreMapper $genreMapper, TrackMapper $trackMapper, Logger $logger) {
		parent::__construct($genreMapper);
		$this->mapper = $genreMapper;
		$this->trackMapper = $trackMapper;
		$this->logger = $logger;
	}

	/**
	 * Adds a genre if it does not exist already (in case insensitive sense) or updates an existing genre
	 * @param string $name the name of the genre
	 * @param string $userId the name of the user
	 * @return \OCA\DEMO\Db\Genre The added/updated genre
	 */
	public function addOrUpdateGenre($name, $userId) {
		$name = Util::truncate($name, 64); // some DB setups can't truncate automatically to column max size

		$genre = new Genre();
		$genre->setName($name);
		$genre->setLowerName(\mb_strtolower($name ?? ''));
		$genre->setUserId($userId);
		return $this->mapper->updateOrInsert($genre);
	}

	/**
	 * Returns all genres of the user, along with the contained track IDs
	 * @param string $userId
	 * @param int|null $limit
	 * @param int|null $offset
	 * @return Genre[] where each instance has also the trackIds property set
	 */
	public function findAllWithTrackIds($userId, $limit=null, $offset=null) {
		$genres = $this->findAll($userId, SortBy::Name, $limit, $offset);
		$tracksByGenre = $this->trackMapper->mapGenreIdsToTrackIds($userId);

		foreach ($genres as &$genre) {
			$genre->setTrackIds($tracksByGenre[$genre->getId()] ?? null);
		}

		return $genres;
	}
}
