<?php
    // Fetch logs with User and Product names joined
    $sql = "SELECT l.*, u.username, p.product_name, p.sku 
            FROM branch_logs l
            LEFT JOIN users u ON l.user_id = u.user_id
            LEFT JOIN products p ON l.product_id = p.product_id
            ORDER BY l.created_at DESC";

    $logs = [];
    if (isset($pdo) && is_object($pdo)) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $logs = $stmt->fetchAll();
    }
?>