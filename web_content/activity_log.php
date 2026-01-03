<?php
    session_start();
    require_once '../render/connection.php';
    include "../src/cdn/cdn_links.php";
    include "../src/fetch/activity_log_querry.php";
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Comprehensive Activity Log | Stock Focus</title>
        
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
            .pagination .page-link { color: #333; border: none; margin: 0 3px; border-radius: 5px; }
            .pagination .active .page-link { background-color: #212529; color: white; }
        </style>
    </head>
    <body>

        <?php include "../nav/header.php"; ?> 

        <div class="container-fluid px-4">
            <div class="row align-items-center mb-4">
                <div class="col-md-6">
                    <h3 class="fw-bold text-dark m-0"><i class="fa-solid fa-clock-rotate-left me-2"></i>Audit Trail</h3>
                    <p class="text-muted small">Showing <?= $offset + 1 ?> to <?= min($offset + $limit, $total_rows) ?> of <?= $total_rows ?> entries</p>
                </div>
                <div class="col-md-6 text-md-end d-flex justify-content-end align-items-center gap-3">
                    <form method="GET" class="d-flex align-items-center gap-2">
                        <label class="small text-muted">Show:</label>
                        <select name="limit" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 80px;">
                            <option value="15" <?= $limit == 15 ? 'selected' : '' ?>>15</option>
                            <option value="30" <?= $limit == 30 ? 'selected' : '' ?>>30</option>
                            <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                        </select>
                    </form>
                    <button class="btn btn-dark btn-sm shadow-sm" onclick="window.location.reload();">
                        <i class="fa-solid fa-rotate me-1"></i> Refresh
                    </button>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Full Timestamp</th>
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
                                <?php while($row = $result_activity_log->fetch_assoc()): 
                                    $dt = strtotime($row['full_datetime']);
                                    $source = $row['source'];
                                    
                                    // Source Color Logic
                                    $source_badge = match($source) {
                                        'Head Office' => 'bg-primary',
                                        'Branch'      => 'bg-info text-dark',
                                        'Inventory'   => 'bg-secondary',
                                        'Damaged'     => 'bg-danger', // Added this for Damage logs
                                        default       => 'bg-dark'
                                    };
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark" style="font-size: 0.85rem;">
                                            <?= date('M d, Y', $dt) ?>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            <?= date('h:i:s A', $dt) ?> </div>
                                    </td>
                                    <td>
                                        <span class="badge <?= $source_badge ?> shadow-sm" style="font-size: 10px;">
                                            <?= strtoupper($source) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                            $action = strtoupper($row['action_type']);
                                            $action_bg = "bg-light text-dark border";
                                            
                                            // Color coding based on action keywords
                                            if(strpos($action, 'RETURN') !== false) $action_bg = "bg-info text-white";
                                            if(strpos($action, 'TRANSFER') !== false || strpos($action, 'ALLOCATION') !== false) $action_bg = "bg-primary text-white";
                                            if(strpos($action, 'DAMAGE') !== false) $action_bg = "bg-danger text-white"; // Red for damages
                                        ?>
                                        <span class="status-pill <?= $action_bg ?>"><?= $action ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($row['product_name'] ?? 'Unknown') ?></div>
                                        <small class="text-muted font-monospace"><?= htmlspecialchars($row['sku'] ?? '---') ?></small>
                                    </td>
                                    <td class="text-center fw-bold">
                                        <?= number_format($row['qty']) ?>
                                    </td>
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
                                                // Handle Damage Statuses
                                                $clean_status = str_replace('PENDING_', '', $details);
                                                $damage_color = (strpos($details, 'REPAIR') !== false) ? 'text-warning border-warning' : 'text-danger border-danger';
                                            ?>
                                                <span class="badge bg-white border <?= $damage_color ?> shadow-sm" style="font-size: 10px;">
                                                    <i class="fa-solid fa-wrench me-1"></i><?= $clean_status ?>
                                                </span>

                                            <?php elseif (strpos($details, '→') !== false): 
                                                // Cool Route Design for Transfers
                                                $parts = explode(' → ', $details);
                                                $origin = $parts[0] ?? 'Unknown';
                                                $dest = $parts[1] ?? 'Unknown';
                                            ?>
                                                <div class="d-flex align-items-center gap-1">
                                                    <span class="badge bg-light text-dark border shadow-sm" style="font-size: 9px; font-weight: 600;">
                                                        <?= $origin ?>
                                                    </span>
                                                    <i class="fa-solid fa-arrow-right-long text-muted mx-1" style="font-size: 10px;"></i>
                                                    <span class="badge bg-dark text-white shadow-sm" style="font-size: 9px; font-weight: 600;">
                                                        <?= $dest ?>
                                                    </span>
                                                </div>

                                            <?php else: ?>
                                                <span class="text-muted"><?= $details ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="pe-4 small text-muted italic">
                                        <?= htmlspecialchars($row['remarks'] ?: '--') ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3 border-top">
                    <div class="text-muted small">
                        Showing <b><?= $result_activity_log->num_rows ?></b> of <?= $total_rows ?> items
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page-1 ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>">
                                    Prev
                                </a>
                            </li>

                            <li class="page-item active">
                                <span class="page-link bg-dark border-dark text-white">
                                    <?= $page ?>
                                </span>
                            </li>

                            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page+1 ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>">
                                    Next
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>



        <script>
            function filterLogs() {
                // Get search input value
                let input = document.getElementById("logSearch");
                let filter = input.value.toLowerCase();
                
                // Get the table rows
                let table = document.querySelector(".table tbody");
                let tr = table.getElementsByTagName("tr");

                // Loop through all rows
                for (let i = 0; i < tr.length; i++) {
                    // We skip the "No logs found" row if it exists
                    if (tr[i].cells.length < 2) continue;

                    // Combine text from columns: Source, Action, Product/SKU, and User
                    let source = tr[i].cells[1].textContent.toLowerCase();
                    let action = tr[i].cells[2].textContent.toLowerCase();
                    let product = tr[i].cells[3].textContent.toLowerCase();
                    let user = tr[i].cells[5].textContent.toLowerCase();

                    // If match found in any of those columns, show row, else hide
                    if (source.includes(filter) || action.includes(filter) || 
                        product.includes(filter) || user.includes(filter)) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        </script>
    </body>
</html>