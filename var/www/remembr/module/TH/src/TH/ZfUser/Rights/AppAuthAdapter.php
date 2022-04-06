<?php

namespace TH\ZfUser\Rights;

use Auth\Rights\AuthAdapter;

class AppAuthAdapter extends AuthAdapter
{
	public function __construct($entityManager, $log)
	{
		parent::__construct('\TH\ZfUser\Entity\UserAccount', '', '', $entityManager, $log);
	}

	/*
	protected function additionalCheck($user, &$messages)
	{
		return true;
	}
	*/

	protected function postLogin($user) {
		$remote = new \Zend\Http\PhpEnvironment\RemoteAddress();
		$this->log->info('User '.$user->getId().' ('.$user->getProfile()->getName().') logged in from IP '.$remote->getIpAddress());
		$user->setLogins($user->getLogins() + 1);

		$session = new \Zend\Session\Container('user_session');
		$session->lastlogin = $user->getLastLogin();

		$user->setLastLogin(new \DateTime());
		$this->entityManager->flush();
	}
}

?>
