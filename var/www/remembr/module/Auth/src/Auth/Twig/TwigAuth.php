<?php

namespace Auth\Twig;

use \Auth\Rights\Right;

class TwigAuth extends \Twig_Extension
{
	public function getName() { return 'auth_twig'; }

	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('hasRight', array('\Auth\Rights\RightList', 'hasAll')),
			new \Twig_SimpleFunction('hasAnyRight', array('\Auth\Rights\RightList', 'hasAny')),
			//new \Twig_SimpleFunction('registerRights', array('\Auth\Twig\TwigAuth', 'registerRights'), array('needs_context' => true))
		);
	}

	public static function getRegisterableRights($className) {
		$class = new \ReflectionClass($className);
		$className = $class->getShortName();
		$result = array();
		foreach ($class->getStaticProperties() as $name => $value) {
			if (is_int($value) || $value instanceof Right) {
				$result[$className.'_'.$name] = $value;
			}
		}
		return $result;
	}

	public static function registerRights($twig, $className) {
		foreach (self::getRegisterableRights($className) as $name => $value) {
			$twig->setVariable($name, $value);
		}
	}
}

?>
