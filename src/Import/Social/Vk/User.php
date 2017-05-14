<?php

namespace Gen\Import\Social\Vk;

use Gen\Db;
use Gen\Exception\ImportException;
use Gen\Import\Import;

/**
 * Class User
 * Imports Vk user.
 *
 * @package Gen\Import\Social\Vk
 */
class User extends Import
{
    /**
     * API request endpoint.
     */
    const API_ENDPOINT = 'users.get';

    /**
     * User ID.
     *
     * @var string|int
     */
    public $user_id;

    /**
     * User's first name.
     *
     * @var string
     */
    public $first_name;

    /**
     * User's last name.
     *
     * @var string
     */
    public $last_name;

    /**
     * Imports user.
     *
     * @param string $message
     * @return \Generator
     */
    public function import(string $message):\Generator
    {
        $user_response = Vk::request(User::API_ENDPOINT, $message);

        foreach ($user_response as $user_data) {
            $this->user_id = $user_data['id'];
            $this->first_name = $user_data['first_name'];
            $this->last_name = $user_data['last_name'];

            $this->save();

            yield $this;
        }
    }

    /**
     * Puts user data to DB.
     *
     * @throws ImportException In case of DB errors.
     */
    public function save()
    {
        try {
            $stm = Db::instanceGet()->prepare('
                INSERT INTO user(id, first_name, last_name)
                VALUES(:id, :first_name, :last_name)
                ON DUPLICATE KEY UPDATE
                first_name=VALUES(first_name),
                last_name=VALUES(last_name)
            ');

            $stm->execute([
                'id' => $this->user_id,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name
            ]);
        } catch (\PDOException $e) {
            throw new ImportException('Error while importing user to database');
        }
    }
}