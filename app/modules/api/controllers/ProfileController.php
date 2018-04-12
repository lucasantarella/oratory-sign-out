<?php
/**
 * Created by PhpStorm.
 * User: lucasantarella
 * Date: 3/22/18
 * Time: 2:10 PM
 */

namespace Oratorysignout\Modules\Api\Controllers;


use Oratorysignout\Models\Students;

class ProfileController extends AuthRequiredControllerBase
{

    public function getProfileAction()
    {
        return $this->sendResponse(Students::findFirst("email = '{$this->getUser()['email']}'"));
    }

}