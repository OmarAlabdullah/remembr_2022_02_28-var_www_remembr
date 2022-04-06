<?php

/**
 * Api for a user and all valid providers.
 *
 * protected function getUserAccount()
 * protected function checkProviders()
 * public function getValidProviders()
 * public function getLoggedInProviders()
 * public function getUserName()
 * public function getUserEmail()
 * public function getTitle()
 * public function getProperty($property)
 * public function getProfilePhoto($provider = null)
 * public function getProfile($provider)
 * public function getUserId()
 * public function isUserConnectedTo($provider)
 * public function hasUserAccessTo($provider)
 */

namespace TH\ZfUser\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Session\Container;

class LoginApi extends AbstractPlugin
{

    protected $em;
    protected $user_id;
    protected $providers;
    protected $user;
    protected $hybridauth;
    protected $redirect;
    protected $profile = array();

    /**
     * Get user_id, providers and user account.
     * Check if all providers are still connected.
     *
     * @return \TH\ZfUser\Controller\Plugin\LoginApi
     */
    public function __invoke($pluginManager = null)
    {
        if ($pluginManager !== null)
            $this->setController($pluginManager->getController());
        // $config = $this->getConfig();
        // Get user_id for logged in user.
        $user_session = new Container('user');
        /**
         * We want to be able to use the api when we're not logged in as well
         * There shouldn't be surprise redirects.
         * @TODO fix problems from not having user_id
         */
//        if (!isset($user_session->user_id))
//        {
//            return $this->getRedirect()->toUrl('/account/login');
//        }

        $this->user_id = $user_session->user_id;

        // Get connected providers for this user.
				$hybrid_auth = $this->getHybridauth();
				if ($hybrid_auth instanceof \Exception)
				{
					error_log('Exception occured in login API: ' . $hybrid_auth->getMessage());
					error_log($hybrid_auth->getTraceAsString());
				}
        $this->providers = $this->getHybridauth()->getConnectedProviders();

        $active_providers = $this->getAvailableProviders();

        foreach ($this->providers as $idx => $provider)
        {
            if (!in_array($provider, $active_providers)) {
                unset($this->providers[$idx]);
            }
        }

        // Always check if providers are still logged in.
        $this->checkProviders();

        // Get user object for this user.
        $this->user = $this->getUserAccount();

        return $this;
    }


    public function getFBfriends()
    {
        $adapter = $this->getHybridauth()->authenticate('facebook');

        $friends = $adapter->getUserContacts();

        $friendsArray = array();

        foreach ($friends as $friend)
        {
            $friendsArray[] = array(
                'uid' => $friend->identifier,
                'name' => $friend->displayName,
                'pict' => $friend->photoURL,
                'profile_url' => $friend->profileURL,
            );
        }

        // sort on name
        $tmp = array();
        foreach ($friendsArray as $a)
        {
            $tmp[] = $a["name"];
        }

        array_multisort($tmp, $friendsArray);

        return $friendsArray;
    }

    protected function getConfig()
    {
        if (!isset($this->config))
        {
            $config = $this->getController()->getServiceLocator()->get('config');
            $this->config = $config;
        }

        return $this->config;
    }

    /**
     * Check if providers are still logged in.
     */
    protected function checkProviders()
    {
        foreach ($this->providers as $provider)
        {
            if (!$this->getProfile($provider))
            {
                $adapter = $this->getHybridauth()->authenticate($provider);
                $adapter->logout();
            }
        }

        // update $this->providers
        $this->providers = $this->getHybridauth()->getConnectedProviders();
    }

    /**
     * Get user account from dB.
     */
    protected function getUserAccount()
    {
        return $this->getEntityManager()->getRepository('TH\ZfUser\Entity\UserAccount')->findOneBy(array('id' => $this->user_id));
    }

    /**
     * Get all valid provider names for this user.
     *
     * @return array
     */
    public function getValidProviders()
    {
        $providers = array();

        foreach ($this->user->getAccesses() as $access)
        {
            $providers[] = $access->getProvider();
        }

        return $providers;
    }

    public function getLoggedInProviders()
    {
        $allproviders = $this->getHybridauth()->getConnectedProviders();

        $allproviders[] = 'default';

        foreach ($allproviders as $provider)
        {
            $provider = strtolower(strip_tags($provider));
            if ($provider === 'default')
            {
                $providers[] = $provider;
            }
            elseif ($this->getHybridauth()->isConnectedWith($provider))
            {
                $providers[] = $provider;
            }
        }

        return $providers;
    }

    public function getAvailableProviders()
    {
        $allproviders = $this->getHybridauth()->getProviders();

        return array_map('strtolower', array_keys($allproviders));
    }

    /**
     * Get user name from userAccount table.
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->user->username;
    }

    /**
     * Get user email from userAccount table.
     *
     * @return string
     */
    public function getUserEmail()
    {
        if ($this->user) {
            return $this->user->email;
        }
    }

    /**
     * Get user title from userAccount table.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->user->profile->title;
    }

    /**
     * Get first property found in profiles of providers.
     *
     * @param string $property :    identifier, webSiteURL, profileURL, (photoURL > use getProfilePhoto() instead),
     *                              displayName, description, firstName, lastName, gender, language, age, birthDay, birthMonth, birthYear,
     *                              email, emailVerified, phone, address, country, region, city, zip

     * @return string
     */
    public function getProperty($property)
    {
        foreach ($this->providers as $provider)
        {
            if (!empty($this->getProfile($provider)->$property))
            {
                return $this->getProfile($provider)->$property;
            }
        }

        return "property not available";
    }

    /**
     * Get provider photo or first found profile photo in providers (default image if not available).
     *
     * @param type $provider
     * @return string
     */
    public function getProfilePhoto($provider = null)
    {
        $providers = !empty($provider) ? array($provider) : $this->providers;

        foreach ($providers as $provider)
        {
            if (!empty($this->getProfile($provider)->photoURL))
            {
                return $this->getProfile($provider)->photoURL;
            }
        }

        return "/images/user-icon-large.png";
    }

    /**
     * Get user profile for provider.
     *
     * @param string $provider
     * @return Hybrid_User_Profile
     */
    public function getProfile($provider)
    {
        if (isset($this->profile[$provider]))
        {
            return $this->profile[$provider];
        }

        if (in_array($provider, $this->providers) && $this->getHybridauth()->isConnectedWith($provider))
        {
            $adapter = $this->getHybridauth()->authenticate($provider);

            try
            {
                $this->profile[$provider] = $adapter->getUserProfile();

                return $this->profile[$provider];
            } catch (\Exception $e)
            {
                return false;
            }
        }
    }

    /**
     * Get current user id.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Check if user is connected to provider
     *
     * @param string $provider
     * @return boolean
     */
    public function isUserConnectedTo($provider)
    {
        return in_array($provider, $this->providers);
    }

    /**
     * Check if user has access to provider
     *
     * @param string $provider
     * @return boolean
     */
    public function hasUserAccessTo($provider)
    {
        return in_array(strtolower($provider), $this->getValidProviders());
    }

    protected function getEntityManager()
    {
        if (null === $this->em)
        {
            $this->em = $this->getController()->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }
        return $this->em;
    }

    protected function getHybridauth()
    {
        if (null === $this->hybridauth)
        {
            $this->hybridauth = $this->getController()->hybridauth();
        }
        return $this->hybridauth;
    }

    protected function getRedirect()
    {
        if (!$this->redirect)
        {
            $this->redirect = $this->getController()->getServiceLocator()->get('controllerpluginmanager')->get('redirect');
        }
        return $this->redirect;
    }

}