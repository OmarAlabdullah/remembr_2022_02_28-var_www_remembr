<?php

namespace Auth\Forms;

class LoginForm extends \Zend\Form\Form
{
	private $authenticator;

	public function __construct(\Auth\Rights\AuthAdapter $authenticator, $name = null) {
		parent::__construct($name);
		$this->authenticator = $authenticator;

		$this->setAttribute('method', 'post');

		$this->add(array(
			'name' => 'email',
			'attributes' => array('type' => 'email', 'placeholder' => 'Your e-mail address', 'size' => '25'),
			'options' => array('label' => 'E-mail address')
		));
		$this->add(array(
			'name' => 'password',
			'attributes' => array('type' => 'password', 'placeholder' => 'Your password', 'size' => '25'),
			'options' => array('label' => 'Password')
		));
		$this->add(array(
			'name' => 'submit',
			'attributes' => array('type' => 'submit', 'value' => 'Sign in', 'class' => 'css-button orange'),
			'options' => array('label' => ' ')
		));
	}

	public function getInputFilter()
	{
		if (!$this->filter) {
			$this->filter = new \Zend\InputFilter\InputFilter();
			$factory = new \Zend\InputFilter\Factory();
			$this->filter->add($factory->createInput(array(
				'name' => 'email',
				'required' => true,
			)));
			$this->authValidator = new \Auth\Validator\AuthValidate(array('userField' => 'email', 'authenticator' => $this->authenticator));
			$this->filter->add($factory->createInput(array(
				'name' => 'password',
				'required' => true,
				'validators' => array($this->authValidator)
			)));
		}
		return $this->filter;
	}

	public function getAuthResult()
	{
		return $this->authValidator->getAuthResult();
	}
}

?>
