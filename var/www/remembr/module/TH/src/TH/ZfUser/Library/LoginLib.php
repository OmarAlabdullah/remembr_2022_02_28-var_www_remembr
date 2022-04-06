<?php

namespace TH\ZfUser\Library;

use TH\ZfUser\Entity\UserAccess;
use TH\ZfUser\Entity\UserAccount;
use Zend\Session\Container;
use TH\ZfUser\Library\UserLib;
use TH\ZfUser\Library\BaseLib;

class LoginLib extends BaseLib //implements ServiceLocatorAwareInterface
{

    /**
     * Log-in or create the user if not exists.
     * @param string $provider
     * @param array $data
     * @return UserAccount Object.
     */
    public function oauthLoginOrCreate($provider, $data, $tokenData) {
        $user = $this->getUserOAuth($provider, $data['id'], $data['email'], $tokenData['access_token']);
        if($user == null) {
            $user = $this->createUserOAuth($provider, $data, $tokenData);
        }

        $this->setLogin($user);
        return $user;
    }

    /**
     * Get user based on OAuth credentials or return null
     * @param String $provider Provider used.
     * @param int $id Provider's ID for this user.
     * @param String $accessToken Access Token as a string.
     * @throws \Exception Thrown on invalid credentials or incorrect login method.
     * @return UserAccount|null
     */
    public function getUserOAuth($provider, $id, $email, $accessToken) {
        /** @var UserAccount $user */
        $user = null;

        // 1. Check by e-mail

        if($email) {
            $repository = $this->getEm()->getRepository('TH\ZfUser\Entity\UserAccount');
            $user = $repository->findOneBy(array('email' => $email));
        }

        // 2. Validate the OAuth

        // find user via oauth
        $repository = $this->getEm()->getRepository('TH\ZfUser\Entity\UserAccess');
        /** @var UserAccess $access */
        $access = $repository->findOneBy(
            array
            (
                'auth_id' => $id,
                'provider' => $provider,
            )
        );
        if($access != null) {
            $access->setAccessToken($accessToken);
            $user = $access->getUser();
        }
        $config = $this->getConfig();

        // 3. Account exists, but there's no valid access.

        if($access == null && $user != null) {
            $message = null;
            if($user->getAccesses()->count() > 0) {
                throw new \Exception($config['TH']['ZfUser']['errormsg'][$this->getLang()]['access_denied'] . $this->provider);
            }
            throw new \Exception($config['TH']['ZfUser']['errormsg'][$this->getLang()]['email_exists'] . $this->provider);
        }

        return $user;
    }

    /**
     * Updates user login statistics and adds to the session data.
     * @param  UserAccount $user User to update.
     * @param bool $flush Flush to database right away.
     */
    public function setLogin($user, $flush = true) {
        $user_session = new Container('user');
        $user_session->user_id = $user->getId();

        $user->setLastLogin(new \DateTime());
        $user->setLogins($user->getLogins()+1);
        if($flush) {
            $this->getEm()->flush($user);
        }
    }
    public function createUserOAuth($provider, $profileData, $tokenData)
    {
        // 1. Create account

        $password = \Auth\Rights\AuthAdapter::create_hash(base64_encode(openssl_random_pseudo_bytes(12)));
        // account data
        $accountdata = array(
            'email' => $profileData['email'],
            'password' => $password,
        );
        $user = new \TH\ZfUser\Entity\UserAccount($accountdata);

        $this->getEm()->persist($user); // save user so we can link the account to it

        // 2. Create public profile

        $profile = new \Application\Entity\UserProfile(array(
            'firstname'=>$profileData['first_name'],
            'lastname' => isset($profileData['last_name']) ? $profileData['last_name'] : '', // twitter doesn't provide a last_name
            'gender' => $profileData['gender']
        ));
        $this->getEm()->persist($profile);
        $user->storeProfile($profile);
        $user->setVerified(true);

        // 3. Add oauth token data

        $access = new UserAccess($user, $tokenData);
        $this->getEm()->persist($access);

        // 4. End, flush to db

        $this->getEm()->flush();

        return $user;
    }

    protected $provider;
    protected $adapter;
    protected $userprofile;

    public function finishLogin($provider)
    {
        // Check if it concerns an internal login (default), or an OAuth login.
        if ($provider === 'default')
        {
            return $this->loginComplete();
        }
        else
        {
            // Try to grab the user profile for this provider.
            $adapter = $this->getHybridauth()->authenticate($provider);

            try
            {
                $userprofile = $adapter->getUserProfile();
            } catch (\Exception $e)
            {
                $adapter->logout();
                $config = $this->getConfig();
                throw new \Exception($config['TH']['ZfUser']['errormsg'][$this->getLang()]['cant_get_profile'] . $provider); //@TODO error.
            }

            return $this->processLogin($userprofile, $provider, $adapter);
        }
    }

    /**
     * Process login for: already-logged-in-user or new-logged-in-user.
     */
    public function processLogin($userprofile, $provider, $adapter)
    {
        $this->provider = $provider;
        $this->adapter = $adapter;
        $this->userprofile = $userprofile;

        if (UserLib::doWeHaveLoggedInUser())
        {
            return $this->processLoggedInUser();
        }
        else
        {
            return $this->processNewLogin();
        }
    }

    /**
     * Process a new login from hybridauth.
     */
    public function processNewLogin()
    {
        $config = $this->getConfig();
        // Can user be retreived from database with this login credentials?
        if ($this->getUserLib()->getStoredAccess($this->userprofile, $this->provider, $this->adapter))
        {
            return $this->loginComplete();
        }
        else
        {
            // Does provider provide e-mail address?
            if (!isset($this->userprofile->email))
            {
                // Store provider in session so it can be retreived after user input.
                $provider_session = new Container('provider');
                $provider_session->provider = $this->provider;
                // Ask for e-mail address if provider does not provide this.
                return $this->getRedirect()->toUrl('/sociallogin/ask-user-details');
            }

            if ($this->getUserLib()->emailAddressExists($this->userprofile->email))
            {
                // Now we have an e-mail address which is already in the dB.
                // So the user does have normal login credentials in the dB but is not logged in yet.
                // Provide a message and logout this (for now only active OAuth provider)
                // and thereby rerouting to the demo page (which, for no login exists, routes to the login page).
                $this->adapter->logout();

                // @TODO does this exception get handled properly?
                throw new \Exception($config['TH']['ZfUser']['errormsg'][$this->getLang()]['email_exists'] . $this->provider);
            }
            else
            {
                // Store new user in db.
                $user = $this->getUserLib()->storeNewUser($this->userprofile);

                // Store provider for this new user.
                $this->storeNewProviderForUser($user);

                return $this->loginComplete();
            }
        }
    }

    /**
     * User is already loggedin.
     */
    protected function processLoggedInUser()
    {
        $config = $this->getConfig();
        // Check if user credentials for this provider already exist in dB.
        if ($access = $this->getUserLib()->getStoredAccess($this->userprofile, $this->provider, $this->adapter)) // deliberately
        {
            if ($access->getUser()->getId() === UserLib::currentUserId())
            {
                // Existing provider for current logged in user; done.
                return $this->loginComplete();
            }
            else
            {
                $this->adapter->logout();
                throw new \Exception($config['TH']['ZfUser']['errormsg'][$this->getLang()]['credentials_for_other_user'] . $this->provider);
            }
        }
        // Check if provider for user already exists with other credentials.
        elseif ($this->getProviderLib()->existingProviderForUser(UserLib::currentUserId(), $this->provider, $this->adapter))
        {
            // An user can only have ONE set credentials per provider.
            // It seems an user tries to login with other credentials for a provider which is already in dB for this user.
            $adapter = $this->getHybridauth()->authenticate($this->provider);
            $adapter->logout();

            throw new \Exception($config['TH']['ZfUser']['errormsg'][$this->getLang()]['access_denied'] . $this->provider);
        }
        else
        {
            // It is a new provider for this loggedin account; add new provider.
            $this->storeNewProviderForUser($this->getUserLib()->getUserAccount(UserLib::currentUserId()));

            // So, now we have a -new- existing provider for current logged in user; done.
            return $this->loginComplete();
        }
    }

    /**
     * Add new provider credentials for user and update hybridauth session data in dB.
     */
    protected function storeNewProviderForUser($user)
    {
        $at = $this->adapter->getAccessToken();
        $data = array(
            'auth_id' => $this->userprofile->identifier,
            'auth_secret' => !empty($this->adapter->config['keys']['secret']) ? $this->adapter->config['keys']['secret'] : '-',
            'provider' => $this->provider,
            'access_token' => !empty($at['access_token']) ? $at['access_token'] : '-',
        );

        $access = new \TH\ZfUser\Entity\UserAccess($user, $data);
        $this->getEm()->persist($access);

        $user->addAccess($access);

        // Store/update hybridauth session data for this user in dB.
        $hybridauth_session_data = $this->getHybridauth()->getSessionData();
        $user->storeHybridauthSession($hybridauth_session_data);

        // grab provider profile photo
        if (!$user->getProfile()->getPhotoid())
        {
            $url = $this->adapter->getUserProfile()->photoURL;
            $filedata = file_get_contents($url);
            $fileName = \Base\Util\Generator::generateKey(16) . '.jpg';
            $file = fopen("./data/uploads/$fileName", 'w+');
            fputs($file, $filedata);
            fclose($file);

            // save photo
            $user->getProfile()->setPhotoid("/uploads/$fileName");
        }

        $this->getEm()->flush();
    }

    /**
     * Login is complete, store user_id and add provider in session.
     * Restore all providers for this user in hybridauth session.
     * Load demo page (set in config, for now).
     */
    public function loginComplete()
    {
        $config = $this->getConfig();

        $user_session = new Container('user');

        if (!isset($user_session->user_id))
        {
            $access = $this->getUserLib()->getStoredAccess($this->userprofile, $this->provider, $this->adapter);
            $user_session->user_id = $access->getUser()->getId();
            $user = $access->getUser();

            // Update number of logins in dB
            $user->setLogins($user->getLogins() + 1);
            $user->setLastLogin(new \DateTime());

            // It is a new logged in user, so reset hybridauth session data from dB.
            // http://hybridauth.sourceforge.net/userguide/HybridAuth_Sessions.html
            $this->getHybridauth()->restoreSessionData($user->getHybridauthSession());
        }
        else
        {
            $user = $this->getUserLib()->getUserAccount($user_session->user_id);
        }

        // Check if reset hybridauth session data is still valid.
        $user_providers = UserLib::getUserProviders($user);

        // check if providers are still set in config
        $active_providers = array();
        foreach ($config['TH']['ZfUser']['hybridauth']['providers'] as $provider => $settings)
        {
            if ($settings['enabled'])
            {
                $active_providers[] = strtolower($provider);
            }
        }

        foreach ($user_providers as $provider)
        {
            if (in_array($provider, $active_providers))
            {
                $adapter = $this->getHybridauth()->getAdapter($provider);
                try
                {
                    $hybridauth_userprofile = $adapter->getUserProfile();

                    // Valid, update access token for this provider access in dB
                    $token = $adapter->getAccessToken();
                    $access_token = $token['access_token'];
                    $this->getAccesstokenLib()->updateAccesstoken($access_token, $adapter, $provider, $hybridauth_userprofile);
                } catch (\Exception $e)
                {
                    $adapter->logout();
                    // No exception, because we want to connect the other providers.
                }
            }
        }

        $user->storeHybridauthSession($this->getHybridauth()->getSessionData());

        $this->getEm()->flush();

        $site_settings = $config['site_settings'];

        return $this->getUrlHelper()->fromRoute($site_settings['home']['route'], array(
                    'controller' => $site_settings['home']['controller'],
                    'action' => $site_settings['home']['action']
        ));
    }

    protected function getLang()
    {
        $translator = $this->getServiceLocator()->get('translator');
        return substr($translator->getLocale(), 0, 2);
    }

}