<?php
    // --- PAGINATION & DATA LOGIC ---
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    $logs = [];
    $total_rows = 0;

    if (isset($pdo)) {
        // Get Total Count
        $total_rows = $pdo->query("SELECT COUNT(*) FROM branch_logs")->fetchColumn();
        $total_pages = ceil($total_rows / $limit);

        // Fetch Branch Logs
        $sql = "SELECT l.*, u.username, p.product_name, p.sku 
                FROM branch_logs l
                LEFT JOIN users u ON l.user_id = u.user_id
                LEFT JOIN products p ON l.product_id = p.product_id
                ORDER BY l.created_at DESC 
                LIMIT $limit OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $logs = $stmt->fetchAll();
    }
?>