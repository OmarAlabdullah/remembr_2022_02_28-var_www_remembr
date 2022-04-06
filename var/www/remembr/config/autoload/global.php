<?php

/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */
return array(
    // ...

    'doctrine_factories' => array(
        'configuration' => 'TH\ZfBase\Doctrine\ConfigurationFactory',
    ),
    'doctrine' => array(
        'connection' => array(
            'orm_default' => array(
                'params' => array(
                    'charset' => 'utf8',
                    'driverOptions' => array(
                        1002 => 'SET NAMES utf8'
                    ),
                ),
            ),
        ),
        'configuration' => array(
            'orm_default' => array(
                'filters' => array(
                    'soft-deleteable' => 'Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter'
                ),
                'metadata_cache' => 'filesystem', // default is 'array'
                'class_metadata_factory_name' => '\TH\ZfBase\Doctrine\ClassMetadataFactory',
                'excluded_entities' => array(
                    'TH\ZfUser\Entity\UserProfile' => true
                )
            ),
//			'orm_default' => array(
//				'metadata_cache'    => 'doc_apc',
//				'metadata_cache' => 'filesystem',
//				'query_cache'       => 'doc_apc',
//				'result_cache'      => 'doc_apc',
//			)
        ),
        'eventmanager' => array(
            'orm_default' => array(
                'subscribers' => array(
                    'Gedmo\Tree\TreeListener',
                    'Gedmo\Timestampable\TimestampableListener',
                    'Gedmo\SoftDeleteable\SoftDeleteableListener',
                ),
            ),
        ),
        'entity_resolver' => array(
            // configuration for the `doctrine.entity_resolver.orm_default` service
            'orm_default' => array(
                'resolvers' => array(
                    'TH\ZfUser\Entity\UserProfile' => 'Application\Entity\UserProfile'
                )
            )
        ),
        'migrations_configuration' => array(
            'orm_default' => array(
                'directory' => 'migrations',
                'name' => 'Doctrine Database Migrations',
                'namespace' => 'Migrations',
                'table' => 'doctrine_migration_versions',
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => array(
//			'Zend\Db\Adapter\Adapter'  =>  'Zend\Db\Adapter\AdapterServiceFactory',
            'Zend\Log' => function($sm) {
                $log = new \Zend\Log\Logger();
                $writer = new \Base\Util\DoctrineWriter($sm->get('doctrine.entitymanager.orm_default'));
                $log->addWriter($writer);
                return $log;
            },
            'SxMail\Service\SxMail' => function($sm) {
                $config = $sm->get('config');
                $sxmailConfig = !empty($config['sxmail']) ? $config['sxmail'] : array();
                return new \SxMail\Service\SxMailService($sm->get('ZfcTwigRenderer'), $sxmailConfig);
            },
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
//			'doctrine.cache.doc_apc' => function ($sm) { return new \Doctrine\Common\Cache\ApcCache();},
            'Banner\Twig\TwigBanner' => function($sm) {
                $config = $sm->get('config');
                $em = $sm->get('doctrine.entitymanager.orm_default');
                return new \Banner\Twig\TwigBanner($config['banner'], $em);
            },
            'th_entitymanager' => 'TH\ZfBase\Doctrine\EntityManagerFactory',
        ),
    ),
    'sxmail' => array(
        'configs' => array(
            'default' => array(
                'message' => array(
                    'layout' => 'application/mail/layout'
                ),
            )
        )
    ),
    'view_helpers' => array(
        'factories' => array(
            'url' => function($sm) {

                $locator = $sm->getServiceLocator();
                $url = new \Base\Util\UrlViewHelper();

                $router = \Zend\Console\Console::isConsole() ? 'HttpRouter' : 'Router';
                $url->setRouter($locator->get($router));

                $match = $locator->get('application')->getMvcEvent()->getRouteMatch();

                if ($match instanceof \Zend\Mvc\Router\RouteMatch)
                {
                    $url->setRouteMatch($match);
                }
                return $url;
            },
            // @TODO: do not know if this is a good solution. The url view helper does not work in de console route.
            'cron_url' => function($sm) {
                $helper = new \Zend\View\Helper\BasePath();
                $helper->setBasePath('http://www.remembr.com/');

                return $helper;
            },
        ),
    ),
    'module_layouts' => array(
        'Application' => 'application/layout/layout',
        'Admin' => 'admin/layout/layout',
    ),
    'zfctwig' => array(
        'disable_zf_model' => false,
        'environment_options' => array(
            'cache' => __DIR__ . '/../../data/twigcache',
        ),
        'extensions' => array(
            'twigBase' => '\Base\Twig\TwigBase',
            'twigAuth' => '\Auth\Twig\TwigAuth',
            'twigBanner' => '\Banner\Twig\TwigBanner'
        )
    ),
    'translator' => array(
//		'locale' => 'en_US',
        'locale' => 'nl_NL',
        'translation_file_patterns' => array(
            array(
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../../language',
                'pattern' => '%s.mo',
            ),
        ),
    ),
    'application' => array(
        // application version
        'version' => '1.1.1',
        'date' => date('Y-m-d', filemtime(__FILE__)),
    ),
    'google' => array(
        'analytics' => array(
            // 'id' => 'UA-XXXX-Y',	// set this in local
            'options' => 'auto'  // can be map with cookieDomain, cookieName etc.
        // https://developers.google.com/analytics/devguides/collection/analyticsjs/advanced
        )
    )
);
