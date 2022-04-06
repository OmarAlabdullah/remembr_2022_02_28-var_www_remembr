<?php
/**
 * Base functionality for Login Library.
 */
namespace TH\ZfUser\Library;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class BaseLib implements ServiceLocatorAwareInterface
{

    protected $serviceLocator;
    protected $entityManager;
    protected $service;
    protected $userlib;
    protected $providerlib;
    protected $accesstokenlib;
    protected $hybridauth;
    protected $redirect;
    protected $config;

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    protected function getIdentity()
    {
        if (!$this->service)
        {
            $this->service = new \Zend\Authentication\AuthenticationService();
        }
        return $this->service->getIdentity();
    }

    protected function getConfig()
    {
        if (!isset($this->config))
        {
			$config = $this->getServiceLocator()->get('config');
            $this->config = $config;
        }

        return $this->config;
    }

    /**
     *
     * @return type
     */
    protected function getRedirect()
    {
        if (!$this->redirect)
        {
            $this->redirect = $this->serviceLocator->get('controllerpluginmanager')->get('redirect');
        }
        return $this->redirect;
    }

    protected function getUrlHelper()
    {
        if (!$this->redirect)
        {
            $this->redirect = $this->serviceLocator->get('controllerpluginmanager')->get('url');
        }
        return $this->redirect;
    }


    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEm()
    {
        if (!$this->entityManager)
        {
            $this->entityManager = $this->serviceLocator->get('th_entitymanager');
        }
        return $this->entityManager;
    }

    protected function getHybridauth()
    {
        if (!$this->hybridauth)
        {
            $this->hybridauth = $this->serviceLocator->get('HybridAuth');   // see Module.php
        }

        return $this->hybridauth;
    }

    /**
     * @return UserLib
     */
    protected function getUserLib()
    {
        if (!$this->userlib)
            $this->userlib = $this->getServiceLocator()->get('TH\ZfUser\Library\UserLib');

        return $this->userlib;
    }

    protected function getProviderLib()
    {
        if (!$this->providerlib)
            $this->providerlib = $this->getServiceLocator()->get('TH\ZfUser\Library\ProviderLib');

        return $this->providerlib;
    }

    protected function getAccesstokenLib()
    {
        if (!$this->accesstokenlib)
            $this->accesstokenlib = $this->getServiceLocator()->get('TH\ZfUser\Library\AccesstokenLib');

        return $this->accesstokenlib;
    }

}