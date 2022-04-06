<?php
/**
 * Created by PhpStorm.
 * User: niek.local
 * Date: 3/17/17
 * Time: 3:14 PM
 */

namespace TH\ZfUser\Controller\Plugin;

use Zend\Http\Request;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\Controller\PluginManager;
use League\OAuth2\Client\Provider\AbstractProvider;

class OAuth2 extends AbstractPlugin
{
    /*TH/ZfUser/OAuth2/
          facebook => [
               className => \League\OAuth2\Client\Provider\Facebook
               init => [
                        'clientId'          => '1804137233196080',
                        'clientSecret'      => '7a718497410d1275fe7b34eddc4bb74b',
                        'redirectUri'       => 'http://remembr.local/sociallogin/hauth?hauth.done=Facebook',
                        'graphApiVersion'   => 'v2.8',
                    ]
               authorizationParameters =>  ['scope' => ['public_profile', 'email']]
          ],

     */


    /**
     * @var array|null
     */
    protected $config = null;
    /**
     * @var AbstractProvider|null
     */
    protected $provider = null;
    /**
     * @param PluginManager $pluginManager
     */
    public function __invoke($pluginManager = null) {
        if ($pluginManager !== null)
            $this->setController($pluginManager->getController());

        $services =  $pluginManager->getServiceLocator();

        $sessionManager = $services->get('Zend\Session\SessionManager')->start(); // we need storage for states
        $this->config = $services->get('Configuration');
        $this->config = $this->config['TH']['ZfUser']['OAuth2'];

        return $this;
    }

    public function initProvider($name) {
        $name = \strtolower($name);
        if(!array_key_exists($name, $this->config))
            throw new \Exception('OAuth client for '.$name.' does not exist');

        $this->config = $this->config[$name];
        $class = $this->config['className'];
        $this->provider = new $class($this->config['init']);
        return $this;
    }

    public function getAuthorizationUrl() {
        return $this->provider->getAuthorizationUrl($this->config['authorizationParameters']);
    }

    public function validate(Request $request) {
        // check state against session's oauth2state

        //if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
        // unset($_SESSION['oauth2state']);
        // echo 'Invalid state.';
        // exit;
        return true; // skip for now
    }

    public function getProviderObject() {
        return $this->provider;
    }

    public function getAccessToken(Request $request) {

        $code = $request->getQuery()->get('code');
        $token = $this->provider->getAccessToken('authorization_code', ['code'=>$code]);

        return $token;
    }

    public function getResourceOwner($token) {
        return $this->provider->getResourceOwner($token);
    }
}