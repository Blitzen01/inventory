<?php
    // =======================================================
    // 💥 SESSION START & SECURITY CHECK 💥
    // =======================================================
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // 1. Check for login status
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: ../../login.php"); // Redirect to login page
        exit(); 
    }

    // 2. Check if the form was submitted via POST
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        header("Location: ../../edit_profile.php"); // Redirect back to the form page
        exit();
    }
    
    // Include necessary files (adjust path relative to ../src/php_script/)
    include "../../render/connection.php"; // Assuming this is the correct relative path to connection.php

    if (!isset($conn) || $conn->connect_error) {
        $_SESSION['message'] = "Error: Database connection failed.";
        $_SESSION['message_type'] = "danger";
        header("Location: ../../profile.php");
        exit();
    }
    
    // Get the ID of the logged-in user (for authorization/security)
    $user_id_to_edit = $_SESSION['user_id'];

    // =======================================================
    // 3. RETRIEVE AND VALIDATE INPUT DATA
    // =======================================================
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // Basic Validation
    if (empty($first_name) || empty($last_name) || empty($username) || empty($email)) {
        $_SESSION['message'] = "Error: First Name, Last Name, Username, and Email are required fields.";
        $_SESSION['message_type'] = "danger";
        header("Location: ../../web_content/profile.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = "Error: The email address provided is not valid.";
        $_SESSION['message_type'] = "danger";
        header("Location: ../../web_content/profile.php");
        exit();
    }

    // =======================================================
    // 4. PERFORM DATABASE UPDATE
    // =======================================================
    $sql_update = "UPDATE users 
                   SET first_name=?, last_name=?, username=?, email=?, phone=? 
                   WHERE user_id=?";
    
    $stmt_update = $conn->prepare($sql_update);
    
    if ($stmt_update) {
        // Bind parameters: (s=string, i=integer)
        $stmt_update->bind_param("sssssi", $first_name, $last_name, $username, $email, $phone, $user_id_to_edit);
        
        if ($stmt_update->execute()) {
            
            // Set success message in session
            $_SESSION['message'] = "Profile updated successfully!";
            $_SESSION['message_type'] = "success";
            
            // Update session variables if data changed (e.g., for header display)
            $_SESSION['username'] = $username;

        } else {
            // Set error message in session
            $_SESSION['message'] = "Database Error: Could not update profile. " . $stmt_update->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt_update->close();
    } else {
        // Set statement preparation error message
        $_SESSION['message'] = "System Error: Failed to prepare database statement. " . $conn->error;
        $_SESSION['message_type'] = "danger";
    }

    // Close connection
    $conn->close();

    // Redirect back to the edit profile page to show the result message
    header("Location: ../../web_content/profile.php");
    exit();
?>