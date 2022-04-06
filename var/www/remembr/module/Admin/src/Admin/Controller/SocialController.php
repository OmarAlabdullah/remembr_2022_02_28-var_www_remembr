<?php

namespace Admin\Controller;

class SocialController extends WrapAdminController
{

    public function __construct() {
		parent::__construct(new \Social\Controller\AdminController());
	}

	public function checkAccess($action) {
		return \Auth\Rights\RightList::hasAll($this->getRights(), null, \Admin\Rights::$all);
	}
}
