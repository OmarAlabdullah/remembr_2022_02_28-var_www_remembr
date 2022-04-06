<?php

namespace Admin\Controller;

class BannerController extends WrapAdminController
{
	public function __construct() {
		parent::__construct(new \Banner\Controller\AdminController());
	}

	public function checkAccess($action) {
		return \Auth\Rights\RightList::hasAll($this->getRights(), null, \Admin\Rights::$banners);
	}
}
