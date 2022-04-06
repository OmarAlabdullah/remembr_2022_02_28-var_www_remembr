<?php

namespace TH\ZfPayment\Controller;

use Base\Controller\BaseController;
use TH\ZfPayment\Comm;

class CallbackController extends BaseController
{
	protected $callbackActionLoggedOut = true;

	public function callbackAction() {
		$params = $this->params();
		$orderId = $params->fromQuery('order', $params->fromRoute('order'));

		$config = $this->getServiceLocator()->get('Config');
		$config = $config['payments'];

		$payment = $this->getEm()->getRepository('TH\ZfPayment\Entity\Payment')->findOneBy(array('orderRef' => $orderId));

		$dd = new Comm\Docdata($config['username'], $config['password'], isset($config['debug']) ? $config['debug'] : false, $this->getEm());
		$dd->updateStatus($payment);
		die;
	}
}

?>
