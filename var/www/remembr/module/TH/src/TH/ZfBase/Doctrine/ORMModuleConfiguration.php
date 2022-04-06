<?php

namespace TH\ZfBase\Doctrine;

class ORMModuleConfiguration extends \DoctrineORMModule\Options\Configuration
{
    protected $excludedEntities = array();

	public function setExcludedEntities($excludedEntities) {
		$this->excludedEntities = $excludedEntities;
	}

	public function getExcludedEntities() {
		return $this->excludedEntities;
	}
}
