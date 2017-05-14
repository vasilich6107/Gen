<?php

namespace Gen\Import\Social\Vk\Photo;

use Gen\Db;
use Gen\Exception\ImportException;
use Gen\Import\Import;
use Gen\Import\Social\Vk\Vk;

/**
 * Class Photo.
 * Imports photo data.
 *
 * @package Gen\Import\Social\Vk\Photo
 */
class Photo extends Import
{
    /**
     * API request endpoint.
     */
    const API_ENDPOINT = 'photos.get';

    /**
     * Album ID.
     *
     * @var int
     */
    public $album_id;

    /**
     * Photo ID.
     *
     * @var int
     */
    public $photo_id;

    /**
     * Photo URL.
     *
     * @var string
     */
    public $photo_url;

    /**
     * Imports bunch of photos.
     *
     * @param string $message
     * @return \Generator
     */
    function import(string $message):\Generator
    {
        $photo_response = Vk::request(Photo::API_ENDPOINT, $message);

        foreach ($photo_response['items'] as $photo_data) {
            $this->photo_id = $photo_data['id'];
            $this->album_id = $photo_data['album_id'];

            $size = [];
            foreach ($photo_data as $key => $value) {
                if (preg_match('/photo_([\d]+)/', $key, $match)) {
                    $size[] = $match[1];
                }
            }

            $this->photo_url = $photo_data['photo_' . max($size)];

            $this->save();

            yield $this;
        }
    }

    /**
     * Puts photo data to DB.
     *
     * @throws ImportException In case of DB errors.
     */
    public function save()
    {
        try {
            $stm = Db::instanceGet()->prepare('
                INSERT INTO photo(id, album_id, url) 
                VALUES(:id, :album_id, :url) 
                ON DUPLICATE KEY UPDATE 
                album_id=VALUES(album_id),
                url=VALUES(url)
            ');

            $stm->execute([
                'id' => $this->photo_id,
                'album_id' => $this->album_id,
                'url' => $this->photo_url
            ]);
        } catch (\PDOException $e) {
            throw new ImportException('Error while importing photos to database');
        }
    }
}