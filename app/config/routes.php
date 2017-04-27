<?php

use Phalcon\Mvc\Router;

$router = $di->get("router");

foreach ($application->getModules() as $key => $module) {
	$controllerNamespace = '\\Oratorysignout\\Modules\\' . explode('\\', $module["className"])[2] . '\\Controllers';

	$group = new Router\Group([
		'module' => $key,
		'namespace' => $controllerNamespace,
	]);

	header("X-{$key}: \"" . $controllerNamespace . " - " . $module["className"]::getMountPath() . "\"");

	$group->setPrefix($module["className"]::getMountPath());

	foreach ($module["className"]::getRoutes() as $route) {
		if (is_array($route))
			$group->add($route['pattern'], $route['attr']);
	}

	if (count($group->getRoutes()) > 0) {
		$router->mount($group);
		header("X-{$key}-routes: " . json_encode($module["className"]::getRoutes()));

	}
}

$di->set("router", $router);
