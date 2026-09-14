<?php

return [
    'name' => getenv('APP_NAME') ?: 'GUDANG PT.BIG',
    'url' => getenv('APP_URL') ?: 'http://localhost/APLIKASI INVENTORI BIGNET/public',
    'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Jakarta',
    'env' => getenv('APP_ENV') ?: 'development',
    'debug' => getenv('APP_DEBUG') !== false ? filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN) : true,
];

