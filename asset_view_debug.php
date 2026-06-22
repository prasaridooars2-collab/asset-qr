<?php
/* ============================================================
   asset_view_debug.php
   QR code er moddhe ei page-er URL (?id=ASSET_ID) thake.
   Phone diye scan korle ei page khule database theke asset-er
   live details dekhabe.

   HOSTING: Render.com (Docker — php:8.2-apache)
   DB     : Aiven MySQL (external, SSL)
   CONFIG : DB password environment variable theke neowa hoy
            (Render dashboard > Environment > DB_PASSWORD)
   ============================================================ */

ini_set('display_errors', '0');
error_reporting(E_ALL);

// ---- DB config (Render environment variable theke) ----
$DB_HOST = "mysql-2482696a-prasari.l.aivencloud.com";
$DB_NAME = "defaultdb";
$DB_USER = "avnadmin";
$DB_PASS = getenv('DB_PASSWORD');
$DB_PORT = 19125;

$assetId = isset($_GET['id']) ? intval($_GET['id']) : 0;

$asset    = null;
$errorMsg = "";

if (!$DB_PASS) {
    error_log("asset_view_debug.php: DB_PASSWORD environment variable set kora nei.");
    $errorMsg = "Service temporarily unavailable. Please try again later.";
} elseif ($assetId <= 0) {
    $errorMsg = "Invalid or missing Asset ID.";
} else {
    $mysqli = mysqli_init();
    mysqli_options($mysqli, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);

    $connected = @mysqli_real_connect(
        $mysqli,
        $DB_HOST,
        $DB_USER,
        $DB_PASS,
        $DB_NAME,
        $DB_PORT,
        null,
        MYSQLI_CLIENT_SSL
    );

    if (!$connected) {
        error_log("asset_view_debug.php DB connection failed: " . mysqli_connect_error());
        $errorMsg = "Unable to load asset details right now. Please try again later.";
    } else {
        $sql = "SELECT
                    far.asset_id,
                    far.asset_code,
                    far.item_name,
                    far.model_no,
                    far.serial_no,
                    far.invoice_no,
                    far.invoice_date,
                    far.asset_value,
                    COALESCE(far.current_status,'Working') AS current_status,
                    COALESCE(am.agency_name,'')      AS agency_name,
                    COALESCE(pm.project_details,'')  AS project_name,
                    COALESCE(lm.location_name,'')    AS location_name,
                    COALESCE(atm.asset_type_name,'') AS asset_type_name,
                    nm.ngo_name,
                    nm.logo
                FROM fixed_assets_register far
                LEFT JOIN agency_master     am  ON am.agency_id      = far.agency_id
                LEFT JOIN project_master    pm  ON pm.project_id     = far.project_id
                LEFT JOIN location_master   lm  ON lm.location_id    = far.location
                LEFT JOIN asset_type_master atm ON atm.asset_type_id = far.asset_type
                LEFT JOIN ngo_master        nm  ON nm.ngo_id         = far.ngo_id
                WHERE far.asset_id = ?
                LIMIT 1";

        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $assetId);
            $stmt->execute();
            $res   = $stmt->get_result();
            $asset = $res->fetch_assoc();
            $stmt->close();
        } else {
            error_log("asset_view_debug.php prepare() failed: " . $mysqli->error);
        }
        $mysqli->close();

        if (!$asset) {
            $errorMsg = "No asset found for ID " . htmlspecialchars($assetId) . ".";
        }
    }
}

function e($v) {
    return htmlspecialchars($v === null ? "" : $v, ENT_QUOTES, "UTF-8");
}

function statusColors($status) {
    switch ($status) {
        case "Working":
            return ["#d1fae5", "#065f46"];
        case "Not Working":
        case "Damaged":
            return ["#fee2e2", "#991b1b"];
        case "Under Servicing":
            return ["#fef3c7", "#b45309"];
        case "Disposed":
        case "Lost":
            return ["#f3f4f6", "#374151"];
        default:
            return ["#dbeafe", "#1e3a5f"];
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $asset ? e($asset['asset_code']) . " — Asset Details" : "Asset Details"; ?></title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background: #f0f2f5;
        padding: 16px;
        color: #111827;
    }
    .card {
        max-width: 480px;
        margin: 0 auto;
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 18px rgba(0,0,0,0.08);
    }
    .card-header {
        background: #1a3a6b;
        color: #fff;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .card-header img {
        width: 42px;
        height: 42px;
        object-fit: contain;
        background: #fff;
        border-radius: 6px;
        padding: 3px;
    }
    .card-header .ngo-name { font-size: 15px; font-weight: 700; }
    .card-header .ngo-sub  { font-size: 11px; opacity: 0.8; margin-top: 2px; }
    .accent-bar { height: 4px; background: #c8902a; }
    .body { padding: 20px; }
    .asset-name {
        font-size: 19px; font-weight: 700;
        color: #111827; margin-bottom: 4px;
    }
    .asset-code {
        font-size: 13px; font-weight: 600;
        color: #1a56db; margin-bottom: 14px;
    }
    .status-badge {
        display: inline-block; font-size: 11px;
        font-weight: 700; padding: 4px 10px;
        border-radius: 20px; margin-bottom: 16px;
    }
    .grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px 16px;
        margin-top: 4px;
    }
    .field-label {
        font-size: 10px; font-weight: 700;
        color: #9ca3af; text-transform: uppercase;
        letter-spacing: 0.4px; margin-bottom: 2px;
    }
    .field-value {
        font-size: 13.5px; color: #111827;
        font-weight: 500; word-break: break-word;
    }
    .field-full { grid-column: 1 / -1; }
    .divider { height: 1px; background: #e5e7eb; margin: 16px 0; }
    .value-highlight { font-size: 17px; font-weight: 700; color: #c0392b; }
    .footer-note {
        text-align: center; font-size: 11px;
        color: #9ca3af; margin-top: 18px;
    }
    .error-box {
        max-width: 420px; margin: 60px auto;
        background: #fff; border-left: 4px solid #dc2626;
        padding: 20px; border-radius: 8px;
        color: #991b1b; font-size: 14px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    }
</style>
</head>
<body>

<?php if ($errorMsg !== ""): ?>
    <div class="error-box">⚠ <?php echo e($errorMsg); ?></div>
<?php else: ?>

    <?php list($stBg, $stFg) = statusColors($asset['current_status']); ?>

    <div class="card">
        <div class="card-header">
            <?php if (!empty($asset['logo'])): ?>
                <img src="data:image/png;base64,<?php echo base64_encode($asset['logo']); ?>" alt="logo">
            <?php endif; ?>
            <div>
                <div class="ngo-name"><?php echo e($asset['ngo_name']); ?></div>
                <div class="ngo-sub">Fixed Assets Register</div>
            </div>
        </div>
        <div class="accent-bar"></div>

        <div class="body">
            <div class="asset-name"><?php echo e($asset['item_name']); ?></div>
            <div class="asset-code">Asset Code: <?php echo e($asset['asset_code']); ?></div>

            <span class="status-badge" style="background:<?php echo $stBg; ?>;color:<?php echo $stFg; ?>;">
                <?php echo e($asset['current_status']); ?>
            </span>

            <div class="grid">
                <div>
                    <div class="field-label">Asset Type</div>
                    <div class="field-value"><?php echo e($asset['asset_type_name']) ?: "—"; ?></div>
                </div>
                <div>
                    <div class="field-label">Location</div>
                    <div class="field-value"><?php echo e($asset['location_name']) ?: "—"; ?></div>
                </div>
                <div>
                    <div class="field-label">Agency</div>
                    <div class="field-value"><?php echo e($asset['agency_name']) ?: "—"; ?></div>
                </div>
                <div>
                    <div class="field-label">Project</div>
                    <div class="field-value"><?php echo e($asset['project_name']) ?: "—"; ?></div>
                </div>
                <div>
                    <div class="field-label">Model No</div>
                    <div class="field-value"><?php echo e($asset['model_no']) ?: "—"; ?></div>
                </div>
                <div>
                    <div class="field-label">Serial No</div>
                    <div class="field-value"><?php echo e($asset['serial_no']) ?: "—"; ?></div>
                </div>
                <div>
                    <div class="field-label">Invoice No</div>
                    <div class="field-value"><?php echo e($asset['invoice_no']) ?: "—"; ?></div>
                </div>
                <div>
                    <div class="field-label">Invoice Date</div>
                    <div class="field-value">
                        <?php echo $asset['invoice_date']
                            ? e(date("d-M-Y", strtotime($asset['invoice_date'])))
                            : "—"; ?>
                    </div>
                </div>
            </div>

            <div class="divider"></div>

            <div class="field-label">Asset Value</div>
            <div class="value-highlight">
                ₹ <?php echo number_format((float)$asset['asset_value'], 2); ?>
            </div>

            <div class="footer-note">
                Asset ID: <?php echo e($asset['asset_id']); ?>
                &nbsp;|&nbsp; Scanned via QR Code
            </div>
        </div>
    </div>

<?php endif; ?>
</body>
</html>
