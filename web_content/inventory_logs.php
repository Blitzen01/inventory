<?php
// Include necessary files
include "../src/cdn/cdn_links.php";
include "../render/connection.php";
include "../nav/header.php"; // ✅ INCLUDE HEADER FIRST

if (!isset($conn)) {
    die("Error: Database connection not established.");
}

// --- FETCH INVENTORY LOG DATA ---
$sql_activity_log = "SELECT 
                        il.log_id, 
                        il.timestamp, 
                        il.action_type, 
                        il.quantity_change, 
                        il.log_details,
                        il.remarks,
                        p.product_name, 
                        p.sku,
                        u.first_name, 
                        u.last_name
                    FROM inventory_log il
                    LEFT JOIN products p 
                        ON il.product_id = p.product_id
                    LEFT JOIN users u 
                        ON il.user_id = u.user_id
                    ORDER BY il.timestamp DESC
                    LIMIT 100";

$inventoryLogsResult = $conn->query($sql_activity_log);

// Helper function to format "time ago"
function time_ago($timestamp) {
    $time_difference = time() - strtotime($timestamp);
    $periods = ["second", "minute", "hour", "day", "week", "month", "year"];
    $lengths = [60, 60, 24, 7, 4.35, 12, 1000];

    if ($time_difference < 5) return "just now";

    for ($i = 0; $time_difference >= $lengths[$i] && $i < count($lengths) - 1; $i++) {
        $time_difference /= $lengths[$i];
    }

    $time_difference = round($time_difference);
    $period = $periods[$i] . ($time_difference != 1 ? "s" : "");

    return "$time_difference $period ago";
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Inventory Logs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body { padding-top: 56px; }
        .table-row-add { background-color: #d1e7dd; }
        .table-row-remove { background-color: #f8d7da; }
        .table-row-update { background-color: #fff3cd; }
    </style>
</head>

<body class="bg-light">

<div class="container-fluid mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-light text-dark">
            <i class="fa-solid fa-box-archive me-2"></i> Inventory Logs
        </h1>
        <button class="btn btn-outline-secondary shadow-sm" onclick="window.location.reload();">
            <i class="fa-solid fa-sync-alt me-2"></i> Refresh Log
        </button>
    </div>

    <div class="card shadow mb-5">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-semibold text-muted">
                Showing Most Recent Inventory Changes
            </h5>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Timestamp</th>
                            <th>Action Type</th>
                            <th>Product (SKU)</th>
                            <th>Quantity Change</th>
                            <th>User</th>
                            <th>Details</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php 
                    if ($inventoryLogsResult && $inventoryLogsResult->num_rows > 0) {
                        while ($row = $inventoryLogsResult->fetch_assoc()) {

                            $user_name = trim($row['first_name'] . ' ' . $row['last_name']);
                            $user_display = $user_name ?: "System/Admin";

                            $product_display = !empty($row['product_name']) && !empty($row['sku'])
                                ? "{$row['product_name']} ({$row['sku']})"
                                : (!empty($row['product_name']) ? $row['product_name'] : "N/A");

                            $time_display = time_ago($row['timestamp']);

                            $row_class = '';
                            $icon = '';
                            $qty_display = 'N/A';

                            switch ($row['action_type']) {
                                case 'Add Product':
                                    $row_class = 'table-row-add';
                                    $icon = '<i class="fa-solid fa-arrow-circle-up text-success me-1"></i>';
                                    $qty_display = "+" . $row['quantity_change'];
                                    break;

                                case 'Product Edit':
                                    $row_class = 'table-row-update';
                                    $icon = '<i class="fa-solid fa-pen-to-square text-warning me-1"></i>';
                                    $qty_display = $row['quantity_change'] == 0
                                        ? '—'
                                        : ($row['quantity_change'] > 0 ? '+' : '') . $row['quantity_change'];
                                    break;

                                case 'New Product':
                                    $row_class = 'table-row-update';
                                    $icon = '<i class="fa-solid fa-box-open text-info me-1"></i>';
                                    break;

                                default:
                                    $icon = '<i class="fa-solid fa-history text-secondary me-1"></i>';
                            }

                            echo "<tr class='{$row_class}'>";
                            echo "<td>{$row['log_id']}</td>";
                            echo "<td><span title='{$row['timestamp']}'>{$time_display}</span></td>";
                            echo "<td>{$icon} {$row['action_type']}</td>";
                            echo "<td>{$product_display}</td>";
                            echo "<td class='fw-bold'>{$qty_display}</td>";
                            echo "<td>{$user_display}</td>";
                            echo "<td>{$row['log_details']}</td>";
                            echo "<td>{$row['remarks']}</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo '<tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    The inventory activity log is currently empty.
                                </td>
                              </tr>';
                    }
                    ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>

</body>
</html>
