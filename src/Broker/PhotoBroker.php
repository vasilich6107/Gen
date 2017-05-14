<?php

namespace Gen\Broker;

use Gen\Exception\ApiException;
use Gen\Exception\ImportException;
use Gen\Import\Social\Vk\Photo\Photo;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class PhotoBroker
 * Produces and consumes messages in order to proceed photos import from album.
 *
 * @package Gen\Broker
 */
class PhotoBroker extends Broker
{
    /**
     * Queue name
     */
    const QUEUE = Photo::API_ENDPOINT;

    /**
     * User ID to import album for.
     *
     * @var string|int
     */
    public $user_id;

    /**
     * Album ID to imports photos from.
     *
     * @var int
     */
    public $album_id;

    /**
     * Amount of items in the album.
     *
     * @var int
     */
    public $size;

    /**
     * Offset to import.
     *
     * @var int
     */
    public $offset = 0;

    /**
     * Amount of photos to import.
     *
     * @var int
     */
    public $count = 30;

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->offset = 0;
        $this->broker->instantiateQueue(self::getQueueName());

        $amount = ceil($this->size / $this->count);

        for ($i = 0; $i < $amount; $i++) {
            // Vk public API limits query rate to 3 per second.
            // I didn't have much time to play around with delayed messaging.
            // So I decided to make this life hack, sorry)
            sleep(1);
            $this->broker->produce(self::message());
            $this->offset += $this->count;
        }
    }

    /**
     * @inheritdoc
     */
    public function message():string
    {
        return json_encode([
            'owner_id' => $this->user_id,
            'album_id' => $this->album_id,
            'offset' => $this->offset,
            'count' => $this->count
        ]);
    }

    /**
     * Consumes message and perform photos import.
     *
     * @param AMQPMessage $message
     */
    public function consume(AMQPMessage $message)
    {
        $photo = new Photo();

        try {
            foreach ($photo->import($message->body) as $photo_data) {
                echo " [*] Photo Imported \n";
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