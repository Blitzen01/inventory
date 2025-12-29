<?php
if($_SESSION['user_type'] == 'Viewer') {
        echo '<script>window.location.href = "inventory.php";</script>';
        exit();
    }

    if (!isset($conn)) {
        die("Error: Database connection not established.");
    }

    function time_ago($timestamp) {
        $time_difference = time() - strtotime($timestamp);
        $periods = ["sec", "min", "hr", "day", "wk", "mo", "yr"];
        $lengths = [60, 60, 24, 7, 4.35, 12, 1000];
        if ($time_difference < 5) return "just now";
        for ($i = 0; $time_difference >= $lengths[$i] && $i < count($lengths) - 1; $i++) {
            $time_difference /= $lengths[$i];
        }
        $time_difference = round($time_difference);
        return "$time_difference " . $periods[$i] . ($time_difference != 1 ? "s" : "") . " ago";
    }

    // --- FETCH METRICS ---
    $active_users = $conn->query("SELECT COUNT(user_id) AS total FROM users WHERE is_active = 1")->fetch_assoc()['total'] ?? 0;
    $low_stock_count = $conn->query("SELECT COUNT(product_id) AS total FROM products WHERE stock_level <= min_threshold AND stock_level > 0")->fetch_assoc()['total'] ?? 0;
    $total_products = $conn->query("SELECT COUNT(product_id) AS total FROM products")->fetch_assoc()['total'] ?? 0;
    $total_val = $conn->query("SELECT SUM(stock_level * unit_cost) AS val FROM products WHERE stock_level > 0")->fetch_assoc()['val'] ?? 0;
    $pending_damage_qty = $conn->query("SELECT SUM(quantity_damaged) AS total FROM damaged_products WHERE status LIKE 'PENDING_%'")->fetch_assoc()['total'] ?? 0;

    // --- TABLES ---
    // FIX 1: Added unit_cost to the query
    $result_critical_stock = $conn->query("SELECT product_id, product_name, sku, stock_level, min_threshold, unit_cost FROM products WHERE stock_level <= min_threshold AND stock_level > 0 ORDER BY stock_level ASC LIMIT 5");

    // FIX 2: Wrapped the UNION query properly to ensure $result_activity_log is defined
    $master_log_sql = "
        SELECT combined.*, p.product_name, u.first_name
        FROM (
            (SELECT timestamp AS dt, action_type, product_id, user_id, 'Inventory' AS src, '' AS sub_status 
             FROM inventory_log)
            UNION ALL
            (SELECT date_reported AS dt, 'DAMAGE REPORT' AS action_type, product_id, reported_by_user_id AS user_id, 'Damaged' AS src, status AS sub_status 
             FROM damaged_products 
             WHERE status LIKE 'PENDING_%')
        ) AS combined
        LEFT JOIN products p ON combined.product_id = p.product_id
        LEFT JOIN users u ON combined.user_id = u.user_id
        ORDER BY combined.dt DESC
        LIMIT 6
    ";

    $result_activity_log = $conn->query($master_log_sql);

    // Error check to prevent the Fatal Error you saw
    if (!$result_activity_log) {
        die("Query Failed: " . $conn->error);
    }
?>