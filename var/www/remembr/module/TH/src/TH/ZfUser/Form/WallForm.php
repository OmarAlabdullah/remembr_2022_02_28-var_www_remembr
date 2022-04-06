<?php

namespace TH\ZfUser\Form;

use Zend\Form\Form;

class WallForm extends Form
{

    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct("Post on Facebook Wall");
        $this->setAttribute('method', 'post');
        $this->setInputFilter(new \TH\ZfUser\Form\WallFilter());

        $this->add(array(
            'name' => 'message',
            'attributes' => array('type' => 'text', 'placeholder' => 'Your message', 'size' => '255'),
            'options' => array('label' => 'Message')
        ));

        $this->add(array(
            'name' => 'description',
            'attributes' => array('type' => 'text', 'placeholder' => 'Your description', 'size' => '255'),
            'options' => array('label' => 'Description')
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Go',
                'id' => 'submitbutton',
            ),
        ));
    }

}
