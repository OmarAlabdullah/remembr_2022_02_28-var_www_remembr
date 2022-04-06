<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ConfirmAction",indexes={@ORM\Index(name="key_idx", columns={"confirmkey"})})
 * @ORM\Entity
 */
class ConfirmAction
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
	 * @ORM\Column(name="confirmkey", type="string", length=32, nullable=false)
	 */
	protected $key;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="action", type="string", length=32, nullable=true)
	 */
	protected $action;

	/**
	 * @var Array
	 *
	 * @ORM\Column(name="data", type="json_array", nullable=true)
	 */
	protected $data;

	/**
	 * @var datetime
	 *
	 * @ORM\Column(name="creationdate", type="datetime")
	 */
	protected $creationdate;
	
	/**
	 * @var datetime
	 *
	 * @ORM\Column(name="expirationdate", type="datetime")
	 */
	protected $expirationdate;

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
	 * @return \DateTime
	 */
	public function getExpirationDate()
	{
		return $this->expirationdate;
	}


	/**
	 * @param \DateTime $expirationdate
	 * @return ConfirmAction
	 */
	public function setExpirationDate($expirationdate)
	{
		$this->expirationdate = $expirationdate;
		return $this;
	}

	/**
	 * @param int $expire
	 * @return \DateTime
	 */
	public function setExpire($expire=7)
	{
		$this->expirationdate = new \DateTime("{$expire} days");
		return $this->expirationdate;
	}

	/**
	 * @param string $action name of action
	 * @return ConfirmAction
	 */
	public function setAction($action)
	{
		$this->action = $action;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * @param Array $data general data
	 * @return \Application\Entity\ConfirmAction
	 */
	public function setData($data)
	{
		$this->data = $data;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getData()
	{
		return $this->data;
	}

    public function __construct($action='', $data=array(), $expire=7)
	{
        $this->key = \Base\Util\Generator::generateKey(32);
		$this->creationdate = new \DateTime();
		$this->setAction($action);
		$this->setData($data);
		$this->setExpire($expire);
    }
}