<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Entity
 */
class Video extends Memory
{
	/**
	 * @var string
	 *
	 * @ORM\Column(name="videoid", type="string", length=255, nullable=false)
	 */
	protected $videoid;

	/**
	 * @param string $videoid
	 * @return string
	 */
	public function setVideoid($videoid)
	{
		$this->videoid = $videoid;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getVideoid()
	{
		return $this->videoid;
	}

	/**
	 *@return  string
	 */
	public function getType()
	{
		return 'video';
	}

	public function exchangeArray($data)
	{
		parent::exchangeArray($data);

		$this->videoid	=	$data['videoid'];

		return $this;
	}

	/**
	 * @return array
	 */
	public function getArrayCopy($depth=0)
	{
		$arr = parent::getArrayCopy($depth);
		$arr['type'] = 'video';
		$arr['videoid'] = $this->videoid;

		return $arr;
	}

}