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

        if (getenv('TIME_OVERRIDE') !== false && extension_loaded('timecop')) {
            timecop_return();
        }

        $client = new Google_Client();
        $result = $client->verifyIdToken(base64_decode($_COOKIE['gtoken']));
        if ($result === false) {
            $this->sendNotFound();
            return false;
        } else
            $this->user = $result;

        if (getenv('TIME_OVERRIDE') !== false && extension_loaded('timecop')) {
            $time = DateTime::createFromFormat('YmdHis', getenv('TIME_OVERRIDE'));
            timecop_freeze(mktime($time->format('H'), $time->format('i'), $time->format('s'), $time->format('m'), $time->format('d'), $time->format('Y')));
        }

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
