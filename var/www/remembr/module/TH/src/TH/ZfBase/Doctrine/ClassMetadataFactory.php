<?php

namespace TH\ZfBase\Doctrine;

class ClassMetadataFactory extends \Doctrine\ORM\Mapping\ClassMetadataFactory
{
	/**
     * @var EntityManager
     */
    protected $em;

    public function setEntityManager(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
		parent::setEntityManager($em); // the idiots used a private property, so we need to have it set there as well as here.
    }

	/**
     * Forces the factory to load the metadata of all classes known to the underlying
     * mapping driver.
     *
     * @return array The ClassMetadata instances of all mapped classes.
     */
    public function getAllMetadata()
    {
		$excluded = $this->em->getConfiguration()->getExcludedEntities();

        if ( ! $this->initialized) {
            $this->initialize();
        }

        $driver = $this->getDriver();
        $metadata = array();
        foreach ($driver->getAllClassNames() as $className)
		{
			if (empty($excluded[$className]))
			{
				$metadata[] = $this->getMetadataFor($className);
			}
        }

        return $metadata;
    }


}
