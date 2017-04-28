<?php

use Parse\ParseClient;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Application;

error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('START_TIME', microtime(true));

/**
 * Composer Autoloader
 */
require_once APP_PATH . '/common/library/vendor/autoload.php';

if (!function_exists('apache_request_headers')) {
	function apache_request_headers()
	{
		$arh = array();
		$rx_http = '/\AHTTP_/';
		foreach ($_SERVER as $key => $val) {
			if (preg_match($rx_http, $key)) {
				$arh_key = preg_replace($rx_http, '', $key);
				$rx_matches = array();
				// do some nasty string manipulations to restore the original letter case
				// this should work in most cases
				$rx_matches = explode('_', $arh_key);
				if (count($rx_matches) > 0 and strlen($arh_key) > 2) {
					foreach ($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
					$arh_key = implode('-', $rx_matches);
				}
				$arh[$arh_key] = $val;
			}
		}
		return ($arh);
	}
}

$dotenv = new Dotenv\Dotenv(BASE_PATH);
try {
	$dotenv->load(); # Try and load if an env is specified
} catch (Dotenv\Exception\InvalidPathException $e) {
	// Do nothing...
}

$dotenv->required('DB_HOST');
$dotenv->required('DB_NAME');
$dotenv->required('DB_USER');
$dotenv->required('DB_PASS');
$dotenv->required('CYCLE_START_DATE');
$dotenv->required('RELEASE_STAGE')->allowedValues(['production', 'staging', 'development', 'local_dev']);
$dotenv->required('ERRORS')->allowedValues(['true', 'false']);
$dotenv->required('DEBUG')->allowedValues(['true', 'false']);
$dotenv->required('ABS_DOMAIN_URL')->notEmpty();

// Set default timezone to UTC
date_default_timezone_set('America/New_York');

// Initialize Bugsnag
$bugsnag = Bugsnag\Client::make('8c9bdbc65cc43175fe0d50abe819ca76');
$bugsnag->setReleaseStage(getenv('RELEASE_STAGE'));
Bugsnag\Handler::register($bugsnag);

$adminIps = explode(',', getenv("ADMIN_IPS"));

define('IS_ADMIN', in_array($_SERVER['REMOTE_ADDR'], $adminIps));
define('SHOW_ERRORS', filter_var(getenv('ERRORS'), FILTER_VALIDATE_BOOLEAN) && IS_ADMIN && (isset($_GET['errors']) && filter_var($_GET['errors'], FILTER_VALIDATE_BOOLEAN)));
define('SHOW_DEBUG', filter_var(getenv('DEBUG'), FILTER_VALIDATE_BOOLEAN) && IS_ADMIN && (isset($_GET['debug']) && filter_var($_GET['debug'], FILTER_VALIDATE_BOOLEAN)));

if (SHOW_ERRORS) {
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	header("X-Errors: true");
} else {
	ini_set('display_errors', false);
	ini_set('display_startup_errors', false);
}

try {

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
	 * Include web environment specific services
	 */
	require APP_PATH . '/config/services_web.php';

	/**
	 * Get config service for use in inline setup below
	 */
	$config = $di->getConfig();

	/**
	 * Include Autoloader
	 */
	include APP_PATH . '/config/loader.php';

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

	echo $application->handle()->getContent();

} catch (\Exception $e) {
	$bugsnag->notifyException($e);
}