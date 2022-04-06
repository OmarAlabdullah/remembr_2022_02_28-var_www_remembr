<?php

namespace Cms;

return array(
    'router' => array(
        'routes' => array(
            /*
             * Admin module handles the admin routing
             */
			
//            'home' => array(
//                'type' => 'Zend\Mvc\Router\Http\Literal',
//                'options' => array(
//                    'route' => '/',
//                    'defaults' => array(
//                        'controller' => 'Cms\Controller\Page',
//                        'action' => 'index',
//                        'slug' => 'home',
//                        'lang' => '',
//                    ),
//                ),
//            ),
            /**
             * Default content: /cmscontent/slug
             */
            'cmscontent' => array(
                'type' => 'Base\Router\Http\SkippableSegment',
                'options' => array(
                    'route' => '[/:format][/:lang]/cmscontent[/:slug][/]',
                    'constraints' => array(
                        'format' => '(?i:html|json|tpl)',
                        'lang' => '(?i:nl-be|nl|en)',
                        'slug' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'format' => 'html',
                        'lang' => '',
                        'slug' => 'home',
                        'controller' => 'Cms\Controller\Page',
                        'action' => 'index',
                    ),
                    'skippable' => array(
                        'format' => true,
                        'lang' => true,
                    ),
                ),
            ),

            'basic_content' => array(
                'type' => 'Base\Router\Http\SkippableSegment',
                'options' => array(
                    'route' => '[/:format][/:lang]/:slug[/]',
                    'constraints' => array(
                        'format' => '(?i:html|json|tpl)',
                        'lang' => '(?i:nl-be|nl|en)',
                        'slug' => '(?i:home|faq|about|over)',
                    ),
                    'defaults' => array(
                        'format' => 'html',
                        'lang' => '',
                        'slug' => 'home',
                        'controller' => 'Cms\Controller\Page',
                        'action' => 'index',
                    ),
                    'skippable' => array(
                        'format' => true,
                        'lang' => true,
                    ),
                ),
            )
        ), // end of routes
    ),
    'controllers' => array(
        'invokables' => array(
            'Cms\Controller\Page' => 'Cms\Controller\PageController',
            'Cms\Controller\Admin' => 'Cms\Controller\AdminController'
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
                'paths' => array(__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity'),
            ),
            'orm_default' => array(
                'drivers' => array(
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                )
            )
        ),
    ),
);
