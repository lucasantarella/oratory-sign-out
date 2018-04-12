<?php

namespace Oratorysignout\Modules\Api\Controllers;

use DateTime;
use Google_Client;


class AuthRequiredControllerBase extends ControllerBase
{

	/**
	 * @var array $user
	 */
	protected $user = null;

	public function beforeExecuteRoute()
	{
		parent::beforeExecuteRoute();

		$client = new Google_Client();
        $result = $client->verifyIdToken(base64_decode($_COOKIE['gtoken']));
        if($result === false) {
            $this->sendNotFound();
            return false;
        }else
            $this->user = $result;

        return true;
	}

    /**
     * @return array
     */
    public function getUser()
    {
        return $this->user;
    }

}
