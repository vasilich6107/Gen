<?php

namespace Gen\Import\Social\Vk;

use Gen\Exception\ApiException;

/**
 * Class Vk
 * API wrapper.
 *
 * @package Gen\Import\Social\Vk
 */
class Vk
{
    /**
     * VK API connection.
     *
     * @var \VK\VK
     */
    private static $instance = null;

    /**
     * Vk constructor.
     */
    private function __construct()
    {
    }

    /**
     * Instantiates VK api connection.
     *
     * @return \VK\VK
     */
    public static function instanceGet():\VK\VK
    {
        if (self::$instance === null) {
            $vk = new \VK\VK(6029660, 'gapM0FyjhxfwooPRzjJf', 'f8ec94b9f8ec94b9f8ec94b9e2f8b095e5ff8ecf8ec94b9a1fa2f8d07f4284c639b11d7');
            $vk->setApiVersion(5.64);
            self::$instance = $vk;
        }

        return self::$instance;
    }

    /**
     * API request wrapper.
     *
     * @param string $api API endpoint.
     * @param string $message Request parameters.
     * @return array API response.
     *
     * @throws ApiException In case of API request errors.
     */
    public static function request(string $api, string $message):array
    {
        try {
            $vk = self::instanceGet();
            $response = $vk->api($api, json_decode($message, true));

            if (array_key_exists('error', $response)) {
                throw new ApiException($response['error']['error_msg'].' The request was ' . $message);
            }
        } catch (\RuntimeException $e) {
            throw new ApiException($e->getMessage());
        }


        return $response['response'];
    }
}