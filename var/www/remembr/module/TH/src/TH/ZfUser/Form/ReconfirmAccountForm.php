<?php

namespace TH\ZfUser\Form;

class ReconfirmAccountForm extends \Zend\Form\Form
{
	public function __construct($name = null)
	{
		parent::__construct($name);
		$this->setAttribute('method', 'post');
		$this->add(array(
			'name' => 'email',
			'attributes' => array('type' => 'text', 'size' => '30', 'autocomplete' => 'off','class' => 'email', 'placeholder' => 'Enter your e-mail address'),
			'options' => array('label' => 'E-mail address')
		));
		$this->add(array(
			'name' => 'submit',
			'attributes' => array('type' => 'submit', 'value' => 'Create account', 'class' => 'css-button orange'),
			'options' => array('label' => ' ')
		));
	}

	public function getInputFilter()
	{
		if (!$this->filter)
		{
			$this->filter = new \Zend\InputFilter\InputFilter();
			$factory = new \Zend\InputFilter\Factory();
			$this->filter->add($factory->createInput(array(
									'name' => 'email',
									'required' => true,
			)));
		}
		return $this->filter;
	}
}