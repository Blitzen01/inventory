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
    include "../src/fetch/dashboard_querry.php";
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