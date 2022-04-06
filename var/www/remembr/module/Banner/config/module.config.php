<?php

namespace Banner;

return array(
	'router' => array(
		'routes' => array(
			/*
			'banners' => array(
				'type'    => 'Zend\Mvc\Router\Http\Segment',
				'options' => array(
					'route'    => '/banner[/[:controller[/[:action[/:id]]]]]',
					'constraints' => array(
						'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
					),
					'defaults' => array(
						'__NAMESPACE__' => 'Banner\Controller',
						'controller'    => 'admin',
						'action'        => 'index',
					),
				),
			),
		  */
			'banners-redirect' => array(
				'type'    => 'Zend\Mvc\Router\Http\Segment',
				'options' => array(
					'route'    => '/banner/redirect/[:id]',
					'defaults' => array(
						'__NAMESPACE__' => 'Banner\Controller',
						'controller'    => 'redirect',
						'action'        => 'redirect',
					),
				),
			),
		),
	),
	'controllers' => array(
		'invokables' => array(
			'Banner\Controller\Admin' => 'Banner\Controller\AdminController',
			'Banner\Controller\Redirect' => 'Banner\Controller\RedirectController',
		),
	),

	'view_manager' => array(
		'template_path_stack' => array(
			__DIR__ . '/../view',
		),
	),

	'doctrine' => array(
		'driver' => array(
			__NAMESPACE__ . '_driver' => array(
				'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
				'cache' => 'array',
				'paths' => array(__DIR__ . '/../src/'.__NAMESPACE__.'/Entity'),
			),
			'orm_default' => array(
				'drivers' => array(
					__NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
				)
			)
		),
	),
);

?>
