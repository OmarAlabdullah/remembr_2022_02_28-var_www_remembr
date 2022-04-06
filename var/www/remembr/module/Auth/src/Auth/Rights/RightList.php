<?php

namespace Auth\Rights;

use \Doctrine\Common\Collections\Collection;
use \Doctrine\ORM\EntityManager;

/**
 * Contains any set of permission bits, even if the bits come from multiple groups. Also has a method to check if
 * another list of permissions is fully contained within this list.
 *
 * For all the RightList methods that need a list of rights as their parameter, they will accept any of the following
 * (except if noted differently):
 * - An instance of Right,
 * - An instance of RightList,
 * - An integer.
 * Integers will be interpreted as a Right in the group that has the empty string as name.
 */
class RightList {
	private $rights = array();

	/**
	 * Simple function to automatically convert any valid right (int, Right, RightList) into a RightList. This
	 * will not duplicate existing RightList instances.
	 */
	public static function convert($right) {
		if ($right instanceof RightList)
			return $right;
		else
			return new RightList($right);
	}

	/**
	 * Converts a list of UserRight to an associative array (path => RightList). This is used to convert a format
	 * that Doctrine can manage into a format that is more useful to query for rights.
	 *
	 * @param array-like of UserRight $assoc
	 */
	public static function fromDoctrine($array) {
		$data = array();
		foreach ($array as $item) {
			if (!isset($data[$item->getPath()]))
				$data[$item->getPath()] = array();
			$data[$item->getPath()][$item->getRightGroup()] = $item->getValue();
		}

		$result = array();
		foreach ($data as $path => $item) {
			$list = new RightList();
			foreach ($item as $key => $value) {
				$list->rights[$key] = $value;
			}
			$result[$path] = $list;
		}
		return $result;
	}

	/**
	 * Create a new instance from a set of rights. The parameter can be any int, Right, RightList, or an array of any
	 * mix of these types. The instance will end up with all the given rights combined.
	 */
	public function __construct($rights = null) {
		if ($rights == null) return;

		if (!is_array($rights)) $rights = array($rights);
		foreach ($rights as $r) {
			if (is_int($r))
				$this->rights[''] = (isset($this->rights['']) ? $this->rights[''] : 0) | $r;
			else if ($r instanceof Right)
				$this->rights[$r->getGroup()] = (isset($this->rights[$r->getGroup()]) ? $this->rights[$r->getGroup()] : 0) | $r->getValue();
			else if ($r instanceof RightList)
				$this->add($r);
			else
				throw new Exception('Invalid right: '.$r);
		}
	}

	/**
	 * Update a doctrine array with the contents of this list.
	 *
	 * @param Collection $array The associative array to save to
	 * @param closure $new A function with parameters name and value that will create a new class that can be
	 *                     saved in the map.
	 */
	public function syncToDoctrine(Collection $array, \Closure $filter, \Closure $new) {
		$toRemove = array();
		$toAdd = $this->rights;
		foreach ($array as $item) {
			if ($filter($item)) continue;

			if (isset($this->rights[$item->getRightGroup()]))
				$item->setValue($this->rights[$item->getRightGroup()]);
			else
				$toRemove[]= $item;
			unset($toAdd[$item->getRightGroup()]);
		}
		foreach ($toRemove as $item) {
			$array->removeElement($item);
		}
		foreach ($toAdd as $name => $value) {
			$array[] = $new($name, $value);
		}
	}

	/**
	 * Adds extra rights to this list.
	 *
	 * @param int|Right|RightList $list The rights to add
	 * @return \Application\Rights\RightList
	 */
	public function add($list) {
		$list = self::convert($list);
		foreach ($list->rights as $group => $value) {
			$this->rights[$group] = (isset($this->rights[$group]) ? $this->rights[$group] : 0) | $value;
		}
	}

	/**
	 * Removes rights from this list.
	 *
	 * @param int|Right|RightList $list The rights to remove
	 * @return \Application\Rights\RightList
	 */
	public function remove($list) {
		$list = self::convert($list);
		foreach ($list->rights as $group => $value) {
			if (isset($this->rights[$group])) {
				$this->rights[$group] &= ~$value;
				if ($this->rights[$group] == 0)
					unset($this->rights[$group]);
			}
		}
	}

	/**
	 * Returns whether this right list contains any rights at all.
	 * @return bool
	 */
	public function isEmpty() {
		return count($this->rights) == 0;
	}

	/**
	 * Check that this list contains all the rights given in the parameter
	 *
	 * @param int|Right|RightList $right The right to check against
	 */
	public function matchAll($right) {
		if (is_int($right))
			return isset($this->rights['']) && ($this->rights[''] & $right) == $right;
		else if ($right instanceof Right)
			return isset($this->rights[$right->getGroup()]) && ($this->rights[$right->getGroup()] & $right->getValue()) == $right->getValue();
		else if ($right instanceof RightList) {
			foreach ($right->rights as $group => $value) {
				if (!isset($this->rights[$group]) || ($this->rights[$group] & $value) != $value)
					return false;
			}

			return true;
		} else
			throw new Exception('Invalid right: '.$right);
	}

	/**
	 * Check that this list contains any of the rights given in the parameter
	 *
	 * @param int|Right|RightList $right The right to check against
	 */
	public function matchAny($right) {
		if (is_int($right))
			return isset($this->rights['']) && ($this->rights[''] & $right) != 0;
		else if ($right instanceof Right)
			return isset($this->rights[$right->getGroup()]) && ($this->rights[$right->getGroup()] & $right->getValue()) != 0;
		else if ($right instanceof RightList) {
			foreach ($right->rights as $group => $value) {
				if (isset($this->rights[$group]) && ($this->rights[$group] & $value) != 0)
					return true;
			}

			return false;
		} else
			throw new Exception('Invalid right: '.$right);
	}

	protected static function buildPath($obj) {
		$path = array();
		$curr = $obj;
		while ($curr != null) {
			$path[]= $curr;
			if (method_exists($curr, 'getRightsParent'))
				$curr = $obj->getRightsParent();
			else
				$curr = null;
		}
		// The constructed path is going from leaf to root, so reverse.
		return array_reverse($path);
	}

	/**
	 * Checks if a list of rights contains a set of required rights on an object.
	 *
	 * The path of the object is determined by querying a 'getRightsParent' method on it until either this
	 * method returns null, or this method is not present on an object.
	 *
	 * Note that the current implementation does not deal with specifying rights on different levels. A single
	 * matching path entry must provide all the queried rights. Rights cannot be revoked again on a more specific
	 * path, and a query that asks for many rights will not match if multiple entries on different matching paths
	 * combine to provide all rights.
	 *
	 * @param array $rights Associative array, the key is the path of the right, and the value is a RightList instance.
	 * @param object $obj The object to check rights on.
	 * @param int|Right|RightList $required
	 * @return boolean
	 */
	public static function hasAll(array $rights, $obj, $required) {
		if (!$required) {
			throw new \Exception('Testing against an empty right has no use');
		}
		$path = self::buildPath($obj);
		$required = RightList::convert($required);

		foreach ($rights as $rightPath => $right) {
			$pathInstance = new PathPattern($rightPath);
			if ($right->matchAll($required) && $pathInstance->matches($path)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if any single one of a list of rights on an object is matched. It is otherwise the same as hasAll.
	 */
	public static function hasAny(array $rights, $obj, $required) {
		if (!$required) {
			throw new \Exception('Testing against an empty right has no use');
		}
		$path = self::buildPath($obj);
		$required = RightList::convert($required);

		foreach ($rights as $rightPath => $right) {
			$pathInstance = new PathPattern($rightPath);
			if ($right->matchAny($required) && $pathInstance->matches($path)) {
				return true;
			}
		}

		return false;
	}
}

?>