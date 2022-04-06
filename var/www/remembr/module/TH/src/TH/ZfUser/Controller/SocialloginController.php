<?php

/**
 * SocialloginController
 *
 * login:
 *
 * 1) user+provider already in dB: loginComplete
 * 2) user+provider not in db: create new user, add provider and loginComplete
 *
 * provide: "my logins"
 *
 * my_logins:
 *
 * 1) get provider credentials
 * 2) check if credentials does not belong to other user
 * 3a) no:  add provider credentials to this existing account
 * 3b) yes: logout this user and login user for this credentials
 */

namespace TH\ZfUser\Controller;

use Base\Controller\BaseController;
use League\OAuth2\Client\Provider\FacebookUser;
use TH\ZfUser\Controller\Plugin\OAuth2;
use TH\ZfUser\Library\LoginLib;
use Zend\Session\Container;
use TH\ZfUser\Form\DetailsForm;

class SocialloginController extends BaseController
{

    protected $indexActionLoggedOut = true;
    protected $askUserDetailsActionLoggedOut = true;
    protected $hauthActionLoggedOut = true;
    protected $provider;
    protected $config;
    protected $loginlib;
    protected $sessionManager;

    /**
     * Form handling for missing user details for this logged in provider.
     */
    public function askUserDetailsAction()
    {
        $config = $this->getConfig();

        $provider_session = new Container('provider');
        $provider = $provider_session->provider;

        $adapter = $this->hybridauth()->authenticate($provider);

        try
        {
            $userprofile = $adapter->getUserProfile();
        } catch (\Exception $e)
        {
            $adapter->logout();
            throw new \Exception($config['TH']['ZfUser']['errormsg'][$this->getLang()]['other']);
        }

        $form = new DetailsForm();

        $request = $this->getRequest();
        if ($request->isPost())
        {
            $form->setData($request->getPost());
            if ($form->isValid())
            {
                $data = $form->getData();
                $userprofile->email = $data['email'];
                $returnurl = $this->getServiceLocator()->get('TH\ZfUser\Library\LoginLib')->processLogin($userprofile, $provider, $adapter);

                // close window en redirect
                $view = new \Zend\View\Model\ViewModel();
                $view->setTerminal(true)
                        ->setVariable('returnurl', $returnurl)
                        ->setTemplate('th/provider/close-connect');
                return $view;
            }
        }

        $this->layout('layout/popup-layout.twig');
        return array(
            'form' => $form,
        );
    }

    protected function getLang()
    {
        $translator = $this->getServiceLocator()->get('translator');
        return substr($translator->getLocale(), 0, 2);
    }

    /**
     * @return LoginLib
     */
    protected function getLoginLib()
    {
        if (!$this->loginlib)
            $this->loginlib = $this->getServiceLocator()->get('TH\ZfUser\Library\LoginLib');

        return $this->loginlib;
    }

    protected function getConfig()
    {
        if (!isset($this->config))
        {
            $config = $this->getServiceLocator()->get('config');
            $this->config = $config['TH']['ZfUser'];
        }

        return $this->config;
    }

    public function hauthAction()
    {
        $params = $this->getRequest()->getQuery()->toArray();

        $provider = null;
        $keys = array(
            'done', 'start'
        );
        $action = '';
        foreach($keys as $key) {
            $action = $key;
            if(array_key_exists('hauth.'.$key, $params)) {
                $provider = $params['hauth.'.$key];
                break;
            }else if (array_key_exists('hauth_'.$key, $params)) {
                $provider = $params['hauth_'.$key];

                break;
            }
        }

        if($provider != null && \strtolower($provider) == 'facebook') {
            /** @var OAuth2 $oauth */
            $oauth = $this->oauth2($this->getPluginManager());
            $oauth->initProvider($provider);
            if($action == 'start') {
                $this->redirect()->toUrl($oauth->getAuthorizationUrl());
                return null;
            }
            $token = $oauth->getAccessToken($this->getRequest());

            /** @var FacebookUser $user */
            $user = $oauth->getResourceOwner($token);

            // specific for FB down here:
            // id, name (=fullname), first name, last name, locale, link, timezone, age_range, picture_url is_silhouette,
            // email

            $profile = [
                'id' => $user->getId(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'locale' => $user->getLocale(),
                'email' => $user->getEmail()
            ];
            $accessToken = [
                'provider' => $provider,
                'auth_id' => $profile['id'],
                'auth_secret' => '', // legacy
                'access_token' => $token->getToken(),
                'oauth_json' => $token->jsonSerialize()
            ];
            try {
                $message = $this->getLoginLib()->oauthLoginOrCreate($provider, $profile, $accessToken);
            }catch(\Exception $ex) {
                return $this->errorAction($ex->getMessage());
            }
            $this->redirect()->toUrl('/provider/fbloggedin');
            return null;

        }

        die('auth error, provider = '.$provider);
		//$config = $this->getServiceLocator()->get('Configuration');
		//$ha_config = $config['TH']['ZfUser']['hybridauth'];
        //\Hybrid_Auth::initialize($ha_config);
        \Hybrid_Endpoint::process();
    }

}
