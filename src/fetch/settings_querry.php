<?php
// --- Fetch System Settings ---
    $settings = [];
    if (isset($conn)) {
        $sql_settings = "SELECT setting_key, setting_value FROM system_settings";
        $result_settings = $conn->query($sql_settings);
        
        if ($result_settings && $result_settings->num_rows > 0) {
            while ($row = $result_settings->fetch_assoc()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        }
    }
?>