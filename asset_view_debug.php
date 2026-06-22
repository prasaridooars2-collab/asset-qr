<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$config = require __DIR__ . '/db_config.php';

$mysqli = mysqli_init();

mysqli_options(
    $mysqli,
    MYSQLI_OPT_SSL_VERIFY_SERVER_CERT,
    false
);

$ok = mysqli_real_connect(
    $mysqli,
    $config['host'],
    $config['user'],
    $config['pass'],
    $config['name'],
    $config['port'],
    null,
    MYSQLI_CLIENT_SSL
);

if (!$ok) {
    die("DB Connection Failed: " . mysqli_connect_error());
}

$assetId = isset($_GET['id'])
    ? intval($_GET['id'])
    : 1;

$sql = "
SELECT
far.asset_id,
far.asset_code,
far.item_name,
far.model_no,
far.serial_no,
far.asset_value,
COALESCE(far.current_status,'Working') current_status
FROM fixed_assets_register far
WHERE far.asset_id=?
LIMIT 1
";

$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    die($mysqli->error);
}

$stmt->bind_param("i", $assetId);

$stmt->execute();

$res = $stmt->get_result();

$row = $res->fetch_assoc();

if (!$row) {
    die("No asset found");
}

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Asset Details</title>

<style>

body{
font-family:Arial;
padding:30px;
background:#f5f5f5;
}

.card{
background:#fff;
padding:25px;
border-radius:10px;
max-width:700px;
margin:auto;
}

.label{
font-weight:bold;
margin-top:10px;
}

</style>

</head>

<body>

<div class="card">

<h2>Asset Details</h2>

<div class="label">
Asset ID
</div>

<div>
<?= htmlspecialchars($row['asset_id']) ?>
</div>

<div class="label">
Asset Code
</div>

<div>
<?= htmlspecialchars($row['asset_code']) ?>
</div>

<div class="label">
Item
</div>

<div>
<?= htmlspecialchars($row['item_name']) ?>
</div>

<div class="label">
Model
</div>

<div>
<?= htmlspecialchars($row['model_no']) ?>
</div>

<div class="label">
Serial
</div>

<div>
<?= htmlspecialchars($row['serial_no']) ?>
</div>

<div class="label">
Status
</div>

<div>
<?= htmlspecialchars($row['current_status']) ?>
</div>

<div class="label">
Value
</div>

<div>
৳ <?= number_format($row['asset_value'],2) ?>
</div>

</div>

</body>
</html>
