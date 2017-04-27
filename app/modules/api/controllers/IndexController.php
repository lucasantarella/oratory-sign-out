<?php

namespace Oratorysignout\Modules\Api\Controllers;

class IndexController extends ControllerBase
{

	public function indexAction()
	{
		$this->response->setJsonContent(['test' => 'test']);
		$this->response->send();
	}

}

