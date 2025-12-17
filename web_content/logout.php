<?php
// Start or resume the existing session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Unset all session variables.
// This removes the user-specific data (like user_id, username, logged_in status)
$_SESSION = array();

// 2. Destroy the session cookie parameters (browser side).
// This makes the session ID invalid.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destroy the session (server side).
// This clears the session file stored on the server.
session_destroy();

// 4. Redirect the user to the login page or homepage.
// Use 'exit()' or 'die()' after a header redirect to prevent further script execution.
header("Location: login.php"); // Change 'login.php' to your actual login page path
exit();
?>