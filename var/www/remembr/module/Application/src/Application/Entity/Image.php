<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation as FORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="Image")
 * @ORM\HasLifecycleCallbacks
 */
class Image
{

    /**
     * @var integer
     * 
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

    public function setId($id)
    {
        $this->id = $id; return $this;
    }

    /**
     * @var string
     * @ORM\Column(name="location", type="string", length=5, nullable=false)
     * @FORM\Filter({"name":"StringTrim"})
     * @FORM\Filter({"name":"StripTags"})
     * @FORM\Validator({"name":"StringLength", "options": {"min":"1", "max":"255"}})
     * @FORM\Options({"label":"Location"})
     * @FORM\Type("Zend\Form\Element\Select")
     */
    protected $location;

    public function setLocation($value)
    {
        $this->location = $value;
        return $this;
    }

    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @var integer
     * @ORM\Column(type="integer", name="roi_offset_x")
     * @FORM\Options({"label":"RoiOffsetX"})
     */
    protected $roi_offset_x;

    public function getRoiOffsetX()
    {
        return $this->roi_offset_x;
    }

    public function setRoiOffsetX($value)
    {
        $this->roi_offset_x = $value;
        return $this;
    }

    /**
     * @var integer
     * @ORM\Column(type="integer", name="roi_offset_y")
     * @FORM\Options({"label":"RoiOffsetY"})
     */
    protected $roi_offset_y;

    public function getRoiOffsetY()
    {
        return $this->roi_offset_y;
    }

    public function setRoiOffsetY($value)
    {
        $this->roi_offset_y = $value;
        return $this;
    }

    /**
     * @var integer
     * @ORM\Column(type="integer", name="roi_width")
     * @FORM\Options({"label":"RoiWidth"})
     */
    protected $roi_width;

    public function getRoiWidth()
    {
        return $this->roi_width;
    }

    public function setRoiWidth($value)
    {
        $this->roi_width = $value;
        return $this;
    }

    /**
     * @var integer
     * @ORM\Column(type="integer", name="roi_height")
     * @FORM\Options({"label":"RoiHeight"})
     */
    protected $roi_height;

    public function getRoiHeight()
    {
        return $this->roi_height;
    }

    public function setRoiHeight($value)
    {
        $this->roi_height = $value;
        return $this;
    }
    
    public function setROI($roi) {
        $this->roi_offset_x = floor($roi['x']);
        $this->roi_offset_y = floor($roi['y']);
        $this->roi_width    = floor($roi['width']);
        $this->roi_height   = floor($roi['height']);
        return $this;
    }
    
    public function getROI() {
        if (is_null($this->roi_offset_x) || is_null($this->roi_offset_y) || is_null($this->roi_height) || is_null($this->roi_width))
            return null;
        return array(
            'x' => $this->roi_offset_x,
            'y' => $this->roi_offset_y,
            'width' => $this->roi_width,
            'height' => $this->roi_height
        );
    }
}