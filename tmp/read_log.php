<?php
$content = file_get_contents('storage/logs/laravel.log');
$pos = strrpos($content, 'Itens before merge:');
if ($pos !== false) {
    echo substr($content, $pos, 5000);
} else {
    echo "Message not found\n";
}
