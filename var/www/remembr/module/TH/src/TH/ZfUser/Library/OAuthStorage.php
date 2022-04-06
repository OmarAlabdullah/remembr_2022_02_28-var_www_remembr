<?php

namespace TH\ZfUser\Library;

use Doctrine\ORM\EntityManager;
use League\OAuth2\Client\Token\AccessToken;
use TH\ZfUser\Entity\UserAccess;
use TH\ZfUser\Entity\UserAccount;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class OAuthStorage implements ServiceLocatorAwareInterface
{
    // implementation of ServiceLocatorAwareInterface

    /** @var ServiceLocatorInterface|null  */
    protected $serviceLocator = null;
    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator(){
        return $this->serviceLocator;
    }

    /**
     * Gets the providers connected to the user.
     * @param UserAccount $user
     * @return array Collection of provider names as strings.
     */
    public function getConnectedProviders($user) {
        $connected = array();
        /** @var UserAccess $access */
        foreach($user->getAccesses() as $access) {
            $connected[] = $access->getProvider();
        }
        return $connected;
    }

    /**
     * Checks if a user has connected a social provider.
     * @param UserAccount $user
     * @param String $provider
     * @return bool True if connected, false if not.
     */
    public function isConnected($user, $provider){
        $con = $this->getConnection($user, $provider, null);

        // todo: check created or expires

        return $con != null;
    }

    /**
     * @param UserAccount $user
     * @param \League\OAuth2\Client\Token\AccessToken $token
     */
    public function addConnection($user, $provider, $token) {
        // recycle the record if possible.
        $current = $this->getConnection($user, $provider);
        if($current == null) {
            $current = new UserAccess($user);
            $current->provider = $provider;
        }
        $current->setAccessToken($token->getToken());
        $current->setOauthJson($token->jsonSerialize());


        /** @var EntityManager $em */
        $em = $this->serviceLocator->get('th_entitymanager');
        $em->persist($current);
        $em->flush();
    }

    public function getToken($user, $provider) {
        $access = $this->getConnection($user, $provider);
        if($access == null)
            return null;

        $token = new AccessToken($access->getOauthJson());
        return $token;
    }

    /**
     * @param UserAccount $user
     * @param String $provider
     * @param object $default
     * @return UserAccess|null
     */
    protected function getConnection($user, $provider, $default=null) {
        foreach($user->getAccesses() as $access) {
            // case insensitive comparison.
            if(0 == strcasecmp($access->getProvider(), $provider)) {
                return $access;
            }
        }
        return $default;
    }
}