<?php
    // 2. Database Connection
    if (!isset($conn) || !$conn instanceof mysqli) {
        // Adjust path if necessary to point to your connection file
        include_once dirname(__DIR__) . "/render/connection.php"; 
    }

    // 3. Fallback for Session variables
    $userType = $_SESSION['user_type'] ?? 'Guest';
    $username = $_SESSION['username'] ?? 'User';

    // 4. App Name Fetching
    $appName = "MVentory"; // Hardcoded default
    if (isset($conn) && $conn instanceof mysqli) {
        $sql = "SELECT setting_value FROM system_settings WHERE setting_key = 'app_name' LIMIT 1";
        if ($result = $conn->query($sql)) {
            if ($row = $result->fetch_assoc()) { 
                $appName = htmlspecialchars($row['setting_value']); 
            }
            $result->free();
        }
    }

    // 5. Layout Helpers
    $parts = explode(' ', $appName, 2);
    $firstWord = $parts[0];
    $restOfName = $parts[1] ?? '';
    $currentPage = basename($_SERVER['PHP_SELF']);

    /**
     * Helper function to highlight the current page in the nav
     */
    function isActive($pageName, $currentPage) {
        return ($pageName === $currentPage) ? 'active fw-bold border-bottom border-info border-2' : '';
    }
?>