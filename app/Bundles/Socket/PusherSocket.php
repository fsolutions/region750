<?php


namespace App\Bundles\Socket;

use Illuminate\Support\Facades\Log;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;
use SplObjectStorage;
use ZMQContext;
use ZMQ;

class PusherSocket implements WampServerInterface
{
    /**
     * Subscribed clients.
     *
     * @var array
     */
    protected $subscribedTopics = [];

    /**
     * Clients.
     *
     * @var array|SplObjectStorage
     */
    protected $clients = [];

    /**
     * Logging.
     *
     * @var
     */
    protected $log = [];

    /**
     * PusherSocket constructor.
     */
    public function __construct()
    {
        $this->clients = new \SplObjectStorage;

        $this->log = Log::channel('push');
    }

    /**
     * A new client connection has been opened.
     *
     * @param ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn)
    {
        echo "New connection ({$conn->resourceId})\n";

        $this->log->info("New connection $conn->resourceId");

        // Store the new connection to send messages to later.
        $this->clients->attach($conn);
    }

    /**
     * The client has subscribed to a channel, expecting to receive events published to the given $topic.
     *
     * @param ConnectionInterface $conn
     * @param \Ratchet\Wamp\Topic|string $topic
     */
    public function onSubscribe(ConnectionInterface $conn, $topic)
    {
        $this->subscribedTopics[$topic->getId()] = $topic;

        $this->log->info("Subscribe $conn->resourceId", ['topic' => (string) $topic]);
    }

    /**
     * The client unsubscribed from a channel, opting out of receiving events from the $topic.
     *
     * @param ConnectionInterface $conn
     * @param \Ratchet\Wamp\Topic|string $topic
     */
    public function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
        //
    }

    /**
     * A client connection has been closed.
     *
     * @param ConnectionInterface $conn
     */
    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages.
        $this->clients->detach($conn);

        echo "Disconnected ({$conn->resourceId})\n";

        $this->log->info("Disconnected $conn->resourceId");
    }

    /**
     * The client has made an RPC to the server. You should send a callResult or callError in return.
     *
     * @param ConnectionInterface $conn
     * @param string $id
     * @param \Ratchet\Wamp\Topic|string $topic
     * @param array $params
     */
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params)
    {
        // In this application if clients send data it's because the user hacked around in console
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();

        $this->log->warning("Hacked console");
    }

    /**
     * The user publishes data to a $topic.
     * You should in return an Event Command to Connections who have Subscribed to the $topic.
     *
     * @param ConnectionInterface $conn
     * @param \Ratchet\Wamp\Topic|string $topic
     * @param string $event
     * @param array $exclude
     * @param array $eligible
     */
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        // In this application if clients send data it's because the user hacked around in console
        $conn->close();

        $this->log->warning("Hacked console");
    }

    /**
     * An error has occurred with a Connection.
     *
     * @param ConnectionInterface $conn
     * @param \Exception $e
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $conn->close();

        echo "Error ($conn->resourceId): {$e->getMessage()}\n";

        $this->log->warning("Error $conn->resourceId", [$e->getMessage()]);
    }

    /**
     * @param string JSON'ified string we'll receive from ZeroMQ
     */
    public function onBlogEntry($entry)
    {
        $entryData = json_decode($entry, true);

        // If the lookup topic object isn't set there is no one to publish to
        if (!array_key_exists($entryData['category'], $this->subscribedTopics)) {
            return;
        }

        $topic = $this->subscribedTopics[$entryData['category']];

        // re-send the data to all the clients subscribed to that category
        $topic->broadcast($entryData);
    }

    /**
     * Send data to server.
     *
     * @param array $data
     * @throws \ZMQSocketException
     */
    public static function sendToDataServer(array $data)
    {
        // This is our new stuff
        $context = new ZMQContext();

        $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');

        $socket->connect("tcp://localhost:5555");

        $socket->send(json_encode($data));

        Log::channel('push')->info("Notification", $data);
    }
}
