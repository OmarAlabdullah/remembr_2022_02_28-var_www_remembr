<?php

namespace TH\ZfPayment\Comm;

class Action {
	const capture    = 0;
	const refund     = 1;
	const chargeback = 2;

	public function __construct($type, $id, $status, Amount $amount, $reason) {
		$this->type = $type;
		$this->id = $id;
		$this->status = $status;
		$this->amount = $amount;
		$this->reason = $reason;
	}

	/** What type the payment was - one of the constants of this class. */
	public function getType() { return $this->type; }
	/** The merchant id for the action. */
	public function getId() { return $this->id; }
	/** The status of the action. */
	public function getStatus() { return $this->status; }
	/** The amount of the action. */
	public function getAmount() { return $this->amount; }
	/** The reason for the action. */
	public function getReason() { return $this->reason; }
}

class PaymentStatus {
	private $id, $method;
	private $authStatus;
	private $amount;
	private $confidenceLevel;
	private $actions;

	const newPayment             = 'NEW';
	const riskCheckOk            = 'RISK_CHECK_OK';
	const riskCheckFailed        = 'RISK_CHECK_FAILED';
	const started                = 'STARTED';
	const startError             = 'START_ERROR';
	const authenticated          = 'AUTHENTICATED';
	const redirectedForAuthentication = 'REDIRECTED_FOR_AUTHENTICATION';
	const authenticationFailed   = 'AUTHENTICATION_FAILED';
	const authenticationError    = 'AUTHENTICATION_ERROR';
	const authorized             = 'AUTHORIZED';
	const redirectedForAuthorization = 'REDIRECTED_FOR_AUTHORIZATION';
	const authorizationRequested = 'AUTHORIZATION_REQUESTED';
	const authorizationFailed    = 'AUTHORIZATION_FAILED';
	const authorizationError     = 'AUTHORIZATION_ERROR';
	const canceled               = 'CANCELED';
	const cancelFailed           = 'CANCEL_FAILED';
	const cancelError            = 'CANCEL_ERROR';
	const cancelRequested        = 'CANCEL_REQUESTED';

	public function __construct($id, $method, $authStatus, Amount $amount, $confidence, array $actions) {
		$this->id = $id;
		$this->method = $method;
		$this->authStatus = $authStatus;
		$this->amount = $amount;
		$this->confidenceLevel = $confidence;
		$this->actions = $actions;
	}

	public function getId() { return $this->id; }

	/** The payment method used for this particular payment */
	public function getMethod() { return $this->method; }
	/** The authorization status of the payments. One of the constants on this class. */
	public function getAuthStatus() { return $this->authStatus; }
	/** The amount that was paid, refunded etc. */
	public function getAmount() { return $this->amount; }
	/** A 'confidence level'. Meaning unknown. */
	public function getConfidenceLevel() { return $this->confidenceLevel; }

	public function getActions() { return $this->actions; }
}

class Status {
	private $totals;
	private $exchangedTo;
	private $exchangeDate;
	private $payments;

	const registered       = 'registered';       // Initial requested total amount
	const shopperPending   = 'shopperPending';   // The amount pending to be paid by the shopper
	const acquirerPending  = 'acquirerPending';  // The amount pending to with an aquirer
	const acquirerApproved = 'acquirerApproved'; // The amount approved by an aquirer
	const captured         = 'captured';         // The currently captured amount
	const refunded         = 'refunded';         // The currently refunded amount
	const chargedback      = 'chargedback';      // the currently charged back amount

	public function __construct($totals, $exchTo, $exchDate, array $payments) {
		$this->totals = $totals;
		$this->exchangedTo = $exchTo;
		$this->exchangeDate = $exchDate;
		$this->payments = $payments;
	}

	/**
	 * Return one or more totals for the payment.
	 *
	 * @param string $type One of the totals constants on this class. If omitted, returns an array with all the totals
	 *                     indexed by these constants.
	 *
	 * Note that these totals are approximates.
	 */
	public function getTotal($type = null) {
		if ($type) {
			return $this->totals[$type];
		} else {
			return $this->totals;
		}
	}

	/**
	 * Gives the currency type in which all the totals are listed. If the order uses multiple currencies, they
	 * will all have been exchanged into this one.
	 *
	 * @return string
	 */
	public function getCurrency() {
		return $this->exchangedTo;
	}

	/**
	 * Returns the date on which the currency exchange rate was obtained to convert currencies.
	 */
	public function getExchangeDate() {
		return $this->exchangeDate;
	}

	public function getPayments() {
		return $this->payments;
	}
}

?>
