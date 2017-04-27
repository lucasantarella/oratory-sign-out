<?php

use Phalcon\Loader;

$loader = new Loader();

/**
 * Register Namespaces
 */
$loader->registerNamespaces([
	'Oratorysignout\Models' => APP_PATH . '/common/models/',
	'Oratorysignout' => APP_PATH . '/common/library/',
]);

/**
 * Register module classes
 */
$loader->registerClasses([
	'Oratorysignout\Modules\Frontend\Module' => APP_PATH . '/modules/frontend/Module.php',
	'Oratorysignout\Modules\Api\Module' => APP_PATH . '/modules/api/Module.php',
	'Oratorysignout\Modules\Cli\Module' => APP_PATH . '/modules/cli/Module.php'
]);

$loader->register();