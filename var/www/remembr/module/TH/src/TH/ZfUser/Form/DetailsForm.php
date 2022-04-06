<?php
namespace TH\ZfUser\Form;

use Zend\Form\Form;

class DetailsForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct("Ask details");
        $this->setAttribute('method', 'post');

       $this->add(array(
			'name' => 'email',
			'attributes' => array('type' => 'email', 'placeholder' => 'Enter your e-mail address', 'size' => '25', 'autocomplete' => 'off'),
			'options' => array('label' => 'E-mail address')
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
