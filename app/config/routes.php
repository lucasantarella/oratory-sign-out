<?php

use Phalcon\Mvc\Router;

$router = $di->get('router');

$router = new Router(false);

$router->setDefaultModule('frontend');
$router->setDefaultNamespace('Oratorysignout\Modules\Frontend\Controllers');
$router->removeExtraSlashes(false);

foreach ($application->getModules() as $key => $module) {

    $controllerNamespace = '\\Oratorysignout\\Modules\\' . explode('\\', $module["className"])[2] . '\\Controllers';

    $group = new Router\Group([
        'module' => $key,
        'namespace' => $controllerNamespace,
    ]);

    $group->setPrefix($module["className"]::getMountPath());

    foreach ($module["className"]::getRoutes() as $route) {
        if (is_array($route))
            $group->add($route['pattern'], $route['attr'], (isset($route['method'])) ? $route['method'] : 'GET');
    }

    if (count($group->getRoutes()) > 0)
        $router->mount($group);
}

$router->setDefaults(array(
    'module' => 'frontend',
    'namespace' => 'Oratorysignout\Modules\Frontend\Controllers',
    'controller' => 'errors',
    'action' => 'notFound'
));

$di->set('router', $router);