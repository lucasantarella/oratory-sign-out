<?php
/**
 * Created by PhpStorm.
 * User: lucasantarella
 * Date: 8/19/16
 * Time: 10:25 AM
 */

namespace Oratorysignout\Modules\Api\Controllers;

use DateTime;
use Phalcon\Logger;
use Phalcon\Mvc\Controller;

/**
 * This controller is just an override from the template. Any specific functions needed should go here.
 * Class ControllerBase
 * @package Doobydude\Modules\Api\Controllers
 */
class ControllerBase extends Controller
{

	/**
	 * @param mixed $responseBody
	 * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
	 */
	public function sendBadRequest($responseBody = null)
	{
		if (is_null($responseBody))
			$responseBody = [
				"status" => "Error",
				"status_details" => "The request did not meet the proper format.",
				"status_type" => "bad_request"
			];

		return $this->sendResponse($responseBody, 400, "Bad Request");
	}

	/**
	 * @param mixed $response
	 * @param int $statusCode
	 * @param string $statusMessage
	 * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
	 */
	public function sendResponse($response = ['status' => "Success", 'status_details' => null, 'query' => null], $statusCode = 200, $statusMessage = "OK")
	{
		$timestamp = new DateTime('now');

		if (!is_array($response))
			$response = [$response];

		if (SHOW_DEBUG) {
			$debug = [];

			$responseTime = ceil((microtime(true) - START_TIME) * 1000);
			$this->response->setHeader("X-Response-Time", $responseTime);
			$debug['debugInfo'] = [
				"status" => $statusCode,
				"timestamp" => (int)$timestamp->format('YmdHis'),
				"responseTime" => $responseTime
			];
			$debug['request'] = ["headers" => apache_request_headers(), "body" => file_get_contents("php://input"), "getVars" => $_GET];
			$debug['debugTrace'] = debug_backtrace();
			$response['debug'] = $debug;
		}

		$this->response->setStatusCode($statusCode, $statusMessage);
		$this->response->setJsonContent($response);
		return $this->response->send();
	}

	/**
	 * @param string $responseBody
	 * @param string $contentType
	 * @param int $statusCode
	 * @param string $statusMessage
	 */
	public function sendRawResponse($responseBody = "", $contentType = "", $statusCode = 200, $statusMessage = "OK")
	{
		$responseTime = ceil((microtime(true) - START_TIME) * 1000);
		$this->response->setContentType($contentType);
		$this->response->setStatusCode($statusCode, $statusMessage);
		if (SHOW_DEBUG)
			$this->response->setHeader("X-Response-Time", $responseTime);
		$this->response->setContent($responseBody);
		$this->response->send();
	}

	/**
	 * @param string $errorMessage
	 * @param string $errorCode
	 * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
	 */
	public function sendNotFound($errorMessage = "The requested resource could not be found.", $errorCode = "not_found")
	{
		$responseBody = ["status" => "Error", "status_details" => $errorMessage, 'code' => $errorCode];

		return $this->sendResponse($responseBody, 404, "Not Found");
	}

	/**
	 * @param string $errorMessage
	 * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
	 */
	public function sendUnauthorized($errorMessage = "Client is unauthorized to access the requested resource. This could be because you not allowed access.")
	{
		return $this->sendResponse([
			'status' => 'Error',
			'status_details' => $errorMessage
		], 401, "Forbidden"); # Overriding 200 for Pat
	}

	/**
	 * @param string $errorMessage
	 * @param string $errorCode
	 */
	public function sendUnauthenticated($errorMessage = "Client is unauthorized to access the requested resource. This could be due to an invalid or expired auth token.", $errorCode = "unauthenticated")
	{
		$responseBody = ["errors" => [['message' => $errorMessage, 'type' => $errorCode]]];

		$this->sendResponse($responseBody, 401, "Unauthorized");
	}

	public function beforeExecuteRoute()
	{
		// Log the request.
		$message = "";
		$message .= "[" . $this->request->getClientAddress(false) . "]";
		$message .= "[" . $this->request->getUserAgent() . "]";
		$message .= " ";
		$message .= "[" . $this->request->getMethod() . "]";
		$message .= "[" . $this->request->getURI() . "]";
		$message .= $this->request->getRawBody();
		$this->getDI()->getShared('logger')->log(Logger::INFO, $message);

		// If the request is anything but a GET or DELETE, make sure the requestBody is JSON.
		if (
			(!$this->request->isGet() || !$this->request->isDelete()) &&
			explode(";", $this->request->getContentType())[0] != "application/json" &&
			$this->request->getJsonRawBody(true) != false
		) {
			$this->sendBadRequest('If a request is to contain a request body, it must be application/json and must have the proper Content-Type headers.');
			return false;
		}
		return true;
	}

}