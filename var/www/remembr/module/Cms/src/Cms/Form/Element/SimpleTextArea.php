<?php

namespace Cms\Form\Element;

use Zend\Form\Element\Textarea;

class SimpleTextArea extends Textarea {

    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = array(
        'type' => 'textarea',
        'class' => 'no-tinymce'
    );
}