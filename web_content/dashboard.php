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
    include "../render/modal.php";
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Dashboard | Stock Focus</title>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            
            /* Quick Action Styles */
            .btn-action { 
                display: flex; align-items: center; padding: 15px; margin-bottom: 10px; 
                background: white; border-radius: 10px; text-decoration: none; 
                transition: 0.2s; border: 1px solid #eee;
            }
            .btn-action:hover { transform: translateX(5px); background: #fafafa; box-shadow: 0 4px 8px rgba(0,0,0,0.05); }

            .search-container {
                display: flex;
                align-items: center;
                background: #ffffff;
                padding: 6px 10px;
                border-radius: 10px;
                border: 1px solid #e0e0e0;
                transition: all 0.2s ease;
            }

            /* Glow effect when typing */
            .search-container:focus-within {
                border-color: #4e73df;
                box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1) !important;
            }

            .search-icon {
                color: #a0a0a0;
                margin-left: 10px;
                font-size: 0.9rem;
            }

            .search-input {
                border: none;
                outline: none;
                width: 100%;
                padding: 8px 12px;
                font-size: 0.875rem; /* Smaller, cleaner font size */
                color: #444;
            }

            .search-input::placeholder {
                color: #bbb;
            }

            .search-shortcut {
                background: #f8f9fa;
                border: 1px solid #e9ecef;
                color: #999;
                padding: 2px 8px;
                border-radius: 5px;
                font-size: 0.75rem;
                font-weight: bold;
                margin-right: 10px;
                display: none; /* Hidden on mobile */
            }

            @media (min-width: 768px) {
                .search-shortcut { display: block; }
            }

            .search-btn {
                background: #4e73df;
                color: white;
                border: none;
                padding: 6px 16px;
                border-radius: 8px;
                font-size: 0.85rem;
                font-weight: 600;
                transition: 0.2s;
            }

            .search-btn:hover {
                background: #2e59d9;
            }
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
            </div>
            
            <div class="row mb-4 justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <form action="inventory.php" method="GET"> 
                        <div class="search-container shadow-sm">
                            <i class="fa-solid fa-magnifying-glass search-icon"></i>
                            <input type="text" name="search" class="search-input" 
                                placeholder="Search anything (press '/' to focus)..." 
                                autocomplete="off">
                            <div class="search-shortcut">/</div>
                            <button type="submit" class="search-btn">Find</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row row-cols-1 row-cols-md-3 row-cols-xl-5 mb-4">
                <div class="col mb-3">
                    <div class="card metric-card h-100 py-2 border-primary">
                        <div class="card-body">
                            <div class="metric-label text-primary mb-1">Active Users</div>
                            <div class="h4 m-0 fw-bold"><?= $active_users ?></div>
                            <i class="fa-solid fa-users position-absolute end-0 top-50 translate-middle-y me-3 opacity-25 fa-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="col mb-3">
                    <div class="card metric-card h-100 py-2 border-danger">
                        <div class="card-body">
                            <div class="metric-label text-danger mb-1">Low Stock Alerts</div>
                            <div class="h4 m-0 fw-bold text-danger"><?= $low_stock_count ?></div>
                            <i class="fa-solid fa-triangle-exclamation position-absolute end-0 top-50 translate-middle-y me-3 opacity-25 fa-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="col mb-3">
                    <div class="card metric-card h-100 py-2 border-warning">
                        <div class="card-body">
                            <div class="metric-label text-warning mb-1">Pending Items</div>
                            <div class="h4 m-0 fw-bold text-warning"><?= number_format($pending_damage_qty) ?></div>
                            <i class="fa-solid fa-boxes-stacked position-absolute end-0 top-50 translate-middle-y me-3 opacity-25 fa-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="col mb-3">
                    <div class="card metric-card h-100 py-2 border-success">
                        <div class="card-body">
                            <div class="metric-label text-success mb-1">Total Value</div>
                            <div class="h5 m-0 fw-bold">₱<?= number_format($total_val, 0) ?></div>
                            <i class="fa-solid fa-wallet position-absolute end-0 top-50 translate-middle-y me-3 opacity-25 fa-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="col mb-3">
                    <div class="card metric-card h-100 py-2 border-dark" style="background-color: #fff5f5;">
                        <div class="card-body">
                            <div class="metric-label text-danger mb-1">Damage Loss</div>
                            <div class="h5 m-0 fw-bold text-danger">₱<?= number_format($total_damage_cost, 0) ?></div>
                            <i class="fa-solid fa-chart-line-down position-absolute end-0 top-50 translate-middle-y me-3 opacity-25 fa-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-12 mb-4">
                    <div class="card shadow-sm h-100">
                        <?php 
                            $health_pct = ($total_products > 0) ? round((($total_products - $low_stock_count) / $total_products) * 100) : 100;
                            $bar_color = ($health_pct > 80) ? 'bg-success' : (($health_pct > 50) ? 'bg-warning' : 'bg-danger');
                        ?>
                        <div class="p-3 border-bottom bg-light d-flex align-items-center justify-content-between">
                            <span class="small fw-bold text-uppercase">Overall Stock Health</span>
                            <div class="d-flex align-items-center w-50">
                                <div class="progress w-100 me-2" style="height: 8px;">
                                    <div class="progress-bar <?= $bar_color ?>" style="width: <?= $health_pct ?>%"></div>
                                </div>
                                <span class="small fw-bold"><?= $health_pct ?>%</span>
                            </div>
                        </div>
                        <div class="card-header bg-white py-3 border-0">
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
                                            <tr><td colspan="5" class="text-center py-4 text-muted">Healthy stock levels.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 fw-bold text-dark"><i class="fa-solid fa-clock-rotate-left me-2"></i>Recent Logs</h6>
                        </div>
                        <div class="card-body">
                            <?php while($row = $result_activity_log->fetch_assoc()): 
                                $class = ""; $label = $row['action_type'];
                                if($row['src'] === 'Damaged') {
                                    $class = "activity-warning";
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
                                        <span class="small fw-bold"><?= $label ?></span>
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

                <div class="col-lg-4 mb-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-bolt me-2 text-warning"></i>Quick Actions</h6>
                    
                    <div role="button" data-bs-toggle="modal" data-bs-target="#addProductModal" class="btn-action shadow-sm">
                        <i class="fa-solid fa-plus-circle text-success fa-xl me-3"></i>
                        <div>
                            <div class="fw-bold text-dark small">Add Stock</div>
                            <div class="text-muted small" style="font-size: 11px;">Restock an existing item</div>
                        </div>
                    </div>

                    <div role="button" data-bs-toggle="modal" data-bs-target="#addUserModal" class="btn-action shadow-sm">
                        <i class="fa-solid fa-user-plus text-info fa-xl me-3"></i>
                        <div>
                            <div class="fw-bold text-dark small">Add Account</div>
                            <div class="text-muted small" style="font-size: 11px;">Create a new team member</div>
                        </div>
                    </div>

                    <div role="button" data-bs-toggle="modal" data-bs-target="#reportDamageModal" class="btn-action shadow-sm">
                        <i class="fa-solid fa-circle-exclamation text-danger fa-xl me-3"></i>
                        <div>
                            <div class="fw-bold text-dark small">Report Damage</div>
                            <div class="text-muted small" style="font-size: 11px;">Log broken or expired items</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="../src/script/add_account_script.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const productSelect = document.getElementById('productSelect');
                const qtyInput = document.getElementById('qtyInput');
                const stockBadge = document.getElementById('stockBadge');
                const submitBtn = document.getElementById('submitDamage');

                productSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    
                    if (selectedOption.value !== "") {
                        const availableStock = selectedOption.getAttribute('data-stock');
                        
                        // Enable input and set the HTML max attribute
                        qtyInput.disabled = false;
                        qtyInput.max = availableStock;
                        qtyInput.placeholder = `Max: ${availableStock}`;
                        
                        // Update the UI Badge
                        stockBadge.innerHTML = `Max: ${availableStock}`;
                        stockBadge.classList.replace('bg-secondary-subtle', 'bg-info-subtle');
                        stockBadge.classList.replace('text-secondary', 'text-info');
                        
                        // Clear current value to avoid errors
                        qtyInput.value = '';
                    } else {
                        qtyInput.disabled = true;
                        stockBadge.innerHTML = `Max: 0`;
                    }
                });

                // Prevent typing numbers higher than max
                qtyInput.addEventListener('input', function() {
                    const max = parseInt(this.max);
                    const val = parseInt(this.value);

                    if (val > max) {
                        this.value = max; // Force it to the max value
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                    }
                });
            });

            document.addEventListener('keydown', function(e) {
                // If user presses "/" and isn't already typing in an input
                if (e.key === '/' && document.activeElement.tagName !== 'INPUT') {
                    e.preventDefault();
                    document.querySelector('.search-input').focus();
                }
            });
        </script>
    </body>
</html>