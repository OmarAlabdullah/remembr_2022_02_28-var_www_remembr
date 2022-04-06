<?php

namespace Admin\Controller;

class CmsController extends WrapAdminController
{

    public function __construct() {
		parent::__construct(new \Cms\Controller\AdminController());
	}

	public function checkAccess($action) {
		return \Auth\Rights\RightList::hasAll($this->getRights(), null, \Admin\Rights::$cms);
	}
	
}
