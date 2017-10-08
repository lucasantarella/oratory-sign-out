<?php

use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Application;

error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('START_TIME', microtime(true));
date_default_timezone_set('America/New_York');

try {

	/**
	 * Include Autoloader
	 */
	include APP_PATH . '/config/loader.php';

//	timecop_freeze(mktime(12,6,0,10,3,2017));

	/**
	 * The FactoryDefault Dependency Injector automatically registers the services that
	 * provide a full stack framework. These default services can be overidden with custom ones.
	 */
	$di = new FactoryDefault();

	/**
	 * Include general services
	 */
	require APP_PATH . '/config/services.php';

	/**
	 * Get config service for use in inline setup below
	 */
	$config = $di->getConfig();

	/**
	 * Include web environment specific services
	 */
	require APP_PATH . '/config/services_web.php';

	/**
	 * Handle the request
	 */
	$application = new Application($di);

	/**
	 * Register application modules
	 */
	$application->registerModules([
		'api' => ['className' => 'Oratorysignout\Modules\Api\Module'],
		'frontend' => ['className' => 'Oratorysignout\Modules\Frontend\Module'],
	]);

	/**
	 * Include routes
	 */
	require APP_PATH . '/config/routes.php';

	echo str_replace(["\n", "\r", "\t"], '', $application->handle()->getContent());

} catch (\Exception $e) {
	echo $e->getMessage() . '<br>';
	echo '<pre>' . $e->getTraceAsString() . '</pre>';
}