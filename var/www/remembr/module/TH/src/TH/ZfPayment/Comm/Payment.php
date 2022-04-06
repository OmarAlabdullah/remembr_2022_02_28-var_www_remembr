<?php

namespace TH\ZfPayment\Comm;

class PaymentSettings {
	private $profile, $daysToPay;
	private $ex1Days = null, $ex1Profile = null;
	private $ex2Days = null, $ex2Profile = null;

	/**
	 * @param string $profile The payment profile to use
	 * @param int $daysToPay The number of days in which the payment must be processed, after this it will expire
	 */
	public function __construct($profile, $daysToPay) {
		$this->profile = $profile;
		$this->daysToPay = $daysToPay;
	}

	/**
	 * Sets exhortation period 1.
	 *
	 * @param int $days Number of days after which this period will start
	 * @param string $profile The payment profile to use in this exhortation
	 */
	public function setExhortation1($days, $profile=null) {
		$this->ex1Days = $days;
		$this->ex1Profile = $profile;
	}

	/**
	 * Sets exhortation period 2. Period 1 must be set before this method can be called successfully.
	 *
	 * @param int $days Number of days after which this period will start
	 * @param string $profile The payment profile to use in this exhortation
	 */
	public function setExhortation2($days, $profile=null) {
		if ($this->ex1Days === null)
			throw new Exception('Exhortation period 1 must be set before period 2.');
		$this->ex2Days = $days;
		$this->ex2Profile = $profile;
	}

	public function getDDRepr() {
		$result = array(
			'profile' => $this->profile,
			'numberOfDaysToPay' => $this->daysToPay
		);
		if ($this->ex1Days !== null) {
			$result['exhortation'] = array(
				'period1' => array('numberOfDays' => $this->ex1Days)
			);
			if ($this->ex1Profile !== null) $result['exhortation']['period1']['profile'] = $this->ex1Profile;
			if ($this->ex2Days) {
				$result['exhortation']['period2'] = array('numberOfDays' => $this->ex2Days);
				if ($this->ex2Profile !== null) $result['exhortation']['period2']['profile'] = $this->ex2Profile;
			}
		}
		return $result;
	}
}

class Name {
	private $initials, $first, $middle, $last;
	private $prefix, $suffix;

	/**
	 *
	 * @param string $first First given name
	 * @param string $last  Family name
	 * @param string $initials Initials
	 * @param string $middle Second and later given names, can also be used for middle initials
	 * @param string $prefix Name prefixes such as Mr, Mrs, Dr, etc.
	 * @param string $suffix Name suffixes such as Ph.D., Jr., etc.
	 */
	public function __construct($first, $last, $initials=null, $middle=null, $prefix=null, $suffix=null) {
		$this->initials = $initials;
		$this->first = $first;
		$this->middle = $middle;
		$this->last = $last;
		$this->prefix = $prefix;
		$this->suffix = $suffix;
	}

	public function getDDRepr() {
		$result = array(
			'first' => $this->first,
			'last' => $this->last
		);
		if ($this->prefix !== null) $result['prefix'] = $this->prefix;        // Optional, the field such as Mr., Dr., etc.
		if ($this->initials !== null) $result['initials'] = $this->initials;  // Optional
		if ($this->middle !== null) $result['middle'] = $this->middle;        // Optional
		if ($this->suffix !== null) $result['suffix'] = $this->suffix;        // Optional, same as prefix but for things after the name.
		return $result;
	}
}

class Address {
	private $street, $number, $postalCode, $city, $country;
	private $company, $numberAdd;

	/**
	 * @param string $street
	 * @param string $number
	 * @param string $numberAdd 'add-on' part of the house number. Use null if this is not present.
	 * @param string $postalCode Note that Docdata applies strict checking of the zip code format, e.g. Dutch codes must not have a space.
	 * @param string $city
	 * @param string $country ISO 3166 country code
	 * @param string $company May be null
	 */
	public function __construct($street, $number, $numberAdd, $postalCode, $city, $country, $company=null) {
		$this->street = $street;
		$this->number = $number;
		$this->numberAdd = $numberAdd;
		$this->postalCode = $postalCode;
		$this->city = $city;
		$this->country = $country;
		$this->company = $company;
	}

	public function getDDRepr() {
		$result = array(
			'street' => $this->street,
			'houseNumber' => $this->number,
			'postalCode' => $this->postalCode,
			'city' => $this->city,
			'country' => array('code' => $this->country)
		);
		if ($this->company !== null) $result['company'] = $this->company;
		if ($this->numberAdd !== null) $result['houseNumberAddition'] = $this->numberAdd;
		return $result;
	}
}

class Shopper {
	private $id, $name, $email, $language, $gender, $dateOfBirth, $phone, $mobilePhone;

	const genderM = 'M';
	const genderF = 'F';
	const genderOther = 'U';

	/**
	 * @param string $id
	 * @param \TH\ZfPayment\Name $name
	 * @param string $email
	 * @param string $language ISO 639 2-letter language code
	 * @param string $gender One of this class's gender* constants
	 * @param DateTime|string $dateOfBirth Birth date of the person. If using a string, the format must be yyyy-mm-dd
	 * @param string $phone
	 * @param string $mobilePhone
	 */
	public function __construct($id, Name $name, $email, $language, $gender, $dateOfBirth=null, $phone=null, $mobilePhone=null) {
		$this->id = $id;
		$this->name = $name;
		$this->email = $email;
		$this->language = $language;
		$this->gender = $gender;
		$this->dateOfBirth = $dateOfBirth instanceof DateTime ? $dateOfBirth->format('Y-m-d') : $dateOfBirth;
		$this->phone = $phone;
		$this->mobilePhone = $mobilePhone;
	}

	public function getDDRepr() {
		$result = array(
			'id' => $this->id,  // Required
			'name' => $this->name->getDDRepr(),
			'email' => $this->email,                         // Used regex is [_a-zA-Z0-9\-\+\.]+@[a-zA-Z0-9\-]+(\.[a-zA-Z0-9\-]+)*(\.[a-zA-Z]+)
			'language' => array('code' => $this->language),  // ISO 639 2-letter language code.
			'gender' => $this->gender,                       // One of 'M'
	'F' or 'U'
		);
		if ($this->dateOfBirth !== null) $result['dateOfBirth'] = $this->dateOfBirth; // Optional, format YYYY-MM-DD
		if ($this->phone !== null) $result['phoneNumber'] = $this->phone; // Optional
		if ($this->mobilePhone !== null) $result['mobilePhoneNumber'] = $this->mobilePhone; // Optional

		return $result;
	}
}

class Destination {
	private $name, $address;

	public function __construct(Name $name, Address $address) {
		$this->name = $name;
		$this->address = $address;
	}

	public function getDDRepr() {
		return array('name' => $this->name->getDDRepr(), 'address' => $this->address->getDDRepr());
	}
}

class Amount {
	private $amount = 0;
	private $currency;

	/**
	 * @param int $amount The amount in the minor unit of the currency (i.e. EUR will be specified in cents)
	 * @param string $currency An ISO 4217:2008 currency code.
	 *
	 * Currency codes can be found at http://en.wikipedia.org/wiki/ISO_4217#Active_codes
	 */
	public function __construct($amount, $currency) {
		$this->amount = $amount;
		$this->currency = $currency;
	}

	public function getAmount() { return $this->amount; }
	public function getCurrency() { return $this->currency; }

	public function getDDRepr() {
		return array('_' => $this->amount, 'currency' => $this->currency);
	}
}

class Rate extends Amount {
	private $rate = 0;

	/**
	 * @param int $amount The amount in the minor unit of the currency
	 * @param string $currency An ISO 4217:2008 currency code.
	 * @param float $rate A rate in percent.
	 */
	public function __construct($amount, $currency, $rate) {
		parent::__construct($amount, $currency);
		$this->rate = $rate;
	}

	public function getRate() { return $this->rate; }

	public function getDDRepr() {
		$result = parent::getDDRepr();
		$result['rate'] = $this->rate;
		return $result;
	}
}

class Quantity {
	private $quantity, $unit;

	const pieces    = 'PCS';
	const seconds   = 'SEC';
	const bytes     = 'BYT';
	const kilobytes = 'KB';

	/**
	 * @param int $quantity The amount
	 * @param string $unit One of the unit constants in this class
	 */
	public function __construct($quantity, $unit) {
		$this->quantity = $quantity;
		$this->unit = $unit;
	}

	public function getDDRepr() {
		return array(
			'_' => $this->quantity,
			'unitOfMeasure' => $this->unit
		);
	}
}

class InvoiceItem {
	private $name, $code, $quantity, $description, $netAmount, $grossAmount, $vat;
	private $image = null;

	/**
	 * @param string $name The item's name
	 * @param string $code The item's article code or similar
	 * @param \TH\ZfPayment\Quantity $quantity The amount of the item that is sold
	 * @param string $description A longer description of the item
	 * @param \TH\ZfPayment\Amount $netAmount Net amount charged
	 * @param \TH\ZfPayment\Amount $grossAmount Gross amount charged
	 * @param \TH\ZfPayment\Rate $vat VAT charged
	 */
	public function __construct($name, $code, Quantity $quantity, $description, Amount $netAmount, Amount $grossAmount, Rate $vat) {
		$this->name = $name;
		$this->code = $code;
		$this->quantity = $quantity;
		$this->description = $description;
		$this->netAmount = $netAmount;
		$this->grossAmount = $grossAmount;
		$this->vat = $vat;
	}

	public function setImage($url) {
		$this->image = $url;
	}

	public function getTotalNet() {
		return new Amount($this->netAmount->getAmount() * $this->quantity->getQuantity(), $this->netAmount->getCurrency());
	}
	public function getTotalGross() {
		return new Amount($this->grossAmount->getAmount() * $this->quantity->getQuantity(), $this->grossAmount->getCurrency());
	}
	public function getTotalVat() {
		return new Rate($this->vat->getAmount() * $this->quantity->getQuantity(), $this->vat->getCurrency(), $this->vat->getRate());
	}

	public function getDDRepr() {
		$result = array(
			'name' => $this->name,
			'code' => $this->code,     // Some code or article number
			'number' => $this->code,   // Use unknown. But it's required. Docdata example code uses the same value as the code field here.
			'quantity' => $this->quantity->getDDRepr(),
			'description' => $this->description,
			'netAmount' => $this->netAmount->getDDRepr(),      // Net amount for a single item
			'grossAmount' => $this->grossAmount->getDDRepr(),  // Gross amount for a single item
			'vat' => $this->vat->getDDRepr(),                  // Vat amount for a single item
			'totalNetAmount' => $this->getTotalNet()->getDDRepr(),       // Total net amount for the item
			'totalGrossAmount' => $this->getTotalGross()->getDDRepr(),   // Total gross amount for the item
			'totalVat' => $this->getTotalVat()->getDDRepr(),             // Total vat amount for the item
		);
		if ($this->image !== null) {
			$result['image'] = $this->image;  // Optional, url pointing to an image of the product. Docdata may specify limits. Actual limits unknown at this point.
		}

		return $result;
	}
}

class Invoice {
	private $items = array();
	private $shipTo;
	private $description;
	private $totalNet, $totalVat;

	/**
	 * @param \TH\ZfPayment\Destination $shipTo Who the item(s) will be shipped to
	 * @param type $description Extra descriptions on the order
	 * @param \TH\ZfPayment\Amount $totalNet The total net amount that is charged
	 * @param \TH\ZfPayment\Rate $totalVat The total VAT amount that is charged
	 *
	 * Note: totalNet and totalVat can be left empty and they will be automatically computed, but this only works if
	 * they all use the same currency units, and (for VAT) the same rate.
	 */
	public function __construct(Destination $shipTo, $description = null, Amount $totalNet = null, Rate $totalVat = null) {
		$this->shipTo = $shipTo;
		$this->description = $description;
		$this->totalNet = $totalNet;
		$this->totalVat = $totalVat;
	}

	public function addItem(InvoiceItem $item) {
		$this->items[]= $item;
	}

	public function getDDRepr() {
		if (count($this->items) == 0) return null;

		$items = array();
		foreach ($this->items as $item) {
			$items[]= $item->getDDRepr();
		}
		$totalNet = $this->totalNet;
		$totalVat = $this->totalVat;
		if (!$totalNet) {
			$totalNet = $this->items[0]->getNet();
			$amount = 0;
			foreach ($this->items as $item) {
				$value = $item->getTotalNet();
				if ($value->getCurrency() != $totalNet->getCurrency()) {
					throw new Exception('When automatically calculating the net total, currencies must be all the same.');
				}

				$amount+= $value->getAmount();
			}
			$totalNet = new Amount($amount, $totalNet->getCurrency());
		}
		if (!$totalVat) {
			$totalVat = $this->items[0]->getVat();
			$amount = 0;
			foreach ($this->items as $item) {
				$value = $item->getTotalVat();
				if ($value->getCurrency() != $totalVat->getCurrency()) {
					throw new Exception('When automatically calculating the vat total, currencies must be all the same.');
				}
				if ($value->getRate() != $totalVat->getRate()) {
					throw new Exception('When automatically calculating the vat total, rates must be all the same.');
				}

				$amount+= $value->getAmount();
			}
			$totalNet = new Rate($amount, $totalVat->getCurrency(), $totalVat->getRate());
		}
		$result = array(  // Optional.
			'totalNetAmount' => $totalNet->getDDRepr(),  // total net amount for order
			'totalVatAmount' => $totalVat->getDDRepr(),
			'item' => $items,     // Between 1 and  1000 items can be specified.
			'shipTo' => $this->shipTo->getDDRepr()
		);
		if ($this->description !== null) {
			$result['additionalDescription'] = $this->description; // Optional, extra description.
		}
		return $result;
	}
}

?>
