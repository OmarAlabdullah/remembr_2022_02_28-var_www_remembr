<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation as FORM;

/**
 * @ORM\Table(name="Label",indexes={@ORM\Index(name="label_idx", columns={"name"})})
 * @ORM\Entity
 */
class Label
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
	 * @ORM\Column(name="name", type="string", length=255, nullable=false)
	 */
	protected $name;


	// @TODO add language?, translation?
	
	/**
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
     * @ORM\ManyToMany(targetEntity="Application\Entity\Page", mappedBy="labels")
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $pages;

	/**
     * @ORM\ManyToMany(targetEntity="Application\Entity\Memory", mappedBy="labels")
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $memories;

	/**
	 * @param \Application\Entity\Memory $memory
	 * @return Label
	 */
	public function addMemory(Memory $memory)
	{
		$this->memories[] = $memory;
		$memory->addLabel($this);
		return $this;
	}
	/**
	 * @param \Application\Entity\Memory $memory
	 * @return Label
	 */
	public function rmMemory(Memory $memory)
	{
		$this->memories->removeElement($memory);
		$memory->getLabels()->removeElement($this);
		return $this;
	}

	/**
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
	public function getMemories()
	{
		return $this->memories;
	}


	/**
	 * @param \Application\Entity\Page $page
	 * @return Label
	 */
	public function addPage(Page $page)
	{
		$this->pages[] = $page;
		$page->addLabel($this);
		return $this;
	}

	/**
	 * @param \Application\Entity\Page $page
	 * @return Label
	 */
	public function rmPage(Page $page)
	{
		$this->pages->removeElement($page);
		$page->getLabels()->removeElement($this);
		return $this;
	}

	/**
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
	public function getPages()
	{
		return $this->pages;
	}


	public function exchangeArray($data)
	{
		$this->name	=	$data['name'];

		return $this;
	}

	/**
	 * @return array
	 */
	public function getArrayCopy()
	{
		return array(
			'id'	=> $this->id,
			'name'	=> $this->name
		);
	}

    public function __construct()
	{
        $this->pages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->memories = new \Doctrine\Common\Collections\ArrayCollection();
    }
}