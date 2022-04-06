<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation as FORM;

/**
 * UserProfile
 *
 * @ORM\Entity
 * @ORM\Table(name="userProfile")
 * @ORM\HasLifecycleCallbacks
 */
class UserProfile
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="TH\ZfUser\Entity\UserAccount", inversedBy="profile")
     */
    protected $account;

    /**
     * @var string
     * @ORM\Column(type="string", length=30, nullable=false)
     */
    protected $firstname = '';

    /**
     * @var string
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    protected $lastname = '';

    /**
	 * @var \Datetime
     * @ORM\Column(type="datetime")
     */
    protected $created;

     /**
     *
     * @var string
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $residence = '';

    /**
     *
     * @var string
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $country = '';

    /**
     *
     * @var string
     * @ORM\Column(type="string", length=6, nullable=true)
     */
    protected $gender = '';

    /**
     *
     * @var string
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    protected $language = '';

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $dateofbirth;

    /**
	 * @var string
	 *
	 * @ORM\Column(name="photoid", type="string", length=255, nullable=true)
	 */
	protected $photoid;



    public function __construct($data = null)
    {
		if (! empty($data))
		{
			$this->exchangeArray($data);
		}
    }

    public function setAccount(\TH\ZfUser\Entity\UserAccount $account)
    {
		$this->account = $account;
		return $this;
    }

    public function getAccount()
    {
		return $this->account;
    }

    public function getFirstName()
    {
        return $this->firstname;
    }

    public function setFirstName($value)
    {
        $this->firstname = $value;
        return $this;
    }

    public function getLastName()
    {
        return $this->lastname;
    }

    public function setLastName($value)
    {
        $this->lastname = $value;
        return $this;
    }

    public function getName()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function setName($value)
    {
		/* @TODO this won't work in every case, why is it needed? */
        $matches = array();
        preg_match('/^\s*(\S*)(?:\s+(.*))?\s*$/', $value, $matches);
        $this->firstname = $matches[1];
        $this->lastname = isset($matches[2]) ? $matches[2] : '';
        return $this;
    }

	public function getId()
	{
		return $this->id;
	}

    // residence
    public function getResidence()
    {
        return $this->residence;
    }

    public function setResidence($value)
    {
        $this->residence = $value;
    }

    // country
    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($value)
    {
        $this->country = $value;
    }

    // gender
    public function getGender()
    {
        return $this->gender;
    }

    public function setGender($value)
    {
        $this->gender = $value;
    }

    // language
    public function getLanguage()
    {
        return $this->language;
    }

    public function setLanguage($value)
    {
        $this->language = $value;
    }

    // dateofbirth
    public function getDateofbirth()
    {
        return $this->dateofbirth;
    }

    public function setDateofbirth($value)
    {
        $this->dateofbirth =  $value;
    }

    /**
	 * @param string $photoid
	 * @return string
	 */
	public function setPhotoid($photoid)
	{
		$this->photoid = $photoid;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPhotoid()
	{
		return $this->photoid;
	}


	/**
     * @ORM\PrePersist
	 */
	public function prePersist()
	{
		$this->created = new \DateTime();
	}

	public function exchangeArray(array $values)
	{
		if (!empty($values['name']))		{	$this->setName($values['name']);	}
		if (!empty($values['firstname']))	{	$this->firstname	= $values['firstname'];	}
		if (!empty($values['lastname']))	{	$this->lastname		= $values['lastname'];	}
		if (!empty($values['title']))		{	$this->title		= $values['title'];	}
		if (!empty($values['created']))		{	$this->created		= new \DateTime($values['created']) ;	}
        if (!empty($values['residence']))	{	$this->residence	= $values['residence'];	}
        if (!empty($values['country']))		{	$this->country		= $values['country'];	}
        if (!empty($values['gender']))		{	$this->gender		= $values['gender'];	}
        if (!empty($values['language']))	{	$this->language		= $values['language'];	}
        if (!empty($values['dateofbirth']))	{	$this->dateofbirth	= new \DateTime($values['dateofbirth']) ;	}
        if (!empty($values['photoid']))		{	$this->photoid		= $values['photoid'];	}

		return $this;
	}
    /**
     * Convert the object to an array.
     *
     * @return array
     */
	public function getArrayCopy()
	{
		return array(
			'id'		 => $this->id,
			'firstname'	 => $this->firstname,
			'lastname'	 => $this->lastname,
			'created'	 => $this->created instanceof \DateTime ? $this->created->format('Y-m-d H:i:s') : '',
            'residence'	 => $this->residence,
            'country'	 => $this->country,
            'gender'	 => $this->gender,
            'language'	 => $this->language,
            'dateofbirth'=> $this->dateofbirth instanceof \DateTime ? $this->dateofbirth->format('Y-m-d') : '',
            'photoid'    => $this->photoid ?: '/images/user-icon-large.png'
		);
	}

}