<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Notification
 *
 * @ORM\Entity
 * @ORM\Table(name="Notification")
 * @ORM\HasLifecycleCallbacks
 */
class Notification
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Page")
     *
     * @var \Application\Entity\Page
     */
    protected $page;

    /**
     * @var \TH\ZfUser\Entity\UserAccount
     * @ORM\ManyToOne(targetEntity="TH\ZfUser\Entity\UserAccount")
     */
    protected $receiver = null;

    /**
     * @var \TH\ZfUser\Entity\UserAccount
     * @ORM\ManyToOne(targetEntity="TH\ZfUser\Entity\UserAccount")
     */
    protected $sender = null;

    /**
     * @var \Datetime
     * @ORM\Column(name="readDate", type="datetime", nullable=true)
     */
    protected $readDate = null;

    /**
     * @var \Datetime
     * @ORM\Column(type="datetime")
     */
    protected $createDate;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Memory")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    protected $memory = null;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Comment")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    protected $comment = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $deleted = false;

    /**
     * @var boolean $newnotification
     * @ORM\Column(name="newnotification", type="boolean", nullable=true)
     */
    protected $newnotification = true;

    /**
     * @var string $type
     * @ORM\Column(name="event", type="string", nullable=true, length=16)
     */
    protected $event = 'shared';


    public function getId()
    {
        return $this->id;
    }

    /**
     * Set newnotification
     *
     * @param boolean $newmsg
     * @return MessageCentreInbox
     */
    public function setNewnotification($value)
    {
        $this->newnotification = $value;
        return $this;
    }

    /**
     * Get newnotification
     *
     * @return boolean
     */
    public function getNewnotification()
    {
        return $this->newnotification;
    }

    /**
     * @param \Application\Entity\Memory $memory
     * @return \Application\Entity\Notification
     */
    public function setMemory($memory)
    {
        $this->memory = $memory;
        return $this;
    }

    /**
     * @return  \Application\Entity\Page $page
     */
    public function getMemory()
    {
        return $this->memory;
    }

    /**
     * @param  \Application\Entity\Comment $comment
     * @return \Application\Entity\Notification
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return  \Application\Entity\Comment $comment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param \Application\Entity\Page $page
     * @return \Application\Entity\Notification
     */
    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return  \Application\Entity\Page $page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set receiver
     *
     * @param \TH\ZfUser\Entity\UserAccount $user
     * @return \Application\Entity\Notification
     */
    public function setReceiver(\TH\ZfUser\Entity\UserAccount $user)
    {
        $this->receiver = $user;
        return $this;
    }

    /**
     *
     * @return \TH\ZfUser\Entity\UserAccount
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * Set sender
     *
     * @param \TH\ZfUser\Entity\UserAccount $user
     * @return \Application\Entity\Notification
     */
    public function setSender(\TH\ZfUser\Entity\UserAccount $user)
    {
        $this->sender = $user;
        return $this;
    }

    /**
     *
     * @return \TH\ZfUser\Entity\UserAccount
     */
    public function getSender()
    {
        return $this->sender;
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
     * Set createDate
     *
     * @param datetime $createDate
     * @return MessageCentreInbox
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;
        return $this;
    }

    /**
     * Get createDate
     *
     * @return datetime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

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
     * @param string $event
     * @return MessageCentreInbox
     */
    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
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

    public function __construct(array $data)
    {
        $this->page = $data['page'];
        $this->receiver = $data['receiver'];
        $this->sender = $data['sender'];

        $this->memory = !empty($data['memory']) ? $data['memory'] : null;
        $this->comment = !empty($data['comment']) ? $data['comment'] : null;

        $this->event = !empty($data['event']) ? $data['event'] : null;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createDate = new \DateTime();
    }

}