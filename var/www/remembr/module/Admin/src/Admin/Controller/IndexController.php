<?php

namespace Admin\Controller;

use Zend\Session\Container;

class IndexController extends BaseAdminController
{
	public function indexAction() {
		// Nothing interesting yet to really display, so throw people at the banners page instead.
		//return $this->redirect()->toUrl('/admin/banners');

		return;
	}
}