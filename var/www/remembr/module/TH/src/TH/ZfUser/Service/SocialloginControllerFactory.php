<?php
/**
 * ScnSocialAuth Module
 *
 * @category   ScnSocialAuth
 * @package    ScnSocialAuth_Service
 */

namespace TH\ZfUser\Service;

use TH\ZfUser\Controller\SocialloginController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @category   ScnSocialAuth
 * @package    ScnSocialAuth_Service
 */
class SocialloginControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        // Just making sure to instantiate and configure
        // It's not actually needed in HybridAuthController
        $hybridAuth = $controllerManager->getServiceLocator()->get('HybridAuth');

        $controller = new SocialloginController();

        return $controller;
    }
}
