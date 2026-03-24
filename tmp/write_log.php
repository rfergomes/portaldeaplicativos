<?php
$content = file_get_contents('storage/logs/laravel.log');
$pos = strrpos($content, 'Itens before merge:');
if ($pos !== false) {
    file_put_contents('tmp/debug_log.txt', substr($content, $pos, 5000));
} else {
    file_put_contents('tmp/debug_log.txt', "Message not found\n");
}
