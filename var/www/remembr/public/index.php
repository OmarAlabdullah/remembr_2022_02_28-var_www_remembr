<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
date_default_timezone_set('Europe/Amsterdam');

chdir(dirname(__DIR__));
define('REQUEST_MICROTIME', microtime(true));
define('APPLICATION_ENV', getenv('APPLICATION_ENV') ? : 'production');

// Setup autoloading
require 'init_autoloader.php';

// if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] === "127.0.0.1")
// {
// 	$_SERVER['SERVER_NAME'] = 'herbert.dev.tgho.nl';
// 	$_SERVER['HTTP_HOST'] = 'herbert.dev.tgho.nl';
// 	$_SERVER['HTTPS'] = true;
// }

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
