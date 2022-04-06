<?php

namespace Admin\Controller;

class PagesettingsController extends WrapAdminController
{

    public function __construct() {
		parent::__construct(new \Pagesettings\Controller\AdminController());
	}

	public function checkAccess($action) {
		return \Auth\Rights\RightList::hasAll($this->getRights(), null, \Admin\Rights::$all);
	}

}
