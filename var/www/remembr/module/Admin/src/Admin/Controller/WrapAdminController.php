<?php

namespace Admin\Controller;

use \Base\Controller\BaseController;

class WrapAdminController extends \Admin\WrapController
{
	protected $loggedOutRedirectUrl = '/admin/account/login';
}
