<?php
session_start();
include '../../render/connection.php';

if (isset($_POST['wipe_user'])) {
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $admin_id = $_SESSION['user_id']; 
    $admin_name = $_SESSION['username'];

    // 1. Get user details before deletion for the log entry
    $name_query = "SELECT first_name, last_name, role_id FROM deleted_users WHERE user_id = ?";
    $n_stmt = mysqli_prepare($conn, $name_query);
    mysqli_stmt_bind_param($n_stmt, "i", $user_id);
    mysqli_stmt_execute($n_stmt);
    $res = mysqli_stmt_get_result($n_stmt);
    $target_user = mysqli_fetch_assoc($res);
    
    $target_name = $target_user ? $target_user['first_name'] . ' ' . $target_user['last_name'] : "Unknown User";
    $role_id = $target_user ? $target_user['role_id'] : "N/A";

    // 2. Perform the permanent delete
    $sql = "DELETE FROM deleted_users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // 3. Log the action into system_audit_logs with correct column names
            $action_details = "Permanently wiped user record: $target_name (ID: $role_id)";
            
            // Using your actual table columns: table_name, record_id, action_type, old_value, new_value, changed_by
            $log_sql = "INSERT INTO system_audit_logs (table_name, record_id, action_type, old_value, new_value, changed_by) 
                        VALUES ('deleted_users', ?, 'DELETE', ?, 'Purged from System', ?)";
            
            $log_stmt = mysqli_prepare($conn, $log_sql);
            
            if ($log_stmt) {
                // Bind parameters: i (record_id), s (old_value), i (changed_by)
                mysqli_stmt_bind_param($log_stmt, "isi", $user_id, $action_details, $admin_id);
                mysqli_stmt_execute($log_stmt);
            }

            $_SESSION['success'] = "Account record has been permanently deleted from the system.";
        } else {
            $_SESSION['error'] = "Error: System failed to wipe the account.";
        }
    }
    
    header("Location: ../../web_content/accounts.php");
    exit();
}