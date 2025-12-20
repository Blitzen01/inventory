<?php
session_start();
require "../../render/connection.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

$admin_id = $_SESSION['user_id'] ?? null;
$target_user_id = $_POST['target_user_id'] ?? null;
$new_status = $_POST['new_status'] ?? null;
$password = $_POST['password'] ?? null;

if (!$admin_id || !$target_user_id || !$new_status || !$password) {
    die("Invalid request");
}

// Prevent self action
if ($admin_id == $target_user_id) {
    die("You cannot change your own status");
}

// Verify admin password
$stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin || !password_verify($password, $admin['password'])) {
    header("Location: ../../web_content/accounts.php?failed=Incorrect Password");
}

// Update status
$stmt = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?");
$stmt->execute([$new_status, $target_user_id]);

header("Location: ../../web_content/accounts.php?success=Status updated");
exit;
