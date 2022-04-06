<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Zend\Form\Annotation as FORM;

/**
 * DraftPage
 *
 * @ORM\Table(name="DraftPage")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class DraftPage extends BasePage
{
    /**
     * @var string
     *
     * @FORM\AllowEmpty
     * @FORM\Filter({"name":"StringTrim"})
     * @FORM\Filter({"name":"StripTags"})
     * @ORM\Column(name="firstname", type="string", length=255, nullable=false)
     */
    protected $firstname;

    /**
     * @var string
     *
     * @FORM\AllowEmpty
     * @FORM\Filter({"name":"StringTrim"})
     * @FORM\Filter({"name":"StripTags"})
     * @ORM\Column(name="lastname", type="string", length=255, nullable=true)
     */
    protected $lastname;
    
    /**
     * @var \DateTime
     *
     * @FORM\AllowEmpty
     * @FORM\Filter({"name":"StringTrim"})
     * @FORM\Validator({"name":"Date"})
     * @ORM\Column(name="dateofbirth", type="datetime", nullable=true)
     */
    protected $dateofbirth;
    
    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string
     * @return \Application\Entity\Page
     */
    public function setStatus($value)
    {
        if ($value != self::DRAFT)
            throw new \Exception ("Status of a DraftPage may only be set to DRAFT, otherwise copy objet to a Page (which should really only happen client-side).");
        $this->status = $value;
        return $this;
    }
    
    /**
     * @return array
     */
    public function getArrayCopy($depth = 0)
    {
        $arr = array(
            'url' => $this->url,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'introtext' => $this->introtext,
            'dateofbirth' => $this->dateofbirth instanceof \DateTime ? $this->dateofbirth->format('Y-m-d') : '',
            'dateofdeath' => $this->dateofdeath instanceof \DateTime ? $this->dateofdeath->format('Y-m-d') : '',
            'creationdate' => $this->creationdate instanceof \DateTime ? $this->creationdate->format('Y-m-d') : '',
            'publishdate' => $this->publishdate instanceof \DateTime ? $this->publishdate->format('Y-m-d') : '',
            'uselabels' => isset($this->uselabels) && $this->uselabels,
            'status' => $this->status,
            'private' => $this->private,
            'gender' => $this->gender,
            'country' => $this->country,
            'residence' => $this->residence,
        );

        return $arr;
    }
}