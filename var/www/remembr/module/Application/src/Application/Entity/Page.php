<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation as FORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Page
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Page extends BasePage
{
    
    /**
	 * @var \Application\Entity\Image
         * @ORM\ManyToOne(targetEntity="\Application\Entity\Image", inversedBy="comments")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="photo_id", referencedColumnName="id", nullable=true)
	 * })
	 */
	protected $photo;

    /**
     * @param \Application\Entity\Label $label
     * @return Page
     */
    public function addLabel(Label $label)
    {
        $this->labels[] = $label;
        return $this;
    }

    /**
     * @param \Application\Entity\Label $label
     * @return Memory
     */
    public function rmLabel(Label $label)
    {
        $this->labels->removeElement($label);
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @param \Application\Entity\Memory $memory
     * @return Label
     */
    public function addMemory(Memory $memory)
    {
        $this->memories[] = $memory;
        $memory->setPage($this);
        return $this;
    }

//	/**
//	 * @param \Application\Entity\Memory $memory
//	 * @return Label
//	 */
//	public function rmMemory(Memory $memory)
//	{
//		$memory->setDeleted(true);
//		return $this;
//	}

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getMemories()
    {
        return $this->memories;
    }
        
    public function getPhoto()       { return $this->photo; }
	public function setPhoto($value) { 
            if (! ($value === null || $value instanceof \Application\Entity\Image))
                throw new \Exception("Can only set Photo as object, not as file location");
            $this->photo = $value;
            return $this;
        }


    public function exchangeArray($data)
    {
        $this->url = $data['url'];
        $this->firstname = $data['firstname'];
        $this->lastname = $data['lastname'];
        $this->photo = empty($data['photo']) ? '' : $data['photo'];
        $this->introtext = empty($data['introtext']) ? '' : $data['introtext'];
        $this->dateofbirth = new \DateTime($data['dateofbirth']);
        $this->dateofdeath = empty($data['dateofdeath']) ? null : new \DateTime($data['dateofdeath']);
        $this->creationdate = new \DateTime(empty($data['creationdate']) ? 'now' : $data['creationdate']);
        $this->publishdate = empty($data['publishdate']) ? null : new \DateTime($data['publishdate']);
        $this->uselabels = isset($data['uselabels']) && $data['uselabels'];
        $this->status = isset($data['status']) ? $data['status'] : self::UNPUBLISHED;
        $this->private = !empty($data['private']);
        $this->gender = empty($data['gender']) ? null : ($data['gender']);
        $this->country = empty($data['country']) ? null : ($data['country']);
        $this->residence = empty($data['residence']) ? null : ($data['residence']);

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
            'photo' => $this->photo && $this->photo->getLocation() ? $this->photo->getLocation() : '/images/user-icon-large.png',
            'roi' => $this->photo && $this->photo->getLocation() ? $this->photo->getROI() : null,
            'dateofbirth' => $this->dateofbirth instanceof \DateTime ? $this->dateofbirth->format('Y-m-d') : '',
            'dateofdeath' => $this->dateofdeath instanceof \DateTime ? $this->dateofdeath->format('Y-m-d') : '',
            'creationdate' => $this->creationdate instanceof \DateTime ? $this->creationdate->format('Y-m-d') : '',
            'publishdate' => $this->publishdate instanceof \DateTime ? $this->publishdate->format('Y-m-d') : '',
            'uselabels' => isset($this->uselabels) && $this->uselabels,
            'status' => $this->status,
            'private' => $this->private,
            'user' => $this->user ? $this->user->getProfile()->getArrayCopy() : null, // we want the profile for json; we don't want to expose everyones email
            'gender' => $this->gender,
            'country' => $this->country,
            'residence' => $this->residence,
            'cropRectangle' => array('x' => null, 'y' => null, 'image' => array('width' => null, 'height'=> null), 'canvas' => array('width' => null, 'height'=> null))
        );

        if ($depth > 0)
        {
            $arr['labels'] = array();
            foreach ($this->labels as $label)
            {
                $arr['labels'][] = $label->getArrayCopy();
            }
        }

        return $arr;
    }
    
    public function getPhotoROI() {
        if ($this->getPhoto() === null)
            return array('x' => null, 'y' => null, 'width' => null, 'height' => null);
        return $this->getPhoto()->getROI();
    }
    
    public function getPhotoLocation() {
        if ($this->getPhoto() === null)
            return '/images/user-icon-large.png';
        else
            return $this->getPhoto()->getLocation();
    }
}
