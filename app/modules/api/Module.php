<?php

namespace Oratorysignout\Modules\Api;

use Phalcon\DiInterface;
use Phalcon\Loader;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use Oratorysignout\ModuleRoutesDefinitionInterface;


class Module implements ModuleDefinitionInterface, ModuleRoutesDefinitionInterface
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
		 * Setting up the view component
		 */
		$di->set('view', function () {
			$view = new View();
			$view->setRenderLevel(View::LEVEL_NO_RENDER);
			return $view;
		});
	}

	/**
	 * @return string
	 */
	public static function getMountPath()
	{
		return '/api';
	}

	/**
	 * @return array
	 */
	public static function getRoutes()
	{
		return [
			[
				'pattern' => '/test',
				'attr' => [
					'controller' => 'index',
					'action' => 'index',
				]
			],
			[
				'pattern' => '/students',
				'attr' => [
					'controller' => 'students',
					'action' => 'students',
				]
			],
			[
				'pattern' => '/schedule',
				'attr' => [
					'controller' => 'schedules',
					'action' => 'schedule',
				]
			],
			[
				'pattern' => '/schedules',
				'attr' => [
					'controller' => 'schedules',
					'action' => 'schedules',
				]
			],
			[
				'pattern' => '/schedules/today',
				'attr' => [
					'controller' => 'schedules',
					'action' => 'schedule',
				]
			],
//			[
//				'pattern' => '/schedule/{date:[0-9]+}',
//				'attr' => [
//					'controller' => 'schedules',
//					'action' => 'schedule',
//				]
//			],
			[
				'pattern' => '/schedules/{id:[0-9]+}',
				'attr' => [
					'controller' => 'schedules',
					'action' => 'schedules',
				]
			],
			[
				'pattern' => '/schedules/{schedule_id:[0-9]+}/periods',
				'attr' => [
					'controller' => 'schedules',
					'action' => 'periods',
				]
			],
			[
				'pattern' => '/schedule/{date:[0-9]+}/periods',
				'attr' => [
					'controller' => 'schedules',
					'action' => 'periodsByDay',
				]
			],
			[
				'pattern' => '/schedule/periods',
				'attr' => [
					'controller' => 'schedules',
					'action' => 'periodsByDay',
				]
			],
			[
				'pattern' => '/schedules/{schedule_id:[0-9]+}/periods/{num:[0-9]+}',
				'attr' => [
					'controller' => 'schedules',
					'action' => 'period',
				]
			],
			[
				'pattern' => '/schedule/{date:[0-9]+}/periods/{num:[0-9]+}',
				'attr' => [
					'controller' => 'schedules',
					'action' => 'periodByDay',
				]
			],
			[
				'pattern' => '/schedule/periods/{num:[0-9]+}',
				'attr' => [
					'controller' => 'schedules',
					'action' => 'periodToday',
				]
			],
			[
				'pattern' => '/rooms',
				'attr' => [
					'controller' => 'rooms',
					'action' => 'rooms',
				]
			],
			[
				'pattern' => '/rooms/{name:[a-zA-Z0-9]+}',
				'attr' => [
					'controller' => 'rooms',
					'action' => 'room',
				]
			],
			[
				'pattern' => '/rooms/{name:[a-zA-Z0-9]+}/students',
				'attr' => [
					'controller' => 'rooms',
					'action' => 'currentStudents',
				]
			],
		];
	}

}
