<?php

namespace Application;

class RemembrException extends \Exception
{
	/**
	 * @var array extra information exposes to client
	 * proposal keys:
	 *     data: for array of relevant data (e.g. for showing public page data for private page)
	 *     actions: for clientside actions that can be taken, such as 'login', 'requestaccess' after a 403
	 *     suberror: further specify the error, e.g. error=forbidden could have suberror=loginrequired or suberror=inviterequired
	 */
	protected $clientextra;

	public function __construct($message, $code, array $clientextra = array())
				{
		parent::__construct($message, $code);
		
		$this->clientextra = $clientextra;
	}

	public function getClientExtra()
	{
		return $this->clientextra;
	}

}