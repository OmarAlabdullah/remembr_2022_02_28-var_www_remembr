<?php

namespace TH\ZfUser\Form;

class ResetPasswordForm extends \Zend\Form\Form
{
	private $locator;

	public function __construct(\Zend\ServiceManager\ServiceLocatorInterface $locator, $name = null) {
		parent::__construct($name);
		$this->locator = $locator;

		$this->setAttribute('method', 'post');
		$this->add(array(
			'name' => 'email',
			'attributes' => array('type' => 'text', 'class' => 'email', 'size' => '25', 'autocomplete' => 'off'),
			'options' => array('label' => 'Email address')
		));
		$this->add(array(
			'name' => 'submit',
			'attributes' => array('type' => 'submit', 'value' => 'Change password', 'class' => 'resetpwd css-button orange', 'autocomplete' => 'off'),
			'options' => array('label' => ' ')
		));
	}

	public function getInputFilter()
	{
		if (!$this->filter) {
			$em = $this->locator->get('doctrine.entitymanager.orm_default');

			$this->filter = new \Zend\InputFilter\InputFilter();
			$factory = new \Zend\InputFilter\Factory();
			$this->filter->add($factory->createInput(array(
				'name' => 'email',
				'required' => true,
				'validators' => array(
					array('name' => '\DoctrineModule\Validator\ObjectExists', 'options' => array(
						'object_repository' => $em->getRepository('TH\ZfUser\Entity\UserAccount'),
						'fields' => 'email',
						'messages' => array(\DoctrineModule\Validator\ObjectExists::ERROR_NO_OBJECT_FOUND => 'This email address is not associated with any account')
					))
				)
			)));
		}
		return $this->filter;
	}
}

?>
