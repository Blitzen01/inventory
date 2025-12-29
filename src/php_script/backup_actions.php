<?php
include "../../render/connection.php";

$server = "localhost";
$user   = "root";
$pass   = "";           
$db     = "inventory";  

date_default_timezone_set('Asia/Manila');

function updateBackupTimestamp($conn) {
    $now = date('Y-m-d H:i:s');
    $sql = "UPDATE system_settings SET setting_value = ? WHERE setting_key = 'last_backup_datetime'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $now);
    $stmt->execute();
}

if (!isset($conn) || $_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['action'])) {
    header("Location: ../../web_content/settings.php?status=error&message=Invalid request.");
    exit;
}

$action = $_POST['action'];

switch ($action) {

    case 'export_inventory_csv':

    updateBackupTimestamp($conn);

    $filename = "full_system_report_" . date('Ymd_His') . ".xls";

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    echo '<?xml version="1.0"?>';
    echo '<?mso-application progid="Excel.Sheet"?>';

    echo '<Workbook
        xmlns="urn:schemas-microsoft-com:office:spreadsheet"
        xmlns:o="urn:schemas-microsoft-com:office:office"
        xmlns:x="urn:schemas-microsoft-com:office:excel"
        xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';

    /* ===== STYLES ===== */
    echo '<Styles>

        <!-- Header style -->
        <Style ss:ID="Header">
            <Font ss:Bold="1"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
            </Borders>
        </Style>

        <!-- Data cell style -->
        <Style ss:ID="Cell">
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
            </Borders>
        </Style>

    </Styles>';

    /* ===== TABLE → SHEET MAP ===== */
    $export_map = [
        'Products'       => 'products',
        'Inventory Logs' => 'inventory_log',
        'Damaged Items'  => 'damaged_products',
        'Branch Logs'    => 'branch_logs',
        'Head Office'    => 'head_office_logs'
    ];

    foreach ($export_map as $sheetName => $tableName) {

        $check = $conn->query("SHOW TABLES LIKE '$tableName'");
        if ($check->num_rows == 0) continue;

        echo '<Worksheet ss:Name="' . htmlspecialchars($sheetName) . '">';
        echo '<Table>';

        /* === COLUMNS (AUTOFIT) === */
        $columns = [];
        $columns_query = $conn->query("SHOW COLUMNS FROM $tableName");
        while ($col = $columns_query->fetch_assoc()) {
            $columns[] = $col['Field'];
            echo '<Column ss:AutoFitWidth="1"/>';
        }

        /* === HEADER ROW === */
        echo '<Row>';
        foreach ($columns as $col) {
            echo '<Cell ss:StyleID="Header">
                    <Data ss:Type="String">' . htmlspecialchars($col) . '</Data>
                  </Cell>';
        }
        echo '</Row>';

        /* === DATA ROWS === */
        $data_query = $conn->query("SELECT * FROM $tableName ORDER BY 1 DESC");
        while ($row = $data_query->fetch_assoc()) {
            echo '<Row>';
            foreach ($row as $value) {
                $type = is_numeric($value) ? 'Number' : 'String';
                echo '<Cell ss:StyleID="Cell">
                        <Data ss:Type="' . $type . '">' . htmlspecialchars($value ?? '') . '</Data>
                      </Cell>';
            }
            echo '</Row>';
        }

        echo '</Table>';

        /* === WORKSHEET OPTIONS === */
        echo '<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
                <DisplayGridlines>True</DisplayGridlines>
              </WorksheetOptions>';

        echo '</Worksheet>';
    }

    echo '</Workbook>';
    exit;

    case 'run_db_backup':
        $mysqldump_path = "C:/xampp/mysql/bin/mysqldump.exe";
        if (!file_exists($mysqldump_path)) {
            header("Location: ../../web_content/settings.php?status=error&message=mysqldump not found.");
            exit;
        }

        updateBackupTimestamp($conn);
        $password_arg = !empty($pass) ? "-p$pass" : "";
        $filename = "database_full_backup_" . date('Ymd_His') . ".sql";
        
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