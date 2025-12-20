<?php
    session_start();
    include "../src/cdn/cdn_links.php";
    include "../render/connection.php";

    // 1. We now pull from head_office_logs
    // 2. We don't need the WHERE clause to be strict because everything 
    //    in this table is ALREADY a Head Office allocation.
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

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Head Office Allocations</title>
        <style>
            body { padding-top: 56px; }
            .log-row { position: relative; border-left: 4px solid transparent; transition: all 0.2s; }
            .log-transfer { border-left-color: #0d6efd; }
            .log-restock { border-left-color: #198754; }
            .log-damage { border-left-color: #dc3545; }
            
            .table-hover tbody tr:hover {
                background-color: rgba(0,0,0,.02);
                transform: scale(1.001);
            }
            .italic { font-style: italic; }
        </style>
    </head>
    <body class="bg-light">

        <?php include "../nav/header.php"; ?> 

        <div class="container-fluid mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="fw-light text-dark"><i class="fa-solid fa-building-shield me-2"></i> Head Office Allocations</h1>
                </div>
                <div>
                    <button class="btn btn-primary shadow-sm" onclick="window.location.reload();">
                        <i class="fa-solid fa-rotate me-1"></i> Refresh
                    </button>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark text-uppercase small" style="letter-spacing: 1px;">Internal Movement History</h5>
                    <span class="badge rounded-pill bg-primary"><?= count($logs); ?> Records Found</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="text-muted small text-uppercase">
                                    <th class="ps-4">Timestamp</th>
                                    <th>Admin</th>
                                    <th>Item Details</th>
                                    <th>Route</th>
                                    <th class="text-center">Quantity</th>
                                    <th>Purpose / Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($logs)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">No Head Office allocations found.</td>
                                    </tr>
                                <?php endif; ?>

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
                                        <span class="small fw-medium"><i class="fa-solid fa-user-check me-1 text-muted"></i> <?= htmlspecialchars($log['username'] ?? 'System') ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-primary mb-0"><?= htmlspecialchars($log['product_name']) ?></div>
                                        <small class="text-muted font-monospace"><?= htmlspecialchars($log['sku']) ?></small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center small">
                                            <span class="<?= strtolower($log['action_type']) === 'return' ? 'badge bg-soft text-primary border-primary' : 'text-muted' ?>">
                                                <?= htmlspecialchars($log['origin_branch']) ?>
                                            </span>

                                            <i class="fa-solid fa-chevron-right mx-2 text-muted" style="font-size: 0.6rem;"></i>

                                            <span class="<?= strtolower($log['action_type']) === 'return' ? 'text-muted' : 'badge bg-soft text-primary border-primary' ?>">
                                                <?= htmlspecialchars($log['destination_branch']) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill bg-dark px-3"><?= number_format($log['quantity']) ?></span>
                                    </td>
                                    <td>
                                        <p class="small text-muted mb-0 italic">
                                            <?= $log['remarks'] ? htmlspecialchars($log['remarks']) : '<span class="opacity-50">N/A</span>'; ?>
                                        </p>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>