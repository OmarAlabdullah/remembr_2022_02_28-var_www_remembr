<?php

namespace Social\Controller;

use Base\Controller\BaseController;

class AdminController extends BaseController
{

	protected $view;

	public function indexAction()
	{}

	// important
	public function setRequest($req)
	{
			$this->request = $req;
	}
}
