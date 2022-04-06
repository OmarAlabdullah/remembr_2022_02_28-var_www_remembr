<?php

/**
 * ProviderController
 *
 * This controller tries to add a provider to the hybridauth object.
 * After an user did succesfully log in for this provider,
 * the SocialloginController will process this user and or provider.
 */

namespace TH\ZfUser\Controller;

use Base\Controller\BaseController;
use TH\ZfUser\Controller\Plugin\OAuth2;
use Zend\Session\Container;

class ProviderController extends BaseController implements \Zend\Mvc\Controller\Plugin\PluginInterface
{

    // @TODO: check which are needed
    protected $addProviderActionLoggedOut = true;
    protected $fbloggedinActionLoggedOut = true;
    protected $logoutActionLoggedOut = true;
    protected $resetProviderActionLoggedOut = true;
    protected $logoutProviderActionLoggedOut = true;
    protected $loginProviderActionLoggedOut = true;
    protected $processProviderActionLoggedOut = true;
    protected $authActionLoggedOut = true;
    protected $accesstokenlib;

    protected $hybrid_auth = null;
    protected $hybrid_auth_init = false;
    
    protected function getAccesstokenLib()
    {
        if (!$this->accesstokenlib)
            $this->accesstokenlib = $this->getServiceLocator()->get('TH\ZfUser\Library\AccesstokenLib');

        return $this->accesstokenlib;
    }



    public function addProviderAction() {
        $params = $this->getRequest()->getQuery();

        if (isset($params->provider)) {
            $provider = $params->provider;
            if (\strtolower($provider) == 'facebook') {
                /** @var OAuth2 $oauth */
                $oauth = $this->oauth2($this->getPluginManager());
                $oauth->initProvider($provider);
                $this->redirect()->toUrl($oauth->getAuthorizationUrl());
                //die($oauth->getAuthorizationUrl());
                return null;
            }
        }
    }

    public function doneAction() {
        $view = new \Zend\View\Model\ViewModel();
        $view->setTerminal(true)
            ->setVariable('returnurl', 'http://remembr.local')
            ->setTemplate('th/provider/close-connect');
        return $view;
    }

    /**
     * Add a provider to the hybridauth object.
     *
     * @deprecated Stuk
     * @return \Zend\View\Model\ViewModel
     */
    public function addProviderAction2()
    {
        $config = $this->getConfig();

        $getparams = $this->getRequest()->getQuery();

        if (isset($getparams->provider))
        {
            $provider = $getparams->provider;
            if(\strtolower($provider) == 'facebook') {
                $oauth = $this->oauth2($this->getPluginManager());
                $oauth->initProvider($provider);
                $this->redirect()->toUrl($oauth->getAuthorizationUrl());
                //die($oauth->getAuthorizationUrl());
                return null;

            }
            else if (is_object($this->hybridauth()) && $this->hybridauth() instanceof \Exception)
            {
                throw new \Exception($config['TH']['ZfUser']['errormsg'][$this->getLang()]['no_connection'] . $provider);
// this might be any HA error, not just connection.

                $errorcode = $this->hybridauth()->getCode();
                $msg = $this->hybridauth()->getMessage();

//				switch( $errorcode ){
//					case 0 : echo "Unspecified error."; break;
//					case 1 : echo "Hybriauth configuration error."; break;
//					case 2 : echo "Provider not properly configured."; break;
//					case 3 : echo "Unknown or disabled provider."; break;
//					case 4 : echo "Missing provider application credentials."; break;
//					case 5 : echo "Authentification failed. The user has canceled the authentication or the provider refused the connection."; break;
//					case 6 : echo "User profile request failed. Most likely the user is not connected to the provider and he should authenticate again.";
//					//$twitter->logout();
//						break;
//					case 7 : echo "User not connected to the provider.";
//					//$twitter->logout();
//						break;
//					case 8 : echo "Provider does not support this feature."; break;
//				}


                die();
            }

            if (is_object($this->hybridauth()) && $this->hybridauth()->isConnectedWith($provider))
            {
                // User is logged in for this provider.
                // Check if access token is still valid.
                $this->getAccesstokenLib()->validateAccessToken($provider);
                return $this->closeConnectWindow($provider);
            }
            else
            {
                $params = array();

                if ($provider == "OpenID")
                {
                    $params["openid_identifier"] = $getparams->openid_identifier;
                }

                if (isset($getparams->redirect_to_idp) && is_object($this->hybridauth()))
                {
                    // After loading view, try to connect to provider.
                    return $this->hybridauth()->authenticate($provider, $params);
                }
                else
                {

                    // Here we display a "loading view" while trying to redirect the user to the provider.
                    $this->layout('layout/layout-noheader.twig');
                    $view = new \Zend\View\Model\ViewModel(array(
                        'provider' => ucfirst(strtolower(strip_tags($provider))),
                    ));

                    $view->setTemplate('th/provider/load-provider');
                    return $view;
                }
            }
        }
        else
        {
            $user_session = new Container('user');
            $connected = isset($user_session->user_id) ? $this->loginApi()->getLoggedInProviders() : array();

            $active_providers = array();
            foreach ($config['TH']['ZfUser']['hybridauth']['providers'] as $provider => $settings)
            {
                if ($settings['enabled'])
                {
                    $active_providers[] = strtolower($provider);
                }
            }

            $this->layout('layout/layout-noheader.twig');
            return array(
                'connected' => $connected,
                'active_providers' => $active_providers
            );
        }
    }

    /**
     * Close the connect window for this provider.
     */
    public function closeConnectWindow($provider)
    {
        // @TODO change name or place, this does not really make sense.
        //$returnurl = $this->getServiceLocator()->get('TH\ZfUser\Library\LoginLib')->finishLogin($provider);
        $returnurl = '/';

        $view = new \Zend\View\Model\ViewModel();
        $view->setTerminal(true)
                ->setVariable('returnurl', $returnurl)
                ->setTemplate('th/provider/close-connect');
        return $view;
    }

    /**
     * Logout specific or all provider(s).
     */
    public function logoutProviderAction()
    {
        $config = $this->getConfig();
        $provider = $this->params()->fromRoute('provider');

        if ($provider === 'default')
        {
            // Site logout: log out user and all providers.
            // $this->hybridauth()->logoutAllProviders();
            // check if providers are still set in config
            $active_providers = array();
            foreach ($config['TH']['ZfUser']['hybridauth']['providers'] as $provider => $settings)
            {
                if ($settings['enabled'])
                {
                    $active_providers[] = $provider;
                }
            }

            foreach ($active_providers as $provider)
            {
                if ($this->hybridauth()->isConnectedWith($provider))
                {
                    $adapter = $this->hybridauth()->authenticate($provider);
                    $adapter->logout();
                }
            }

            $user_session = new Container('user');
            $user_session->getManager()->getStorage()->clear('user');

            $this->hybridauth()->storage()->clear();

            // Logout normal login user account.
            return $this->redirect()->toUrl('/account/logout');
        }
        else
        {
            // Just logout this provider.
            $adapter = $this->hybridauth()->authenticate($provider);
            $adapter->logout();
        }

        $this->toHomePage();
    }

    protected function toHomePage()
    {
        $config = $this->getConfig();
        $site_settings = $config['site_settings'];

        return $this->redirect()->toRoute($site_settings['home']['route'], array(
                    'controller' => $site_settings['home']['controller'],
                    'action' => $site_settings['home']['action']
        ));
    }

    /**
     * Logout specific or all provider(s).
     */
    public function logoutProviderAjaxAction()
    {
        $provider = $this->params()->fromRoute('provider');

        if ($provider === 'default')
        {
// Site logout: log out user and all providers.

            $this->hybridauth()->logoutAllProviders();

            $user_session = new Container('user');
            $user_session->getManager()->getStorage()->clear('user');

             // clear HA session
            $this->hybridauth()->storage()->clear();

// Logout normal login user account.
            $service = new \Zend\Authentication\AuthenticationService();
            $service->clearIdentity();
// reload page
        }
        else
        {
// Just logout this provider.
            $adapter = $this->hybridauth()->authenticate($provider);
            $adapter->logout();
        }

        return;
    }

    /**
     * Check if fb access token is still valid.
     */
    public function fbloggedinAction()
    {
        #$this->getAccesstokenLib()->validateAccessToken("facebook");
        #$this->hybridauth()->authenticate("facebook");
        return $this->closeConnectWindow("facebook");
    }

    /**
     * Logout from provider and rerout to login page.
     */
    public function resetProviderAction()
    {
        $config = $this->getConfig();

        $provider = $this->getEvent()->getRouteMatch()->getParam('provider');
        $adapter = $this->hybridauth()->authenticate($provider);
        $adapter->logout();

        throw new \Exception($config['TH']['ZfUser']['errormsg'][$this->getLang()]['provider_not_connected'] . $provider);
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

    protected function getLang() {
         $translator = $this->getServiceLocator()->get('translator');
                return substr($translator->getLocale(), 0, 2);
    }

    public function getController() {
        return $this;
    }

    public function setController(\Zend\Stdlib\DispatchableInterface $controller) {
        throw new \Exception("Cannot set controller");
    }

}
