<?php
    // --- PAGINATION & SEARCH LOGIC ---
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 15;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
    $offset = ($page - 1) * $limit;

    // --- THE MASTER UNION SQL ---
    $master_sql = "
        (SELECT log_id, timestamp AS full_datetime, action_type, quantity_change AS qty, 
                log_details, remarks, product_id, user_id, 'Inventory' AS source 
        FROM inventory_log)
        UNION ALL
        (SELECT log_id, created_at AS full_datetime, action_type, quantity AS qty, 
                CONCAT(origin_branch, ' → ', destination_branch) AS log_details, remarks, product_id, user_id, 'Head Office' AS source 
        FROM head_office_logs)
        UNION ALL
        (SELECT log_id, created_at AS full_datetime, action_type, quantity AS qty, 
                CONCAT(origin_branch, ' → ', destination_branch) AS log_details, remarks, product_id, user_id, 'Branch' AS source 
        FROM branch_logs)
        UNION ALL
        (SELECT damage_id AS log_id, date_reported AS full_datetime, 'DAMAGE REPORT' AS action_type, quantity_damaged AS qty, 
                status AS log_details, reason AS remarks, product_id, reported_by_user_id AS user_id, 'Damaged' AS source 
        FROM damaged_products)
    ";

    // --- GLOBAL SEARCH FILTER ---
    $search_where = "";
    if (!empty($search)) {
        $search_where = " WHERE 
            p.product_name LIKE '%$search%' OR 
            p.sku LIKE '%$search%' OR 
            u.first_name LIKE '%$search%' OR 
            u.last_name LIKE '%$search%' OR 
            combined.action_type LIKE '%$search%' OR 
            combined.source LIKE '%$search%' OR 
            combined.remarks LIKE '%$search%'";
    }

    // --- CALCULATE TOTAL ROWS (Filtered by search) ---
    $count_query = "SELECT COUNT(*) AS total FROM ($master_sql) AS combined 
                    LEFT JOIN products p ON combined.product_id = p.product_id
                    LEFT JOIN users u ON combined.user_id = u.user_id 
                    $search_where";
    $count_result = $conn->query($count_query);
    $total_rows = $count_result->fetch_assoc()['total'];
    $total_pages = ceil($total_rows / $limit);

    // --- FETCH PAGINATED & FILTERED DATA ---
    $sql_activity_log = "
        SELECT combined.*, p.product_name, p.sku, u.first_name, u.last_name
        FROM ($master_sql) AS combined
        LEFT JOIN products p ON combined.product_id = p.product_id
        LEFT JOIN users u ON combined.user_id = u.user_id
        $search_where
        ORDER BY full_datetime DESC 
        LIMIT $limit OFFSET $offset";

    $result_activity_log = $conn->query($sql_activity_log);

    function time_ago($timestamp) {
        $time_difference = time() - strtotime($timestamp);
        if ($time_difference < 1) return "1 sec ago";
        $periods = ["sec", "min", "hr", "day", "wk", "mo", "yr"];
        $lengths = [60, 60, 24, 7, 4.35, 12, 1000];
        for ($i = 0; $time_difference >= $lengths[$i] && $i < count($lengths) - 1; $i++) {
            $time_difference /= $lengths[$i];
        }
        $time_difference = round($time_difference);
        return "$time_difference " . $periods[$i] . ($time_difference != 1 ? "s" : "") . " ago";
    }
?>