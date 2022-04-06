<?php

namespace Auth\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserRight
 *
 * @ORM\Table(name="UserRight")
 * @ORM\Entity
 */
class UserRight
{
	/**
	 * @var string
	 *
	 * @ORM\Column(name="path", type="string", length=255)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="NONE")
	 */
	private $path;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="rightGroup", type="string", length=255)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="NONE")
	 */
	private $rightGroup;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="value", type="integer")
	 */
	private $value;

	/**
	 * @var \Base\Entity\User
	 *
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="NONE")
	 * @ORM\Column(name="user_id", type="integer", nullable=false)
	 */
	private $user;

	public function __construct($user, $path, $group, $value) {
		$this->user = $user;
		$this->path = $path;
		$this->rightGroup = $group;
		$this->value = $value;
	}

	/**
	 * Set path
	 *
	 * @param string $path
	 * @return UserRight
	 */
	public function setPath($path)
	{
			$this->path = $path;

			return $this;
	}

	/**
	 * Get path
	 *
	 * @return string
	 */
	public function getPath()
	{
			return $this->path;
	}

	/**
	 * Set rightGroup
	 *
	 * @param string $rightGroup
	 * @return UserRight
	 */
	public function setRightGroup($rightGroup)
	{
			$this->rightGroup = $rightGroup;

			return $this;
	}

	/**
	 * Get rightGroup
	 *
	 * @return string
	 */
	public function getRightGroup()
	{
			return $this->rightGroup;
	}

	/**
	 * Set value
	 *
	 * @param integer $value
	 * @return UserRight
	 */
	public function setValue($value)
	{
			$this->value = $value;

			return $this;
	}

	/**
	 * Get value
	 *
	 * @return integer
	 */
	public function getValue()
	{
			return $this->value;
	}

	/**
	 * Set user
	 *
	 * @param \Base\Entity\User $user
	 * @return UserRight
	 */
	public function setUser(\Base\Entity\User $user)
	{
			$this->user = $user;

			return $this;
	}

	/**
	 * Get user
	 *
	 * @return \Base\Entity\User
	 */
	public function getUser()
	{
			return $this->user;
	}
}
