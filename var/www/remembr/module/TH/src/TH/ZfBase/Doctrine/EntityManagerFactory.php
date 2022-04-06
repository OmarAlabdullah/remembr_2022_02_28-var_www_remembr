<?php

namespace TH\ZfBase\Doctrine;

use TH\ZfBase\Doctrine\DecoratedEntityManager;

class EntityManagerFactory implements \Zend\ServiceManager\FactoryInterface
{
    /**
     * {@inheritDoc}
     * @return EntityManager
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $sl)
    {
		$em = $sl->get('doctrine.entitymanager.orm_default');
		$dem = new DecoratedEntityManager($em);

		$config = $sl->get('Config');
		if (isset($config['doctrine']['entity_resolver']['orm_default']['resolvers']))
		{
			$entitymap = $config['doctrine']['entity_resolver']['orm_default']['resolvers'];
			$dem->setEntityMap($entitymap);
		}

		return $dem;
    }
}
