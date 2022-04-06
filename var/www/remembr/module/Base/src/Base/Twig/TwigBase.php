<?php

namespace Base\Twig;

use \Base\Rights\Right;

class TwigBase extends \Twig_Extension
{
	public function getName() { return 'base_twig'; }

	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('class',      function($a)     { return get_class($a);        }),
			new \Twig_SimpleFunction('instanceof', function($a, $b) { return $a instanceof $b;     }),
			new \Twig_SimpleFunction('iterate',    function($a, $b) { return new iterator($a, $b); })
		);
	}
	public function getFilters()
	{
		return array(
			new \Twig_SimpleFilter('addslashes','addslashes'),
		);
	}
}

class iterator implements \Iterator
{
	protected $first;
	protected $current;
	protected $nextfunction;

	public function __construct($object, $nextfunction='next')
	{
		$this->current = $this->first = $object;
		$this->nextfunction = $nextfunction;
	}

	public function current()
	{
		return $this->current;
	}
	public function key() // not available
	{
		return null;
	}
	public function next()
	{
		$this->current = call_user_func(array($this->current, $this->nextfunction));
	}
	public function rewind()
	{
		$this->current = $this->first;
	}
	public function valid ()
	{
		return ! empty($this->current);
	}

}
?>
