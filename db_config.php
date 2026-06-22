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

$conn = mysqli_init();

mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

mysqli_real_connect(
    $conn,
    getenv("DB_HOST"),
    getenv("DB_USER"),
    getenv("DB_PASS"),
    getenv("DB_NAME"),
    (int)getenv("DB_PORT"),
    NULL,
    MYSQLI_CLIENT_SSL
);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
