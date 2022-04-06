<?php

namespace Auth\Rights;

/**
 * Contains a set of permission bits for a single group. A group can be any string.
 *
 * The rights system is based on permission bits. To make it easy to create multiple modules without
 * doing lots of accounting to keep the permissions separate, or running out of bits, permissions can
 * be put into a named group. This class contains any set of permission bits for a single group.
 *
 * See the RightList class for more info on the rights system, and the init method for a simple
 * initialization of many permissions.
 */
class Right {
	private $group;
	private $value;

	public function getGroup() { return $this->group; }
	public function getValue() { return $this->value; }

	public function __construct($group, $value) {
		$this->group = $group;
		if (!is_int($value))
			throw new Exception('A right value must be an int');
		$this->value = $value;
	}

	/**
	 * Easily initialize a set of permission bits. This will analyze the class given in the parameter, and replace
	 * any static properties that have been initialized with an integer with a Right instance that contains this integer
	 * as value, and the class name as group name.
	 *
	 * The easiest way to use this is by calling Right::init(__NAMESPACE__.'\<classname>'); directly after the class
	 * definition, replacing <classname> by the actual name.
	 */
	public static function init($className) {
		$class = new \ReflectionClass($className);
		$className = $class->getName();
		foreach ($class->getStaticProperties() as $name => $value) {
			if (is_int($value))
				$class->setStaticPropertyValue($name, new Right($className, $value));
		}
	}
}

?>