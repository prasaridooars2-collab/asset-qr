<?php
/* ============================================================
   db_config.php
   !!! EI FILE "htdocs" FOLDER-ER BAIRE, ROOT-E RAKHUN !!!
   (jekhane .htaccess ar .override file gulo ache)

   Ei file web theke kokhonoi direct access kora jabe na, karon
   InfinityFree shudhu "htdocs"-er bhitorer file gulo serve kore.
   Tai password ei file-e rakha "htdocs"-er bhitore rakhar cheye
   onek beshi secure.

   asset_view.php ei file-ke include() kore credentials nibe.
   ============================================================ */

<?php

$config = [
    'host' => getenv('DB_HOST'),
    'port' => getenv('DB_PORT'),
    'user' => getenv('DB_USER'),
    'pass' => getenv('DB_PASS'),
    'name' => getenv('DB_NAME')
];

$conn = mysqli_init();

mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

mysqli_real_connect(
    $conn,
    $config['host'],
    $config['user'],
    $config['pass'],
    $config['name'],
    (int)$config['port'],
    NULL,
    MYSQLI_CLIENT_SSL
);

if (!$conn) {
    die("DB connection failed");
}
?>
