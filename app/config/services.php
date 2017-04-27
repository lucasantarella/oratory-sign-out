<?php

use Phalcon\Crypt;
use Phalcon\Events\Manager;
use Phalcon\Loader;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;


/**
 * Shared configuration service
 */
$di->setShared('config', function () {
	return include APP_PATH . "/config/config.php";
});

/**
 * Logger for access everywhere.
 */
$di->setShared('logger', function () {
	$logger = new File(BASE_PATH . "/logs/system.log");

	return $logger;
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () use ($di) {
	$config = $this->getConfig();

	$class = 'Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;

	$eventsManager = new Manager();

	$logger = $di->getShared('logger');

	// Listen all the database events
	$eventsManager->attach('db', function ($event, $connection) use ($logger) {
		if ($event->getType() == 'beforeQuery') {
			$logger->log($connection->getSQLStatement(), Logger::INFO);
		}
	});

	$connection = new $class([
		'host' => $config->database->host,
		'username' => $config->database->username,
		'password' => $config->database->password,
		'dbname' => $config->database->dbname,
		'charset' => $config->database->charset
	]);

	$connection->setEventsManager($eventsManager);

	return $connection;
});

/**
 * Configure the Volt service for rendering .volt templates
 */
$di->setShared('voltShared', function ($view) {
	$config = $this->getConfig();

	$volt = new VoltEngine($view, $this);
	$volt->setOptions([
		'compiledPath' => function($templatePath) use ($config) {

			// Makes the view path into a portable fragment
			$templateFrag = str_replace($config->application->appDir, '', $templatePath);

			// Replace '/' with a safe '%%'
			$templateFrag = str_replace('/', '%%', $templateFrag);

			return $config->application->cacheDir . 'volt/' . $templateFrag . '.php';
		}
	]);

	return $volt;
});

/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->setShared('modelsMetadata', function () {
	return new MetaDataAdapter();
});

$di->setShared('crypt', function () {
	$crypt = new Crypt();
	$crypt->setKey('c^J5u[6Nhlj1"M18l}+wYY9Png:cagwe');
	return $crypt;
});