<?php

/*
 * - every object is part of a hierarchy of that type of objects as a forest - there may be many roots. It's perfectly
 *   possible that an object doesn't actually support a tree of itself (e.g. users).
 * - An object can have an owner. This should be either its tree parent, or if it's the root, some other object that
 *   owns it. An object of course can have no owner.
 * - The 'rights path' traces from the object all the way to the first object that doesn't have an owner. The right
 *   path is object-type aware.
 * - The right path is checked against the stored right path. Borrowing some semantics from XPath, the stored path
 *   can contain:
 *   - A specific typed instance. This must contain an object id, and also its type, since different types are stored in
 *     different tables a single id can be used by multiple objects. Any subclass of the type also matches.
 *     Syntax: <type>:<int>
 *   - A generic typed instance. This indicates that any instance of this type (or a subclass) is valid.
 *     Syntax: <type>:*
 *   - Anything. This indicates that the right applies to anything, regardless of type.
 *     Syntax: *
 *   - Levels are separated by /, but will in comments be typed as dots. Levels are significant! A path of *.*.Obj:1
 *     assigns rights to anything of type object with id 1 if it is exactly the child of the child of a root.
 *     To use arbitrary depths, use // (as per xpath): ..*.Obj:1
 *     This indicates rights on any obj.id=1 being a child of something (*) that exists somewhere, either as a root
 *     or a child.
 *   - As a shortcut, the type does not need to be specified on every level that requires it to be specified. If it's
 *     left out, the most recently specified type (i.e. directly left to this point) is used instead:
 *     *.Obj:1.3.6.Foo:3.:*
 *     Note that to reuse the type for a type-specific wildcard, the : is required to differ it from the object-less
 *     wildcard.
 *   - The path is specified from root to leaf.
 */

namespace Auth\Rights;

class PathPattern
{
	const anyPath = 'anyPath';
	const anyObject = 'anyObject';
	const objectId = 'objectId';

	private $pattern;
	private $parts = null;

	public function __construct($pattern) {
		$this->pattern = $pattern;
	}

	private static function compile($path) {
		$lastObj = null;
		$parts = array();
		for ($i = 0; $i < strlen($path); ) {
			if ($path[$i] == '/') {
				if ($i < strlen($path)-1 && $path[$i+1] == '/') {
					if (count($parts) == 0 || $parts[count($parts)-1] != self::anyPath) // Fold sequential uses of //
						$parts[]= self::anyPath;
					$i++;
				}

				$i++;
				$end = $i;
				while ($end < strlen($path) && $path[$end] != '/') $end++; // Skip to *past* the final character of the interesting string
				if ($end == $i) {
					// This should only happen if we end the path with a slash.
					throw new \Exception('No limit after slash at position '.$i);
				}
				$sub = substr($path, $i, $end-$i);
				if ($sub == '*') {
					$parts[] = self::anyObject;
				} else if ($sub == ':*') {
					if ($lastObj == null)
						throw new \Exception('Rights path uses an implicit object specifier without a previous object at position '.$i);
					$parts[] = array('type' => self::objectId, 'class' => $lastObj, 'id' => '*');
				} else {
					// Should be something of the form <type>:<id>, or just an id.
					$separPos = strpos($sub, ':');
					if ($separPos === false) {
						if ($lastObj == null)
							throw new \Exception('Rights path uses an implicit object specifier without a previous object at position '.$i);
						$parts[] = array('type' => self::objectId, 'class' => $lastObj, 'id' => $sub);
					} else {
						$lastObj = substr($sub, 0, $separPos);
						$parts[] = array('type' => self::objectId, 'class' => $lastObj, 'id' => substr($sub, $separPos+1));
					}
				}

				$i = $end;
			} else {
				throw new \Exception('Unexpected character '.$path[$i].' in rights path '.$path.' at position '.$i);
			}
		}

		if (count($parts) > 0 && $parts[count($parts)-1] == self::anyPath)
			// Paths are matched only on prefix, so ending on a // match is useless - it's done anyway.
			$parts = array_slice($parts, 0, count($parts)-1);

		return $parts;

		$result = new MatchPath();
		$result->parts = $parts;
		return $result;
	}

	/**
	 * Checks if a single part of the path matches a pattern.
	 * The path part can be one of two things:
	 * - An object instance with a getId method
	 * - An array with a class name as the first element, and an id as the second element.
	 */
	private function matchesPart($pattern, $pathPart) {
		if ($pattern == self::anyObject) return true;
		if ($pattern['type'] != self::objectId) return false;
		if (is_array($pathPart)) {
			return ($pattern['id'] == '*' || $pathPart[1] == $pattern['id']) && is_a($pathPart[0], $pattern['class'], true);
		} else {
			return ($pattern['id'] == '*' || $pathPart->getId() == $pattern['id']) && $pathPart instanceof $pattern['class'];
		}
	}

	/**
	 * Find the first part of a path that matches an element search pattern, starting from a given offset.
	 *
	 * Trackback information consists of the provided $searchIdx parameter, and the index in $path at which
	 * the match was found.
	 *
	 * @param int $searchIdx The index in the parts array to use as element search pattern.
	 * @param array $path The complete path to search in
	 * @param int $start The index in $path from which to start searching.
	 * @param array $stack The stack to which trackback information is pushed on a successful match.
	 */
	private function findNextMatch($searchIdx, array $path, $start, array &$stack) {
		$i = $start;
		while ($i < count($path) && !$this->matchesPart($this->parts[$searchIdx], $path[$i]))
			$i++;
		if ($i >= count($path))
			return false;
		array_push($stack, array($searchIdx, $i));
		return true;
	}

	private function backtrack(&$matchIdx, &$pathIdx, &$stack) {
		$pop = array_pop($stack);
		if ($pop == null)
			return false;
		list($matchIdx, $pathIdx) = $pop;
		$pathIdx++;
		return true;
	}

	/**
	 * @param array $path An array of the object path, from root to obj. Elements are an array of (classname, id)
	 */
	public function matches(array $path) {
		if ($this->parts == null)
			$this->parts = self::compile($this->pattern);

		$matchIdx = $pathIdx = 0;

		$backtrack = array();

		if (count($path) == 0) {
			return count($this->parts)==1 && $this->parts[0] == self::anyObject;
		}

		do {
			$part = $this->parts[$matchIdx];
			if ($part == self::anyPath) {
				// Find the next matching part in the path.

				$matchIdx++;
				while (!$this->findNextMatch($matchIdx, $path, $pathIdx, $backtrack)) {
					if (!$this->backtrack($matchIdx, $pathIdx, $backtrack))
						return false;
				}
				list($matchIdx, $pathIdx) = $backtrack[count($backtrack)-1];
				$matchIdx++;
				$pathIdx++;
			} else if ($this->matchesPart($part, $path[$pathIdx])) {
				$matchIdx++;
				$pathIdx++;
			} else {
				do {
					if (!$this->backtrack($matchIdx, $pathIdx, $backtrack))
						return false;
				} while (!$this->findNextMatch($matchIdx, $path, $pathIdx, $backtrack));
				list($matchIdx, $pathIdx) = $backtrack[count($backtrack)-1];
				$matchIdx++;
				$pathIdx++;
			}
		} while ($matchIdx < count($this->parts) && $pathIdx < count($path));

		return $matchIdx == count($this->parts);
	}
}

?>
