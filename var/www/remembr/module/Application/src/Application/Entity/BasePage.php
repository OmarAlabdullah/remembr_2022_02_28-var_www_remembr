<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation as FORM;
use Gedmo\Mapping\Annotation as Gedmo;

class BasePage
{
    const DRAFT = 'draft'; // draft pages are a 'late feature' and hence are
                           // stored in a different table using the DraftPage
                           // to store them in a different db table and
                           // prevent mixups in existing queries.
    const UNPUBLISHED = 'unpublished';
    const DEACTIVATED = 'deactivated';
    const TOBEDELETED = 'tobedeleted';
    const PUBLISHED = 'published';
    const DELETED = 'deleted';
    const NIP = 'nip';       // not important people
    const VIP = 'vip';       // very important people
    const ANIMAL = 'animal';    // furry stuff mostly

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @FORM\Exclude()
     */

    protected $id;

    /**
     * @var string
     *
     * @FORM\Filter({"name":"StringTrim"})
     * @FORM\Filter({"name":"StripTags"})
     * @ORM\Column(name="firstname", type="string", length=255, nullable=false)
     */
    protected $firstname;

    /**
     * @var string
     *
     * @FORM\Filter({"name":"StringTrim"})
     * @FORM\Filter({"name":"StripTags"})
     * @ORM\Column(name="lastname", type="string", length=255, nullable=true)
     */
    protected $lastname;

    /**
     * @var string
     *
     * @FORM\AllowEmpty
     * @FORM\Filter({"name":"StringTrim"})
     * @FORM\Filter({"name":"StripTags"})
     * @ORM\Column(name="url", type="string", length=255, nullable=false, unique=true)
     */
    protected $url;

    /**
     * @var string
     *
     * @FORM\AllowEmpty
     * @FORM\Filter({"name":"StringTrim"})
     * @FORM\Filter({"name":"StripTags"})
     * @ORM\Column(name="introtext", type="text")
     */
    protected $introtext;


    /**
     * @var \DateTime
     *
     * @FORM\Filter({"name":"StringTrim"})
     * @FORM\Validator({"name":"Date"})
     * @ORM\Column(name="dateofbirth", type="datetime", nullable=true)
     */
    protected $dateofbirth;

    /**
     * @var \DateTime
     *
     * @FORM\AllowEmpty
     * @FORM\Filter({"name":"StringTrim"})
     * @FORM\Validator({"name":"Date"})
     * @ORM\Column(name="dateofdeath", type="datetime", nullable=true)
     */
    protected $dateofdeath;

    /**
     * @var \DateTime
     *
     * @FORM\AllowEmpty
     * @FORM\Filter({"name":"StringTrim"})
     * @FORM\Validator({"name":"Date"})
     * @ORM\Column(name="creationdate", type="datetime", nullable=true)
     */
    protected $creationdate;

    /**
     * @var \DateTime
     *
     * @FORM\AllowEmpty
     * @FORM\Filter({"name":"StringTrim"})
     * @FORM\Validator({"name":"Date"})
     * @ORM\Column(name="publishdate", type="datetime", nullable=true)
     */
    protected $publishdate;

    /**
     * @var boolean
     *
     * @FORM\AllowEmpty
     * @FORM\Filter({"name":"StringTrim"})
     * //@@FORM\Validator({"name":"Callback", "options":{"callback":"is_bool"}})
     * @ORM\Column(name="uselabels", type="boolean", nullable=true)
     */
    protected $uselabels = false;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=16, nullable=false))
     */
    protected $status = self::UNPUBLISHED;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=16, nullable=true))
     */
    protected $type = NULL;

    /**
     * @var boolean
     *
     * @ORM\Column(name="rotating", type="boolean", nullable=true)
     */
    protected $rotating = false;

    /**
     * @var boolean
     *
     * @FORM\AllowEmpty
     * @FORM\Filter({"name":"StringTrim"})
     * //@@FORM\Validator({"name":"Callback", "options":{"callback":"is_bool"}})
     * @ORM\Column(name="private", type="boolean", nullable=true)
     */
    protected $private = false;

     /**
     * @var string
     *
     * @FORM\AllowEmpty
     * @FORM\Filter({"name":"StringTrim"})
     * @FORM\Filter({"name":"StripTags"})
     * @ORM\Column(name="gender", type="string", length=6, nullable=true)
     */
    protected $gender = '';

     /**
     * @var string
     *
     * @FORM\AllowEmpty
     * @FORM\Filter({"name":"StringTrim"})
     * @FORM\Filter({"name":"StripTags"})
     * @ORM\Column(name="country", type="string", length=100, nullable=true)
     */
    protected $country = '';

    /**
     * @var string
     *
     * @FORM\AllowEmpty
     * @FORM\Filter({"name":"StringTrim"})
     * @FORM\Filter({"name":"StripTags"})
     * @ORM\Column(name="residence", type="string", length=100, nullable=true)
     */
    protected $residence = '';

    /**
     * @var \TH\ZfUser\Entity\UserAccount
     * @FORM\Exclude()
     * @ORM\ManyToOne(targetEntity="TH\ZfUser\Entity\UserAccount")
     */
    protected $user;

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($value)
    {
        $this->user = $value;
        return $this;
    }

    // @TODO add  admins, language, viewcount, theme, blocked-users,

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set first name
     *
     * @param string $email
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set last name
     *
     * @param string $email
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return string
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set introtext
     *
     * @param string $introtext
     * @return string
     */
    public function setIntrotext($introtext)
    {
        $this->introtext = $introtext;

        return $this;
    }

    /**
     * Get introtext
     *
     * @return string
     */
    public function getIntrotext()
    {
        return $this->introtext;
    }

    /**
     * Set date of birth
     *
     * @param \DateTime $creationdate
     * @return \DateTime
     */
    public function setDateOfBirth($dateofbirth)
    {
        $this->dateofbirth = $dateofbirth;

        return $this;
    }

    /**
     * Get creation date
     *
     * @return \DateTime
     */
    public function getDateOfBirth()
    {
        return $this->dateofbirth;
    }

    /**
     * Set date of death
     *
     * @param \DateTime $publishdate
     * @return \DateTime
     */
    public function setDateOfDeath($dateofdeath)
    {
        $this->dateofdeath = $dateofdeath;

        return $this;
    }

    /**
     * Get publish date
     *
     * @return \DateTime
     */
    public function getDateOfDeath()
    {
        return $this->dateofdeath;
    }

    /**
     * Set creation date
     *
     * @param \DateTime $creationdate
     * @return \DateTime
     */
    public function setCreationDate($creationdate)
    {
        $this->creationdate = $creationdate;

        return $this;
    }

    /**
     * Get creation date
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationdate;
    }

    /**
     * Set publish date
     *
     * @param \DateTime $publishdate
     * @return \DateTime
     */
    public function setPublishDate($publishdate)
    {
        $this->publishdate = $publishdate;

        return $this;
    }

    /**
     * Get publish date
     *
     * @return \DateTime
     */
    public function getPublishDate()
    {
        return $this->publishdate;
    }

    public function getUseLabels()
    {
        return $this->uselabels;
    }

    public function setUseLabels($value)
    {
        $this->uselabels = $value;
        return $this;
    }

    public function getPrivate()
    {
        return $this->private;
    }

    public function setPrivate($value)
    {
        $this->private = $value;
        return $this;
    }

    /**
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    protected $deletedAt;

    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    /**
     * @ORM\ManyToMany(targetEntity="Application\Entity\Label", inversedBy="memories")
     * @FORM\Exclude()
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $labels;

    /**
     * @ORM\OneToMany(targetEntity="Application\Entity\Memory", mappedBy="page", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     * @FORM\Exclude()
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $memories;

    public function __construct()
    {
        $this->labels = new \Doctrine\Common\Collections\ArrayCollection();
        $this->memories = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string
     * @return \Application\Entity\Page
     */
    public function setStatus($value)
    {
        $this->status = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string
     * @return \Application\Entity\Page
     */
    public function setType($value)
    {
        $this->type = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getRotating()
    {
        return $this->rotating;
    }

    /**
     * @param string
     * @return \Application\Entity\Page
     */
    public function setRotating($value)
    {
        $this->rotating = $value;
        return $this;
    }

    // gender
    public function getGender()
    {
        return $this->gender;
    }

     /**
     * @param string
     * @return \Application\Entity\Page
     */
    public function setGender($value)
    {
        $this->gender = $value;

        return $this;
    }

    // country
    public function getCountry()
    {
        return $this->country;
    }

     /**
     * @param string
     * @return \Application\Entity\Page
     */
    public function setCountry($value)
    {
        $this->country = $value;

        return $this;
    }

    // residence
    public function getResidence()
    {
        return $this->residence;
    }

     /**
     * @param string
     * @return \Application\Entity\Page
     */
    public function setResidence($value)
    {
        $this->residence = $value;

        return $this;
    }

    public function prePersist()
    {
        if (empty($this->creationdate))
        {
            $this->creationdate = new \DateTime();
        }
    }
}
