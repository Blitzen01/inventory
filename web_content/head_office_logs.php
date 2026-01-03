<?php
    session_start();
    include "../src/cdn/cdn_links.php";
    include "../render/connection.php";
    include "../src/fetch/head_office_logs_querry.php"; // Ensure this file defines $logs, $total_rows, $limit, $page, and $total_pages
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Head Office Allocations</title>
        <style>
            body { background-color: #f4f7f6; padding-top: 70px; }
            .card { border: none; border-radius: 10px; }
            .log-row { position: relative; border-left: 4px solid transparent; transition: all 0.2s; }
            .log-transfer { border-left-color: #0d6efd; }
            .log-restock { border-left-color: #198754; }
            .log-damage { border-left-color: #dc3545; }
            
            .table thead th { 
                background-color: #f8f9fa; 
                text-transform: uppercase; 
                font-size: 11px; 
                letter-spacing: 1px; 
                color: #555;
                border-top: none;
            }
            .italic { font-style: italic; }
            .pagination .page-link { color: #333; border: none; margin: 0 3px;}
            .pagination .active .page-link { background-color: #212529; color: white; }
        </style>
    </head>
    <body class="bg-light">

        <?php include "../nav/header.php"; ?> 

        <div class="container-fluid px-4">
            <div class="row align-items-center mb-4">
                <div class="col-md-6">
                    <h3 class="fw-bold text-dark m-0"><i class="fa-solid fa-building-shield me-2"></i>Head Office Allocations</h3>
                </div>
                <div class="col-md-6 text-md-end d-flex justify-content-end align-items-center gap-3">
                    <button class="btn btn-dark btn-sm shadow-sm" onclick="window.location.reload();">
                        <i class="fa-solid fa-rotate me-1"></i> Refresh
                    </button>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center p-0 px-4 py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-dark text-uppercase small" style="letter-spacing: 1px;">Internal Movement History</h5>
                    <form method="GET" class="d-flex align-items-center gap-2 mb-0">
                        <label class="small text-muted">Show:</label>
                        <select name="limit" class="form-select form-select-sm shadow-sm" onchange="this.form.submit()" style="width: 80px; border-radius: 6px;">
                            <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                            <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
                        </select>
                        <?php if(!empty($search)): ?>
                            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                        <?php endif; ?>
                    </form>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-muted small text-uppercase">
                                    <th class="ps-4">Timestamp</th>
                                    <th>Admin</th>
                                    <th>Item Details</th>
                                    <th>Route</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="pe-4">Purpose / Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($logs)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">No Head Office allocations found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($logs as $log): 
                                        $logTypeClass = match(strtolower($log['action_type'])) {
                                            'transfer' => 'log-transfer',
                                            'restock'   => 'log-restock',
                                            'damage report' => 'log-damage',
                                            default => ''
                                        };
                                    ?>
                                    <tr class="log-row <?= $logTypeClass; ?>">
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark" style="font-size: 0.85rem;">
                                                <?= date('M d, Y', strtotime($log['created_at'])) ?>
                                            </div>
                                            <div class="text-muted small"><?= date('h:i A', strtotime($log['created_at'])) ?></div>
                                        </td>
                                        <td>
                                            <span class="small fw-medium">
                                                <i class="fa-solid fa-user-check me-1 text-muted"></i> 
                                                <?= htmlspecialchars($log['username'] ?? 'System') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-primary mb-0"><?= htmlspecialchars($log['product_name']) ?></div>
                                            <small class="text-muted font-monospace"><?= htmlspecialchars($log['sku']) ?></small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center small">
                                                <span class="badge bg-light text-dark border shadow-sm" style="font-size: 9px; font-weight: 600;">
                                                    <?= htmlspecialchars($log['origin_branch']) ?>
                                                </span>
                                                <i class="fa-solid fa-arrow-right-long text-muted mx-2" style="font-size: 10px;"></i>
                                                <span class="badge bg-dark text-white shadow-sm" style="font-size: 9px; font-weight: 600;">
                                                    <?= htmlspecialchars($log['destination_branch']) ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge rounded-pill bg-dark px-3"><?= number_format($log['quantity']) ?></span>
                                        </td>
                                        <td class="pe-4">
                                            <p class="small text-muted mb-0 italic">
                                                <?= $log['remarks'] ? htmlspecialchars($log['remarks']) : '<span class="opacity-50">N/A</span>'; ?>
                                            </p>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3 border-top">
                    <div class="text-muted small">
                        Showing <b><?= count($logs) ?></b> of <?= $total_rows ?> items
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link shadow-sm" href="?page=<?= $page-1 ?>&limit=<?= $limit ?>">Prev</a>
                            </li>

                            <li class="page-item active">
                                <span class="page-link bg-dark border-dark text-white px-3">
                                    <?= $page ?>
                                </span>
                            </li>

                            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                <a class="page-link shadow-sm" href="?page=<?= $page+1 ?>&limit=<?= $limit ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </body>
</html>