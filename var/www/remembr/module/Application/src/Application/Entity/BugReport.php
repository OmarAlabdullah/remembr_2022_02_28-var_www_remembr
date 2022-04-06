<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="BugReport")
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class BugReport
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
	 * @var Array
	 *
	 * @ORM\Column(name="data", type="json_array", nullable=true)
	 */
	protected $data;

	/**
	 * @var Array
	 *
	 * @ORM\Column(name="files", type="json_array", nullable=true)
	 */
	protected $files;

	/**
	 * @var datetime
	 *
	 * @ORM\Column(name="creationdate", type="datetime")
	 */
	protected $creationdate;

	/**
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return \DateTime
	 */
	public function getCreationDate()
	{
		return $this->creationdate;
	}

	/**
	 * @param Array $data
	 * @return \Application\Entity\BugReport
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

	/**
	 * @param Array $files
	 * @return \Application\Entity\BugReport
	 */
	public function setFiles($files)
	{
		$this->files = $files;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getFiles()
	{
		return $this->files;
	}

	/**
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    protected $deletedAt;

    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

	/**
	 * @return \Application\Entity\BugReport
	 */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
		return $this;
    }

    public function __construct($data=array(), $files=array())
	{
		$this->creationdate = new \DateTime();
		$this->setData($data);
		$this->setFiles($files);
    }
}