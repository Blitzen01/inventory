<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: login.php");
        exit();
    }

    include "../src/cdn/cdn_links.php";
    include "../render/connection.php";

    if($_SESSION['user_type'] == 'Viewer') {
        echo '<script>window.location.href = "inventory.php";</script>';
        exit();
    }

    if (!isset($conn)) {
        die("Error: Database connection not established.");
    }

    function time_ago($timestamp) {
        $time_difference = time() - strtotime($timestamp);
        $periods = ["sec", "min", "hr", "day", "wk", "mo", "yr"];
        $lengths = [60, 60, 24, 7, 4.35, 12, 1000];
        if ($time_difference < 5) return "just now";
        for ($i = 0; $time_difference >= $lengths[$i] && $i < count($lengths) - 1; $i++) {
            $time_difference /= $lengths[$i];
        }
        $time_difference = round($time_difference);
        return "$time_difference " . $periods[$i] . ($time_difference != 1 ? "s" : "") . " ago";
    }

    // --- FETCH METRICS ---
    $active_users = $conn->query("SELECT COUNT(user_id) AS total FROM users WHERE is_active = 1")->fetch_assoc()['total'] ?? 0;
    $low_stock_count = $conn->query("SELECT COUNT(product_id) AS total FROM products WHERE stock_level <= min_threshold AND stock_level > 0")->fetch_assoc()['total'] ?? 0;
    $total_products = $conn->query("SELECT COUNT(product_id) AS total FROM products")->fetch_assoc()['total'] ?? 0;
    $total_val = $conn->query("SELECT SUM(stock_level * unit_cost) AS val FROM products WHERE stock_level > 0")->fetch_assoc()['val'] ?? 0;
    $pending_damage_qty = $conn->query("SELECT SUM(quantity_damaged) AS total FROM damaged_products WHERE status LIKE 'PENDING_%'")->fetch_assoc()['total'] ?? 0;

    // --- TABLES ---
    // FIX 1: Added unit_cost to the query
    $result_critical_stock = $conn->query("SELECT product_id, product_name, sku, stock_level, min_threshold, unit_cost FROM products WHERE stock_level <= min_threshold AND stock_level > 0 ORDER BY stock_level ASC LIMIT 5");

    // FIX 2: Wrapped the UNION query properly to ensure $result_activity_log is defined
    $master_log_sql = "
        SELECT combined.*, p.product_name, u.first_name
        FROM (
            (SELECT timestamp AS dt, action_type, product_id, user_id, 'Inventory' AS src, '' AS sub_status 
             FROM inventory_log)
            UNION ALL
            (SELECT date_reported AS dt, 'DAMAGE REPORT' AS action_type, product_id, reported_by_user_id AS user_id, 'Damaged' AS src, status AS sub_status 
             FROM damaged_products 
             WHERE status LIKE 'PENDING_%')
        ) AS combined
        LEFT JOIN products p ON combined.product_id = p.product_id
        LEFT JOIN users u ON combined.user_id = u.user_id
        ORDER BY combined.dt DESC
        LIMIT 6
    ";

    $result_activity_log = $conn->query($master_log_sql);

    // Error check to prevent the Fatal Error you saw
    if (!$result_activity_log) {
        die("Query Failed: " . $conn->error);
    }
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Dashboard | Stock Focus</title>
        <style>
            body { background-color: #f4f7f6; padding-top: 70px; }
            .card { border: none; border-radius: 12px; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
            .metric-card { border-left: 4px solid; transition: transform 0.2s; }
            .metric-card:hover { transform: translateY(-3px); }
            .metric-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
            .table thead th { background-color: #f8f9fa; font-size: 11px; text-transform: uppercase; color: #666; border: none; }
            .activity-item { border-left: 3px solid #e9ecef; padding-left: 15px; margin-bottom: 15px; position: relative; }
            .activity-item::before { content: ''; position: absolute; left: -6px; top: 0; width: 10px; height: 10px; border-radius: 50%; background: #dee2e6; }
            .activity-success { border-left-color: #198754; }
            .activity-danger { border-left-color: #dc3545; }
            .activity-warning { border-left-color: #ffc107; }
        </style>
    </head>
    <body>
        <?php include "../nav/header.php"; ?>

        <div class="container-fluid px-4">
            <div class="row align-items-center mb-4">
                <div class="col-md-6">
                    <h3 class="fw-bold text-dark m-0">System Overview</h3>
                    <p class="text-muted small">Real-time status of your inventory and team activity.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="btn-group shadow-sm">
                        <a href="inventory.php" class="btn btn-white border btn-sm">Manage Stock</a>
                        <a href="damage_monitoring.php" class="btn btn-dark btn-sm">Damage Reports</a>
                    </div>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card metric-card h-100 py-2 border-primary">
                        <div class="card-body">
                            <div class="metric-label text-primary mb-1">Active Users</div>
                            <div class="h4 m-0 fw-bold"><?= $active_users ?></div>
                            <i class="fa-solid fa-users position-absolute end-0 top-50 translate-middle-y me-3 opacity-25 fa-2x"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card metric-card h-100 py-2 border-danger">
                        <div class="card-body">
                            <div class="metric-label text-danger mb-1">Low Stock Alerts</div>
                            <div class="h4 m-0 fw-bold text-danger"><?= $low_stock_count ?></div>
                            <i class="fa-solid fa-triangle-exclamation position-absolute end-0 top-50 translate-middle-y me-3 opacity-25 fa-2x"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card metric-card h-100 py-2 border-warning">
                        <div class="card-body">
                            <div class="metric-label text-warning mb-1">Pending Damages</div>
                            <div class="h4 m-0 fw-bold text-warning"><?= number_format($pending_damage_qty) ?></div>
                            <i class="fa-solid fa-house-crack position-absolute end-0 top-50 translate-middle-y me-3 opacity-25 fa-2x"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card metric-card h-100 py-2 border-success">
                        <div class="card-body">
                            <div class="metric-label text-success mb-1">Inventory Value</div>
                            <div class="h4 m-0 fw-bold">₱<?= number_format($total_val, 2) ?></div>
                            <i class="fa-solid fa-wallet position-absolute end-0 top-50 translate-middle-y me-3 opacity-25 fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-7 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 fw-bold text-dark"><i class="fa-solid fa-bell me-2 text-danger"></i>Critical Reorder List</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">Product Info</th>
                                            <th class="text-center">Current</th>
                                            <th class="text-center">Min.</th>
                                            <th class="text-center">Est. Price</th>
                                            <th class="text-end pe-4">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($result_critical_stock->num_rows > 0): ?>
                                            <?php while($row = $result_critical_stock->fetch_assoc()): ?>
                                                <tr>
                                                    <td class="ps-4">
                                                        <div class="fw-bold"><?= $row['product_name'] ?></div>
                                                        <small class="text-muted"><?= $row['sku'] ?></small>
                                                    </td>
                                                    <td class="text-center fw-bold text-danger"><?= $row['stock_level'] ?></td>
                                                    <td class="text-center text-muted"><?= $row['min_threshold'] ?></td>
                                                    <td class="text-center text-muted">₱<?= number_format($row['unit_cost'], 2) ?></td>
                                                    <td class="text-end pe-4">
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 10px;">REORDER NOW</span>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">
                                                    All stock levels are healthy.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 fw-bold text-dark"><i class="fa-solid fa-clock-rotate-left me-2"></i>Recent Logs</h6>
                        </div>
                        <div class="card-body">
                            <?php while($row = $result_activity_log->fetch_assoc()): 
                                $class = "";
                                $label = $row['action_type'];
                                if($row['src'] === 'Damaged') {
                                    $class = "activity-warning";
                                    // This makes "PENDING_REPAIR" look like "REPAIR" for a cleaner UI
                                    $clean_status = str_replace('PENDING_', '', $row['sub_status']);
                                    $label = $label . " (" . $clean_status . ")";
                                } elseif(strpos($row['action_type'], 'Add') !== false) {
                                    $class = "activity-success";
                                } elseif(strpos($row['action_type'], 'Remove') !== false) {
                                    $class = "activity-danger";
                                }
                            ?>
                                <div class="activity-item <?= $class ?>">
                                    <div class="d-flex justify-content-between">
                                        <span class="small fw-bold">
                                            <?php if($row['src'] === 'Damaged'): ?>
                                                <i class="fa-solid fa-triangle-exclamation text-warning me-1"></i>
                                            <?php endif; ?>
                                            <?= $label ?>
                                        </span>
                                        <span class="text-muted" style="font-size: 10px;"><?= time_ago($row['dt']) ?></span>
                                    </div>
                                    <div class="text-dark small"><?= $row['product_name'] ?: 'System Change' ?></div>
                                    <div class="text-muted" style="font-size: 11px;">By <?= $row['first_name'] ?: 'System' ?></div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        <div class="card-footer bg-light border-0 py-3 text-center">
                            <a href="activity_log.php" class="small text-decoration-none fw-bold text-dark">View All Logs</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>