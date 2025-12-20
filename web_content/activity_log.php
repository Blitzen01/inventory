<?php
session_start();
require_once '../render/connection.php';
include "../src/cdn/cdn_links.php";

// --- PAGINATION & SEARCH LOGIC ---
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$offset = ($page - 1) * $limit;

// --- THE MASTER UNION SQL ---
$master_sql = "
    (SELECT log_id, timestamp AS full_datetime, action_type, quantity_change AS qty, 
            log_details, remarks, product_id, user_id, 'Inventory' AS source 
     FROM inventory_log)
    UNION ALL
    (SELECT log_id, created_at AS full_datetime, action_type, quantity AS qty, 
            CONCAT(origin_branch, ' → ', destination_branch) AS log_details, remarks, product_id, user_id, 'Head Office' AS source 
     FROM head_office_logs)
    UNION ALL
    (SELECT log_id, created_at AS full_datetime, action_type, quantity AS qty, 
            CONCAT(origin_branch, ' → ', destination_branch) AS log_details, remarks, product_id, user_id, 'Branch' AS source 
     FROM branch_logs)
    UNION ALL
    (SELECT damage_id AS log_id, date_reported AS full_datetime, 'DAMAGE REPORT' AS action_type, quantity_damaged AS qty, 
            status AS log_details, reason AS remarks, product_id, reported_by_user_id AS user_id, 'Damaged' AS source 
     FROM damaged_products)
";

// --- GLOBAL SEARCH FILTER ---
$search_where = "";
if (!empty($search)) {
    $search_where = " WHERE 
        p.product_name LIKE '%$search%' OR 
        p.sku LIKE '%$search%' OR 
        u.first_name LIKE '%$search%' OR 
        u.last_name LIKE '%$search%' OR 
        combined.action_type LIKE '%$search%' OR 
        combined.source LIKE '%$search%' OR 
        combined.remarks LIKE '%$search%'";
}

// --- CALCULATE TOTAL ROWS (Filtered by search) ---
$count_query = "SELECT COUNT(*) AS total FROM ($master_sql) AS combined 
                LEFT JOIN products p ON combined.product_id = p.product_id
                LEFT JOIN users u ON combined.user_id = u.user_id 
                $search_where";
$count_result = $conn->query($count_query);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// --- FETCH PAGINATED & FILTERED DATA ---
$sql_activity_log = "
    SELECT combined.*, p.product_name, p.sku, u.first_name, u.last_name
    FROM ($master_sql) AS combined
    LEFT JOIN products p ON combined.product_id = p.product_id
    LEFT JOIN users u ON combined.user_id = u.user_id
    $search_where
    ORDER BY full_datetime DESC 
    LIMIT $limit OFFSET $offset";

$result_activity_log = $conn->query($sql_activity_log);

function time_ago($timestamp) {
    $time_difference = time() - strtotime($timestamp);
    if ($time_difference < 1) return "1 sec ago";
    $periods = ["sec", "min", "hr", "day", "wk", "mo", "yr"];
    $lengths = [60, 60, 24, 7, 4.35, 12, 1000];
    for ($i = 0; $time_difference >= $lengths[$i] && $i < count($lengths) - 1; $i++) {
        $time_difference /= $lengths[$i];
    }
    $time_difference = round($time_difference);
    return "$time_difference " . $periods[$i] . ($time_difference != 1 ? "s" : "") . " ago";
}
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

        <div class="card-footer bg-white border-0 py-4">
            <nav>
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page-1 ?>&limit=<?= $limit ?>"><i class="fa-solid fa-chevron-left"></i></a>
                    </li>

                    <?php 
                    $range = 2; // Show 2 pages before and after current page
                    for($i = 1; $i <= $total_pages; $i++): 
                        if($i == 1 || $i == $total_pages || ($i >= $page - $range && $i <= $page + $range)): ?>
                            <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&limit=<?= $limit ?>"><?= $i ?></a>
                            </li>
                        <?php elseif($i == $page - $range - 1 || $i == $page + $range + 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; 
                    endfor; ?>

                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page+1 ?>&limit=<?= $limit ?>"><i class="fa-solid fa-chevron-right"></i></a>
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