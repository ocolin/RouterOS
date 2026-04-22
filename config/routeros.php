<?php

return [
    'host'          => env('ROUTEROS_HOST' ),
    'username'      => env('ROUTEROS_USERNAME', 'admin' ),
    'password'      => env('ROUTEROS_PASSWORD', '' ),
    'ssl'           => env('ROUTEROS_SSL', false ),
    'port'          => env('ROUTEROS_PORT', 8728 ),
    'sslPort'       => env('ROUTEROS_SSL_PORT', 8729 ),
    'timeout'       => env('ROUTEROS_TIMEOUT', 10 ),
    'socketTimeout' => env('ROUTEROS_SOCKET_TIMEOUT', 30 ),
    'sslVerify'     => env('ROUTEROS_SSL_VERIFY', false ),
];