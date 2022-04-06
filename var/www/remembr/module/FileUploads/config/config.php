<?php
return array(
    'controllers' => array(
        'factories' => array(
            'Acelaya\Index' => function ($sm) {
                return new Acelaya\IndexController($sm->getServiceLocator()->get('Acelaya\FilesService'));
            }
        )
    ),

    'service_manager' => array(
        'factories' => array(
            'Acelaya\FilesOptions' => function ($sm) {
                $config = $sm->get('Config');
                return new Acelaya\Files\FilesOptions(isset($config['files']) ? $config['files'] : []);
            },
            'Acelaya\FilesService' => function ($sm) {
                $options = $sm->get('Acelaya\FilesOptions');
                return new Acelaya\Files\FilesService($options);
            }
        )
    ),

    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        'controller' => 'Acelaya\Index',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'upload-files' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => 'upload-files',
                            'defaults' => array(
                                'action' => 'upload'
                            )
                        )
                    ),
                    'list-files' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => 'list-files',
                            'defaults' => array(
                                'action' => 'list'
                            )
                        )
                    )
                )
            )
        )
    ),

    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => '404',
        'exception_template'       => 'error',
        'layout'                   => 'layout',
        'template_map' => array(
            'layout'    => __DIR__ . '/../view/layout.phtml',
            'index'     => __DIR__ . '/../view/index.phtml',
            '404'       => __DIR__ . '/../view/404.phtml',
            'error'     => __DIR__ . '/../view/error.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy'
        )
    ),

    'files' => array(
        'base_path' => 'files',
        'max_size' => '1536MB'
    )
);
