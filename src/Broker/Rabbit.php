<?php

namespace Gen\Broker;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class RabbitMQ
 *
 * @package Gen\Broker
 */
class Rabbit
{
    /**
     * Connection instance.
     *
     * @var AMQPStreamConnection
     */
    private $connection;

    /**
     * Channel queue.
     *
     * @var AMQPChannel
     */
    private $channel;

    /**
     * Queue name.
     *
     * @var string
     */
    private $queue;

    /**
     * Gets current chanel instance.
     *
     * @return AMQPChannel
     */
    public function getChannel(): AMQPChannel
    {
        return $this->channel;
    }

    /**
     * Instantiates queue with given name.
     *
     * @param string $queue Queue name.
     * @return void
     */
    public function instantiateQueue(string $queue)
    {
        $this->queue = $queue;
        $this->channel->queue_declare($this->queue, false, true, false, false);
    }

    /**
     * Rabbit constructor.
     *
     * @param string $host Connection hostname.
     * @param int $port Port for connection.
     * @param string $login Login name.
     * @param string $password Password.
     */
    public function __construct($host = 'localhost', $port = 5672, $login = 'guest', $password = 'guest')
    {
        $this->connection = new AMQPStreamConnection($host, $port, $login, $password);
        $this->channel = $this->connection->channel();
    }

    /**
     * Publishes message to the queue.
     *
     * @param string $message Message string.
     */
    public function produce(string $message)
    {
        $msg = new AMQPMessage($message,
            array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
        );

        $this->channel->basic_publish($msg, '', $this->queue);
    }

    /**
     * Destructor for closing connections.
     */
    function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}