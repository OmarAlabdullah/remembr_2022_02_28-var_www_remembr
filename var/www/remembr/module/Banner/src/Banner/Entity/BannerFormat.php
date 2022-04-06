<?php

namespace Banner\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="BannerFormat")
 * @ORM\Entity
 */
class BannerFormat
{
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id", type="integer", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id;
	public function getId() { return $this->id; }

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=40)
	 */
	protected $title;
	public function getTitle() { return $this->title; }

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer")
	 */
	protected $width;
	public function getWidth() { return $this->width; }

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer")
	 */
	protected $height;
	public function getHeight() { return $this->height; }

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=40)
	 */
	protected $cssClass;
	public function getCssClass() { return $this->cssClass; }
}

?>
