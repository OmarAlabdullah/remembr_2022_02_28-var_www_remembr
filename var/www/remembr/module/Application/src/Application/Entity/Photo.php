<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Photo
 *
 * @ORM\Entity
 */
class Photo extends Memory
{
	/**
	 * @var string
	 *
	 * @ORM\Column(name="photoid", type="string", length=255, nullable=false)
	 */
	protected $photoid;

	/**
	 * @param string $photoid
	 * @return string
	 */
	public function setPhotoid($photoid)
	{
		$this->photoid = $photoid;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPhotoid()
	{
		return $this->photoid;
	}

	/**
	 *@return  string
	 */
	public function getType()
	{
		return 'photo';
	}

	public function exchangeArray($data)
	{
		parent::exchangeArray($data);

		$this->photoid	=	$data['photoid'];

		return $this;
	}

	/**
	 * @return array
	 */
	public function getArrayCopy($depth=0)
	{
		$arr = parent::getArrayCopy($depth);
		$arr['type'] = 'photo';
		$arr['photoid'] = $this->photoid;

		return $arr;
	}
}
