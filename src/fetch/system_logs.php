<?php
    // --- 1. LOGIC (Must be at the top) ---
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 15;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
    $offset = ($page - 1) * $limit;

    // The base query for your audit logs
    $master_sql = "
        SELECT audit_id, timestamp AS full_datetime, action_type, table_name as source, 
               record_id, old_value, new_value, changed_by AS user_id,
               'System Log' as product_name, record_id as sku, -- Placeholders if real product data isn't joined yet
               new_value as log_details, '' as remarks, 1 as qty
        FROM system_audit_logs
    ";

    $search_where = "";
    if (!empty($search)) {
        $search_where = " WHERE 
            u.first_name LIKE '%$search%' OR 
            combined.source LIKE '%$search%' OR 
            combined.action_type LIKE '%$search%'";
    }

    // Calculate Totals
    $count_query = "SELECT COUNT(*) AS total FROM ($master_sql) AS combined 
                    LEFT JOIN users u ON combined.user_id = u.user_id $search_where";
    $count_result = $conn->query($count_query);
    $total_rows = $count_result ? $count_result->fetch_assoc()['total'] : 0;
    $total_pages = ceil($total_rows / $limit);

    // Fetch Result - We use $result_activity_log to match your HTML table variable
    $sql_audit_log = "
        SELECT combined.*, u.first_name, u.last_name
        FROM ($master_sql) AS combined
        LEFT JOIN users u ON combined.user_id = u.user_id
        $search_where
        ORDER BY full_datetime DESC 
        LIMIT $limit OFFSET $offset";
    $result_activity_log = $conn->query($sql_audit_log);
?>