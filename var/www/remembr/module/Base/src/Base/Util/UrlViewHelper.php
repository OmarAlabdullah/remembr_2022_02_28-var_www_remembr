<?php

namespace Base\Util;

use Zend\Mvc\ModuleRouteListener;
//use Zend\Mvc\Router\RouteMatch;
//use Zend\Mvc\Router\RouteStackInterface;
use Zend\View\Exception;

class UrlViewHelper extends \Zend\View\Helper\Url
{
	/**
		 * Generates an url given the name of a route.
		 *
		 * @see    Zend\Mvc\Router\RouteInterface::assemble()
		 * @param  string  $name               Name of the route
		 * @param  array   $params             Parameters for the link
		 * @param  array   $options            Options for the route
		 * @param  mixed $reuseMatchedParams Whether to reuse matched parameters
		 * @return string Url                  For the link href attribute
		 * @throws Exception\RuntimeException  If no RouteStackInterface was provided
		 * @throws Exception\RuntimeException  If no RouteMatch was provided
		 * @throws Exception\RuntimeException  If RouteMatch didn't contain a matched route name
		 */
	public function __invoke($name = null, $params = array(), $options = array(), $reuseMatchedParams = false)
	{
		if (null === $this->router) {
			throw new Exception\RuntimeException('No RouteStackInterface instance provided');
		}

		if (3 == func_num_args() && is_bool($options)) {
			$reuseMatchedParams = $options;
			$options = array();
		}

		if ($name === null) {
			if ($this->routeMatch === null) {
				throw new Exception\RuntimeException('No RouteMatch instance provided');
			}

			$name = $this->routeMatch->getMatchedRouteName();

			if ($name === null) {
				throw new Exception\RuntimeException('RouteMatch does not contain a matched route name');
			}
		}

		if ($reuseMatchedParams && $this->routeMatch !== null) {
			$routeMatchParams = $this->routeMatch->getParams();
			if (is_array($reuseMatchedParams))
			{
				$reuseMatchedParams[] = '__CONTROLLER__'; // @TODO do this in a nicer way
				$reuseMatchedParams[] = 'action';
				$routeMatchParams = array_intersect_key($routeMatchParams, array_combine($reuseMatchedParams,$reuseMatchedParams));
			}

			if (isset($routeMatchParams[ModuleRouteListener::ORIGINAL_CONTROLLER])) {
				$routeMatchParams['controller'] = $routeMatchParams[ModuleRouteListener::ORIGINAL_CONTROLLER];
				unset($routeMatchParams[ModuleRouteListener::ORIGINAL_CONTROLLER]);
			}

			if (isset($routeMatchParams[ModuleRouteListener::MODULE_NAMESPACE])) {
				unset($routeMatchParams[ModuleRouteListener::MODULE_NAMESPACE]);
			}

			$params = array_merge($routeMatchParams, $params);
		}

		$options['name'] = $name;

		return $this->router->assemble($params, $options);
	}
}
