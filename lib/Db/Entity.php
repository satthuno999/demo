<?php declare(strict_types=1);
namespace OCA\Demo\Db;
class Entity extends \OCP\AppFramework\Db\Entity {
	public $userId;
	public $created;
	public $updated;
}
