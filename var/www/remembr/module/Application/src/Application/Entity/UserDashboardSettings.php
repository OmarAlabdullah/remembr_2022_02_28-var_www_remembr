<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation as FORM;

/**
 * UserDashboardSettings
 *
 * @ORM\Entity
 * @ORM\Table(name="userDashboardSettings")
 * @ORM\HasLifecycleCallbacks
 */
class UserDashboardSettings
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

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

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $receivePageMessages = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $receiveCommentMessages = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $receivePrivateMessages = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $receiveUpdates = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $receiveTips = false;

    /**
     *
     * @var string
     * @ORM\Column(type="string", length=6, nullable=true)
     *
     * direct, daily, weekly
     */
    protected $mailFrequency = '';

    public function __construct($data = null)
    {
        if (!empty($data))
        {
            $this->exchangeArray($data);
        }
    }

    public function exchangeArray(array $data)
	{
        $this->receivePageMessages = $data['receivePageMessages'];
        $this->receiveCommentMessages = $data['receiveCommentMessages'];
        $this->receivePrivateMessages = $data['receivePrivateMessages'];
        $this->receiveUpdates = $data['receiveUpdates'];
        $this->receiveTips = $data['receiveTips'];
        $this->mailFrequency = $data['mailFrequency'];

    }

    /**
     * Convert the object to an array.
     *
     * @return array
     */
	public function getArrayCopy()
	{
		return array(
			'receivePageMessages'		=> $this->receivePageMessages,
			'receiveCommentMessages'	=> $this->receiveCommentMessages,
			'receivePrivateMessages'	=> $this->receivePrivateMessages,
            'receiveUpdates'            => $this->receiveUpdates,
            'receiveTips'               => $this->receiveTips,
            'mailFrequency'             => $this->mailFrequency
		);
	}

    public function setReceivePageMessages($value)
    {
        $this->receivePageMessages = $value;
    }

    public function getReceivePageMessages()
    {
        return $this->receivePageMessages;
    }

    public function setReceiveCommentMessages($value)
    {
        $this->receiveCommentMessages = $value;
    }

    public function getReceiveCommentMessages()
    {
        return $this->receiveCommentMessages;
    }

    public function setReceivePrivateMessages($value)
    {
        $this->receivePrivateMessages = $value;
    }

    public function getReceivePrivateMessages()
    {
        return $this->receivePrivateMessages;
    }

    public function setReceiveUpdates($value)
    {
        $this->receiveUpdates = $value;
    }

    public function getReceiveUpdates()
    {
        return $this->receiveUpdates;
    }

    public function setReceiveTips($value)
    {
        $this->receiveTips = $value;
    }

    public function getReceiveTips()
    {
        return $this->receiveTips;
    }

    public function setMailFrequency($value)
    {
        $this->mailFrequency = $value;
    }

    public function getMailFrequency()
    {
        return $this->mailFrequency;
    }

}