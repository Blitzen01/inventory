<?php
session_start();
require "../../render/connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore_user'])) {
    $admin_id = $_SESSION['user_id'];
    $target_user_id = $_POST['user_id'];

    try {
        $pdo->beginTransaction();

        // 1. Fetch the archived data from deleted_users
        $userStmt = $pdo->prepare("SELECT * FROM deleted_users WHERE user_id = ?");
        $userStmt->execute([$target_user_id]);
        $userData = $userStmt->fetch();

        if (!$userData) {
            $pdo->rollBack();
            header("Location: ../../web_content/accounts.php?error=user_not_found");
            exit;
        }

        // 2. Insert data back into the active 'users' table
        $restoreSql = "INSERT INTO users 
            (user_id, role_id, username, first_name, last_name, phone, role, status, email, password, profile_image, is_active, last_login, created_at)
            VALUES 
            (:uid, :rid, :uname, :fname, :lname, :phone, :role, 'active', :email, :pass, :img, :active, :last_log, :created)";
        
        $restoreStmt = $pdo->prepare($restoreSql);
        $restoreStmt->execute([
            'uid'      => $userData['user_id'],
            'rid'      => $userData['role_id'],
            'uname'    => $userData['username'],
            'fname'    => $userData['first_name'],
            'lname'    => $userData['last_name'],
            'phone'    => $userData['phone'],
            'role'     => $userData['role'],
            'email'    => $userData['email'],
            'pass'     => $userData['password'],
            'img'      => $userData['profile_image'],
            'active'   => $userData['is_active'],
            'last_log' => $userData['last_login'],
            'created'  => $userData['created_at']
        ]);

        // 3. Log the action in Audit Logs (Updated action_type to RESTORED)
        $log_details = "Restored User: " . $userData['username'] . " (ID: " . $userData['role_id'] . ")";
        $logStmt = $pdo->prepare("INSERT INTO system_audit_logs 
                                    (table_name, record_id, action_type, old_value, new_value, changed_by) 
                                    VALUES ('deleted_users', :record_id, 'RESTORED', :old_v, 'Moved back to active users', :admin)");
        $logStmt->execute([
            'record_id' => $target_user_id,
            'old_v'     => $log_details,
            'admin'     => $admin_id
        ]);

        // 4. Remove from the archived table
        $delete = $pdo->prepare("DELETE FROM deleted_users WHERE user_id = ?");
        $delete->execute([$target_user_id]);

        $pdo->commit();
        header("Location: ../../web_content/accounts.php?success=restored");

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("Critical Database Error: " . $e->getMessage());
    }
    exit;
} else {
    header("Location: ../../web_content/accounts.php");
    exit;
}