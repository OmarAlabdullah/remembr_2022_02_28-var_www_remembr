<?php

namespace TH\ZfUser\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserProfile
 *
 * @ORM\Entity
 * @ORM\Table(name="userProfile")
 * @property int $id
 * @property string $firstname
 * @property string $lastname
 * @property datetime $created
 */
class UserProfile
{

    protected $inputFilter;

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

    public function __construct($data)
    {
        $this->firstname = $data['firstname'];
        $this->lastname = !empty($data['lastname']) ? $data['lastname'] : '';   // No lastnames for Twitter, thanks...
        $this->title = !empty($data['title']) ? $data['title'] : '';
        $this->created = new \DateTime(date('Y-m-d H:i:s'));
    }

    public function setAccount(UserAccount $account)
    {
        if ($account === null || $account instanceof UserAccount)
        {
            $this->account = $account;
        }
        else
        {
            throw new InvalidArgumentException('$account must be instance of Entity\UserAccount or null!');
        }
    }

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
     *
     * @var string
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $title = '';

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
        $matches = array();
        preg_match('/^\s*(\S*)(?:\s+(.*))?\s*$/', $value, $matches);
        $this->firstname = $matches[1];
        $this->lastname = isset($matches[2]) ? $matches[2] : 'Lastname';
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($value)
    {
        $this->title = $value;
        return $this;
    }

    public function exchangeArray(array $values)
    {
        if (isset($values['name']))
            $this->setName($values['name']);

        if (isset($values['title']))
            $this->title = $values['title'];
    }

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

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

    public function getId()
    {
        return $this->id;
    }

    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return array_intersect_key(get_object_vars($this), array_flip(
					array('id', 'firstname', 'lastname', 'title', 'created')
				));
    }

}