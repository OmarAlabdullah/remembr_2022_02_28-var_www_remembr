<?php

namespace TH\ZfPayment\Comm;

// See http://tomi.vanek.sk/index.php?page=wsdl-viewer for making the wsdl a bit more insightful

require_once('Payment.php');

class DocdataException extends \Exception {
	private $textCode = null;

	const unknownError = 'UNKNOWN_ERROR';
	const requestDataMissing = 'REQUEST_DATA_MISSING';
	const requestDataIncorrect = 'REQUEST_DATA_INCORRECT';
	const securityError = 'SECURITY_ERROR';
	const internalError = 'INTERNAL_ERROR';

	public function __construct($message, $code=null) {
		parent::__construct($message.' (code '.$code.')');
		$this->textCode = $code;
	}

	public function getPureMessage() {
		return $this->message;
	}

	public function getTextCode() {
		return $this->textCode;
	}
}

class Docdata {
	const testUrl       = 'https://test.docdatapayments.com/ps/';
	const productionUrl = 'https://secure.docdatapayments.com/ps/';
	//$base_url 	= 'http://localhost/docdatapayment/';								// only required for example

	private $client;
	private $debug;
	private $baseUrl;

	private $em;

	/**
	 *
	 * @param string $merchantName Merchant user id for API
	 * @param string $merchantPass Merchant password for API
	 * @param bool $debug Indicates if testing or production environment should be used.
	 */
	public function __construct($merchantName, $merchantPass, $debug, \Doctrine\ORM\EntityManagerInterface $em) {
		$this->baseUrl = $debug ? self::testUrl : self::productionUrl;
		$this->debug = $debug;
		$this->merchant = array('name' => $merchantName, 'password' => $merchantPass);
		$this->em = $em;
	}

	protected function getClient() {
		if (!$this->client)
			$this->client = new \SoapClient($this->baseUrl.'services/paymentservice/1_0?wsdl', array('features' => SOAP_SINGLE_ELEMENT_ARRAYS));
		return $this->client;
	}

	/**
	 * Creates a new payment.
	 *
	 * @param \TH\ZfPayment\PaymentSettings $paymentSettings
	 * @param string $orderRef A reference for the order that the merchant can use to identify orders
	 * @param \TH\ZfPayment\Amount $totalAmount The amount to be paid
	 * @param \TH\ZfPayment\Shopper $shopper Information on who is making the payment
	 * @param \TH\ZfPayment\Destination $billTo Information on where the bill should be sent to/addressed to
	 * @param string $description Optional description of the payment
	 * @param string $receiptText Optional text that payment providers can use on their statements towards the buyer
	 * @param \TH\ZfPayment\Invoice $invoice Optional invoice with details on the order
	 * @param int $menuCssId Optional CSS id for the payment menu
	 */
	public function create(
		PaymentSettings $paymentSettings,
		$orderRef, Amount $totalAmount,
		Shopper $shopper, Destination $billTo,
		$description = null, $receiptText = null,
		Invoice $invoice = null,
		$menuCssId = null
	) {
		$args = array(
			'version' => '1.0',     // Required fixed value.
			'merchant' => $this->merchant,
			'paymentPreferences' => $paymentSettings->getDDRepr(),
			'merchantOrderReference' => $orderRef,
			'totalGrossAmount' => $totalAmount->getDDRepr(),
			'shopper' => $shopper->getDDRepr(),    // Info on the person placing the order
			'billTo' => $billTo->getDDRepr(),
		);
		if ($description) $args['description'] = $description;  // Optional, describes the order
		if ($receiptText) $args['receiptText'] = $receiptText;  // Optional, text used by payment providers on shopper statements
		if ($invoice) $args['invoice'] = $invoice->getDDRepr();
		if ($menuCssId !== null) {
			$args['menuPreferences'] = array(
				'css' => array('id' => $menuCssId)
			);
		}
			/*
			'includeCosts' => false, // Optional. If true, costs of the payment method are added by DD to the final amount.
			                         // If false (default), these costs are not added, and are assumed included in the final price.
															 // This will not work for every payment method, and may be deprecated in the future.
															 // As such, we're not even going to expose it.
		  */
			/*
			'paymentRequest' => array( // Optional. No clue what this will be for :)
				'initialPaymentReference' => array(
					'linkId' => '',  // LinkId token
					'paymentId' => '',
					'merchantReference' => ''
				)
			),
			*/

		$response = $this->getClient()->create($args);

		if (isset($response->createSuccess)) {
			$key = $response->createSuccess->key;

			$payment = new \TH\ZfPayment\Entity\Payment($key, $orderRef);
			$this->em->persist($payment);
			$this->em->flush();

			return $payment;
			/*
			 * If there was a 'paymentRequest' section in the request, there will also be one of:
			 * $response->createSuccess->paymentResponse->paymentSuccess
			 * $response->createSuccess->paymentResponse->paymentInsufficientData
			 * $response->createSuccess->paymentResponse->paymentError
			 */
		} else {
			throw new DocdataException($response->createError->error->_, $response->createError->error->code);
		}
	}

	/**
	 *
	 * @param type $paymentKey The key previously obtained from Docdata when creating the payment.
	 * @param type $returnUrls An array with optional return urls for the various states. Keys can be: success, pending, canceled or error.
	 * @param type $language Two-letter language code to override the menu's language.
	 * @param type $defaultPayMethod If this is given, the named payment method will appear first in the menu, and this method will
	 *                               be selected as well.
	 * @param type $defaultPayAct If this is true, and the defaultPayMethod is set, the user will be sent straight towards the payment
	 *                            screen for the given payment method if this can be done for the payment method. For iDeal payments,
	 *                            this parameter must contain a string with the issuer id of the bank instead.
	 */
	public function getMenuUrl($paymentKey, $returnUrls=null, $language=null, $defaultPayMethod=null, $defaultPayAct=false) {
		if (!$returnUrls) $returnUrls = array();
		$badKeys = array_diff_key($returnUrls, array('success' => true, 'pending' => true, 'canceled' => true, 'error' => true));
		if (count($badKeys) > 0) {
			throw new DocdataException('Invalid keys in the return urls parameter: '.  implode(',', array_keys($badKeys)));
		}

		$url = $this->baseUrl.'menu?command=show_payment_cluster&merchant_name='.$this->merchant['name'].'&payment_cluster_key='.$paymentKey;
		if ($language) $url.= '&client_language='.$language;
		if ($defaultPayMethod) {
			$url.= '&default_pm='.$defaultPayMethod;
			if ($defaultPayAct) {
				$url.= '&default_act=true';
				if ($defaultPayMethod == 'IDEAL' && is_string($defaultPayAct)) {
					$url.= '&default_issuer_id='.$defaultPayAct;
				}
			}
		}
		return $url;
	}

	/**
	 * Capture a payment.
	 *
	 * I'm not actually sure when this is needed. Examining the DocData docs this seems to be necessary to actually get
	 * the paid money, but actual testing indicates captures are done automatically.
	 */
	public function capture($paymentId, $captureRef = null, Amount $amount = null, $itemCode = null, $description = null,
		$finalCapture = null, $cancelReserved = null, DateTime $date = null) {
		$args = array(
			'version' => '1.0',     // Required fixed value.
			'merchant' => $this->merchant,
			'paymentId' => $paymentId,
		);
		if ($captureRef !== null) $args['merchantCaptureReference'] = $captureRef;
		if ($amount !== null) $args['amount'] = $amount->getDDRepr();
		if ($itemCode !== null) $args['itemCode'] = $itemCode;
		if ($description !== null) $args['description'] = $description;
		if ($finalCapture !== null) $args['finalCapture'] = $finalCapture;
		if ($cancelReserved !== null) $args['cancelReserved'] = $cancelReserved;
		if ($date !== null) $args['requiredCaptureDate'] = $date->format('Y-m-d H:i:s');

		$response = $this->getClient()->capture($args);

		if (isset($response->captureSuccess)) {
			return true;
		} else {
			throw new DocdataException($response->captureError->error->_, $response->captureError->error->code);
		}
	}

	public function cancel($paymentKey) {
		$response = $this->getClient()->cancel(array(
			'version' => '1.0',     // Required fixed value.
			'merchant' => $this->merchant,
			'paymentOrderKey' => $paymentKey
		));

		if (isset($response->cancelSuccess)) {
			return true;
		} else {
			throw new DocdataException($response->cancelError->error->_, $response->cancelError->error->code);
		}
	}

	public function refund($paymentId, $refundRef=null, Amount $amount=null, $itemCode=null, $description=null, $cancelReserved=null,
		DateTime $date=null, $bankAccount=null) {
		$args = array(
			'version' => '1.0',     // Required fixed value.
			'merchant' => $this->merchant,
			'paymentId' => $paymentId,
		);
		if ($refundRef !== null) $args['merchantRefundReference'] = $refundRef;
		if ($amount !== null) $args['amount'] = $amount->getDDRepr();
		if ($itemCode !== null) $args['itemCode'] = $itemCode;
		if ($description !== null) $args['description'] = $description;
		if ($cancelReserved !== null) $args['cancelReserved'] = $cancelReserved;
		if ($date !== null) $args['requiredRefundDate'] = $date->format('Y-m-d');

		$response = $this->getClient()->refund($args);

		if (isset($response->refundSuccess)) {
			return true;
		} else {
			throw new DocdataException($response->refundError->error->_, $response->refundError->error->code);
		}
	}

	/**
	 * Get a status report on the payment.
	 */
	public function status($paymentKey/*, $extended=false*/) {
		/*
		 * Note: At time of development, the test account had the extended status command disabled. As such, no testing
		 * could be done on it and its support is therefore disabled.
		 */
		$args = array(
			'version' => '1.0',     // Required fixed value.
			'merchant' => $this->merchant,
			'paymentOrderKey' => $paymentKey
		);
		//$response = $extended ? $this->getClient()->statusExtended($args) : $this->getClient()->status($args);
		$response = $this->getClient()->status($args);

		if (isset($response->statusSuccess)) {
			Status::captured;

			$report = $response->statusSuccess->report;

			$totals = array();
			foreach (get_object_vars($report->approximateTotals) as $key => $value) {
				if (substr($key, 0, 5) == 'total') {
					$totals[lcfirst(substr($key, 5))] = $value;
				}
			}
			$payments = array();
			foreach ($report->payment as $p) {
				$actions = array();
				if (isset($p->authorization->capture)) {
					foreach ($p->authorization->capture as $action) {
						$actions[] = new Action(Action::capture,
							isset($action->merchantCaptureId) ? $action->merchantCaptureId : null,
							$action->status, new Amount($action->amount->_, $action->amount->currency),
							isset($action->reason) ? $action->reason : null
						);
					}
				}
				if (isset($p->authorization->refund)) {
					foreach ($p->authorization->refund as $action) {
						$actions[] = new Action(Action::refund,
							isset($action->merchantRefundId) ? $action->merchantRefundId : null,
							$action->status, new Amount($action->amount->_, $action->amount->currency),
							isset($action->reason) ? $action->reason : null
						);
					}
				}
				if (isset($p->authorization->chargeback)) {
					foreach ($p->authorization->chargeback as $action) {
						$actions[] = new Action(Action::chargeback,
							isset($action->chargebackId) ? $action->chargebackId : null,
							$action->status, new Amount($action->amount->_, $action->amount->currency),
							isset($action->reason) ? $action->reason : null
						);
					}
				}
				$payments[]= new PaymentStatus($p->id, $p->paymentMethod, $p->authorization->status,
					new Amount($p->authorization->amount->_, $p->authorization->amount->currency),
					$p->authorization->confidenceLevel,
					$actions
				);
			}
			return new Status($totals, $report->approximateTotals->exchangedTo,
				\DateTime::createFromFormat('Y-m-d H:i:s', $report->approximateTotals->exchangeRateDate),
				$payments
			);
		} else {
			throw new DocdataException($response->statusError->error->_, $response->statusError->error->code);
		}
	}

	public function updateStatus(\TH\ZfPayment\Entity\Payment $payment) {
		$status = $this->status($payment->getDdKey());

		$totals = $status->getTotal();
		$payment->setAmtCurrency($status->getCurrency());
		$payment->setAmtRegistered($totals[Status::registered]);
		$payment->setAmtPendingShopper($totals[Status::shopperPending]);
		$payment->setAmtPendingAcquirer($totals[Status::acquirerPending]);
		$payment->setAmtApprovedAcquirer($totals[Status::acquirerApproved]);
		$payment->setAmtCaptured($totals[Status::captured]);
		$payment->setAmtRefunded($totals[Status::refunded]);
		$payment->setAmtChargedback($totals[Status::chargedback]);

		$status = \TH\ZfPayment\Entity\Payment::stPending;
		if ($totals[Status::captured] >= $totals[Status::registered])
			$status = \TH\ZfPayment\Entity\Payment::stPaid;
		if ($totals[Status::acquirerApproved]+$totals[Status::acquirerPending]+$totals[Status::shopperPending] >= $totals[Status::registered])
			$status = \TH\ZfPayment\Entity\Payment::stPaid;
		if ($totals[Status::chargedback] >= $totals[Status::registered])
			$status = \TH\ZfPayment\Entity\Payment::stCanceled;
		if ($totals[Status::refunded] >= $totals[Status::registered])
			$status = \TH\ZfPayment\Entity\Payment::stCanceled;

		$payment->setStatus($status);

		$this->em->flush();
	}
}

?>
