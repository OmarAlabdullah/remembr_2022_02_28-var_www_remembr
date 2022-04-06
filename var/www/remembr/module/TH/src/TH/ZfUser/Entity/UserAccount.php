<?php
namespace TH\ZfUser\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserAccount
 *
 * @ORM\Entity
 * @ORM\Table(name="userAccount")
 * @property int $id
 * @property string $password
 * @property string $email
 * @property string $username
 * @property datetime $created
 * @property text $hybridauth_session
 * @property string(40) $confirmKey
 * @property datetime $confirmRequest
 * @property integer $logins
 * @property datetime $lastLogin
 * @property boolean $verified
 * @property boolean $deleted
 */
class UserAccount
{

    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="TH\ZfUser\Entity\UserProfile", mappedBy="account", cascade={"persist"})
     */
    protected $profile;

    /**
     * @ORM\Column(type="string")
     */
    protected $email;

    /**
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @ORM\Column(type="string",  nullable=true)
     */
    protected $username;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $hybridauth_session;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var integer
     *
     * @ORM\Column(name="logins", type="integer", nullable=false)
     */
    protected $logins = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastLogin", type="datetime", nullable=true)
     */
    protected $lastLogin;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="confirmRequest", type="datetime", nullable=true)
     */
    protected $confirmRequest;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="acceptedTermsDate", type="datetime", nullable=true)
     */
    protected $acceptedTermsDate;

    /**
     * Set setAcceptedTermsDate
     *
     * @param datetime $date
     * @return MessageCentreMessage
     */
    public function setAcceptedTermsDate($date)
    {
        $this->acceptedTermsDate = $date;
        return $this;
    }

    /**
     * Get getAcceptedTermsDate
     *
     * @return datetime
     */
    public function getAcceptedTermsDate()
    {
        return $this->acceptedTermsDate;
    }

     /**
     * @var boolean
     *
     * @ORM\Column(name="deleted", type="boolean")
     */
    protected $deleted = false;


     /**
     * Get deleted
     *
     * @return boolean
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set deleted
     *
     * @param type boolean $deleted
     * @return User
     */
    public function setDeleted($value)
    {
        $this->deleted = $value;

         return $this;
    }

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deletedDate", type="datetime", nullable=true)
     */
    protected $deletedDate;

    /**
     * Get deleted
     *
     * @return boolean
     */
    public function getDeletedDate()
    {
        return $this->deletedDate;
    }

    /**
     * Set deleted
     *
     * @param type boolean $deleted
     * @return User
     */
    public function setDeletedDate($value)
    {
        $this->deletedDate = $value;

         return $this;
    }

    /**
     *
     * @ORM\Column(name="restoreKey", type="string", length=40, nullable=true)
     */
    protected $restoreKey;

    public function setRestoreKey($key)
    {
        $this->restoreKey = $key;
    }

    public function getRestoreKey()
    {
        return $this->restoreKey;
    }


    /**
     * Set logins
     *
     * @param integer $logins
     * @return User
     */
    public function setLogins($logins)
    {
        $this->logins = $logins;

        return $this;
    }

    /**
     * Get logins
     *
     * @return integer
     */
    public function getLogins()
    {
        return $this->logins;
    }

    /**
     * Set lastLogin
     *
     * @param \DateTime $lastLogin
     * @return User
     */
    public function setLastLogin($lastLogin)
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    /**
     * Get lastLogin
     *
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     *
     * @ORM\Column(name="confirmKey", type="string", length=40, nullable=true)
     */
    protected $confirmKey;

    /**
     * @var boolean $verified
     *
     * @ORM\Column(name="verified", type="boolean", nullable=false)
     */
    private $verified = false;

    /**
     * Magic getter to expose protected properties.
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property)
    {
        return $this->$property;
    }

    /**
     * Magic setter to save protected properties.
     *
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value)
    {
        $this->$property = $value;
    }

    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function __construct($data = array())
    {
        $this->accesses = new \Doctrine\Common\Collections\ArrayCollection();

        $this->email = $data['email'];
        //$this->username = $data['username'];
        $this->password = $data['password'];
        $this->verified = isset($data['verified']) ? $data['verified'] : true;
        $this->created = new \DateTime(date('Y-m-d H:i:s'));
        $this->acceptedTermsDate = new \DateTime(date('Y-m-d H:i:s'));
    }

    public function exchangeArray(array $values)
    {
//        if (isset($values['username']))
//            $this->username = $values['username'];

        if (isset($values['email']))
            $this->email = $values['email'];

        if (isset($values['password']))
            $this->password = $values['password'];

        $this->profile->exchangeArray($values);
    }

    /**
     * @ORM\OneToMany(targetEntity="TH\ZfUser\Entity\UserAccess", mappedBy="account")
     */
    protected $accesses;

    public function addAccess(\TH\ZfUser\Entity\UserAccess $access)
    {
        $this->accesses[] = $access;
    }

    public function getAccesses()
    {
        return $this->accesses;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setPassword($pwd)
    {
        $this->password = $pwd;
    }

    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return array_intersect_key(get_object_vars($this), array_flip(array(
					'id', 'email', 'password', 'username', 'hybridauth_session', 'created', 'logins', 'lastLogin',
					'confirmRequest', 'confirmKey', 'verified'
				)));
    }

    public function storeHybridauthSession($sessiondata)
    {
        $this->hybridauth_session = $sessiondata;
    }

    public function getHybridauthSession()
    {
        return $this->hybridauth_session;
    }

    public function setKey($key)
    {
        $this->confirmKey = $key;
    }

    public function getKey()
    {
        return $this->confirmKey;
    }

    public function setConfirmRequest($date)
    {
        $this->confirmRequest = $date;
    }

    public function getConfirmRequest()
    {
        return $this->confirmRequest;
    }

    public function getUserName()
    {
        return $this->username;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($value)
    {
        $this->email = $value;
    }

    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Store profile data from an array.
     *
     * @param array $data
     */
    public function storeProfile($profile)
    {
        $this->profile = $profile;
        $this->profile->setAccount($this);

        return $this;
    }

    /**
     * Set verified
     *
     * @param boolean $verified
     * @return Admin
     */
    public function setVerified($verified)
    {
        $this->verified = $verified;
        return $this;
    }

    /**
     * Get verified
     *
     * @return boolean
     */
    public function getVerified()
    {
        return $this->verified;
    }
		
	public function isConfirmed() {
		return $this->getKey() == null;
	}

	public function isConfirmationExpired($days_valid = 7) {
		if ($this->isConfirmed())
			return false;
		return $this->getConfirmRequest()
			->diff(
				new \DateTime(date('Y-m-d H:i:s'))
			)->format("%a") >= $days_valid;
	}
	
	public function softDelete() {
		$this->setDeleted(true);
		$this->setDeletedDate(new \DateTime(date('Y-m-d H:i:s')));
		$this->setDeletedDate(null);
	}
	
	public function restoreSoftDelete() {
		$this->setDeleted(false);
		$this->setDeletedDate(null);
	}
}
