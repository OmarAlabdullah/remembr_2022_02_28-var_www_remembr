<?php

namespace TH\ZfUser\Form;

use Zend\InputFilter\InputFilter;

class WallFilter extends InputFilter
{

    public function __construct()
    {
         $this->add(array(
            'name' => 'friend',
            'required' => false,
        ));

        $this->add(array(
            'name' => 'message',
            'required' => true,
        ));

        $this->add(array(
            'name' => 'description',
            'required' => true,
        ));
    }

}