<?php

namespace ImageUpload\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation as FORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="uploadedImage")
 * @ORM\HasLifecycleCallbacks
 */
class UploadedImage
{

    /**
     * @var integer
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @FORM\Exclude()
     */
    protected $id;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @var string
     * @ORM\Column(name="filename", type="string", length=5, nullable=false)
     * @FORM\Filter({"name":"StringTrim"})
     * @FORM\Filter({"name":"StripTags"})
     * @FORM\Validator({"name":"StringLength", "options": {"min":"1", "max":"128"}})
     * @FORM\Options({"label":"Filename"})
     * @FORM\Type("Zend\Form\Element\Select")
     */
    protected $fileName;

    public function setFileName($value)
    {
        $this->fileName = $value;
        return $this;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @var string
     * @ORM\Column(type="string")
     * @FORM\Validator({"name":"Regex", "options":{"pattern":"/^[a-zA-Z][a-zA-Z0-9._-]{0,149}$/"}})
     * @FORM\Validator({"name":"StringLength", "options": {"min":"2", "max":"150"}})	
     * @FORM\Options({"label":"Slug"})
     */
    protected $slug;

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($value)
    {
        $this->slug = $value;
        return $this;
    }

    /**
     * @var string
     * @ORM\Column(type="string")
     * @FORM\Filter({"name":"StringTrim"})
     * @FORM\Filter({"name":"StripTags"})
     * @FORM\Options({"label":"Alternative"})
     */
    protected $alternative;

    public function getAlternative()
    {
        return $this->alternative;
    }

    public function setAlternative($value)
    {
        $this->alternative = $value;
        return $this;
    }

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=false)
     * @FORM\Exclude()
     */
    protected $createDate;

    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=false)
     * @FORM\Exclude()
     */
    protected $updateDate;

    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @FORM\Exclude()
     */
    protected $deleted = false;

    public function getDeleted()
    {
        return $this->deleted;
    }

    public function setDeleted($value)
    {
        $this->deleted = $value;
    }

    public function __construct()
    {
        $this->deleted = false;
    }

    public function getArrayCopy()
    {
        return array(
            'id' => $this->id,
            'createDate' => $this->createDate,
            'updateDate' => $this->updateDate,
            'alternative' => $this->alternative,
            'alternative' => $this->alternative,
            'slug' => $this->slug,
            'deleted' => $this->deleted,
        );
    }

    /**
     * @FORM\Type("Zend\Form\Element\Submit")
     * @FORM\Attributes({"value":"Submit"})
     */
    public $submit;

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createDate = new \DateTime();
        $this->updateDate = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updateDate = new \DateTime();
    }

    public function exchangeArray(array $values)
    {
        foreach (array('filename', 'alternative', 'slug', 'deleted') as $name)
        {
            if (isset($values[$name]))
            {
                $this->$name = $values[$name];
            }
        }
    }

}
