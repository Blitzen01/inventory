<?php
    session_start();

    include "../src/cdn/cdn_links.php";
    include "../render/connection.php";
    include "../nav/header.php";
    include "../src/fetch/inventory_logs_querry.php";
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Inventory Logs | Management</title>
        <style>
            body { background-color: #f4f7f6; padding-top: 70px; }
            .card { border: none; border-radius: 10px; }
            .table thead th { 
                background-color: #f8f9fa; 
                text-transform: uppercase; 
                font-size: 11px; 
                letter-spacing: 1px; 
                color: #555;
                border-top: none;
            }
            
            /* Status Pill / Badge Design */
            .status-pill { font-size: 10px; font-weight: 700; padding: 4px 10px; letter-spacing: 0.5px; }
            
            /* Pagination Logic: Dark Active, Light Inactive */
            .pagination .page-link { 
                border: 1px solid #dee2e6;
                color: #212529; 
                padding: 5px 12px;
                font-weight: 500;
                transition: all 0.2s;
            }
            .pagination .page-item.active .page-link { 
                background-color: #212529 !important; 
                border-color: #212529 !important; 
                color: #ffffff !important; 
            }
            .pagination .page-link:hover { background-color: #e9ecef; color: #000; }
            
            .text-italic { font-style: italic; opacity: 0.8; }
        </style>
    </head>
<body>

<div class="container-fluid px-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold text-dark m-0">Inventory Activity</h3>
            <p class="text-muted small">Monitor stock movements and system adjustments</p>
        </div>
        <div class="col-md-6 text-md-end">
            <button class="btn btn-outline-dark btn-sm shadow-sm" onclick="window.location.href='inventory_logs.php'">
                <i class="fa-solid fa-sync"></i> Reset View
            </button>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control form-control-sm search-input" placeholder="Search SKU or Product..." value="<?= htmlspecialchars($search) ?>">
                        <button class="btn btn-dark btn-sm search-btn" type="submit">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="d-flex align-items-center gap-2">
                        <label class="small text-muted">Show:</label>
                        <select name="limit" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 100px;">
                            <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                            <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>20</option>
                            <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-5 text-end">
                    <span class="badge bg-light text-dark border p-2">
                        Total Logs: <?= $total_results ?>
                    </span>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Timestamp</th>
                            <th>Action</th>
                            <th>Product Information</th>
                            <th class="text-center">Qty</th>
                            <th>Responsible User</th>
                            <th class="pe-4">Details/Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($inventoryLogsResult && $inventoryLogsResult->num_rows > 0): ?>
                            <?php while ($row = $inventoryLogsResult->fetch_assoc()): 
                                $action = $row['action_type'];
                                
                                // Logic for Icons and Colors based on Action
                                $badge_class = 'bg-light text-dark border';
                                $icon = 'fa-circle-info';

                                if (stripos($action, 'Add') !== false) {
                                    $badge_class = 'bg-success text-white';
                                    $icon = 'fa-plus-circle';
                                } elseif (stripos($action, 'Edit') !== false || stripos($action, 'Update') !== false) {
                                    $badge_class = 'bg-warning text-dark';
                                    $icon = 'fa-pen-to-square';
                                } elseif (stripos($action, 'Delete') !== false) {
                                    $badge_class = 'bg-danger text-white';
                                    $icon = 'fa-trash-can';
                                } elseif (stripos($action, 'Damage') !== false) {
                                    $badge_class = 'bg-secondary text-white';
                                    $icon = 'fa-screwdriver-wrench';
                                }

                                $qty = (int)$row['quantity_change'];
                                $qty_color = $qty > 0 ? 'text-success' : ($qty < 0 ? 'text-danger' : 'text-muted');
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark" style="font-size: 13px;"><?= time_ago($row['timestamp']) ?></div>
                                    <small class="text-muted" style="font-size: 10px;"><?= date('d M Y, h:i A', strtotime($row['timestamp'])) ?></small>
                                </td>
                                <td>
                                    <span class="badge rounded-pill status-pill <?= $badge_class ?> shadow-sm">
                                        <i class="fa-solid <?= $icon ?> me-1"></i> <?= strtoupper($action) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($row['product_name'] ?? 'N/A') ?></div>
                                    <small class="text-muted text-uppercase" style="font-size: 11px;"><?= htmlspecialchars($row['sku'] ?? 'NO-SKU') ?></small>
                                </td>
                                <td class="text-center fw-bold <?= $qty_color ?>">
                                    <?php if($qty > 0): ?>
                                        <i class="fa-solid fa-arrow-trend-up me-1"></i>+<?= $qty ?>
                                    <?php elseif($qty < 0): ?>
                                        <i class="fa-solid fa-arrow-trend-down me-1"></i><?= $qty ?>
                                    <?php else: ?>
                                        <span class="text-muted">--</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px; font-size: 10px;">
                                            <?= strtoupper(substr($row['first_name'], 0, 1)) ?>
                                        </div>
                                        <span class="small"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name'] ?: 'System') ?></span>
                                    </div>
                                </td>
                                <td class="pe-4">
                                    <div class="small text-dark fw-medium"><?= htmlspecialchars($row['log_details']) ?></div>
                                    <div class="text-muted small text-italic">"<?= htmlspecialchars($row['remarks'] ?: 'No remarks provided') ?>"</div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-5 text-muted">No logs matching your criteria.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3 border-top">
            <div class="text-muted small">
                <?php 
                    $start = $offset + 1;
                    $end = min($offset + $limit, $total_results);
                ?>
                Showing <b><?= $start ?></b> to <b><?= $end ?></b> of <?= $total_results ?> items
            </div>
            <nav>
                <ul class="pagination pagination-sm justify-content-end mb-0">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link shadow-sm" href="?page=<?= $page - 1 ?>&limit=<?= $limit ?>&search=<?= $search ?>">Prev</a>
                    </li>

                    <li class="page-item active">
                        <span class="page-link bg-dark border-dark text-white">
                            <?= $page ?>
                        </span>
                    </li>

                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link shadow-sm" href="?page=<?= $page + 1 ?>&limit=<?= $limit ?>&search=<?= $search ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

</body>
</html>