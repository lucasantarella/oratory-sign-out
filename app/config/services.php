<?php

use Phalcon\Events\Manager;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;

/**
 * DotEnv Setup
 */
try {
    $dotenv = new Dotenv\Dotenv(APP_PATH);
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    // Do nothing if no env is supplied...
}
$adminIps = explode(',', getenv("ADMIN_IPS"));

define('IS_ADMIN', isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], $adminIps));
define('SHOW_ERRORS', filter_var(getenv('ERRORS'), FILTER_VALIDATE_BOOLEAN) && IS_ADMIN && (isset($_GET['errors']) && filter_var($_GET['errors'], FILTER_VALIDATE_BOOLEAN)));
define('SHOW_DEBUG', filter_var(getenv('DEBUG'), FILTER_VALIDATE_BOOLEAN) && IS_ADMIN && (isset($_GET['debug']) && filter_var($_GET['debug'], FILTER_VALIDATE_BOOLEAN)));

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
    $logger = new File(APP_PATH . "/logs/debug.log");

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
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->setShared('modelsMetadata', function () {
    $adapter = new MetaDataAdapter();
    $adapter->setStrategy(new \Phalcon\Mvc\Model\MetaData\Strategy\Annotations());
    return $adapter;
});

/**
 * Configure the Volt service for rendering .volt templates
 */
$di->setShared('voltShared', function ($view) {
    $config = $this->getConfig();

    $volt = new VoltEngine($view, $this);
    $volt->setOptions([
        'compiledPath' => function ($templatePath) use ($config) {
            $basePath = $config->application->appDir;
            if ($basePath && substr($basePath, 0, 2) == '..') {
                $basePath = dirname(__DIR__);
            }

            $basePath = realpath($basePath);
            $templatePath = trim(substr($templatePath, strlen($basePath)), '\\/');

            $filename = basename(str_replace(['\\', '/'], '_', $templatePath), '.volt') . '.php';

            $cacheDir = $config->application->cacheDir;
            if ($cacheDir && substr($cacheDir, 0, 2) == '..') {
                $cacheDir = __DIR__ . DIRECTORY_SEPARATOR . $cacheDir;
            }

            $cacheDir = realpath($cacheDir);

            if (!$cacheDir) {
                $cacheDir = sys_get_temp_dir();
            }

            if (!is_dir($cacheDir . DIRECTORY_SEPARATOR . 'volt')) {
                @mkdir($cacheDir . DIRECTORY_SEPARATOR . 'volt', 0755, true);
            }

            return $cacheDir . DIRECTORY_SEPARATOR . 'volt' . DIRECTORY_SEPARATOR . $filename;
        }
    ]);

    return $volt;
});