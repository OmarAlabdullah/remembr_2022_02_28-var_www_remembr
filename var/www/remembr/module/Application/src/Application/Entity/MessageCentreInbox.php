<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use TH\ZfUser\Entity\UserAccount;

/**
 * Message
 *
 * @ORM\Entity
 * @ORM\Table(name="messageCentreInbox")
 */
class MessageCentreInbox
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
    private $to;

    /**
     * @var \Datetime
     * @ORM\Column(name="readDate", type="datetime", nullable=true)
     */
    private $readDate = null;

    /**
     * @var Application\Entity\MessageCentreMessage
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

    /**
     *  Set to
     * @param \TH\ZfUser\Entity\UserAccount $user
     * @return \Application\Entity\MessageCentreInbox
     */
    public function setTo(\TH\ZfUser\Entity\UserAccount $user)
    {
        $this->to = $user;
        return $this;
    }

    /**
     * Get to_id
     *
     * @return integer
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Set readDate
     *
     * @param datetime $readDate
     * @return MessageCentreInbox
     */
    public function setReadDate($readDate)
    {
        $this->readDate = $readDate;
        return $this;
    }

    /**
     * Get readDate
     *
     * @return datetime
     */
    public function getReadDate()
    {
        return $this->readDate;
    }

    /**
     * Set message
     *
     * @param Entity\MessageCentreMessage $message
     * @return MessageCentreInbox
     */
    public function setMessage(\Application\Entity\MessageCentreMessage $message = null)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Get message
     *
     * @return Entity\MessageCentreMessage
     */
    public function getMessage()
    {
        return $this->message;
    }

    public function __construct()
    {
        $this->message = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * @var boolean $deleted
     * @ORM\Column(name="deleted", type="boolean", nullable=true)
     */
    private $deleted = false;

    /**
     * Set deleted
     *
     * @param boolean $deleted
     * @return MessageCentreInbox
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

    /**
     * @var boolean $newmsg
     * @ORM\Column(name="newmsg", type="boolean", nullable=true)
     */
    private $newmsg = true;

    /**
     * Set newmsg
     *
     * @param boolean $newmsg
     * @return MessageCentreInbox
     */
    public function setNewmsg($value)
    {
        $this->newmsg = $value;
        return $this;
    }

    /**
     * Get newmsg
     *
     * @return boolean
     */
    public function getNewmsg()
    {
        return $this->newmsg;
    }


    /**
     * @var boolean $answered
     *
     * @ORM\Column(name="answered", type="boolean", nullable=true)
     */
    private $answered = false;

    /**
     * Set answered
     *
     * @param boolean $answered
     * @return MessageCentreInbox
     */
    public function setAnswered($answered)
    {
        $this->answered = $answered;
        return $this;
    }

    /**
     * Get answered
     *
     * @return boolean
     */
    public function getAnswered()
    {
        return $this->answered;
    }

      /**
     * @var boolean $reminded
     *
     * @ORM\Column(name="reminded", type="boolean", nullable=true)
     */
    private $reminded = false;


    /**
     * Set reminded
     *
     * @param boolean $reminded
     * @return MessageCentreInbox
     */
    public function setReminded($reminded)
    {
        $this->reminded = $reminded;
        return $this;
    }

    /**
     * Get reminded
     *
     * @return boolean
     */
    public function getReminded()
    {
        return $this->reminded;
    }

}