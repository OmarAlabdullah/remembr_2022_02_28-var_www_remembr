<?php

namespace Cms\Service;


use Zend\Mvc\Router\Http\TreeRouteStack;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CmsViewHelperFactory  implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $services)
    {
		$sl = $services->getServiceLocator();

		$em = $sl->get('th_entitymanager');
		$translator = $sl->get('translator');

		return new \Cms\View\Helper\Cms($em, $translator->getLocale());
    }
}