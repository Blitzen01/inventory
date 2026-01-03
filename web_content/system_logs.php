<?php
    session_start();
    require_once '../render/connection.php';
    include "../src/cdn/cdn_links.php";
    include "../src/fetch/system_logs.php";
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Activity Audit Trail | M-Ventory</title>
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
        .status-pill { font-size: 10px; font-weight: 700; padding: 4px 10px; border-radius: 50px; }
        .user-tag { background: #eee; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; }
        .pagination .page-link { color: #333; border: none; margin: 0 3px; }
        .pagination .active .page-link { background-color: #212529; color: white; }
    </style>
</head>
<body>

<?php include "../nav/header.php"; ?> 

<div class="container-fluid px-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold text-dark m-0"><i class="fa-solid fa-clock-rotate-left me-2"></i>Audit Trail</h3>
            <p class="text-muted small mb-0">Unified history of stock movements, transfers, and damages</p>
        </div>
        <div class="col-md-6 text-md-end">
            <button class="btn btn-dark btn-sm shadow-sm" onclick="window.location.reload();">
                <i class="fa-solid fa-rotate me-1"></i> Refresh
            </button>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center p-0 px-4 py-3 border-bottom">
            <h5 class="mb-0 fw-bold text-dark text-uppercase small" style="letter-spacing: 1px;">Activity History</h5>
            
            <form method="GET" class="d-flex align-items-center gap-2 mb-0">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search product, user, or action..." value="<?= htmlspecialchars($search) ?>">
                </div>

                <label class="small text-muted ms-2">Show:</label>
                <select name="limit" class="form-select form-select-sm shadow-sm" onchange="this.form.submit()" style="width: 80px; border-radius: 6px;">
                    <option value="15" <?= $limit == 15 ? 'selected' : '' ?>>15</option>
                    <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                </select>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Timestamp</th>
                            <th>Source</th>
                            <th>Action</th>
                            <th>Product & SKU</th>
                            <th class="text-center">Qty</th>
                            <th>User</th>
                            <th>Route / Details</th>
                            <th class="pe-4">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_activity_log && $result_activity_log->num_rows > 0): ?>
                            <?php while($row = $result_activity_log->fetch_assoc()): 
                                $dt = strtotime($row['full_datetime']);
                                $source = $row['source'];
                                $source_badge = match($source) {
                                    'Head Office' => 'bg-primary',
                                    'Branch'      => 'bg-info text-dark',
                                    'Inventory'   => 'bg-secondary',
                                    'Damaged'     => 'bg-danger',
                                    default       => 'bg-dark'
                                };
                                
                                $action = strtoupper($row['action_type']);
                                $action_bg = "bg-light text-dark border";
                                if(strpos($action, 'RETURN') !== false) $action_bg = "bg-info text-white";
                                if(strpos($action, 'TRANSFER') !== false || strpos($action, 'ALLOCATION') !== false) $action_bg = "bg-primary text-white";
                                if(strpos($action, 'DAMAGE') !== false) $action_bg = "bg-danger text-white";
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark" style="font-size: 0.85rem;"><?= date('M d, Y', $dt) ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;"><?= date('h:i:s A', $dt) ?></div>
                                </td>
                                <td>
                                    <span class="badge <?= $source_badge ?> shadow-sm" style="font-size: 10px;">
                                        <?= strtoupper($source) ?>
                                    </span>
                                </td>
                                <td><span class="status-pill <?= $action_bg ?>"><?= $action ?></span></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['product_name'] ?? 'Unknown') ?></div>
                                    <small class="text-muted font-monospace"><?= htmlspecialchars($row['sku'] ?? '---') ?></small>
                                </td>
                                <td class="text-center fw-bold"><?= number_format($row['qty']) ?></td>
                                <td>
                                    <span class="user-tag">
                                        <i class="fa-solid fa-circle-user me-1 text-muted"></i>
                                        <?= htmlspecialchars($row['first_name'] ?: "System") ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="small text-dark" style="max-width: 250px;">
                                        <?php 
                                        $details = htmlspecialchars($row['log_details']);
                                        if ($row['source'] === 'Damaged'): 
                                            $clean_status = str_replace('PENDING_', '', $details);
                                            $damage_color = (strpos($details, 'REPAIR') !== false) ? 'text-warning border-warning' : 'text-danger border-danger';
                                        ?>
                                            <span class="badge bg-white border <?= $damage_color ?> shadow-sm" style="font-size: 10px;">
                                                <i class="fa-solid fa-wrench me-1"></i><?= $clean_status ?>
                                            </span>
                                        <?php elseif (strpos($details, '→') !== false): 
                                            $parts = explode(' → ', $details);
                                        ?>
                                            <div class="d-flex align-items-center gap-1">
                                                <span class="badge bg-light text-dark border shadow-sm" style="font-size: 9px;"><?= $parts[0] ?></span>
                                                <i class="fa-solid fa-arrow-right-long text-muted mx-1" style="font-size: 10px;"></i>
                                                <span class="badge bg-dark text-white shadow-sm" style="font-size: 9px;"><?= $parts[1] ?></span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted"><?= $details ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="pe-4 small text-muted italic"><?= htmlspecialchars($row['remarks'] ?: '--') ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center py-5 text-muted">No activity found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3 border-top">
            <div class="text-muted small">
                Showing <b><?= $result_activity_log ? $result_activity_log->num_rows : 0 ?></b> of <?= $total_rows ?> items
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link shadow-sm" href="?page=<?= $page-1 ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>">Prev</a>
                    </li>
                    <li class="page-item active">
                        <span class="page-link bg-dark border-dark text-white px-3"><?= $page ?></span>
                    </li>
                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                        <a class="page-link shadow-sm" href="?page=<?= $page+1 ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

</body>
</html>