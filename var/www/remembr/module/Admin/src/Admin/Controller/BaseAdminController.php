<?php

namespace Admin\Controller;

use \Base\Controller\BaseController;

class BaseAdminController extends BaseController
{
	protected $loggedOutRedirectUrl = '/admin/account/login';
}
