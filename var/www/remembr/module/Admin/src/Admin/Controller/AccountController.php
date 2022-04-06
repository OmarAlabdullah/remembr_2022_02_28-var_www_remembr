<?php

namespace Admin\Controller;

class AccountController extends \Base\Controller\BaseController
{
	public function loginAction()
	{
		if ($this->getUser())
			return $this->redirect()->toUrl('/admin');
		
		return array(
				'loginForm' => new \TH\ZfUser\Form\LoginForm(new \TH\ZfUser\Rights\AppAuthAdapter($this->getEm(), $this->getLog()), 'login')
		);
	}
}
