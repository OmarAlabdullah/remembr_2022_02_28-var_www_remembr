<?php

namespace Cms\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation as FORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="cmsPage")
 * @ORM\HasLifecycleCallbacks
 */
class CmsPage
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
     * @ORM\Column(name="lang", type="string", length=5, nullable=false)
     * @FORM\Filter({"name":"StringTrim"})
     * @FORM\Filter({"name":"StripTags"})
     * @FORM\Validator({"name":"StringLength", "options": {"min":"2", "max":"25"}})
     * @FORM\Options({"label":"Lang"})
     * @FORM\Type("Zend\Form\Element\Select")
     */
    protected $lang;

    public function setLang($value)
    {
        $this->lang = $value;
        return $this;
    }

    public function getLang()
    {
        return $this->lang;
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
     * @FORM\AllowEmpty
     * @FORM\Filter({"name":"StringTrim"})
     * @FORM\Filter({"name":"StripTags"})
     * @FORM\Options({"label":"Title"})
     */
    protected $title;

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($value)
    {
        $this->title = $value;
        return $this;
    }

    /**
     * @var text
	 * @FORM\AllowEmpty
     * @ORM\Column(type="text", name="metaDescription", nullable=true)
     * @FORM\Filter({"name":"StringTrim"})
     * @FORM\Type("Cms\Form\Element\SimpleTextArea")
     * @FORM\Options({"label":"HTML meta description tag"})
     */
    protected $metaDescription;

    public function setMetaDescription($value)
    {
        $this->metaDescription = $value;
        return $this;
    }

    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * @var text
     * @ORM\Column(type="text")
     * @FORM\Filter({"name":"StringTrim"})
     * @FORM\Type("Zend\Form\Element\Textarea")
     * @FORM\Options({"label":"Text"})
     */
    protected $text;

    public function setText($value)
    {
        $this->text = $value;
        return $this;
    }

    public function getText()
    {
        return $this->text;
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
            'title' => $this->title,
            'metaDescription' => $this->metaDescription,
            'text' => $this->text,
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
        foreach (array('title', 'text', 'slug', 'lang', 'deleted', 'metaDescription') as $name)
        {
            if (isset($values[$name]))
            {
                $this->$name = $values[$name];
            }
        }
    }

}
