<?php

namespace TH\ZfBase\Doctrine;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\Exception\InvalidArgumentException;

class ConfigurationFactory extends \DoctrineORMModule\Service\ConfigurationFactory
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var $options \DoctrineORMModule\Options\Configuration */
        $options = $this->getOptions($serviceLocator);
        $config  = new Configuration();

        $config->setExcludedEntities($options->getExcludedEntities());

        $config->setAutoGenerateProxyClasses($options->getGenerateProxies());
        $config->setProxyDir($options->getProxyDir());
        $config->setProxyNamespace($options->getProxyNamespace());

        $config->setEntityNamespaces($options->getEntityNamespaces());

        $config->setCustomDatetimeFunctions($options->getDatetimeFunctions());
        $config->setCustomStringFunctions($options->getStringFunctions());
        $config->setCustomNumericFunctions($options->getNumericFunctions());

        $config->setClassMetadataFactoryName($options->getClassMetadataFactoryName());

        foreach ($options->getNamedQueries() as $name => $query) {
            $config->addNamedQuery($name, $query);
        }

        foreach ($options->getNamedNativeQueries() as $name => $query) {
            $config->addNamedNativeQuery($name, $query['sql'], new $query['rsm']);
        }

        foreach ($options->getCustomHydrationModes() as $modeName => $hydrator) {
            $config->addCustomHydrationMode($modeName, $hydrator);
        }

        foreach ($options->getFilters() as $name => $class) {
            $config->addFilter($name, $class);
        }

        $config->setMetadataCacheImpl($serviceLocator->get($options->getMetadataCache()));
        $config->setQueryCacheImpl($serviceLocator->get($options->getQueryCache()));
        $config->setResultCacheImpl($serviceLocator->get($options->getResultCache()));
        $config->setHydrationCacheImpl($serviceLocator->get($options->getHydrationCache()));
        $config->setMetadataDriverImpl($serviceLocator->get($options->getDriver()));

        if ($namingStrategy = $options->getNamingStrategy()) {
            if (is_string($namingStrategy)) {
                if (!$serviceLocator->has($namingStrategy)) {
                    throw new InvalidArgumentException(sprintf('Naming strategy "%s" not found', $namingStrategy));
                }

                $config->setNamingStrategy($serviceLocator->get($namingStrategy));
            } else {
                $config->setNamingStrategy($namingStrategy);
            }
        }

        if ($repositoryFactory = $options->getRepositoryFactory()){
            if (is_string($repositoryFactory)) {
                if (!$serviceLocator->has($repositoryFactory)) {
                    throw new InvalidArgumentException(sprintf('Repository factory "%s" not found', $repositoryFactory));
                }

                $config->setRepositoryFactory($serviceLocator->get($repositoryFactory));
            } else {
                $config->setRepositoryFactory($repositoryFactory);
            }
        }

        $this->setupDBALConfiguration($serviceLocator, $config);

        return $config;
    }

    protected function getOptionsClass()
    {
        return 'TH\ZfBase\Doctrine\ORMModuleConfiguration';
    }
}
