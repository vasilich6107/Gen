<?php

namespace Gen\Import\Social\Vk\Photo;

use Gen\Db;
use Gen\Exception\ImportException;
use Gen\Import\Import;
use Gen\Import\Social\Vk\Vk;

/**
 * Class Album
 * Imports photo album data.
 *
 * @package Gen\Import\Social\Vk\Photo
 */
class Album extends Import
{
    /**
     * API request endpoint.
     */
    const API_ENDPOINT = 'photos.getAlbums';

    /**
     * User ID.
     *
     * @var string|int
     */
    public $user_id;

    /**
     * Album ID.
     *
     * @var int
     */
    public $album_id;

    /**
     * Album name.
     *
     * @var string
     */
    public $name;

    /**
     * Amount of photos in the album.
     *
     * @var int
     */
    public $size;

    /**
     * Imports album data.
     *
     * @param string $message
     * @return \Generator
     */
    public function import(string $message):\Generator
    {
        $album_response = Vk::request(self::API_ENDPOINT, $message);

        foreach ($album_response['items'] as $album_data) {
            $this->user_id = $album_data['owner_id'];
            $this->album_id = $album_data['id'];
            $this->name = $album_data['title'];
            $this->size = $album_data['size'];

            $this->save();

            yield $this;
        }
    }

    /**
     * Puts album data to DB.
     *
     * @throws ImportException In case of DB errors.
     */
    public function save()
    {
        try {
            $stm = Db::instanceGet()->prepare('
                INSERT INTO album(id, user_id, name) 
                VALUES(:id, :user_id, :name) 
                ON DUPLICATE KEY UPDATE 
                user_id=VALUES(user_id),
                name=VALUES(name)
            ');

            $stm->execute([
                'id' => $this->album_id,
                'user_id' => $this->user_id,
                'name' => $this->name
            ]);
        } catch (\PDOException $e) {
            throw new ImportException('Error while importing photos to database');
        }
    }
}