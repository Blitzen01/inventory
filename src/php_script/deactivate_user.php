<?php
session_start();
require "../../render/connection.php";

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

// 1. Get data from POST
$admin_id = $_SESSION['user_id'];
$target_user_id = $_POST['user_id'] ?? null;
$admin_password_input = $_POST['admin_password'] ?? '';
$new_status = $_POST['new_status'] ?? 'inactive';

if (!$target_user_id || !$admin_password_input) {
    die("Error: Missing required information.");
}

// 2. FETCH the Admin's password AND the Target User's current status
// We do this in one block to ensure we have all data needed for the audit
$stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

$userStmt = $pdo->prepare("SELECT username, status FROM users WHERE user_id = ?");
$userStmt->execute([$target_user_id]);
$targetUser = $userStmt->fetch();

if (!$targetUser) {
    die("Error: Target user not found.");
}

// 3. VERIFY the admin password
if (!$admin || !password_verify($admin_password_input, $admin['password'])) {
    die("Incorrect Admin Password. Action cancelled.");
}

// 4. TRANSACTION: Update status and Log the action
try {
    $pdo->beginTransaction();

    $old_status = $targetUser['status'];
    $target_username = $targetUser['username'];

    // Only update and log if the status is actually different
    if ($old_status !== $new_status) {
        
        // A. Update the user
        $updateStmt = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?");
        $updateStmt->execute([$new_status, $target_user_id]);

        // B. Insert into system_audit_logs
        // We record the username in the description to make the log readable
        $log_sql = "INSERT INTO system_audit_logs 
                    (table_name, record_id, action_type, old_value, new_value, changed_by) 
                    VALUES ('users', :record_id, 'UPDATE', :old_v, :new_v, :admin)";
        
        $logStmt = $pdo->prepare($log_sql);
        $logStmt->execute([
            'record_id' => $target_user_id,
            'old_v'     => "Status: $old_status (User: $target_username)",
            'new_v'     => "Status: $new_status",
            'admin'     => $admin_id
        ]);
    }

    $pdo->commit();
    header("Location: ../../web_content/accounts.php?success=status_updated");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("Database error: Could not update status. " . $e->getMessage());
}