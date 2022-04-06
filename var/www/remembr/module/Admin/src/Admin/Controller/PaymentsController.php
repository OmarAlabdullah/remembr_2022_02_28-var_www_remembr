<?php

namespace Admin\Controller;

class PaymentsController extends WrapAdminController
{
	public function __construct() {
		parent::__construct(new \TH\ZfPayment\Controller\OverviewController());
	}

	public function checkAccess($action) {
		return \Auth\Rights\RightList::hasAll($this->getRights(), null, \Admin\Rights::$payments);
	}
}