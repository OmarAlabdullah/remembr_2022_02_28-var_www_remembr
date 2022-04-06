<?php
/**
 * ScnSocialAuth Module
 *
 * @category   ScnSocialAuth
 * @package    ScnSocialAuth_Service
 */

namespace TH\ZfUser\Service;

use Hybrid_Auth;
use Zend\Mvc\Router\Http\TreeRouteStack;
use Zend\Mvc\Controller\Plugin\PluginInterface;
use Zend\Stdlib\DispatchableInterface as Dispatchable;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Hybrid_AuthPlugin extends Hybrid_Auth implements PluginInterface
{
    /**
     * @var null|Dispatchable
     */
    protected $controller;

    /**
     * Set the current controller instance
     *
     * @param  Dispatchable $controller
     * @return void
     */
    public function setController(Dispatchable $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Get the current controller instance
     *
     * @return null|Dispatchable
     */
    public function getController()
    {
        return $this->controller;
    }
}

/**
 * @category   ScnSocialAuth
 * @package    ScnSocialAuth_Service
 */
class HybridAuthFactory implements FactoryInterface
{
	
	
	public function createService(ServiceLocatorInterface $services)
	{
		// Making sure the SessionManager is initialized
		// before creating HybridAuth components
		$sessionManager = $services->get('Zend\Session\SessionManager')->start();

		$config = $services->get('Configuration');
		$ha_config = $config['TH']['ZfUser']['hybridauth'];

		$baseUrl = $this->getBaseUrl($services);
		$ha_config['base_url'] = $baseUrl;

		try
		{
			$hybridAuth = new Hybrid_AuthPlugin($ha_config);
		}
		catch (\Exception $e)
		{
			// $hybridAuth = new Hybrid_Auth($ha_config);
			return $e;
		}

		$hybridAuth::$store = new \TH\ZfUser\Library\HAStorage();
		//\Hybrid_Endpoint::$initDone = true;

		return $hybridAuth;
	}

    public function getBaseUrl(ServiceLocatorInterface $services)
    {
        $router = $services->get('Router');
        if (!$router instanceof TreeRouteStack) {
            throw new ServiceNotCreatedException('TreeRouteStack is required to create a fully qualified base url for HybridAuth');
        }

        $request = $services->get('Request');
        if (!$router->getRequestUri() && method_exists($request, 'getUri')) {
            $router->setRequestUri($request->getUri());
        }
        if (!$router->getBaseUrl() && method_exists($request, 'getBaseUrl')) {
            $router->setBaseUrl($request->getBaseUrl());
        }
        
        $r = $router->assemble(
            array(),
            array(
                'name' => 'scn-social-auth-hauth',
                'force_canonical' => true,
            )
        );
        return $r;
    }
}
