<?php

namespace TH\ZfBase\Doctrine;

class DecoratedEntityManager extends \Doctrine\ORM\Decorator\EntityManagerDecorator
{
	protected $entitymap = array();

	public function setEntityMap(array $em)
	{
		$this->entitymap = $em;
	}

	public function resolveEntity($entityname, $params = array())
	{
		$entityname = trim($entityname, '\\');
		if (isset($this->entitymap[$entityname]))
		{
			$entityname = $this->entitymap[$entityname];
		}

		$class = new \ReflectionClass($entityname);
		return $class->newInstanceArgs($params);
	}
}
