<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation as FORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * User
 *
 * @ORM\Entity
 * @ORM\Table(name="Content")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"memory" = "Memory", "photo" = "Photo", "video" = "Video", "condolence"="Condolence"})
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Memory
{
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id", type="integer", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id;

	/**
	 * @var string
	 *
	 * @FORM\AllowEmpty
	 * @FORM\Filter({"name":"StringTrim"})
	 * @FORM\Filter({"name":"StripTags"})
	 * @ORM\Column(name="text", type="text", nullable=true)
	 */
	protected $text;

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
	 * @var \Doctrine\Common\Collections\Collection
	 *
	 * @ORM\OneToMany(targetEntity="Comment", mappedBy="memory", orphanRemoval=true, cascade={"persist","remove","detach","merge","refresh"})
	 * @ORM\OrderBy({"id" = "DESC"})
	 */
	protected $comments;

	/**
	 * @var string
	 *
	 * @FORM\AllowEmpty
	 * @FORM\Filter({"name":"StringTrim"})
	 * @FORM\Filter({"name":"StripTags"})
	 * @ORM\Column(name="username", type="string", length=32, nullable=true)
	 */
	protected $username;

	/**
	 * @var string
	 *
	 * @FORM\AllowEmpty
	 * @FORM\Filter({"name":"StringTrim"})
	 * @FORM\Filter({"name":"StripTags"})
	 * @ORM\Column(name="modificationKey", type="string", length=40, nullable=true)
	 */
    protected $modificationKey;
    
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
	 * @var \TH\ZfUser\Entity\UserAccount
	 * @FORM\Exclude()
	 * @ORM\ManyToOne(targetEntity="TH\ZfUser\Entity\UserAccount")
	 */
	protected $user;
	public function getUser()       { return $this->user; }
	public function setUser($value)
	{
		if ($value instanceof \TH\ZfUser\Entity\UserAccount)
		{
			$prof = $value->getProfile();
			$this->setUserName($prof->getFirstName() . ' ' . $prof->getLastName());
		}
		$this->user = $value;
		return $this;
	}

	/**
	 * @param string $text
	 * @return string
	 */
	public function setUserName($username)
	{
		$this->username = $username;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getUserName()
	{
		return $this->username;
	}

	/**
	 * @param string $text
	 * @return string
	 */
	public function setModificationKey($mkey)
	{
		$this->modificationKey = $mkey;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getModificationKey()
	{
		return $this->modificationKey;
	}
	// @TODO add creator, privacy-settings?, language?, viewcount?,


	/**
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param string $text
	 * @return string
	 */
	public function setText($text)
	{
		$this->text = $text;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getText()
	{
		return $this->text;
	}

	/**
	 * @param \DateTime $creationdate
	 * @return \DateTime
	 */
	public function setCreationDate($creationdate)
	{
		$this->creationdate = $creationdate;

		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getCreationDate()
	{
		return $this->creationdate;
	}

	public function prePersist()
	{
		$this->creationdate = new \DateTime;
	}

	/**
     * @ORM\ManyToMany(targetEntity="Application\Entity\Label", inversedBy="memories")
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $labels;


	/**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Page", inversedBy="memories", fetch="EAGER")
	 *
	 * @var \Application\Entity\Page
	 */
	protected $page;

    public function __construct()
	{
        $this->labels = new \Doctrine\Common\Collections\ArrayCollection();
    }

	/**
	 * @param \Application\Entity\Label $label
	 * @return Memory
	 */
	public function addLabel(Label $label)
	{
		$this->labels[] = $label;
		return $this;
	}

	/**
	 * @param \Application\Entity\Label $label
	 * @return Memory
	 */
	public function rmLabel(Label $label)
	{
		$this->labels->removeElement($label);
		return $this;
	}

	/**
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
	public function getLabels()
	{
		return $this->labels;
	}

	/**
	 * @param \Application\Entity\Page $page
	 * @return Memory
	 */
	public function setPage($page)
	{
		 $this->page = $page;
		 return $this;
	}

	/**
	 *@return  \Application\Entity\Page $page
	 */
	public function getPage()
	{
		 return $this->page;
	}

	/**
	 *@return  string
	 */
	public function getType()
	{
		return 'memory';
	}


	/**
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
	public function getComments()
	{
		return $this->comments;
	}



	public function exchangeArray($data)
	{
		$this->text	= isset($data['text']) ? $data['text'] : '';
		$this->creationdate	=	new \DateTime(empty($data['creationdate']) ? 'now' : $data['creationdate']) ;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getArrayCopy($depth=0)
	{
		$arr = array(
			'id'			=> $this->id,
			'text'			=> $this->text,
			'type'			=> 'memory',
			'creationdate'	=> $this->creationdate instanceof \DateTime ? $this->creationdate->format('Y-m-d H:i:s') : '',
			'user'			=> $this->user ? $this->user->getProfile()->getArrayCopy() : null, // we want the profile for json; we don't want to expose everyones email
			'username'		=> $this->username,
            'numbercomments'=> count($this->comments)
		);

		if ($depth > 0)
		{
			$arr['labels'] = array();
			foreach($this->labels as $label)
			{
				$arr['labels'][] = $label->getArrayCopy();
			}
		}

		return $arr;
	}

}