<?php
    if (!isset($conn)) {
        die("Error: Database connection not established.");
    }

    // --- PAGINATION & FILTER LOGIC ---
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
    $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

    // Build Search Query
    $search_query = "";
    if ($search) {
        $search_query = " WHERE p.product_name LIKE '%$search%' OR p.sku LIKE '%$search%' OR il.action_type LIKE '%$search%' ";
    }

    // Get total records for pagination
    $total_query = "SELECT COUNT(*) as total FROM inventory_log il LEFT JOIN products p ON il.product_id = p.product_id $search_query";
    $total_results = $conn->query($total_query)->fetch_assoc()['total'];
    $total_pages = ceil($total_results / $limit);

    // --- FETCH DATA ---
    $sql_activity_log = "SELECT 
                            il.log_id, il.timestamp, il.action_type, 
                            il.quantity_change, il.log_details, il.remarks,
                            p.product_name, p.sku,
                            u.first_name, u.last_name
                        FROM inventory_log il
                        LEFT JOIN products p ON il.product_id = p.product_id
                        LEFT JOIN users u ON il.user_id = u.user_id
                        $search_query
                        ORDER BY il.timestamp DESC
                        LIMIT $limit OFFSET $offset";

    $inventoryLogsResult = $conn->query($sql_activity_log);

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
?>