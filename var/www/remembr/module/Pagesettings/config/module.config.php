<?php

namespace Pagesettings;

return array(
    'router' => array(
        /*
        'routes' => array(
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
         */
    ),
    'controllers' => array(
        'invokables' => array(
            'Pagesettings\Controller\Admin' => 'Pagesettings\Controller\AdminController',
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
