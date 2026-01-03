<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: login.php");
        exit();
    }

    include "../render/connection.php";
    
    if (!isset($conn)) {
        die("Error: Database connection not established.");
    }

    // --- HELPER FUNCTION ---
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
    // Total Active Users
    $active_users = $conn->query("SELECT COUNT(user_id) AS total FROM users WHERE is_active = 1")->fetch_assoc()['total'] ?? 0;
    
    // Total Count of all unique products
    $total_products = $conn->query("SELECT COUNT(product_id) AS total FROM products")->fetch_assoc()['total'] ?? 0;
    
    // Products below threshold
    $low_stock_count = $conn->query("SELECT COUNT(product_id) AS total FROM products WHERE stock_level <= min_threshold AND stock_level > 0")->fetch_assoc()['total'] ?? 0;
    
    // Total Inventory Monetary Value
    $total_val = $conn->query("SELECT SUM(stock_level * unit_cost) AS val FROM products WHERE stock_level > 0")->fetch_assoc()['val'] ?? 0;
    
    // Damage Quantity and Cost (Pending reports only)
    $dmg_sql = "SELECT SUM(dp.quantity_damaged) AS total_qty, SUM(dp.quantity_damaged * p.unit_cost) AS total_loss 
                FROM damaged_products dp 
                JOIN products p ON dp.product_id = p.product_id 
                WHERE dp.status LIKE 'PENDING_%'";
    $dmg_data = $conn->query($dmg_sql)->fetch_assoc();
    
    $pending_damage_qty = $dmg_data['total_qty'] ?? 0;
    $total_damage_cost = $dmg_data['total_loss'] ?? 0;

    // --- TABLES DATA ---
    // Fetch top 5 critical items
    $result_critical_stock = $conn->query("SELECT product_id, product_name, sku, stock_level, min_threshold, unit_cost FROM products WHERE stock_level <= min_threshold AND stock_level > 0 ORDER BY stock_level ASC LIMIT 5");

    // Unified Activity Log (Inventory + Damage Reports)
    $master_log_sql = "
        SELECT combined.*, p.product_name, p.unit_cost, u.first_name
        FROM (
            (SELECT timestamp AS dt, action_type, product_id, user_id, 'Inventory' AS src, '' AS sub_status, 0 AS qty_dmg 
             FROM inventory_log)
            UNION ALL
            (SELECT date_reported AS dt, 'DAMAGE REPORT' AS action_type, product_id, reported_by_user_id AS user_id, 'Damaged' AS src, status AS sub_status, quantity_damaged AS qty_dmg 
             FROM damaged_products 
             WHERE status LIKE 'PENDING_%')
        ) AS combined
        LEFT JOIN products p ON combined.product_id = p.product_id
        LEFT JOIN users u ON combined.user_id = u.user_id
        ORDER BY combined.dt DESC
        LIMIT 6
    ";
    $result_activity_log = $conn->query($master_log_sql);
