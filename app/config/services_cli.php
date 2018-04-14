<?php

use Phalcon\Cli\Dispatcher;

/**
 * Set the default namespace for dispatcher
 */
$di->setShared('dispatcher', function () {
    $dispatcher = new Dispatcher();
    $dispatcher->setDefaultNamespace('Oratorysignout\Modules\Cli\Tasks');
    return $dispatcher;
});
