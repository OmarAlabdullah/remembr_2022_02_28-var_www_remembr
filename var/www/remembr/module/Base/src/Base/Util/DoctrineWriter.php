<?php

namespace Base\Util;

class DoctrineWriter extends \Zend\Log\Writer\AbstractWriter
{
	private $em;

	public function __construct(\Doctrine\ORM\EntityManagerInterface $em)
	{
		$this->em = $em;
	}

	protected function doWrite(array $event)
	{
		$event = $this->formatter ? $this->formatter->format($event) : $event;

		$entry = new \Base\Entity\LogEntry();
		$entry->setTime($event['timestamp']);
		$entry->setPriority($event['priority']);
		$entry->setPriorityName($event['priorityName']);
		$entry->setMessage($event['message']);
		$entry->setExtra(count($event['extra']) > 0 ? implode("\n", $event['extra']) : null);

		$this->em->persist($entry);
		$this->em->flush($entry);
	}
}

?>
