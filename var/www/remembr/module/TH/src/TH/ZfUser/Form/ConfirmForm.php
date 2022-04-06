<?php

namespace TH\ZfUser\Form;

class ConfirmForm extends \Zend\Form\Form
{

    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'key',
            'attributes' => array('type' => 'hidden'),
        ));
        $this->add(array(
            'name' => 'password',
            'attributes' => array('type' => 'password', 'size' => '25', 'class' => 'setpassword', 'autocomplete' => 'off'),
            'options' => array('label' => 'Password')
        ));
        $this->add(array(
            'name' => 'password2',
            'attributes' => array('type' => 'password', 'size' => '25', 'autocomplete' => 'off'),
            'options' => array('label' => 'Repeat password')
        ));
        $this->add(array(
            'name' => 'submit',
            'attributes' => array('type' => 'submit', 'value' => 'Confirm', 'class' => 'css-button orange'),
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
        }
        return $this->filter;
    }

}

?>
