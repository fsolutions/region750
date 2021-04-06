<?php


namespace App\Bundles\Socket;

use Illuminate\Support\Facades\Log;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use GrahamCampbell\Throttle\Facades\Throttle;

class ChatSocket implements MessageComponentInterface
{
    /**
     * Clients.
     *
     * @var array|\SplObjectStorage
     */
    protected $clients = [];

    /**
     * Current connection.
     *
     * @var
     */
    protected $conn = [];

    /**
     * Logging.
     *
     * @var array|\Psr\Log\LoggerInterface
     */
    protected $log = [];

    /**
     * Connection limit
     *
     * @var array
     */
    protected $limit = 20;

    /**
     * Throttled config.
     *
     * @var array
     */
    protected $config = [
        'onOpen'    => '10:1',
        'onMessage' => '20:1',
    ];

    /**
     * ChatSocket constructor.
     */
    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->log = Log::channel('chat');
    }

    /**
     * A new client connection has been opened.
     *
     * @param ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $this->conn = $conn;

        echo "New connection: {$conn->resourceId}\n";

        $this->log->info("New connection: $conn->resourceId");

        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        $this->connectionThrottle();

        $this->limit();
    }

    /**
     * Check connections throttle.
     */
    protected function connectionThrottle()
    {
        if ($this->isThrottled($this->conn, 'onOpen'))
        {
            echo "Connection throttled: {$this->conn->resourceId} \n";

            $this->log->info("Connection throttled: {$this->conn->resourceId} ");

            $this->conn->close();
        }
    }

    /**
     * Check send message throttle.
     */
    protected function messageThrottle()
    {
        if ($this->isThrottled($this->conn, 'onMessage')) {
            echo "Message throttled: {$this->conn->resourceId} \n";

            $this->log->info("Message throttled: {$this->conn->resourceId} ");

            $this->conn->close();
        }
    }

    /**
     * Check to limit connections.
     */
    protected function limit()
    {
        if (count($this->clients) > $this->limit)
        {
            $this->log->info("Limit connection exceeded!");

            $this->conn->send('Limit connection exceeded!');

            $this->conn->close();
        }
    }

    /**
     * Check throttle.
     *
     * @param $conn
     * @param $setting
     * @return bool
     */
    protected function isThrottled($conn, $setting)
    {
        $connectionThrottle = explode(':', $this->config[$setting]);

        return !Throttle::attempt(
            [
                'ip'    => $conn->remoteAddress,
                'route' => $setting,
            ],
            (int) $connectionThrottle[0],
            (int) $connectionThrottle[1]
        );
    }

    /**
     * Send to message all clients.
     *
     * @param ConnectionInterface $from
     * @param string $msg
     */
    public function onMessage(ConnectionInterface $from, $msg)
    {
        // Count clients to send
        $numRecv = count($this->clients) - 1;

//        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
//            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');



        foreach ($this->clients as $client)
        {
            if ($from !== $client)
            {
                // The sender is not the receiver, send to each client connected
                $client->send($msg);

                $dataToLog = [
                    'client' => $client->resourceId,
                    'message' => $msg,
                    'count_clients' => $numRecv,
                    'sender' => $from->resourceId
                ];

                $this->log->info("Message:", $dataToLog);
            }
        }
    }

    /**
     * A client connection has been closed.
     *
     * @param ConnectionInterface $conn
     */
    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Disconnected ($conn->resourceId)\n";

        $this->log->info("Disconnected $conn->resourceId");
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

        echo "Error: {$e->getMessage()}\n";

        $this->log->warning("Error: $conn->resourceId", [$e->getMessage()]);
    }
}
