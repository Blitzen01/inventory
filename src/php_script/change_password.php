<?php
session_start();
include '../../render/connection.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password     = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Basic Validation
    if ($new_password !== $confirm_password) {
        header("Location: ../../web_content/profile.php?error=Passwords do not match");
        exit;
    }

    if (strlen($new_password) < 8) {
        header("Location: ../../web_content/profile.php?error=Password too short");
        exit;
    }

    try {
        // 2. Fetch the current hashed password from the database
        // Assuming your users table is named 'users' and has 'password' and 'user_id'
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user || !password_verify($current_password, $user['password'])) {
            header("Location: ../../web_content/profile.php?error=Incorrect current password");
            exit;
        }

        // 3. Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // 4. Update the database
        $update = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $update->bind_param("si", $hashed_password, $user_id);
        
        if ($update->execute()) {
            header("Location: ../../web_content/profile.php?success=Password updated successfully");
        } else {
            throw new Exception("Failed to update password");
        }

    } catch (Exception $e) {
        header("Location: ../../web_content/profile.php?error=Database error: " . urlencode($e->getMessage()));
    }
    exit;
}