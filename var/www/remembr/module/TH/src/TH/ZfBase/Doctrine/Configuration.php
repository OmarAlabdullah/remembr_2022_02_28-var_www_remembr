<?php

namespace TH\ZfBase\Doctrine;

class Configuration extends \Doctrine\ORM\Configuration
{
    public function setExcludedEntities($excludedEntities)
    {
        $this->_attributes['excludedEntities'] = $excludedEntities;
    }

    public function getExcludedEntities()
    {
        if (isset($this->_attributes['excludedEntities'])) {
            return $this->_attributes['excludedEntities'];
        }

        return array();
    }
}
