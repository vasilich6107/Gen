<?php

require_once __DIR__ . '/vendor/autoload.php';

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

try {
  $receiver = new \Gen\Broker\AlbumBroker();
  $receiver->listen();
}
catch (ErrorException $e) {
  echo " [x] ERROR\n";
}