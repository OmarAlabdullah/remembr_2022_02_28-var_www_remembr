<?php

namespace TH\ZfPayment\Controller;

use Base\Controller\BaseController;
use TH\ZfPayment\Comm;

class OverviewController extends BaseController
{
	protected $indexActionLoggedOut = true;
	protected $detailsActionLoggedOut = true;

	public function setRequest($req) {
		$this->request = $req;
	}

	public function indexAction() {
		$config = $this->getServiceLocator()->get('Config');
		$config = $config['payments'];

		$filter = array(
			'name' => $this->params()->fromQuery('name'),
			'orderId' => $this->params()->fromQuery('order'),
			'status' => $this->params()->fromQuery('status'),
		);

		$qb = $this->getEm()->createQueryBuilder();
		$qb->select('p')->from('TH\ZfPayment\Entity\Payment', 'p');
		if ($filter['name']) ;
		if ($filter['orderId']) $qb->andWhere('p.orderRef = :orderRef')->setParameter('orderRef', $filter['orderId']);
		if ($filter['status']) $qb->andWhere('p.status = :status')->setParameter('status', $filter['status']);
		$payments = $qb->getQuery()->getResult();
		$dd = new Comm\Docdata($config['username'], $config['password'], isset($config['debug']) ? $config['debug'] : false, $this->getEm());

		return array(
			'payments' => $payments,
			'dd' => $dd,
			'filter' => $filter,
		);
	}

	public function detailsAction() {
		$orderRef = $this->params()->fromRoute('id');
		if (!$orderRef) die;

		$payment = $this->getEm()->getRepository('TH\ZfPayment\Entity\Payment')->findOneBy(array('orderRef' => $orderRef));

		$config = $this->getServiceLocator()->get('Config');
		$config = $config['payments'];
		$dd = new Comm\Docdata($config['username'], $config['password'], isset($config['debug']) ? $config['debug'] : false, $this->getEm());
		$status = $dd->status($payment->getDdKey());

		return array(
			'payment' => $payment,
			'status'  => $status,
		);
	}
}

?>
