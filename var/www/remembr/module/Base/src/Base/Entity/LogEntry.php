<?php

namespace Base\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="LogEntry")
 * @ORM\Entity
 */
class LogEntry
{
	/**
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;

	/** @ORM\Column(name="time", type="datetime") */
	private $time;

	/** @ORM\Column(name="priority", type="integer") */
	private $priority;

	/** @ORM\Column(name="priorityName", type="string", length=10) */
	private $priorityName;

	/** @ORM\Column(name="message", type="text") */
	private $message = '';

	/** @ORM\Column(name="extra", type="text", nullable=true) */
	private $extra;

	public function setTime($value)         { $this->time = $value; }
	public function setPriority($value)     { $this->priority = $value; }
	public function setPriorityName($value) { $this->priorityName = $value; }
	public function setMessage($value)      { $this->message = $value; }
	public function setExtra($value)        { $this->extra = $value; }

	public function getTime()         { return $this->time; }
	public function getPriority()     { return $this->priority; }
	public function getPriorityName() { return $this->priorityName; }
	public function getMessage()      { return $this->message; }
	public function getExtra()        { return $this->extra; }
}

?>
