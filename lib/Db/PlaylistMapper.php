<?php declare(strict_types=1);
namespace OCA\Demo\Db;

use OCP\IDBConnection;

/**
 * @phpstan-extends BaseMapper<Playlist>
 */
class PlaylistMapper extends BaseMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'music_playlists', Playlist::class, 'name');
	}

	/**
	 * @param int $trackId
	 * @return Playlist[]
	 */
	public function findListsContainingTrack($trackId) {
		$sql = $this->selectEntities('`track_ids` LIKE ?');
		$params = ['%|' . $trackId . '|%'];
		return $this->findEntities($sql, $params);
	}

	/**
	 * @see \OCA\Demo\Db\BaseMapper::findUniqueEntity()
	 * @param Playlist $playlist
	 * @return Playlist
	 */
	protected function findUniqueEntity(Entity $playlist) : Entity {
		// The playlist table has no unique constraints, and hence, this function
		// should never be called.
		throw new \BadMethodCallException('not supported');
	}
}