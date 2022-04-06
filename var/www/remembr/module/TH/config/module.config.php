<?php

namespace TH;

return array(
    'controller_plugins' => array(
        'factories' => array(
            'resolveEntity' => 'TH\\ZfBase\\Controller\\Plugin\\Service\\ResolveEntityFactory',
            'hybridauth' => 'TH\ZfUser\Controller\Plugin\Hybridauth',
            'loginApi' => 'TH\ZfUser\Controller\Plugin\LoginApi',
            'oauth2' => 'TH\ZfUser\Controller\Plugin\OAuth2'
        )
    ),
    'router' => array(
        'routes' => array(
            'provider' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/provider[/:action][/:provider]',
                    'constraints' => array(
                        '__NAMESPACE__' => 'TH\ZfUser',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'provider' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'TH\ZfUser',
                        'controller' => 'Provider',
                    ),
                ),
            ),
            'account' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '[/:format][/:lang]/account[/:action][/:formid]',
                    'constraints' => array(
                        '__NAMESPACE__' => 'TH\ZfUser',
                        'format' => '(?i:html|json|tpl)',
                        'lang' => '(?i:nl-be|nl|en)',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'formid' => '[a-zA-Z0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'TH\ZfUser',
                        'controller' => 'Account',
                        'action' => 'login',
                    ),
                ),
            ),

            'sociallogin' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/sociallogin[/:action][/:provider]',
                    'constraints' => array(
                        '__NAMESPACE__' => 'TH\ZfUser',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'provider' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'TH\ZfUser',
                        'controller' => 'Sociallogin',
                    ),
                ),
            ),
            'ajax' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/th-user/ajax[/:action][/:provider]',
                    'constraints' => array(
                        '__NAMESPACE__' => 'TH\ZfUser',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'provider' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'TH\ZfUser',
                        'controller' => 'Ajax',
                    ),
                ),
            ),
            'demo' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/demo[/:action]',
                    'constraints' => array(
                        '__NAMESPACE__' => 'TH\ZfUser',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'TH\ZfUser',
                        'controller' => 'Demo',
                        'action' => 'index',
                    ),
                ),
            ),
            'apidemo' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/apidemo[/:action]',
                    'constraints' => array(
                        '__NAMESPACE__' => 'TH\ZfUser',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'TH\ZfUser',
                        'controller' => 'Apidemo',
                        'action' => 'index',
                    ),
                ),
            ),
            'scn-social-auth-hauth' => array(
                'type' => 'Literal',
                'priority' => 2000,
                'options' => array(
                    'route' => '/sociallogin/hauth',
                    'defaults' => array(
                        '__NAMESPACE__' => 'TH\ZfUser',
                        'controller' => 'Sociallogin',
                        'action' => 'hauth',
                    ),
                ),
            ),
			'ThQrCode' => array(
				'type'    => 'Zend\Mvc\Router\Http\Segment',
				'options' => array(
					'route'    => '/qr/:qrid',
					'constraints' => array(
						'qrid'     => '[[:alnum:]\.]+',
					),
					'defaults' => array(
						'__NAMESPACE__' => 'TH\ZfQrCode',
                        'controller' => 'QrCode',
                        'action' => 'qrcode',
					),
				),
			),
            'ThZfMinify' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/minify',
                    'defaults' => array(
						'__NAMESPACE__' => 'TH\ZfMinify',
                        'controller' => 'Minify',
                        'action' => 'minify',
                    ),
                ),
            ),
			'ThPayment-overview' => array(
				'type' => 'Zend\Mvc\Router\Http\Segment',
				'options' => array(
					'route' => '/payment[/:controller][/:action][/:id]',
					'defaults' => array(
						'__NAMESPACE__' => 'TH\ZfPayment',
						'controller' => 'Overview',
						'action' => 'index',
					),
				),
			),
			'ThPayment-callback' => array(
				'type' => 'Zend\Mvc\Router\Http\Segment',
				'options' => array(
					'route' => '/payment/cb[/:order]',
					'defaults' => array(
						'__NAMESPACE__' => 'TH\ZfPayment',
						'controller' => 'Callback',
						'action' => 'callback',
					),
				),
			),
			'ThPayment-test' => array(
				'type' => 'Zend\Mvc\Router\Http\Segment',
				'options' => array(
					'route' => '/payment',
					'defaults' => array(
						'__NAMESPACE__' => 'TH\ZfPayment',
						'controller' => 'Test',
						'action' => 'index',
					),
				),
			),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'TH\ZfMinify\Minify' => 'TH\ZfMinify\Controller\MinifyController',
			'TH\ZfPayment\Callback' => 'TH\ZfPayment\Controller\CallbackController',
			'TH\ZfUser/Sociallogin' => 'TH\ZfUser\Controller\SocialloginController',
			'TH\ZfPayment\Test' => 'TH\ZfPayment\Controller\TestController',
			'TH\ZfPayment\Overview' => 'TH\ZfPayment\Controller\OverviewController',
            'TH\ZfQrcode\QrCode' => 'TH\ZfQrcode\Controller\QrCodeController',
            'TH\ZfUser\Account' => 'TH\ZfUser\Controller\AccountController',
            'TH\ZfUser\Ajax' => 'TH\ZfUser\Controller\AjaxController',
            'TH\ZfUser\Provider' => 'TH\ZfUser\Controller\ProviderController',
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'HybridAuth' => 'TH\ZfUser\Service\HybridAuthFactory',
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
        ),
        'invokables' => array(
            'Zend\Session\SessionManager' => 'Zend\Session\SessionManager',
        ),
    ),
    // Doctrine config
    'doctrine' => array(
        'driver' => array(
            'TH\ZfPayment_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(),
            ),
            'TH\ZfUser_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(),
            ),
            'orm_default' => array(
                'drivers' => array(
                    'TH\ZfPayment\Entity' => 'TH\ZfPayment_driver',
                    'TH\ZfUser\Entity' => 'TH\ZfUser_driver',
                )
            )
        ),
    ),
	'TH' => array(
		'QrCode' => array(
			'path' => getcwd() . '/data/qr',
			'filetype' => 'jpg'
		),
        'ZfMinify' => array(
            'webroot' => __DIR__ . '../../public/'
        )
	),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'zfctwig' => array(
        'extensions' => array(
            'zfctwig_trans' => 'TH\ZfctwigTranslation\Extension\I18n',
        ),
        'environment_options' => array(
//            'cache' => __DIR__ . '/../../../data/twigcache',
            'auto_reload' => true,
            'debug' => true
        ),
    ),
    'view_helpers' => array(
        'factories' => array(
            'cms' => 'Cms\Service\CmsViewHelperFactory',
        ),
    ),
);

 