<?php

namespace TH\ZfPayment\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="Payment")
 * @ORM\Entity
 */
class Payment
{
	const stStarted  = 0;  // Payment has been registered with docdata. No status updates were available yet,
	                       // or the payment simply isn't far into the sequence yet. the amt* fields on the object
												 // are to be ignored at this state, they may not contain any real data.
	const stPending  = 1;  // Payment is waiting to be paid by the customer, or waiting for authorization.
	const stPaid     = 2;  // Payment has been successfully paid.
	const stCanceled = 3;  // Payment has been canceled, refunded or charged back (or something else signifying a 'non paid but complete' status).

	public function __construct($ddKey, $orderRef) {
		$this->ddKey = $ddKey;
		$this->orderRef = $orderRef;
	}

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id", type="integer", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id;
	public function getId() { return $this->id; }

	/**
	 * The Docdata payment key.
	 * @var string
	 * @ORM\Column(type="string", length=255, nullable=false, unique=true)
	 */
	protected $ddKey;
	public function getDdKey() { return $this->ddKey; }

	/**
	 * The order reference provided while creating the payment.
	 * @var string
	 * @ORM\Column(type="string", length=35, nullable=false, unique=true)
	 */
	protected $orderRef;
	public function getOrderRef() { return $this->orderRef; }

	/**
	 * One of the status* constants
	 * @var integer
	 * @ORM\Column(type="integer")
	 */
	protected $status = self::stStarted;
	public function getStatus()       { return $this->status; }
	public function setStatus($value) { $this->status = $value; }

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $amtCurrency = '';
	public function getAmtCurrency()       { return $this->amtCurrency; }
	public function setAmtCurrency($value) { $this->amtCurrency = $value; }

	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 */
	protected $amtRegistered = 0;
	public function getAmtRegistered()       { return $this->amtRegistered; }
	public function setAmtRegistered($value) { $this->amtRegistered = $value; }

	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 */
	protected $amtPendingShopper = 0;
	public function getAmtPendingShopper()       { return $this->amtPendingShopper; }
	public function setAmtPendingShopper($value) { $this->amtPendingShopper = $value; }

	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 */
	protected $amtPendingAcquirer = 0;
	public function getAmtPendingAcquirer()       { return $this->amtPendingAcquirer; }
	public function setAmtPendingAcquirer($value) { $this->amtPendingAcquirer = $value; }

	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 */
	protected $amtApprovedAcquirer = 0;
	public function getAmtApprovedAcquirer()       { return $this->amtApprovedAcquirer; }
	public function setAmtApprovedAcquirer($value) { $this->amtApprovedAcquirer = $value; }

	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 */
	protected $amtCaptured = 0;
	public function getAmtCaptured()       { return $this->amtCaptured; }
	public function setAmtCaptured($value) { $this->amtCaptured = $value; }

		/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 */
	protected $amtRefunded = 0;
	public function getAmtRefunded()       { return $this->amtRefunded; }
	public function setAmtRefunded($value) { $this->amtRefunded = $value; }

		/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 */
	protected $amtChargedback = 0;
	public function getAmtChargedBack()       { return $this->amtChargedback; }
	public function setAmtChargedback($value) { $this->amtChargedback = $value; }

}

?>
