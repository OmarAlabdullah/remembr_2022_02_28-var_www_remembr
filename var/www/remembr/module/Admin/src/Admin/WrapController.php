<?php

namespace Admin;

use \Zend\Mvc\MvcEvent;

abstract class WrapController extends \Base\Controller\BaseController
{
	private $wrapped;
	protected $url403 = '/admin/account/login';

	public function __construct($wrappedController) {
		$this->wrapped = $wrappedController;
	}

	protected function attachDefaultListeners()
	{
		parent::attachDefaultListeners();
		$events = $this->getEventManager();
		$events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'fixTemplatePath'), -85);
	}

	private $wrappedNamespace = null;

	public function onDispatch(MvcEvent $e) {
		$routeMatch = $e->getRouteMatch();
		if (!$routeMatch) {
			throw new Exception\DomainException('Missing route matches; unsure how to retrieve action');
		}

		// Extract the view path stack from the wrapped module and prepend it to the current stack.
		$classParts = explode('\\', get_class($this->wrapped));
		
		if (count($classParts) >= 2 && $classParts[count($classParts) - 2] === 'Controller')
		{
			$moduleClassName = implode('\\', array_slice($classParts, 0, count($classParts)-2)).'\\Module';
		}
		else
		{
			$moduleClassName = implode('\\', array_slice($classParts, 0, count($classParts)-1)).'\\Module';
		}
		
		$otherModule = new $moduleClassName();
		$otherConfig = $otherModule->getConfig();
		$otherViewPaths = $otherConfig['view_manager']['template_path_stack'];
		$this->wrappedNamespace = implode('\\', array_slice($classParts, 0, count($classParts)-1));

		$stack = $this->getServiceLocator()->get('ZfcTwigLoaderTemplatePathStack');
		foreach ($otherViewPaths as $p)
			$stack->prependPath($p);
		$otherTemplatePath = array();
		foreach ($classParts as $c) {
			$otherTemplatePath[]= strtolower($c);
		}

		// If we're just getting the template, then we won't need to modify the session.
		$format = $routeMatch->getParam('format');
		if ($format == 'tpl') {
			$this->getServiceLocator()->get('Zend\Session\SessionManager')->writeClose();
		}

		$action = $routeMatch->getParam('action', 'not-found');
		$method = static::getMethodFromAction($action);

		$this->wrapped->setEvent($e);
		$this->wrapped->setRequest($this->getRequest());

		if (!method_exists($this->wrapped, $method)) {
			$method = 'notFoundAction';
		} else {
			$access = true;
			if (method_exists($this, 'checkAccess')) {
				$access = $this->checkAccess($action);
			}
			if ($access && method_exists($this, $action.'Access')) {
				$access = $this->{$action.'Access'}();
			}
			if (!$access) {
				return $this->redirect()->toUrl($this->url403);
			}
		}

		$otherTemplatePath[]= $action;
		$otherTemplatePath = implode('/', $otherTemplatePath);

		if ($method == 'notFoundAction') {
			if (method_exists($this->wrapped, $method)) {
				$actionResponse = $this->wrapped->$method();
			} else {
				$actionResponse = $this->$method();
			}
		} else {
			$actionResponse = $this->wrapped->$method();
		}

		$e->setResult($actionResponse);

		return $actionResponse;
	}

	/**
	 * Compute the default template path as per InjectTemplateListener::injectTemplate, but using the wrapped
	 * name instead.
	 */
	public function fixTemplatePath(MvcEvent $e) {
		// Note that the cloned event still has the original model in it, so the template injector will modify the
		// actual used model.
		$fakeEvent = clone $e;
		$fakeRm = clone $e->getRouteMatch();
		$fakeEvent->setRouteMatch($fakeRm);
		$fakeEvent->setTarget($this->wrapped);
		$itl = new \Zend\Mvc\View\Http\InjectTemplateListener();
		$itl->injectTemplate($fakeEvent);
	}

	public function setServiceLocator(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
		parent::setServiceLocator($serviceLocator);
		$this->wrapped->setServiceLocator($serviceLocator);
	}
}
