<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use TH\ZfUser\Entity\UserAccount;

/**
 * Message
 *
 * @ORM\Entity
 * @ORM\Table(name="messageCentreOutbox")
 */
class MessageCentreOutbox
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \TH\ZfUser\Entity\UserAccount
     * @ORM\ManyToOne(targetEntity="TH\ZfUser\Entity\UserAccount")
     */
    private $from;

   /**
     * @var Entity\MessageCentreMessage
     * @ORM\ManyToOne(targetEntity="Application\Entity\MessageCentreMessage")
     */
    private $message;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function setFrom(\TH\ZfUser\Entity\UserAccount $user)
    {
        $this->from = $user;
        return $this;
    }

    /**
     * Set message
     *
     * @param Application\Entity\MessageCentreMessage $message
     * @return MessageCentreOutbox
     */
    public function setMessage(\Application\Entity\MessageCentreMessage $message = null)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Get message
     *
     * @return Application\Entity\MessageCentreMessage
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $deleted = false;

    /**
     * Set deleted
     *
     * @param boolean $deleted
     * @return MessageCentreOutbox
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
        return $this;
    }

    /**
     * Get deleted
     *
     * @return boolean
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    public function __construct()
    {
        $this->message = new \Doctrine\Common\Collections\ArrayCollection();
    }


}