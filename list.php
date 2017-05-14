<?php

require_once __DIR__ . '/handler.php';
require_once __DIR__ . '/vendor/autoload.php';

$r = \Gen\Db::instanceGet()->query('
  SELECT user.first_name, user.last_name, album.name, photo.url FROM user 
  RIGHT JOIN album ON user.id=album.user_id
  RIGHT JOIN photo ON album.id=photo.album_id
  ORDER BY user.id, album.id
');

$previous_header = '';
while ($data = $r->fetch()) {
    $header = "{$data['first_name']} {$data['last_name']} -- {$data['name']}\n";
    if ($header !== $previous_header) {
        echo $previous_header = $header;
    }

    echo "  - {$data['url']}\n";
}