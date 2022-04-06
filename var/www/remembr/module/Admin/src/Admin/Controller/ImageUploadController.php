<?php

namespace Admin\Controller;

class ImageUploadController extends WrapAdminController
{

	public function __construct() {
		parent::__construct(
			new \ImageUpload\Controller\AdminController()
		);
	}

	public function checkAccess($action) {
		return \Auth\Rights\RightList::hasAll($this->getRights(), null, \Admin\Rights::$all);
	}
}
