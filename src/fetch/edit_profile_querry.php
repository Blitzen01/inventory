<?php
    if (!isset($conn) || $conn->connect_error) {
        die("Error: Database connection not established.");
    }

    $message = '';
    $message_type = '';

    // HANDLE UPDATE
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        if (empty($first_name) || empty($last_name) || empty($username) || empty($email)) {
            $message = "All fields (except Phone) are required.";
            $message_type = "danger";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Invalid email format.";
            $message_type = "danger";
        } else {
            $sql_update = "UPDATE users SET first_name=?, last_name=?, username=?, email=?, phone=? WHERE user_id=?";
            $stmt_update = $conn->prepare($sql_update);
            
            if ($stmt_update) {
                $stmt_update->bind_param("sssssi", $first_name, $last_name, $username, $email, $phone, $user_id_to_edit);
                if ($stmt_update->execute()) {
                    $_SESSION['username'] = $username;
                    header("Location: profile.php?status=update_success"); 
                    exit();
                } else {
                    $message = "Error updating profile: " . $conn->error;
                    $message_type = "danger";
                }
                $stmt_update->close();
            }
        }
    }

    // FETCH DATA FOR FORM
    $sql_fetch = "SELECT first_name, last_name, username, email, phone FROM users WHERE user_id = ?";
    $stmt_fetch = $conn->prepare($sql_fetch);
    $stmt_fetch->bind_param("i", $user_id_to_edit);
    $stmt_fetch->execute();
    $user_data = $stmt_fetch->get_result()->fetch_assoc();
    $stmt_fetch->close();

    if (!$user_data) die("Error: User profile data not found.");
?>