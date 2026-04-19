<?php

date_default_timezone_set('America/Chicago');

if (PHP_OS_FAMILY === 'Darwin') {
    define('BASE_PATH', 'http://localhost:8888/FuelTrackpro');
} else {
    define('BASE_PATH', 'http://localhost/FuelTrackpro');
}

define('ALLOWED_NETWORKS', [
    '::1',
    '127.0.0.1',
    '192.168.4.34/24',
    '192.168.4.35'
]);
