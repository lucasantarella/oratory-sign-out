<?php

namespace Oratorysignout\Modules\Api\Controllers;


class IndexController extends ControllerBase
{

    public function indexAction()
    {
        $this->sendResponse(['test' => 'test']);
    }

}

