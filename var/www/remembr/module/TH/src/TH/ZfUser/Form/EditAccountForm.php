<?php

namespace TH\ZfUser\Form;

class EditAccountForm extends \Zend\Form\Form
{
	public function __construct($name = null) {
		parent::__construct($name);

		$this->setAttribute('method', 'post');

		/*$this->add(array(
			'name' => 'username',
			'attributes' => array('type' => 'text', 'size' => '30', 'autocomplete' => 'off', 'class' => 'username'),
			'options' => array('label' => 'User name')
		));*/

        $this->add(array(
			'name' => 'name',
			'attributes' => array('type' => 'text', 'size' => '30', 'autocomplete' => 'off', 'class' => 'name'),
			'options' => array('label' => 'Name')
		));

//		$this->add(array(
//			'name' => 'title',
//			'attributes' => array('type' => 'text', 'size' => '30', 'class' => 'title'),
//			'options' => array('label' => 'Title')
//		));

		// I'd like to add the email as just a static text, but ZF2 doesn't seem to support such things by itself.
		// Do we have to write a custom element for that?
		$this->add(array(
			'name' => 'email',
			'attributes' => array('type' => 'text', 'disabled' => true, 'size' => '30', 'autocomplete' => 'off'),
			'options' => array('label' => 'Email address')
		));
		$this->add(array(
			'name' => 'submit',
			'attributes' => array('type' => 'submit', 'value' => 'Save settings', 'class' => 'css-button orange'),
			'options' => array('label' => ' ')
		));
	}

	public function getInputFilter()
	{
		if (!$this->filter) {
			$this->filter = new \Zend\InputFilter\InputFilter();
			$factory = new \Zend\InputFilter\Factory();
			/*$this->filter->add($factory->createInput(array(
				'name' => 'username',
				'required' => true,
			)));*/
            $this->filter->add($factory->createInput(array(
				'name' => 'name',
				'required' => true,
			)));
		}
		return $this->filter;
	}
}