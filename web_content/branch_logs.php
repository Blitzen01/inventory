<?php
    session_start();
    include "../src/cdn/cdn_links.php";
    include "../render/connection.php";
    include "../src/fetch/branch_logs_querry.php";
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Branch Transaction Logs</title>
        <style>
            body { padding-top: 56px; }
            /* The colored bar on the left of the row */
            .log-row { position: relative; border-left: 4px solid transparent; transition: all 0.2s; }
            .log-transfer { border-left-color: #0d6efd; } /* Blue */
            .log-restock { border-left-color: #198754; }  /* Green */
            .log-damage { border-left-color: #dc3545; }   /* Red */
            .log-adjustment { border-left-color: #ffc107; } /* Yellow */
            
            .table-hover tbody tr:hover {
                background-color: rgba(0,0,0,.02);
                transform: scale(1.002);
                box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            }
            .badge-soft { font-weight: 600; padding: 0.35em 0.65em; }
        </style>
    </head>
    <body class="bg-light">

        <?php include "../nav/header.php"; ?> 

        <div class="container-fluid mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="fw-light text-dark"><i class="fa-solid fa-clipboard-list me-2"></i> Branch Logs</h1>
                <div>
                    <button class="btn btn-primary shadow-sm" onclick="window.location.reload();">
                        <i class="fa-solid fa-rotate me-1"></i> Refresh
                    </button>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark">Transaction History</h5>
                    <span class="badge rounded-pill bg-light text-dark border"><?= count($logs); ?> Total Entries</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="text-muted small text-uppercase">
                                    <th class="ps-4">Details</th>
                                    <th>User</th>
                                    <th>Product & SKU</th>
                                    <th>Movement Path</th>
                                    <th class="text-center">Qty</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): 
                                    // Match the border color to the action type
                                    $logTypeClass = match(strtolower($log['action_type'])) {
                                        'transfer' => 'log-transfer',
                                        'restock'   => 'log-restock',
                                        'damage report' => 'log-damage',
                                        'adjustment' => 'log-adjustment',
                                        default => ''
                                    };

                                    $badgeClass = match(strtolower($log['action_type'])) {
                                        'transfer' => 'text-bg-primary',
                                        'restock'   => 'text-bg-success',
                                        'damage report' => 'text-bg-danger',
                                        default => 'text-bg-warning'
                                    };
                                ?>
                                <tr class="log-row <?= $logTypeClass; ?>">
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark" style="font-size: 0.85rem;">
                                            <?= date('M d, Y', strtotime($log['created_at'])) ?>
                                        </div>
                                        <div class="text-muted small"><?= date('h:i A', strtotime($log['created_at'])) ?></div>
                                        <span class="badge <?= $badgeClass; ?> mt-1" style="font-size: 0.7rem;">
                                            <?= strtoupper($log['action_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2" style="width:30px; height:30px;">
                                                <i class="fa-solid fa-user-ninja text-muted small"></i>
                                            </div>
                                            <span class="small fw-medium"><?= htmlspecialchars($log['username']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-primary mb-0"><?= htmlspecialchars($log['product_name']) ?></div>
                                        <small class="text-muted font-monospace"><?= htmlspecialchars($log['sku']) ?></small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center small">
                                            <span class="text-muted"><?= htmlspecialchars($log['origin_branch'] ?: 'Source') ?></span>
                                            <i class="fa-solid fa-arrow-right-long mx-2 text-muted" style="font-size: 0.7rem;"></i>
                                            <span class="fw-bold text-dark"><?= htmlspecialchars($log['destination_branch'] ?: 'Dest.') ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill bg-dark px-3"><?= number_format($log['quantity']) ?></span>
                                    </td>
                                    <td>
                                        <p class="small text-muted mb-0 italic" style="max-width: 250px;">
                                            <?= $log['remarks'] ? htmlspecialchars($log['remarks']) : '<span class="opacity-50">-- No remarks --</span>'; ?>
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