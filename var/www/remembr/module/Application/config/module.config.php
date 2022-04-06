<?php

namespace Application;

return array(
    'router' => array(
        'routes' => array(
            'remembr' => array(
                'type' => 'Base\Router\Http\SkippableSegment',
                'options' => array(
                    'route' => '[/:format][/:lang][/]',
                    'constraints' => array(
                        'format' => '(?i:html|json|tpl)',
                        'lang' => '(?i:nl-be|nl|en)',
                    ),
                    'defaults' => array(
                        'format' => 'html',
                        'lang' => '',
                        'controller' => 'Cms\Controller\Page',
                        'action' => 'index',
                        'slug' => 'home'
                    ),
                    'skippable' => array(
                        'format' => true,
                        'lang' => true,
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'generic' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => ':controller[/[:action]]',
                            'constraints' => array(
                                'controller' => '(?!admin)[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'Application\Controller',
                                'controller' => 'page',
                                'action' => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'wildcard' => array(
                                'type' => 'Wildcard'
                            )
                        ),
                    ),
                    'search' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => ':controller[/[:searchterm]]',
                            'constraints' => array(
                                'controller' => 'herdenkingspaginas|memorialpages|huisdieren|pets|vips',
                                'action' => '.*',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'Application\Controller',
                                'controller' => 'search',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'page' => array(
                        'type' => 'Base\Router\Http\DatabaseLiteral', // maybe extend segment route instead?
                        'options' => array(
                            'entity' => '\Application\Entity\Page',
                            'column' => 'url',
                            'parname' => 'page',
                            'defaults' => array(
                                '__NAMESPACE__' => 'Application\Controller',
                                'controller' => 'page',
                                'action' => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'action' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/:action',
//                                                'defaults' => array(
//                                                        'controller'    => 'page',
//                                                        'action'        => 'index',
//                                                ),
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'wildcard' => array(
                                        'type' => 'Wildcard'
                                    )
                                ),
                            ),
                            'memory' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/memory/:id',
                                                'defaults' => array(
                                                        'controller'    => 'page',
                                                        'action'        => 'show',
                                                ),
                                )
                            ),
                            'edit-anonymous' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/edit-anonymous/:key',
                                    'defaults' => array(
                                        'controller'    => 'page',
                                        'action'        => 'index',
                                    ),
                                )
                            ),
                            'get-anonymous-condolence' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/get-anonymous-condolence/:key',
                                    'defaults' => array(
                                        'controller'    => 'page',
                                        'action'        => 'get-anonymous-condolence',
                                    ),
                                )
                            ),
                            'save-anonymous-condolence' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/save-anonymous-condolence/:key',
                                    'defaults' => array(
                                        'controller'    => 'page',
                                        'action'        => 'save-anonymous-condolence',
                                    ),
                                )
                            ),
                            'delete-anonymous-condolence' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/delete-anonymous-condolence/:key',
                                    'defaults' => array(
                                        'controller'    => 'page',
                                        'action'        => 'delete-anonymous-condolence',
                                    ),
                                )
                            ),
                            'subcontroller' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/:controller[/:action]',
                                    'constraints' => array(
                                        'controller' => 'settings|content'
                                    ),
                                    'defaults' => array(
                                        'controller' => 'content',
                                        'action' => 'index',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'wildcard' => array(
                                        'type' => 'Wildcard'
                                    )
                                ),
                            ),
                        ),
                    ), //
                ),
            ),
        ), // end of routes
    ),
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\CreatePage' => 'Application\Controller\CreatePageController',
            'Application\Controller\Settings' => 'Application\Controller\SettingsController',
            'Application\Controller\Landing' => 'Application\Controller\LandingController',
            'Application\Controller\User' => 'Application\Controller\UserController',
            'Application\Controller\Page' => 'Application\Controller\PageController',
            'Application\Controller\Content' => 'Application\Controller\ContentController',
            'Application\Controller\Ajax' => 'Application\Controller\AjaxController',
            'Application\Controller\Admin' => 'Application\Controller\AdminController',
            'Application\Controller\Comment' => 'Application\Controller\CommentController',
            'Application\Controller\Translation' => 'Application\Controller\TranslationController',
            'Application\Controller\Dashboard' => 'Application\Controller\DashboardController',
            'Application\Controller\Search' => 'Application\Controller\SearchController',
            'Application\Controller\Herdenkingspaginas' => 'Application\Controller\SearchController',
            'Application\Controller\Memorialpages' => 'Application\Controller\SearchController',
            'Application\Controller\Vips' => 'Application\Controller\SearchController',
            'Application\Controller\Pets' => 'Application\Controller\SearchController',
            'Application\Controller\Huisdieren' => 'Application\Controller\SearchController',
            'Application\Controller\Ajax' => 'Application\Controller\AjaxController',
            'Application\Controller\Messages' => 'Application\Controller\MessagesController',
            'Application\Controller\Notifications' => 'Application\Controller\NotificationsController',
            'Application\Controller\Sitemap' => 'Application\Controller\SitemapController',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'application/layout/layout' => __DIR__ . '/../view/layout/layout.twig',
            'application/mail/layout' => __DIR__ . '/../view/layout/mail.twig',
            'error/404' => __DIR__ . '/../view/error/404.twig',
            'error/index' => __DIR__ . '/../view/error/index.twig',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy'
        ),
    ),
    // Doctrine config
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
    // Image upload settins, apparently both shoudl have the same value, otherwise the PageController logic width > height is not sensible ... :S
    'image_upload_settings' => array(
        'width' => '1280',
        'height' => '1280',
    )
);
