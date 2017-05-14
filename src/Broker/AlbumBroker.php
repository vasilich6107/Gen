<?php

namespace Gen\Broker;

use Gen\Exception\ApiException;
use Gen\Exception\ImportException;
use Gen\Import\Social\Vk\Photo\Album;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class AlbumBroker.
 * Produces and consumes messages in order to proceed photo album import.
 *
 * @package Gen\Broker
 */
class AlbumBroker extends Broker
{
    /**
     * Queue name
     */
    const QUEUE = Album::API_ENDPOINT;

    /**
     * User ID to import album for.
     *
     * @var string|int
     */
    public $user_id;

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->broker->instantiateQueue(self::getQueueName());
        $this->broker->produce(self::message());
    }

    /**
     * @inheritdoc
     */
    public function message():string
    {
        return json_encode([
            'owner_id' => $this->user_id
        ]);
    }

    /**
     * Consumes control message and perform album import.
     * Initiate further photos import.
     *
     * @param AMQPMessage $message
     */
    public function consume(AMQPMessage $message)
    {
        $album = new Album();

        try {
            $photo_broker = new PhotoBroker();

            foreach ($album->import($message->body) as $album_data) {
                $photo_broker->user_id = $album_data->user_id;
                $photo_broker->album_id = $album_data->album_id;
                $photo_broker->size = $album_data->size;
                $photo_broker->execute();

                echo " [*] Album imported\n";
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