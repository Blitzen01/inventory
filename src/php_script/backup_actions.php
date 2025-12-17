<?php
// Load DB connection
include "../../render/connection.php";

// --- FIX: Define DB connection variables for mysqldump ---
// These variables MUST be set correctly for the backup to run.
// FIX: If you confirmed your root user HAS a password, enter it here.
// If you confirmed your root user has NO password, leave it as $pass = "";
$server = "localhost";
$user   = "root";
$pass   = "";       // Your database password (CHANGE THIS IF YOU HAVE ONE)
$db     = "inventory";  // Your database name (MUST match your database name)
// --------------------------------------------------------

// Must be POST with action
if (!isset($conn) || $_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['action'])) {
    header("Location: ../../settings.php?status=error&message=Invalid request or database connection failed.");
    exit;
}

$action = $_POST['action'];

switch ($action) {

    case 'export_inventory_csv':

        $table = 'products';
        $filename = "inventory_export_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

         $columns_query = $conn->query("SHOW COLUMNS FROM $table");
        $header = [];
        while ($col = $columns_query->fetch_assoc()) {
            $header[] = $col['Field'];
        }
        fputcsv($output, $header);

        $data_query = $conn->query("SELECT * FROM $table ORDER BY product_id DESC");
        while ($row = $data_query->fetch_assoc()) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;

    case 'run_db_backup':

        if (!isset($server, $user, $pass, $db)) {
            header("Location: ../../web_content/settings.php?status=error&message=Missing DB configuration variables.");
            exit;
        }

        $mysqldump_path = "C:/xampp/mysql/bin/mysqldump.exe";

        if (!file_exists($mysqldump_path)) {
            header("Location: ../../web_content/settings.php?status=error&message=" . urlencode("mysqldump not found at: $mysqldump_path."));
            exit;
        }

        $password_arg = !empty($pass) ? "-p$pass" : "";

        $filename = "database_backup_" . date('Ymd_His') . ".sql";
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $command = "\"$mysqldump_path\" --single-transaction --routines --events --triggers " .
            "-h $server -u $user $password_arg $db 2>&1";
        
        passthru($command);

        exit;


    default:
        header("Location: ../../web_content/settings.php?status=warning&message=Unknown action.");
    exit;
}
?>