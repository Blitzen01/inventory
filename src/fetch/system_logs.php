<?php
    // --- PAGINATION & SEARCH LOGIC ---
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 15;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
    $offset = ($page - 1) * $limit;

    // --- THE MASTER UNION SQL FOR SYSTEM CHANGES ---
    // Note: This assumes you named the audit table 'system_audit_logs' as discussed
    $master_sql = "
        (SELECT audit_id, timestamp AS full_datetime, action_type, table_name, 
                record_id, old_value, new_value, changed_by AS user_id, 'System' AS source 
        FROM system_audit_logs)
    ";

    // --- GLOBAL SEARCH FILTER ---
    $search_where = "";
    if (!empty($search)) {
        $search_where = " WHERE 
            u.first_name LIKE '%$search%' OR 
            u.last_name LIKE '%$search%' OR 
            combined.table_name LIKE '%$search%' OR 
            combined.record_id LIKE '%$search%' OR 
            combined.action_type LIKE '%$search%'";
    }

    // --- CALCULATE TOTAL ROWS ---
    $count_query = "SELECT COUNT(*) AS total FROM ($master_sql) AS combined 
                    LEFT JOIN users u ON combined.user_id = u.user_id 
                    $search_where";
    $count_result = $conn->query($count_query);
    $total_rows = $count_result->fetch_assoc()['total'];
    $total_pages = ceil($total_rows / $limit);

    // --- FETCH DATA ---
    $sql_audit_log = "
        SELECT combined.*, u.first_name, u.last_name, u.role
        FROM ($master_sql) AS combined
        LEFT JOIN users u ON combined.user_id = u.user_id
        $search_where
        ORDER BY full_datetime DESC 
        LIMIT $limit OFFSET $offset";

    $result_audit_log = $conn->query($sql_audit_log);
?>