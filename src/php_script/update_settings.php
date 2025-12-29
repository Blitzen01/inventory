<?php
session_start(); // Ensure session is started to get the user_id
include "../../render/connection.php"; 

if (!isset($conn)) {
    header("Location: settings.php?status=error&message=Database connection failed.");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../web_content/settings.php");
    exit;
}

// Get the ID of the user performing the action
$current_user_id = $_SESSION['user_id'] ?? 0; 
$settings_to_update = [];
$error_count = 0;

// --- 1. SANITIZE POST DATA ---
foreach ($_POST as $key => $value) {
    $setting_key = $conn->real_escape_string($key);
    
    if (in_array($setting_key, ['low_stock_threshold_percent', 'liquidation_percentage', 'eol_duration_years'])) {
        $value = floatval($value);
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

// --- 2. HANDLE BOOLEAN SWITCHES ---
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

// --- 3. FETCH CURRENT VALUES FOR AUDIT ---
$current_settings = [];
$sql_current = "SELECT setting_key, setting_value FROM system_settings";
$res_current = $conn->query($sql_current);
while($row = $res_current->fetch_assoc()){
    $current_settings[$row['setting_key']] = $row['setting_value'];
}

// --- 4. EXECUTE UPDATE & LOGGING ---
if (!empty($settings_to_update)) {
    $conn->begin_transaction();
    
    foreach ($settings_to_update as $key => $new_val) {
        // Only proceed if the key exists and the value is different
        if (isset($current_settings[$key]) && $current_settings[$key] !== $new_val) {
            
            $old_val = $current_settings[$key];
            
            // Perform Update
            $sql_update = "UPDATE system_settings SET setting_value = '$new_val' WHERE setting_key = '$key'";
            
            if ($conn->query($sql_update)) {
                // Insert Audit Log for this specific change
                $log_sql = "INSERT INTO system_audit_logs 
                            (table_name, record_id, action_type, old_value, new_value, changed_by) 
                            VALUES 
                            ('settings', '$key', 'UPDATE', '$old_val', '$new_val', '$current_user_id')";
                
                if (!$conn->query($log_sql)) {
                    $error_count++;
                }
            } else {
                $error_count++;
            }
        }
    }

    if ($error_count === 0) {
        $conn->commit();
        $message = "All settings saved and logged successfully!";
        $status = "success";
    } else {
        $conn->rollback();
        $message = "Error saving settings. $error_count action(s) failed.";
        $status = "error";
    }
} else {
    $message = "No changes detected.";
    $status = "warning";
}

header("Location: ../../web_content/settings.php?status=$status&message=" . urlencode($message));
exit;
?>