<?php

namespace Admin;

use Zend\Mvc\MvcEvent;

class Module
{
	public function onBootstrap(MvcEvent $e) {
		$eventManager = $e->getApplication()->getEventManager();
		$eventManager->attach(MvcEvent::EVENT_RENDER, array($this, 'onRender'));
	}

	public function onRender(MvcEvent $e)
	{
		$result = $e->getResult();
		$sm = $e->getApplication()->getServiceManager();

		if ($result instanceof \Zend\View\Model\ViewModel) {
			$user = null;

			$em = $sm->get('doctrine.entitymanager.orm_default');
			$repository = $em->getRepository('TH\ZfUser\Entity\UserAccount');
			$userSession = new \Zend\Session\Container('user');
			$user = isset($userSession->user_id)
				? $repository->findOneBy(array('id' => $userSession->user_id))
				: null;

			$twig = $sm->get('Twig_Environment');
			foreach (\Auth\Twig\TwigAuth::getRegisterableRights('Application\\Rights') as $name => $value) {
				$twig->addGlobal($name, $value);
			}
			$twig->addGlobal('user', $user);
		}
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
			'invokables' => array(
				'inlinescript' => 'Zend\View\Helper\InlineScript',
			),
		);
	}
}
