<?php
// Include the connection file to establish database link
include "../../render/connection.php"; 

if (!isset($conn)) {
    header("Location: settings.php?status=error&message=Database connection failed.");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../web_content/settings.php");
    exit;
}

$settings_to_update = [];
$error_count = 0;

// --- SANITIZE POST DATA ---
foreach ($_POST as $key => $value) {
    $setting_key = $conn->real_escape_string($key);
    
    // Special handling for numeric settings
    if (in_array($setting_key, ['low_stock_threshold_percent', 'liquidation_percentage', 'eol_duration_years'])) {
        $value = floatval($value); // Convert to number
        
        if ($setting_key === 'low_stock_threshold_percent' || $setting_key === 'liquidation_percentage') {
            if ($value < 0) $value = 0;
            if ($value > 100) $value = 100;
        } elseif ($setting_key === 'eol_duration_years') {
            if ($value < 1) $value = 1;
        }
    }
    
    $setting_value = $conn->real_escape_string($value);
    $settings_to_update[$setting_key] = $setting_value;
}

// --- HANDLE BOOLEAN SWITCHES ---
$sql_boolean_keys = "SELECT setting_key FROM system_settings WHERE setting_type = 'boolean'";
$result_boolean_keys = $conn->query($sql_boolean_keys);

if ($result_boolean_keys) {
    while ($row = $result_boolean_keys->fetch_assoc()) {
        $bool_key = $row['setting_key'];
        if (!isset($settings_to_update[$bool_key])) {
            $settings_to_update[$bool_key] = '0';
        }
    }
}

// --- EXECUTE UPDATE ---
if (!empty($settings_to_update)) {
    $conn->begin_transaction();
    
    foreach ($settings_to_update as $key => $value) {
        $sql_update = "UPDATE system_settings SET setting_value = '$value' WHERE setting_key = '$key'";
        if (!$conn->query($sql_update)) {
            $error_count++;
        }
    }

    if ($error_count === 0) {
        $conn->commit();
        $message = "All settings saved successfully!";
        $status = "success";
    } else {
        $conn->rollback();
        $message = "Error saving settings. $error_count setting(s) failed.";
        $status = "error";
    }
} else {
    $message = "No settings data was received.";
    $status = "warning";
}

// Redirect back to settings page
header("Location: ../../web_content/settings.php?status=$status&message=" . urlencode($message));
exit;
?>
