<?php

namespace Oratorysignout\Modules\Api\Controllers;

use DateTime;
use Google_Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Stream;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use League\OAuth2\Server\Exception\OAuthServerException;
use Orthotrax\Models\Company;
use Orthotrax\Models\OauthAccessTokens;
use Orthotrax\Models\OauthClients;
use Orthotrax\Models\Users;
use Phalcon\Logger;


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
