<?php

namespace TH;

use Zend\Mvc\MvcEvent;
// use Zend\View\Model\ViewModel;

class Module //implements BootstrapListenerInterface, AutoloaderProviderInterface, ConfigProviderInterface
{

    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    'TH\ZfBase' => __DIR__ . '/ZfBase',
                    'TH\ZfUser' => __DIR__ . '/ZfUser',
                    'TH\ZfctwigTranslation' => __DIR__ . '/ZfctwigTranslation',
                    'TH\ZfUpload' => __DIR__ . '/ZfUpload',
                    'TH\ZfQrCode' => __DIR__ . '/ZfQrCode',
                    'TH\ZfPayment' => __DIR__ . '/ZfPayment',
                    'TH\ZfMinify' => __DIR__ . '/ZfMinify',
                    'TH' => __DIR__,
                ),
            ),
        );
    }

    /**
    * Register view helpers for this module
    *
    * @return array
    */
    public function getViewHelperConfig()
    {
        return array(
            'invokables' => array(
                'headlink' => 'TH\ZfMinify\View\Helper\HeadLink',
                'headscript' =>  'TH\ZfMinify\View\Helper\HeadScript',
                'inlinescript' =>  'TH\ZfMinify\View\Helper\InlineScript',
                'image' =>  'TH\ZfMinify\View\Helper\Image',
            ),
            'factories' => array (
                'qrcode' =>  function($sm)
                {
                    $config = $sm->getServiceLocator()->get('Config');
                    $path = $config['TH']['QrCode']['path'];
                    $filetype = $config['TH']['QrCode']['filetype'];

                    return new \TH\ZfQrcode\View\Helper\QrCode($path, $filetype);
                },
                'qrcodeurl' =>  function($sm)
                {
                    $config = $sm->getServiceLocator()->get('Config');
                    $path = $config['TH']['QrCode']['path'];
                    $filetype = $config['TH']['QrCode']['filetype'];

                    return new \TH\ZfQrcode\View\Helper\QrCodeUrl($path, $filetype);
                },
            )
        );
    }

    public function onBootstrap(\Zend\Mvc\MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $eventManager->attach('dispatch.error', function($event) {
            $controller = $event->getTarget();

            $exception = $event->getResult()->exception;
            if ($exception)
            {
                // A custom error page.
                $viewModel = $event->getResult();
                $viewModel->setVariables(array('errormsg' => $exception->getMessage()))
                        ->setTerminal(true)->setTemplate('layout/error'); //th/error/errorcustom
            }
        });
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                /* 'HybridAuth' => function($sm) {
                    $config = $sm->get('config');
                    $haConfig = !empty($config['hybridauth']) ? $config['hybridauth'] : array();
                    $hybridAuth = new \Hybrid_Auth($haConfig);

                    return $hybridAuth;
                },*/
                'TH\ZfctwigTranslation\Extension\I18n' =>  function($sm) {
                    $twigTrans = new \TH\ZfctwigTranslation\Extension\I18n();
                    $zendTrans = $sm->get('translator');
                    $twigTrans->setTranslator($zendTrans);
                    return $twigTrans;
                },
                'qrcodeurl' =>  function($sm)
				{
					$config = $sm->getServiceLocator()->get('Config');
					$path = $config['TH']['QrCode']['path'];
					$filetype = $config['TH']['QrCode']['filetype'];

					return new View\Helper\QrCodeUrl($path, $filetype);

                 },
                'qrcode' =>  function($sm)
				{
					$config = $sm->getServiceLocator()->get('Config');
					$path = $config['TH']['QrCode']['path'];
					$filetype = $config['TH']['QrCode']['filetype'];

					return new View\Helper\QrCode($path, $filetype);

                },
            ),
            'invokables' => array(
                'TH\ZfUser\Library\UserLib' => 'TH\ZfUser\Library\UserLib',
                'TH\ZfUser\Library\ProviderLib' => 'TH\ZfUser\Library\ProviderLib',
                'TH\ZfUser\Library\AccesstokenLib' => 'TH\ZfUser\Library\AccesstokenLib',
                'TH\ZfUser\Library\OAuthStorage' =>   'TH\ZfUser\Library\OAuthStorage',
                'TH\ZfUser\Library\LoginLib' => 'TH\ZfUser\Library\LoginLib',
                'TH\ZfUser\Controller\DemoProviderBase' => 'TH\ZfUser\Controller\DemoProviderBase',
            ),
        );
    }
}

