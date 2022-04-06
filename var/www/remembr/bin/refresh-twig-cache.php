#!/usr/bin/env php
<?php

chdir(dirname(__DIR__));
require 'init_autoloader.php';

use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Service\ServiceManagerConfig;

class Bootstrap
{
	static $serviceManager;

	static public function init()
	{
		$config = require 'config/application.config.php';
		$app = Zend\Mvc\Application::init($config);
                self::$serviceManager = $app->getServiceManager();
	}

	static public function getServiceManager()
	{
		return self::$serviceManager;
	}

	static public function runTemplates()
	{
		$smConfig = self::$serviceManager->get('config');
		$templatePathStack = $smConfig['view_manager']['template_path_stack'];

		$twig = self::$serviceManager->get('Twig_Environment');
		$twig->addFilter(new \Twig_SimpleFilter('translateinto',
			function ($text, $locale) {
				return $text;
			}
		));
		$twig->clearCacheFiles();
		$twig->clearTemplateCache();
		$twig->enableAutoReload();

		foreach ($templatePathStack as $templatePath)
		{
			foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($templatePath), RecursiveIteratorIterator::LEAVES_ONLY) as $file)
			{
				// force compilation
				if ($file->isFile() && substr($file, -5) == '.twig' )
				{
					$twig->loadTemplate(str_replace($templatePath.'/', '', $file));
				}
			}
		}
		echo 'done (re)generating template-cache' . PHP_EOL;
	}
}

Bootstrap::init();
Bootstrap::runTemplates();

?>
