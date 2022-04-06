<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation as FORM;

/**
 * @ORM\Table(name="Newsletter")
 * @ORM\Entity
 */
class Newsletter
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
     * @var string
     *
     * @FORM\Exclude()
     * @ORM\Column(type="string", length=40, nullable=true, unique=true)
     */
    protected $confirmkey;

    /**
     * @FORM\Exclude()
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $confirmed = 0;

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
     * @return string
     */
    public function getConfirmkey()
    {
        return $this->confirmkey;
    }

    /**
     * Set deleted
     *
     * @param boolean $deleted
     */
    public function setConfirmed($value)
    {
        $this->confirmed = $value;
        return $this;
    }

    /**
     * Get deleted
     *
     * @return boolean
     */
    public function getConfirmed()
    {
        return $this->confirmed;
    }

    public function exchangeArray($data)
    {
        if (!empty($data['email'])) {$this->email = $data['email'];}

        return $this;
    }

    /**
     * @return array
     */
    public function getArrayCopy()
    {
        return array(
            'id' => $this->id,
            'email' => $this->email,
            'confirmkey' => $this->confirmkey,
            'creationdate' => $this->creationdate->format(DATE_ISO8601)
        );
    }

    public function __construct()
    {
        $this->confirmkey = \Base\Util\Generator::generateKey(32);
        $this->creationdate = new \DateTime();
    }

}