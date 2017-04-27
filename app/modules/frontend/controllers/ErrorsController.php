<?php

namespace Oratorysignout\Modules\Frontend\Controllers;

use Phalcon\Mvc\View;

class ErrorsController extends ControllerBase
{

    public function notFoundAction()
    {
        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
        $this->response->setStatusCode(404, 'NOT FOUND');
    }

}

