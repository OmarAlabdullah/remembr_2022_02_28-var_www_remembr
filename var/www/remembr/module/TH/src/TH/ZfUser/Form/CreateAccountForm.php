<?php

namespace TH\ZfUser\Form;

class CreateAccountForm extends \Zend\Form\Form
{

    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->setAttribute('method', 'post');

//        $this->add(array(
//            'name' => 'username',
//            'attributes' => array('type' => 'text', 'size' => '30', 'autocomplete' => 'off', 'class' => 'username', 'placeholder' => 'Username'),
//            'options' => array('label' => 'User name')
//        ));

        $this->add(array(
            'name' => 'firstname',
            'attributes' => array('type' => 'text', 'size' => '30', 'autocomplete' => 'off', 'class' => 'firstname', 'placeholder' => 'Firstname'),
            'options' => array('label' => 'First name')
        ));
        $this->add(array(
            'name' => 'lastname',
            'attributes' => array('type' => 'text', 'size' => '30', 'autocomplete' => 'off','class' => 'lastname', 'placeholder' => 'Lastname'),
            'options' => array('label' => 'Last name')
        ));

        $this->add(array(
            'name' => 'email',
            'attributes' => array('type' => 'text', 'size' => '30', 'autocomplete' => 'off','class' => 'email', 'placeholder' => 'Enter your e-mail address'),
            'options' => array('label' => 'E-mail address')
        ));
        $this->add(array(
            'name' => 'password',
            'attributes' => array('type' => 'password', 'size' => '30', 'class' => 'setpassword', 'autocomplete' => 'off','class' => 'password', 'placeholder' => 'Enter your password', 'data-ng-model' => 'pw1', 'data-pw-check' => 'pw1'),
            'options' => array('label' => 'Password')
        ));
        $this->add(array(
            'name' => 'password2',
            'attributes' => array('type' => 'password', 'size' => '30', 'autocomplete' => 'off','class' => 'password2', 'placeholder' => 'Confirm your password'),
            'options' => array('label' => 'Confirm password'),
        ));
        $this->add(array(
            'name' => 'terms',
            'type' => 'checkbox',
            //'attributes' => array('class' => 'styled rememberme'),
            'options' => array(
                'label' => 'I accept the terms and conditions and privacy and cookie policy'
            // 'checked_value' => 'true', without value here will be 1
            // 'unchecked_value' => 'false', // witll be 1
            ),
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
                        'name' => 'firstname',
                        'required' => false
            )));
            $this->filter->add($factory->createInput(array(
                        'name' => 'lastname',
                        'required' => false,
            )));
            $this->filter->add($factory->createInput(array(
                        'name' => 'email',
                        'required' => true,
            )));
             $this->filter->add($factory->createInput(array(
                        'name' => 'terms',
                        'required' => true,
            )));

            $this->filter->add($factory->createInput(array(
                        'name' => 'password',
                        'required' => true,
            )));
            $this->filter->add($factory->createInput(array(
                        'name' => 'password2',
                        'required' => true,
                        'validators' => array(
                            array('name' => 'identical', 'options' => array('token' => 'password'))
                        )
            )));

            $this->filter->add($factory->createInput(array(
                        'name' => 'title',
                        'required' => false
            )));
        }
        return $this->filter;
    }

}