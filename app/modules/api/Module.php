<?php

namespace Oratorysignout\Modules\Api;

use Phalcon\DiInterface;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Config;


class Module implements ModuleDefinitionInterface
{
	/**
	 * Registers an autoloader related to the module
	 *
	 * @param DiInterface $di
	 */
	public function registerAutoloaders(DiInterface $di = null)
	{
		$loader = new Loader();

		$loader->registerNamespaces([
			'Oratorysignout\Modules\Api\Controllers' => __DIR__ . '/controllers/',
			'Oratorysignout\Modules\Api\Models' => __DIR__ . '/models/'
		]);

		$loader->register();
	}

	/**
	 * Registers services related to the module
	 *
	 * @param DiInterface $di
	 */
	public function registerServices(DiInterface $di)
	{
		/**
		 * Try to load local configuration
		 */
		if (file_exists(__DIR__ . '/config/config.php')) {
			$override = new Config(include __DIR__ . '/config/config.php');;

			if ($config instanceof Config) {
				$config->merge($override);
			} else {
				$config = $override;
			}
		}

		/**
		 * Setting up the view component
		 */
		$di['view'] = function () {
			$view = new View();
			$view->setRenderLevel(View::LEVEL_NO_RENDER);
			return $view;
		};
	}
}
