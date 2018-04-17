<?php

namespace Oratorysignout\Modules\Cli\Tasks;

use Google_Client;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\WebSocket\MessageComponentInterface;
use Ratchet\WebSocket\WsServerInterface;
use React\EventLoop\Factory;

class MainTask extends \Phalcon\Cli\Task
{
    public function mainAction()
    {
        $loop = Factory::create();
        $pusher = new WebsocketController;

        $webSock = new \React\Socket\Server('0.0.0.0:9090', $loop); // Binding to 0.0.0.0 means remotes can connect
        $webServer = new \Ratchet\Server\IoServer(
            new \Ratchet\Http\HttpServer(
                new \Ratchet\WebSocket\WsServer(
                    new WebsocketController()
                )
            ),
            $webSock
        );

        $loop->run();

    }


}

class WebsocketController implements MessageComponentInterface, WsServerInterface
{

    /**
     * When a new connection is opened it will be passed to this method
     * @param  ConnectionInterface $conn The socket/connection that just connected to your application
     * @throws \Exception
     */
    function onOpen(ConnectionInterface $conn)
    {
        $cookiesRaw = $conn->httpRequest->getHeader('Cookie');

        if (count($cookiesRaw)) {
            $cookiesArr = \GuzzleHttp\Psr7\parse_header($cookiesRaw)[0]; // Array of cookies
        }

        $client = new Google_Client();
        $result = $client->verifyIdToken(base64_decode($cookiesArr['gtoken']));
        if ($result === false) {
            $conn->close();
        } else
            $conn->user = $result;

        echo "User " . $conn->user['email'] . " connected" . PHP_EOL;
    }

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     * @param  ConnectionInterface $conn The socket/connection that is closing/closed
     * @throws \Exception
     */
    function onClose(ConnectionInterface $conn)
    {
        echo "Connection Closed" . PHP_EOL;
    }

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     * @param  ConnectionInterface $conn
     * @param  \Exception $e
     * @throws \Exception
     */
    function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo $e->getTraceAsString();
    }

    public function onMessage(ConnectionInterface $conn, MessageInterface $msg)
    {
        $json = json_decode($msg, true);
        if (array_key_exists('msg', $json) && $json['msg'] == 'ping')
            $conn->send(json_encode(['msg' => 'pong']));
    }

    /**
     * If any component in a stack supports a WebSocket sub-protocol return each supported in an array
     * @return array
     * @todo This method may be removed in future version (note that will not break code, just make some code obsolete)
     */
    function getSubProtocols()
    {
        return ['student', 'teacher'];
    }

}