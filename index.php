<?php

require_once __DIR__ . '/handler.php';
require_once __DIR__ . '/vendor/autoload.php';

echo " [x] Please specify the VK user id or relative path to *.csv file. \n";

try {
    $stdin = fopen('php://stdin', 'r');
    $response = trim(fgets($stdin));

    $data = [$response];

    if (strpos($response, '.csv') !== false) {
        if (file_exists(__DIR__ . '/' . $response)) {
            $file = fopen($response, "r");

            $data = [];
            while (!feof($file)) {
                $data[] = fgetcsv($file)[0];
            }

            fclose($file);
        } else {
            echo " [x] Unable to open file, please check the path. \n";
            echo "     ", __DIR__ . '/' . $response, "\n";
            die();
        }
    }

    $sender = new \Gen\Broker\UserBroker();

    foreach ($data as $user_id) {
        try {
            $sender->user_id = $user_id;
            $sender->execute();
        } catch (TypeError $e) {
            echo " [x] ERROR: Invalid user ID - $user_id \n";
        }
    }
} catch (ErrorException $e) {
    echo " [x] ERROR ", $e->getMessage(), "\n";
}