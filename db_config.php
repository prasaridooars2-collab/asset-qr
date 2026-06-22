<?php

return [
    'host' => 'mysql-2482696a-prasari.l.aivencloud.com',
    'port' => 19125,
    'name' => 'defaultdb',
    'user' => 'avnadmin',

    // Render Environment Variable থেকে নেবে
    'pass' => getenv('DB_PASSWORD')
];
