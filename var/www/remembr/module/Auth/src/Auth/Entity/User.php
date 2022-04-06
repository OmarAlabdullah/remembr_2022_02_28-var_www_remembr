<?php

namespace Auth\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ ORM\Table(name="User")
 * @ ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class User
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
	 * @ORM\Column(name="email", type="string", length=255, nullable=false, unique=true)
	 */
	protected $email;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="password", type="string", length=100)
	 */
	protected $password;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="logins", type="integer", nullable=false)
	 */
	protected $logins = 0;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="lastLogin", type="datetime", nullable=true)
	 */
	protected $lastLogin;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 *
	 * @ORM\OneToMany(targetEntity="Auth\Entity\UserRight", mappedBy="user", orphanRemoval=true, cascade={"persist","remove","detach","merge","refresh"})
	 */
	protected $rights;
	protected $rightsObj = array();

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->rights = new \Doctrine\Common\Collections\ArrayCollection();
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set email
	 *
	 * @param string $email
	 * @return User
	 */
	public function setEmail($email)
	{
		$this->email = $email;

		return $this;
	}

	/**
	 * Get email
	 *
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Set password
	 *
	 * @param string $password
	 * @return User
	 */
	public function setPassword($password)
	{
		$this->password = $password;

		return $this;
	}

	/**
	 * Get password
	 *
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * Set logins
	 *
	 * @param integer $logins
	 * @return User
	 */
	public function setLogins($logins)
	{
		$this->logins = $logins;

		return $this;
	}

	/**
	 * Get logins
	 *
	 * @return integer
	 */
	public function getLogins()
	{
		return $this->logins;
	}

	/**
	 * Set lastLogin
	 *
	 * @param \DateTime $lastLogin
	 * @return User
	 */
	public function setLastLogin($lastLogin)
	{
		$this->lastLogin = $lastLogin;

		return $this;
	}

	/**
	 * Get lastLogin
	 *
	 * @return \DateTime
	 */
	public function getLastLogin()
	{
		return $this->lastLogin;
	}

	private function syncRights($path) {
		$this_ = $this;
		$this->rightsObj[$path]->syncToDoctrine(
			$this->rights,
			function($item) use ($path) { return $item->getPath() != $path; },
			function($name, $value) use ($this_, $path) { return new \Auth\Entity\UserRight($this_, $path, $name, $value); }
		);
	}

	/**
	 * Add rights
	 *
	 * @param \Auth\Entity\UserRight $rights
	 * @return User
	 */
	public function addRight($path, $newRights)
	{
		if (!isset($this->rightsObj[$path])) {
			$this->rightsObj[$path] = \Auth\Rights\RightList::convert($newRights);
		} else {
			$this->rightsObj[$path]->add($newRights);
		}
		$this->syncRights($path);

		return $this;
	}

	public function setRights($path, $newRights) {
		$this->rightsObj[$path] = \Auth\Rights\RightList::convert($newRights);
		$this->syncRights($path);
	}

	/**
	 * Remove rights
	 *
	 * @param \Auth\Entity\UserRight $rights
	 */
	public function removeRight($path, $rights)
	{
		if (!isset($this->rightsObj[$path]))
			// No need to bother removing rights if you didn't have any on a path in the first place.
			return;

		$this->rightsObj[$path]->remove($rights);
		if ($this->rightsObj[$path]->isEmpty()) {
			unset($this->rightsObj[$path]);
		}
		$this->syncRights($path);
	}

	/**
	 * Get rights
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getRights()
	{
		return $this->rightsObj;
	}
	/**
	 * @ORM\PostLoad
	 */
	public function refreshRights()
	{
		$this->rightsObj = \Auth\Rights\RightList::fromDoctrine($this->rights);
	}
}