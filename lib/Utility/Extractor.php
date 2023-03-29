<?php declare(strict_types=1);
namespace OCA\DEMO\Utility;

use OCP\Files\File;

interface Extractor {

	/**
	 * get metadata info for a media file
	 *
	 * @param File $file the file
	 * @return array extracted data
	 */
	public function extract(File $file): array;

	/**
	 * extract embedded cover art image from media file
	 *
	 * @param File $file the media file
	 * @return array|null Dictionary with keys 'mimetype' and 'content', or null if not found
	 */
	public function parseEmbeddedCoverArt(File $file) : ?array;
}
