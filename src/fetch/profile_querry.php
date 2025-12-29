<?php
    if (!isset($conn) || $conn->connect_error) {
        die("Error: Database connection not established.");
    }

    function time_ago($timestamp) {
        $time_difference = time() - strtotime($timestamp);
        if ($time_difference < 60) return max(1, $time_difference) . ' secs ago';
        if ($time_difference < 3600) return round($time_difference / 60) . ' mins ago';
        if ($time_difference < 8400) return round($time_difference / 3600) . ' hrs ago';
        return date('M j, Y', strtotime($timestamp));
    }

    $sql_profile = "SELECT first_name, last_name, username, email, phone, role, status, created_at, last_login, profile_image FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql_profile);
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $user_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user_data) die("Error: User profile not found.");

    $full_name = htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']);
    $has_profile_image = !empty($user_data['profile_image']);
    $profile_image_path = htmlspecialchars($user_data['profile_image'] ?? '');
?>