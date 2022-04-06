<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Message
 *
 * @ORM\Entity
 * @ORM\Table(name="messageCentreMessage")
 */
class MessageCentreMessage
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=80, nullable=false)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    protected $content;

    /**
     * @var \Datetime
     * @ORM\Column(type="datetime")
     */
    protected $sendDate;


    /**
     * @var Array
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $extra = null;


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
     * Set title
     *
     * @param text $title
     * @return MessageCentreMessage
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get title
     *
     * @return text
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content
     *
     * @param text $content
     * @return MessageCentreMessage
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get content
     *
     * @return text
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set sendDate
     *
     * @param datetime $sendDate
     * @return MessageCentreMessage
     */
    public function setSendDate($sendDate)
    {
        $this->sendDate = $sendDate;
        return $this;
    }

    /**
     * Get sendDate
     *
     * @return datetime
     */
    public function getSendDate()
    {
        return $this->sendDate;
    }

	/**
     * @param array $extra, extra payload .e.g invite-request
     * @return MessageCentreMessage
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;
        return $this;
    }

    /**
     * @return array
     */
    public function getExtra()
    {
        return $this->extra;
    }


    public function __construct(array $data)
    {
		$this->exchangeArray($data);
    }

	public function exchangeArray(array $data)
	{
		$this->title	= $data['title'];
		$this->content	= $data['content'];
		$this->sendDate	= $data['senddate'];
		$this->extra	= isset($data['extra']) ? $data['extra'] : null;
	}

	/**
	 * @return array
	 */
	public function getArrayCopy()
	{
		return array(
			'title'		=> $this->title,
			'content'	=> $this->content,
			'senddate'	=> $this->sendDate,
			'extra'		=> $this->extra
		);
	}

}