<?php

namespace Oratorysignout\Modules\Api\Controllers;


class IndexController extends ControllerBase
{

	public function indexAction()
	{
		$this->sendResponse(['test' => 'test']);
	}

	public function bugsnagAction()
	{
		throw new \Exception('Testing Bugsnag!');
		$this->sendResponse(['success' => true]);
	}

}

