<?php

namespace TH\ZfUser\Form;

use Zend\Form\Form;

class LoginForm extends Form
{

    private $authenticator;

    public function __construct(\Auth\Rights\AuthAdapter $authenticator, $name = null)
    {
        parent::__construct('login');
        $this->authenticator = $authenticator;

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'email',
            'attributes' => array('type' => 'email', 'placeholder' => 'Enter your e-mail address', 'size' => '25', 'autocomplete' => 'off', 'class' => 'email'),
            'options' => array('label' => 'E-mail address')
        ));

        $this->add(array(
            'name' => 'password',
            'attributes' => array('type' => 'password', 'placeholder' => 'Your password', 'size' => '25', 'autocomplete' => 'off', 'class' => 'password'),
            'options' => array('label' => 'Password')
        ));

        $this->add(array(
            'name' => 'rememberme',
            'type' => 'checkbox',
            // 'Zend\Form\Element\Checkbox',
            // 'attributes' => array( // Is not working this way
            // 'type'  => '\Zend\Form\Element\Checkbox',
            // ),
            'attributes' => array('class' => 'styled rememberme'),
            'options' => array(
                'label' => 'Remember Me'
            // 'checked_value' => 'true', without value here will be 1
            // 'unchecked_value' => 'false', // witll be 1
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Login',
                'id' => 'submitbutton',
            ),
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
            $this->authValidator = new \TH\ZfUser\Validator\AuthValidate(array('userField' => 'email', 'authenticator' => $this->authenticator));
            $this->filter->add($factory->createInput(array(
                        'name' => 'password',
                        'required' => true,
                        'validators' => array($this->authValidator)
            )));
            $this->filter->add($factory->createInput(array(
                        'name' => 'rememberme',
                        'required' => false,
            )));
        }
        return $this->filter;
    }

    public function getAuthResult()
    {
        return $this->authValidator->getAuthResult();
    }

}
