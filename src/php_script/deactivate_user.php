<?php
session_start();
require "../../render/connection.php";

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$admin_id = $_SESSION['user_id'];
$target_user_id = $_POST['target_user_id'] ?? null;
$password = $_POST['password'] ?? '';

if (!$target_user_id || !$password) {
    die("Invalid request");
}

/* Get admin password */
$stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

if (!$admin || !password_verify($password, $admin['password'])) {
    die("Incorrect password");
}

/* Deactivate user */
$stmt = $pdo->prepare("UPDATE users SET status = 'inactive' WHERE user_id = ?");
$stmt->execute([$target_user_id]);

header("Location: ../../web_content/accounts.php");
exit;
