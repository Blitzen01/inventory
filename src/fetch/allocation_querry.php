<?php
    if($_SESSION['user_type'] == 'Viewer') {
        echo '<script>window.location.href = "inventory.php";</script>';
        exit();
    }

    // Fetch Active Allocations
    $sql_active = "SELECT a.*, p.product_name, p.sku, p.brand 
                   FROM product_allocations a 
                   JOIN products p ON a.product_id = p.product_id 
                   WHERE a.status = 'Deployed' 
                   ORDER BY a.date_allocated DESC";
    $res_active = $conn->query($sql_active);
?>