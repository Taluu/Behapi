<?php

declare(strict_types=1);

header('Content-Type: application/json');

$code = 200;
$message = 'OK';

if ('/' !== $_SERVER['REQUEST_URI']) {
    $explode = explode('/', ltrim($_SERVER['REQUEST_URI'], '/'), 2);

    if (!isset($explode[1])) {
        $explode[1] = '';
    }

    [$code, $message] = $explode;

    $message = urldecode($message);
}

header("HTTP/1.1 {$code} {$message}");


echo <<<JSON
[
 {
   "foo": "bar"
 },

 {
   "foo": {"baz": "baz"}
 },

 {
   "foo": [1, 2, 3]
 }
]
JSON;
