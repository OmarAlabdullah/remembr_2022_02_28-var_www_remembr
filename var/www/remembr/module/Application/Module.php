<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ModelInterface;

class Module
{
	private $em;

	public function onBootstrap(MvcEvent $e)
	{
        
        $application = $e->getApplication();
        $em = $application->getEventManager();
        $serviceManager = $application->getServiceManager();
		//$em->attach(\Zend\Mvc\MvcEvent::EVENT_DISPATCH_ERROR, function($e){var_dump($e);die('oh jee');});

		\Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace('Gedmo\Mapping\Annotation', 'vendor/gedmo/doctrine-extensions/lib');
		$serviceManager->get('th_entitymanager')->getFilters()->enable('soft-deleteable');

		$eventManager = $e->getApplication()->getEventManager();
		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);

		$eventManager->attach(MvcEvent::EVENT_ROUTE, array($this, 'onRoute'));
		$eventManager->attach(MvcEvent::EVENT_RENDER, array($this, 'onRender'));
		
//		$eventManager->attach(MvcEvent::EVENT_FINISH, function(MvcEvent $e){
//			$content = $e->getResponse()->getContent();
//			$cleancontent = clean_up_html(content);
//			$e->getResponse()->setContent($cleancontent);
//		});
		
		$twig = $serviceManager->get('Twig_Environment');
		
		$twig->addFunction(new \Twig_SimpleFunction('addglobal',
			function($name, $value) use($twig)
            {
				$twig->addGlobal($name, $value);
			}
		));
	}

	public function onRoute(MvcEvent $e)
	{
		$locale = null;

		// Set local based on http header
		$hal = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']) : null;
		switch(substr($hal, 0, 2))
		{
			case 'nl' : $locale = 'nl_NL'; break;
            case 'nl-be' : $locale = 'nl_BE'; break;
			case 'en' :
			default   : $locale = 'en_US'; break;
		}

		$userlang = '';
		$sm = $e->getApplication()->getServiceManager();
		$user = $sm->get('TH\ZfUser\Library\UserLib')->getUser();
		$twig = $sm->get('Twig_Environment');
		$twig->addGlobal('user', $user);
		if ($user) {
			$userlang = $user->getProfile()->getLanguage();
		}
		
		// Facebook localization, turned off because FB is buggy with this
		//$lang = NULL;
		//$request = $e->getRequest();
		//if ($request instanceof \Zend\Http\Request)
		//{
			//$lang = $request->getQuery()->get('fb_locale', NULL);
			//$hlang = $request->getHeaders()->get('X-Facebook-Locale');
			//error_log('Fetched page with fb_locale=="' . $lang . '" and X_FACEBOOK_LOCALE=="' . ($hlang ? $hlang->getFieldValue() : "") . '" and url "' . $request->getUri() . '"');
		//}
		//if($lang == NULL) {
		$lang = $e->getRouteMatch()->getParam('lang', $userlang);
		//} else {
		//	$lang = substr($lang,0,2);
		//}
        
		$sm->get('Twig_Environment')->addGlobal('lang', $lang );
		
		$explicitLocale = null;
		if ($lang)
		{
			switch ($lang)
			{
				case 'en': $locale = 'en_US'; $explicitLocale = 'en_US'; break;
				case 'nl': $locale = 'nl_NL'; $explicitLocale = 'nl_NL'; break;
                case 'nl-be': $locale = 'nl_BE'; $explicitLocale = 'nl_BE'; break;
			}
		}
		
		$sm->get('Twig_Environment')->addGlobal('locale', $locale);
		$sm->get('Twig_Environment')->addGlobal('explicitLocale', $explicitLocale);
		$sm->get('Twig_Environment')->addGlobal('isMemorialPage', false);
        $sm->get('Twig_Environment')->addGlobal('metaDescriptionAlreadySet', false);
		
		$translator = $e->getApplication()->getServiceManager()->get('translator');
		$translator->setLocale($locale);
		
		$translator2 = $e->getApplication()->getServiceManager()->get('MvcTranslator'); /* fucking zend */
		$translator2->setLocale($locale);
		setlocale(LC_ALL, "$locale.utf8", $locale);
		
		$twig->addFilter(new \Twig_SimpleFilter('translateinto',
			function ($text, $locale) use ($translator) {
				return $translator->translate($text, 'default', $locale);
			}
		));
	}

	protected function setJsonError(MvcEvent $e)
	{
		$result = $e->getResult();
		if ($result instanceof JsonModel)
		{
            return;
        }

        //ini_set('html_errors', 0);

		$response = $e->getResponse();

		$exception = $result->getVariable('exception');
		if ($exception && $exception->getCode())
		{
			$response->setStatusCode($exception->getCode());
		}

        $model = new JsonModel(array(
            'httpStatus' => $response->getStatusCode(),
            'title'      => $response->getReasonPhrase(),
			'error'      => strtolower(str_replace(' ', '', $response->getReasonPhrase()))
        ));

		if ($exception instanceof \Application\RemembrException)
		{
			$model->setVariable('extra', $exception->getClientExtra());
		}

		if ($result->getVariable('display_exceptions'))
		{
			if ($result instanceof ModelInterface && $result->reason)
			{
				$model->reason = $result->reason;
			}

			if ($exception)
			{
				$details = array($exception->getMessage());
				while ($exception = $exception->getPrevious()) {
					$details[] = "* " . $exception->getMessage();
				};

				$model->details = $details;
			}
		}

		$model->setTerminal(true);
        $e->setResult($model);
        $e->setViewModel($model);
	}

    /**
     *
        // to debug some cron behaviour add this
        if ($e->isError())
        {
            var_dump(($e->getViewModel()->getResult()));
            die;
        }
     *
     */
	public function onRender(MvcEvent $e)
	{
		$route = $e->getRouteMatch();
		/**
		 *	If there's an error, and the requested format is json, change the result-model.
		 */
		if ($e->isError() && (
				($route && $route->getParam('format') == 'json') ||
				preg_match('#/json(/|$)#i',$e->getRequest()->getrequestUri())
			))
		{
			return $this->setJsonError($e);
		}

		$result = $e->getResult();
		$sm = $e->getApplication()->getServiceManager();

		$twig = $sm->get('Twig_Environment');
		$config = $sm->get('Config');

		$version= isset($config['application']['version']) ? $config['application']['version'] : 1.0;
		$date	= isset($config['application']['date']) ? $config['application']['date'] : 'now';
		$GA_id	= isset($config['google']['analytics']['id']) ? $config['google']['analytics']['id'] : '';
		$GA_options	= isset($config['google']['analytics']['options']) ? $config['google']['analytics']['options'] : null;
		$request = $e->getRequest();
		if (method_exists($request, 'getUri'))
		{
			$uri = $request->getUri();
			$canonicalurl = $uri->getScheme() . '://' . $uri->getHost() . '%s' . preg_replace('#^/nl/?|^/nl-be/?|^/en/?#', '/', $uri->getPath());
			$twig->addGlobal('canonicalurl', $canonicalurl);
			$baseurl = $uri->getScheme() . '://' . $uri->getHost() . '/';
			$twig->addGlobal('baseurl', $baseurl);
		}
		
		$twig->addGlobal('facebookApiKey', $config['TH']['ZfUser']['hybridauth']['providers']['Facebook']['keys']['id']);
        $twig->addGlobal('included_resources', $config['included_resources']);
        
        if ($route !== null)
        {
            $twig->addGlobal('route', $route);
            $twig->addGlobal('format', $route->getParam('format'));
        }
		$twig->addGlobal('appversion', $version);
		$twig->addGlobal('releasedate', new \DateTime($date));

		$twig->addGlobal('GA_id', $GA_id);
		$twig->addGlobal('GA_options', $GA_options);
		//$twig->addGlobal('requestURL',  $_SERVER["REQUEST_URI"]);
	}

	public function getConfig()
	{
		return include __DIR__ . '/config/module.config.php';
	}

	public function getAutoloaderConfig()
	{
		return array(
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
				),
			),
		);
	}

	    public function getViewHelperConfig()
    {
        return array(
//            'factories' => array(
//                'surl' => function ($sm) {
//					$helper = new \Base\Util\UrlViewHelper;
//					$router = \Zend\Console\Console::isConsole() ? 'HttpRouter' : 'Router';
//					$helper->setRouter($sm->get($router));
//
//					$match = $sm->get('application')
//						->getMvcEvent()
//						->getRouteMatch()
//					;
//
//					if ($match instanceof \Zend\Mvc\Router\RouteMatch) {
//						$helper->setRouteMatch($match);
//					}
//
//					return $helper;
//				},
//            ),
        );
    }
}
