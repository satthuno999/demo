<?php declare(strict_types=1);
namespace OCA\DEMO\AppFramework\Utility;

/**
 * Reads and parses annotations from doc comments
 */
class MethodAnnotationReader {
	private $annotations;

	/**
	 * @param object|string $object an object or classname
	 * @param string $method the method which we want to inspect for annotations
	 */
	public function __construct($object, string $method) {
		$this->annotations = [];

		$reflection = new \ReflectionMethod($object, $method);
		$docs = $reflection->getDocComment();

		// extract everything prefixed by @ and first letter uppercase
		$matches = null;
		\preg_match_all('/@([A-Z]\w+)/', $docs, $matches);
		$this->annotations = $matches[1];
	}

	/**
	 * Check if a method contains an annotation
	 * @param string $name the name of the annotation
	 * @return bool true if the annotation is found
	 */
	public function hasAnnotation(string $name) : bool {
		return \in_array($name, $this->annotations);
	}
}
