<?php


ini_set("memory_limit", "250M");

/**
 * Local Configuration Override
 *
 * This configuration override file is for overriding environment-specific and
 * security-sensitive configuration information. Copy this file without the
 * .dist extension at the end and populate values as needed.
 *
 * @NOTE: This file is ignored from Git by default with the .gitignore included
 * in ZendSkeletonApplication. This is a good practice, as it prevents sensitive
 * credentials from accidentally being committed into version control.
 */

return array(
    // Whether or not to enable a configuration cache.
    // If enabled, the merged configuration will be cached and used in
    // subsequent requests.
    //'config_cache_enabled' => false,
    // The key used to create the configuration cache file name.
    //'config_cache_key' => 'module_config_cache',
    // The path in which to cache merged configuration.
    //'cache_dir' =>  './data/cache',
    // ...

    'doctrine' => array(
        'connection' => array(
            'orm_default' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                'params' => array(
//                    'host'     => 'th-static01-ax.priv.tgho.nl',
                    'host'     => 'rm-prod-db.priv.tgho.nl',
                    'port'     => '3306',
                    'user'     => 'remembr-admin',
                    'password' => 'AbBiHnVZ9Tv1IyOFC9DE',
                    'dbname'   => 'remembr',
                )
            )
        )
    ),

    'sxmail' => array(
        'configs' => array(
            'default' => array(
                'message' => array(
                    'options' => array(
                        'from'  => 'info@remembr.com',
                    ),
                ),
            ),
        ),
    ),


/*
    'sxmail' => array(
        'configs' => array(
            'default' => array(
                'transport' => array(
                    'type'      => 'smtp',
                    'options'   => array(
                        'name'              => 'smtp.gmail.com',
                        'host'              => 'smtp.gmail.com',
                        'connection_class'  => 'login',
                        'connection_config' => array(
                            'ssl' => 'tls',
                            'port' => 587,
                            'username' => '****@gmail.com',
                            'password' => '****',
                        ),
                    ),
                ),
            ),
        ),
    ),
*/
    'zfctwig' => array(
        'environment_options' => array(
            'auto_reload' => true,
            'debug' => true
        ),
    ),

     'banner' => array(
	'bannerDir' => './public_html/public/images/banners/',
	'bannerWebDir' => '/public/images/banners/'
      ),

	'google' => array(
		'analytics' => array(
			'id' => 'UA-27843856-2',
			'option' => 'remembr.com'
		)
    ),

	'TH' => array(
		'ZfMinify' => array(
			'requestoptions' => array(
				'image' => array(
					'checkReferrer' => false,
					'numberOfAllowedFilters' => 10,
					'allowedFiltersTimeLimit' => 60
				)
			)
		)
	),
	'included_resources' => array(
		'style' => array('debug' => false),
		'script' => array('debug' => false)
	),


);

