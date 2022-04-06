<?php

namespace Banner\Forms;

use Banner\Entity\Banner;

class BannerForm extends \Zend\Form\Form
{
	public function __construct($name, $formats, array $config, $edit = false) {
		parent::__construct($name);

		$this->config = $config;

		$this->setAttribute('method', 'post');

		$formatValues = array();
		foreach ($formats as $v) {
			$formatValues[$v->getId()] = $v->getTitle();
		}

		$this->add(array(
			'type' => 'Zend\Form\Element\Select',
			'name' => 'format',
			'options' => array(
				'label' => 'Format',
				'value_options' => $formatValues,
				'empty_option' => 'Please select one'
			)
		));
		$this->add(array(
			'type' => 'Zend\Form\Element\Radio',
			'name' => 'type',
			'attributes' => array(),
			'options' => array('label' => 'Type', 'value_options' => array(Banner::typeHtml => 'HTML', Banner::typeImg => 'Image'))
		));
		$this->add(array(
			'type' => 'textarea',
			'name' => 'content',
			'options' => array('label' => 'Content')
		));
		$this->add(array(
			'type' => 'file',
			'name' => 'image',
			'options' => array('label' => 'Image')
		));
		$this->add(array(
			'name' => 'url',
			'attributes' => array('type' => 'text', 'placeholder' => 'Target URL for the banner'),
			'options' => array('label' => 'Url')
		));
		$this->add(array(
			'name' => 'maxViews',
			'attributes' => array(),
			'options' => array('label' => 'Max views')
		));
		$this->add(array(
			'name' => 'maxClicks',
			'attributes' => array(),
			'options' => array('label' => 'Max clicks')
		));
		$this->add(array(
			'name' => 'submit',
			'attributes' => array('type' => 'submit', 'value' => $edit ? 'Save' : 'Create', 'class' => 'css-button orange'),
			'options' => array('label' => '')
		));
	}

	public function getInputFilter()
	{
		if (!$this->filter) {
			$this->filter = new \Zend\InputFilter\InputFilter();
			$factory = new \Zend\InputFilter\Factory();

			$this->filter->add($type = $factory->createInput(array('name' => 'type')));
			$this->filter->add($factory->createInput(array('name' => 'url')));

			$this->filter->add($factory->createInput(array(
				'name' => 'format',
				'required' => true,
			)));

			$this->filter->add($factory->createInput(array(
				'name' => 'content',
				'validators' => array(
					array('name' => 'notempty', 'options' => array('null')),
					array('name' => 'callback', 'options' => array(
						'callback' => function($value) use ($type) {
							return $value || $type->getValue() != Banner::typeHtml;
						}
					))
				)
			)));

			if (!isset($this->config['banner']) || !isset($this->config['banner']['bannerDir']))
				throw new \Exception('No banner directory configured.');

			$this->filter->add($factory->createInput(array(
				'name' => 'image',
				'validators' => array(
					array('name' => 'notempty', 'options' => array('null')),
					array('name' => 'callback', 'options' => array(
						'callback' => function($value) use ($type) {
							return $value || $type->getValue() != Banner::typeImg;
						}
					))
				),
				'filters' => array(
					array('name' => 'filerenameupload', 'options' => array(
						'target' => $this->config['banner']['bannerDir'],
						'overwrite' => false,
						'randomize' => true,
						'use_upload_extension' => true
					)),
				)
			)));

			$this->filter->add($factory->createInput(array(
				'name' => 'maxViews',
				'required' => true,
				'validators' => array(
					array('name' => 'digits'),
					array('name' => 'greaterthan', 'options' => array('min' => 0, 'inclusive' => true))
				)
			)));

			$this->filter->add($factory->createInput(array(
				'name' => 'maxClicks',
				'required' => true,
				'validators' => array(
					array('name' => 'digits'),
					array('name' => 'greaterthan', 'options' => array('min' => 0, 'inclusive' => true))
				)
			)));
		}
		return $this->filter;
	}
}

?>
