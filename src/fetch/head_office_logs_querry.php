<?php
    // --- PAGINATION SETTINGS ---
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 15;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // --- GET TOTAL COUNT ---
    $count_result = $conn->query("SELECT COUNT(*) as total FROM head_office_logs");
    $total_rows = $count_result->fetch_assoc()['total'];
    $total_pages = ceil($total_rows / $limit);

    // --- FETCH LOGS WITH LIMIT ---
    // Replace your existing $logs query with something like this:
    $sql = "SELECT l.*, p.product_name, p.sku, u.username 
            FROM head_office_logs l
            LEFT JOIN products p ON l.product_id = p.product_id
            LEFT JOIN users u ON l.user_id = u.user_id
            ORDER BY l.created_at DESC 
            LIMIT $limit OFFSET $offset";

    $result = $conn->query($sql);
    $logs = $result->fetch_all(MYSQLI_ASSOC);
?>