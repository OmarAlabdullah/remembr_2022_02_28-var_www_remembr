<?php

namespace Admin\Controller;

class CsvController extends WrapAdminController
{

    public function __construct() {
		parent::__construct(new \Csv\Controller\AdminController());
	}

	public function checkAccess($action) {
		return \Auth\Rights\RightList::hasAll($this->getRights(), null, \Admin\Rights::$all);
	}

}
