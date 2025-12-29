<?php
session_start();
require "../../render/connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $admin_id = $_SESSION['user_id'];
    $target_user_id = $_POST['user_id'];
    $admin_password = $_POST['admin_password'];

    // 1. Verify Admin Password
    $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($admin_password, $admin['password'])) {
        try {
            $pdo->beginTransaction();

            // 2. Fetch all data for archiving
            $userStmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
            $userStmt->execute([$target_user_id]);
            $userData = $userStmt->fetch();

            if (!$userData) {
                $pdo->rollBack();
                header("Location: ../../web_content/accounts.php?error=user_not_found");
                exit;
            }

            // 3. Archive data into deleted_users table
            $archiveSql = "INSERT INTO deleted_users 
                (user_id, role_id, username, first_name, last_name, phone, role, status, email, password, profile_image, is_active, last_login, created_at, deleted_at)
                VALUES 
                (:uid, :rid, :uname, :fname, :lname, :phone, :role, 'deleted', :email, :pass, :img, :active, :last_log, :created, CURRENT_TIMESTAMP)";
            
            $archiveStmt = $pdo->prepare($archiveSql);
            $archiveStmt->execute([
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

            // 4. Log the action in Audit Logs (Action type set to DELETE)
            $log_details = "Archived User: " . $userData['username'] . " (ID: " . $userData['role_id'] . ")";
            $logStmt = $pdo->prepare("INSERT INTO system_audit_logs 
                                    (table_name, record_id, action_type, old_value, new_value, changed_by) 
                                    VALUES ('users', :record_id, 'DELETE', :old_v, 'Moved to deleted_users', :admin)");
            $logStmt->execute([
                'record_id' => $target_user_id,
                'old_v'     => $log_details,
                'admin'     => $admin_id
            ]);

            // 5. Permanently remove from the active users table
            $delete = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $delete->execute([$target_user_id]);

            $pdo->commit();
            header("Location: ../../web_content/accounts.php?success=deleted");

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            die("Critical Database Error: " . $e->getMessage());
        }
    } else {
        header("Location: ../../web_content/accounts.php?error=wrong_password");
    }
    exit;
} else {
    header("Location: ../../web_content/accounts.php");
    exit;
}