<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="Comment")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Comment
{
	/**
	 * @var integer
	 * @ORM\Column(type="integer", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id;
	public function getId() { return $this->id; }

	/**
	 * @var \TH\ZfUser\Entity\UserAccount
	 * @ORM\ManyToOne(targetEntity="TH\ZfUser\Entity\UserAccount")
	 */
	protected $user;
	public function getUser()       { return $this->user; }
	public function setUser($value) { $this->user = $value; return $this; }

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime", nullable=false)
	 */
	protected $createDate;
	public function getCreateDate() { return $this->createDate; }

	/**
	 * @var Memory
   * @ORM\ManyToOne(targetEntity="Memory", inversedBy="comments")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="memory_id", referencedColumnName="id", nullable=false)
	 * })
	 */
	protected $memory;
	public function getMemory()       { return $this->memory; }
	public function setMemory($value) { $this->memory = $value; return $this; }

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $text;
	public function getText()       { return $this->text; }
	public function setText($value) { $this->text = $value; return $this; }

    public function getArrayCopy()
    {
         return array(
            'id' => $this->id,
            'createDate' => $this->createDate,
            'text' => $this->text,
            'user' => $this->user ? $this->user->getProfile()->getArrayCopy() : null // we want the profile for json; we don't want to expose everyones email
        );
	}

     /**
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    private $deletedAt;

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
	 * @ORM\PrePersist
	 */
	public function prePersist() {
		$this->createDate = new \DateTime();
	}

}
