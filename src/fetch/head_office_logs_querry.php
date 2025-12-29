<?php
    $sql = "SELECT l.*, u.username, p.product_name, p.sku 
            FROM head_office_logs l
            LEFT JOIN users u ON l.user_id = u.user_id
            LEFT JOIN products p ON l.product_id = p.product_id
            ORDER BY l.created_at DESC";

    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        die("Query Failed: " . mysqli_error($conn));
    }

    $logs = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>