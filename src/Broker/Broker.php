<?php

namespace Gen\Broker;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class Broker
 *
 * @package Gen\Broker
 */
abstract class Broker
{
    /**
     * Queue name.
     */
    const QUEUE = null;

    /**
     * Message broker instance.
     *
     * @var Rabbit|null
     */
    protected $broker = null;

    /**
     * Broker constructor.
     */
    public function __construct()
    {
        $this->broker = new Rabbit();
    }

    /**
     * Gets name for the message queue.
     *
     * @return string Queue name.
     */
    public static function getQueueName():string
    {
        return static::QUEUE;
    }

    /**
     * Executes message publishing to the queue.
     *
     * @return void
     */
    abstract public function execute();

    /**
     * Provides message body for publishing in the queue.
     *
     * @return string Message body.
     */
    abstract public function message():string;

    /**
     * Listens queue for incoming messages.
     *
     * @return void
     */
    public function listen()
    {
        $this->broker->instantiateQueue(static::getQueueName());

        $channel = $this->broker->getChannel();

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume(
            static::getQueueName(),
            '',
            false,
            false,
            false,
            false,
            [$this, 'consume']
        );

        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }

    /**
     * Proceeds and acknowledges message.
     *
     * @param AMQPMessage $message
     */
    public function consume(AMQPMessage $message)
    {
        /** @var AMQPChannel $channel */
        $channel = $message->delivery_info['channel'];
        $channel->basic_ack($message->delivery_info['delivery_tag']);
    }
}