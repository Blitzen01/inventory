<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // 2. Check for login status
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        // If the user is NOT logged in, redirect them to the login page
        header("Location: login.php");
        exit(); // Stop script execution immediately after redirect
    }

    include "../src/cdn/cdn_links.php";
    include "../render/connection.php";
    include "../src/cdn/cdn_links.php";


    // Check if the connection is available
    if (!isset($conn)) {
        die("Error: Database connection not established. Check connection.php.");
    }

    function time_ago($timestamp) {
        try {
            $time = new DateTime($timestamp);
            $now  = new DateTime("now");
                        
            $diff = $now->format('U.u') - $time->format('U.u');


            if ($diff < 2) return "just now"; 
            if ($diff < 60) return $diff . " seconds ago";
            if ($diff < 3600) return floor($diff / 60) . " minutes ago";
            if ($diff < 86400) return floor($diff / 3600) . " hours ago";

            return $time->format("M j, g:i a");
        }
        catch (Exception $e) {
            return "Unknown time";
        }
    }
    $sql_users = "SELECT COUNT(user_id) AS active_users FROM users WHERE is_active = 1";
    $result_users = $conn->query($sql_users);
    $active_users = $result_users->fetch_assoc()['active_users'] ?? 0;

    // Metric 2: Low Stock Items (Count products where stock_level <= min_threshold)
    $sql_low_stock = "SELECT COUNT(product_id) AS low_stock_count FROM products WHERE stock_level <= min_threshold AND stock_level > 0";
    $result_low_stock = $conn->query($sql_low_stock);
    $low_stock_count = $result_low_stock->fetch_assoc()['low_stock_count'] ?? 0;

    // Metric 3: Total Products Tracked (Count all products)
    $sql_total_products = "SELECT COUNT(product_id) AS total_products FROM products";
    $result_total_products = $conn->query($sql_total_products);
    $total_products = $result_total_products->fetch_assoc()['total_products'] ?? 0;
    
    $sql_inventory_value = "
        SELECT SUM(p.stock_level * p.unit_cost) AS total_value
        FROM products p
        WHERE p.stock_level > 0;
    ";
    $result_value = $conn->query($sql_inventory_value);
    $total_inventory_value = $result_value->fetch_assoc()['total_value'] ?? 0;
    $display_value = '₱' . number_format($total_inventory_value, 2);

    // Table 1: Critical Low Stock Report (Select top 5 items with stock_level <= min_threshold)
    $sql_critical_stock = "SELECT product_id, product_name, stock_level, min_threshold 
                            FROM products 
                            WHERE stock_level <= min_threshold AND stock_level > 0
                            ORDER BY (min_threshold - stock_level) DESC
                            LIMIT 5";
    $result_critical_stock = $conn->query($sql_critical_stock);

    // Table 2: Recent Inventory Activity (Select top 5 log entries)
    $sql_activity_log = "SELECT 
                            il.timestamp, il.action_type, il.quantity_change, il.log_details,
                            p.product_name, u.first_name, u.last_name
                          FROM inventory_log il
                          LEFT JOIN products p ON il.product_id = p.product_id
                          LEFT JOIN users u ON il.user_id = u.user_id
                          ORDER BY il.timestamp DESC
                          LIMIT 5";
    $result_activity_log = $conn->query($sql_activity_log);
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Inventory Dashboard - Stock Focus</title>
        <style>
            body {
                padding-top: 56px;
            }
            /* Custom styles for professional cards */
            .metric-card {
                transition: transform 0.2s, box-shadow 0.2s;
                border: 1px solid #e9ecef;
                border-left: 5px solid;
            }
            .metric-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
            }
            /* Specific card colors */
            .border-left-primary { border-left-color: #0d6efd !important; }
            .border-left-danger { border-left-color: #dc3545 !important; }
            .border-left-info { border-left-color: #0dcaf0 !important; }
            .border-left-success { border-left-color: #198754 !important; }
        </style>
    </head>
    <body class="bg-light">
        
        <?php include "../nav/header.php"; ?>
        
        <div class="container-fluid mt-4"> 
            <h1 class="mb-4 fw-light text-dark"><i class="fa-solid fa-tachometer-alt me-2"></i> Stock Monitoring Overview</h1>

            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card metric-card h-100 py-2 border-left-primary">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col me-2">
                                    <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                        Active System Users
                                    </div>
                                    <div class="h5 mb-0 fw-bold text-gray-800">
                                        <?php echo $active_users; ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fa-solid fa-user-check fa-2x text-primary opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card metric-card h-100 py-2 border-left-danger">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col me-2">
                                    <div class="text-xs fw-bold text-danger text-uppercase mb-1">
                                        Low Stock Items (Needs Re-stock)
                                    </div>
                                    <div class="h5 mb-0 fw-bold text-gray-800">
                                        <?php echo $low_stock_count; ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fa-solid fa-exclamation-triangle fa-2x text-danger opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card metric-card h-100 py-2 border-left-success">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col me-2">
                                    <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                        Total Unique Products
                                    </div>
                                    <div class="h5 mb-0 fw-bold text-gray-800">
                                        <?php echo $total_products; ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fa-solid fa-boxes fa-2x text-success opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card metric-card h-100 py-2 border-left-info">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col me-2">
                                    <div class="text-xs fw-bold text-info text-uppercase mb-1">
                                        Est. Inventory Value
                                    </div>
                                    <div class="h5 mb-0 fw-bold text-gray-800">
                                        <?php echo $display_value; ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fa-solid fa-peso-sign fa-2x text-info opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-7 mb-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 fw-bold text-danger">⚠️ Critical Low Stock Report</h6>
                            <a href="inventory.php?filter=low_stock" class="btn btn-sm btn-outline-secondary">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product ID</th>
                                            <th>Product Name</th>
                                            <th>Current Stock</th>
                                            <th>Min. Threshold</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        if ($result_critical_stock && $result_critical_stock->num_rows > 0) {
                                            while($row = $result_critical_stock->fetch_assoc()) {
                                                // Determine the text color based on stock level
                                                $stock_color = ($row['stock_level'] < $row['min_threshold'] / 2) ? 'text-danger' : 'text-warning';
                                                
                                                echo "<tr>";
                                                echo "<td>{$row['product_id']}</td>";
                                                echo "<td>{$row['product_name']}</td>";
                                                echo "<td class='fw-bold {$stock_color}'>{$row['stock_level']}</td>";
                                                echo "<td>{$row['min_threshold']}</td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo '<tr><td colspan="4" class="text-center text-muted py-3">No products are currently below the minimum stock threshold.</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5 mb-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-history me-1"></i> Recent Inventory Activity</h6>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <?php 
                                if ($result_activity_log && $result_activity_log->num_rows > 0) {
                                    while($row = $result_activity_log->fetch_assoc()) {
                                        // Customize display based on action type
                                        $icon = '';
                                        $text_color = 'text-primary';
                                        $product_name = $row['product_name'] ?? 'N/A';
                                        $user_name = trim($row['first_name'] . ' ' . $row['last_name']);
                                        $user_display = empty($user_name) ? 'System' : $user_name;
                                        
                                        $details = $row['log_details'];
                                        
                                        switch ($row['action_type']) {
                                            case 'ADD':
                                                $icon = 'fa-solid fa-circle-plus';
                                                $text_color = 'text-success';
                                                $details = "Received **{$row['quantity_change']}** units of **{$product_name}**.";
                                                break;
                                            case 'REMOVE':
                                                $icon = 'fa-solid fa-circle-minus';
                                                $text_color = 'text-danger';
                                                $details = "Deducted **{$row['quantity_change']}** units of **{$product_name}**.";
                                                break;
                                            case 'NEW_PRODUCT':
                                                $icon = 'fa-solid fa-box';
                                                $text_color = 'text-info';
                                                $details = "New product **{$product_name}** was registered.";
                                                break;
                                            default:
                                                $icon = 'fa-solid fa-circle';
                                                $text_color = 'text-secondary'; // Changed default to secondary
                                        }

                                        // Use the reusable function
                                        $time_display = time_ago($row['timestamp']);
                                        
                                        echo '<div class="list-group-item d-flex justify-content-between align-items-start">';
                                        echo '  <div class="ms-2 me-auto">';
                                        echo "    <div class='fw-bold {$text_color}'><i class='{$icon} me-1'></i> {$row['action_type']}</div>";
                                        echo "    <small>{$details} by {$user_display}.</small>";
                                        echo '  </div>';
                                        echo "  <small class='text-muted' title='{$row['timestamp']}'>{$time_display}</small>";
                                        echo '</div>';
                                    }
                                } else {
                                    echo '<div class="list-group-item text-center text-muted py-3">No recent activity found.</div>';
                                }
                                ?>
                                <div class="list-group-item text-center">
                                    <a href="activity_log.php" class="small text-decoration-none">View full log...</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>