<?php
// Define the target login page and the fallback error page
$login_page = 'web_content/login.php';
$error_page = 'web_content/error.php';

// Check if the target login page file exists on the server.
// Note: This is a basic file existence check. It does NOT check if the PHP script
// runs without internal errors (e.g., database connection issues).
if (file_exists($login_page)) {
    // If the file exists, set the HTTP status code and redirect the user to the login page.
    header('Location: ' . $login_page);
    exit; // Stop script execution after sending the header
} else {
    // If the file does NOT exist, redirect the user to the error page.
    header('Location: ' . $error_page);
    exit; // Stop script execution after sending the header
}
// ?>