<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation as FORM;

/**
 * @ORM\Table(name="Invite")
 * @ORM\Entity
 */
class Invite
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
	 * @FORM\Exclude()
	 * @ORM\Column(name="invitekey", type="string", length=32, nullable=false)
	 */
	protected $key;

	/**
	 * @var string
	 *
	 * @FORM\Filter({"name":"StringTrim"})
	 * @FORM\Filter({"name":"StripTags"})
	 * @ORM\Column(name="email", type="string", length=255, nullable=false)
	 */
	protected $email;

	/**
	 * @var datetime
	 *
	 * @FORM\Exclude()
	 * @ORM\Column(name="creationdate", type="datetime")
	 */
	protected $creationdate;


	/**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Page")
	 * @FORM\Exclude()
	 * @var \Application\Entity\Page
	 */
	protected $page;

	/**
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * @return \DateTime
	 */
	public function getCreationDate()
	{
		return $this->creationdate;
	}

	/**
	 * @return string
	 */
	public function setEmail($email)
	{
		$this->email = $email;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @param \Application\Entity\Page $page
	 * @return Label
	 */
	public function setPage(Page $page)
	{
		$this->page = $page;
		return $this;
	}

	/**
	 * @return \Application\Entity\Page
	 */
	public function getPage()
	{
		return $this->page;
	}


	public function exchangeArray($data)
	{
		if (!empty($data['email'])) { $this->email = $data['email']; }
		if (!empty($data['page']))  { $this->page  = $data['page']; }

		return $this;
	}

	/**
	 * @return array
	 */
	public function getArrayCopy()
	{
		return array(
			'id'	=> $this->id,
			'email'	=> $this->email,
			'key'	=> $this->key,
			'pageid'=> $this->page ? $this->page->getId() : null,
			'creationdate' => $this->creationdate->format(DATE_ISO8601)
		);
	}

    public function __construct()
	{
        $this->key = \Base\Util\Generator::generateKey(32);
		$this->creationdate = new \DateTime();
    }
}