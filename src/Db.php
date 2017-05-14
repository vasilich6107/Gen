<?php

namespace Gen;

use PDO;

/**
 * Database
 *
 * @package Gen
 */
class Db
{
    /**
     * Database connection
     *
     * @var null|PDO
     */
    private static $instance = null;

    /**
     * Db constructor.
     */
    private function __construct()
    {
    }

    /**
     * Instantiates database connection.
     *
     * @return PDO
     */
    public static function instanceGet(): PDO
    {
        if (self::$instance === null) {
            $opt = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            self::$instance = new PDO('mysql:host=localhost;dbname=gen;charset=utf8', 'root', 'lkchpy91', $opt);
        }

        return self::$instance;
    }
}