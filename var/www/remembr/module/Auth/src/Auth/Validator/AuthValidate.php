<?php

namespace Auth\Validator;

use Zend\Authentication\Result;

class AuthValidate extends \Zend\Validator\AbstractValidator
{
	protected $userField;
	protected $authenticator;
	protected $result;

	protected $messageTemplates = array(
		Result::FAILURE => 'Authentication failed',
		Result::FAILURE_IDENTITY_NOT_FOUND => 'User was not found',
		Result::FAILURE_IDENTITY_AMBIGUOUS => 'User was ambiguous',
		Result::FAILURE_CREDENTIAL_INVALID => 'Invalid login information',
		Result::FAILURE_UNCATEGORIZED => 'Uncategorized authentication failure',
	);

	public function __construct($options = null)
	{
		parent::__construct($options);
		if (is_array($options))
		{
			foreach ($options as $key => $value)
			{
				switch ($key) {
				case 'userField': $this->userField = $value; break;
				case 'authenticator': $this->authenticator = $value; break;
				}
			}
		}

		if ($this->userField == null)
		{
			throw new Exception('Login validator must know the user field name.');
		}
		if ($this->authenticator == null)
		{
			throw new Exception('Login validator must have an authenticator.');
		}
	}

	public function getAuthResult()
	{
		return $this->result;
	}

	public function isValid($value, $context = null)
	{
		if ($context == null || !isset($context[$this->userField]) || $context[$this->userField] == '')
		{
			// When anything is really missing, we assume this is allowed, and will not interfere in validation.
			return true;
		}
		$this->authenticator->setEmail($context[$this->userField]);
		$this->authenticator->setPassword($value);
		$service = new \Zend\Authentication\AuthenticationService();
		$this->result = $service->authenticate($this->authenticator);
		if (!$this->result->isValid()) {
			if (isset($this->messageTemplates[$this->result->getCode()]))
				$this->error($this->result->getCode());
			else
				$this->error(Result::FAILURE);
		}
		return $this->result->isValid();
	}
}