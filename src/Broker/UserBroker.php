<?php

namespace Gen\Broker;

use Gen\Exception\ApiException;
use Gen\Exception\ImportException;
use Gen\Import\Social\Vk\User;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class UserBroker
 * Produces and consumes messages in order to proceed user import.
 *
 * @package Gen\Broker
 */
class UserBroker extends Broker
{
    /**
     * Queue name
     */
    const QUEUE = User::API_ENDPOINT;

    /**
     * User ID to import.
     *
     * @var string|int
     */
    public $user_id;

    /**
     * @inheritdoc
     */
    public function message():string
    {
        return json_encode([
            'user_ids' => $this->user_id
        ]);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->broker->instantiateQueue(self::getQueueName());
        $this->broker->produce(self::message());
    }

    /**
     * Consumes message and perform user import.
     *
     * @param AMQPMessage $message
     */
    public function consume(AMQPMessage $message)
    {
        $user = new User();

        try {
            foreach ($user->import($message->body) as $user_data) {
                $album_broker = new AlbumBroker();
                $album_broker->user_id = $user_data->user_id;
                $album_broker->execute();

                echo " [*] User Imported\n";
            }
        } catch (ImportException $e) {
            echo " [*] ", $e->getMessage(), "\n";
        } catch (ApiException $e) {
            echo " [*] ", $e->getMessage(), "\n";
        } finally {
            parent::consume($message);
        }
    }
}