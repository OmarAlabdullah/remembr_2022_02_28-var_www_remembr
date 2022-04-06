<?php

namespace Admin;

return array(
	'router' => array(
		'routes' => array(
            // The following is a route to simplify getting started creating
			// new controllers and actions without needing to create a new
			// module. Simply drop new controllers in, and you can access them
			// using the path /application/:controller/:action
			'admin' => array(
				'type'    => 'Base\Router\Http\SkippableSegment',
				'options' => array(
					'route'    => '/admin[/:lang][/[:controller[/[:action[/:id]]]]]',
					'constraints' => array(
						'lang'		=> '(nl|en|nlbe)',
						'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
					),
					'defaults' => array(
						'__NAMESPACE__' => 'Admin\Controller',
						'lang'          => 'en',
						'controller'    => 'index',
						'action'        => 'index',
					),
					'skippable' => array(
						'lang'          => true,
					),
				),

				'may_terminate' => true,
				'child_routes' => array(
					'wildcard' => array(
						'type' => 'Wildcard'
					),
				),
			),
		),
	),

	'controllers' => array(
		'invokables' => array(
			'Admin\Controller\Index'       => 'Admin\Controller\IndexController',
			'Admin\Controller\Account'     => 'Admin\Controller\AccountController',
			'Admin\Controller\Payments'    => 'Admin\Controller\PaymentsController',
		//'Admin\Controller\Banners'     => 'Banner\Controller\AdminController',
			'Admin\Controller\Banners'     => 'Admin\Controller\BannerController',
			'Admin\Controller\Cms'         => 'Admin\Controller\CmsController',
			'Admin\Controller\Pagesettings'=> 'Admin\Controller\PagesettingsController',
			'Admin\Controller\Csv'         => 'Admin\Controller\CsvController',
			'Admin\Controller\Social'      => 'Admin\Controller\SocialController',
			'Admin\Controller\ImageUpload' => 'Admin\Controller\ImageUploadController',
				
		),
	),
	'view_manager' => array(
		'template_map' => array(
			'admin/layout/layout'=> __DIR__ . '/../view/layout/layout.twig',
		),
		'template_path_stack' => array(
				__DIR__ . '/../view',
		),
		'strategies' => array(
			'ViewJsonStrategy',
		),
	),

	// Doctrine config
	/*'doctrine' => array(
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
	),*/
);
