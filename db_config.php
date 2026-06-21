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

return [
    'host' => "mysql-2482696a-prasari.l.aivencloud.com",
    'name' => "defaultdb",
    'user' => "avnadmin",
    'pass' => "REAL_AIVEN_PASSWORD_DIN_EKHANE",   // <-- shudhu ei file-e password thakbe
    'port' => 19125,
];
