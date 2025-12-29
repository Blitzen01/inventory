<?php
    session_start();
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    include "../src/cdn/cdn_links.php";
    include "../render/connection.php";
    include "../render/modal.php";
    include "../src/fetch/damage_monitoring_querry.php";
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Damage Monitoring</title>
        <style>
            body { padding-top: 56px; }
            .pending-row { background: #fffcf0; }
            .completed-row { background: #f4fff4; }
            
            /* Normal state: Dark text on white/grayish background */
            .pagination .page-link {
                color: #212529; /* Dark text */
                background-color: #fff;
                border: 1px solid #dee2e6;
                transition: all 0.2s;
            }

            /* Hover state */
            .pagination .page-link:hover {
                background-color: #e9ecef;
                color: #000;
            }

            /* Active state: Dark background with light text */
            .pagination .page-item.active .page-link {
                background-color: #212529 !important; /* Solid dark */
                border-color: #212529 !important;
                color: #ffffff !important; /* Light text */
            }

            /* Disabled state */
            .pagination .page-item.disabled .page-link {
                color: #6c757d;
                pointer-events: none;
                background-color: #fff;
            }
        </style>
    </head>
    <body class="bg-light">
    <?php include "../nav/header.php"; ?>

    <div class="container mt-4">
        <h3><i class="fa-solid fa-screwdriver-wrench me-2"></i>Damage Management</h3>
        <?= $message ?>

        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <form method="POST" action="../src/php_script/process_damage.php">
                    <input type="hidden" name="report_damage" value="1">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Product</label>
                            <select name="product_id" id="productSelect" class="form-select" required>
                                <option value="">Select...</option>
                                <?php 
                                // Reset pointer if already used or fetch fresh
                                $products->data_seek(0); 
                                while ($p = $products->fetch_assoc()): ?>
                                    <option value="<?= $p['product_id'] ?>" data-stock="<?= $p['stock_level'] ?>">
                                        <?= $p['product_name'] ?> (Available: <?= $p['stock_level'] ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>Qty</label>
                            <input type="number" name="quantity_damaged" id="qtyInput" class="form-control" min="1" required>
                            <div id="stockWarning" class="text-danger small" style="display:none;">Exceeds available stock!</div>
                        </div>
                        <div class="col-md-3">
                            <label>Action</label>
                            <select name="action_required" class="form-select" required>
                                <option value="REPAIR">Repair (Return to Stock)</option>
                                <option value="REPLACE">Replace (New Unit)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Reason</label>
                            <input type="text" name="reason" class="form-control" required>
                            <button type="submit" class="btn btn-danger mt-3 w-100">Log Damage</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm mb-5">
            <table class="table mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Manage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $damaged->fetch_assoc()): 
                        $status = $row['status'];
                        $is_pending = (strpos($status, 'PENDING') !== false);
                    ?>
                        <tr class="<?= $is_pending ? 'pending-row' : 'completed-row' ?>">
                            <td>
                                <div class="fw-bold"><?= $row['product_name'] ?></div>
                                <small class="text-muted"><?= $row['sku'] ?></small>
                            </td>
                            <td class="fw-bold"><?= $row['quantity_damaged'] ?></td>
                            <td>
                                <?php 
                                    $is_repair = (strpos($status, 'REPAIR') !== false);
                                    $badge_class = $is_repair ? 'bg-info' : 'bg-secondary';
                                    $icon = $is_repair ? 'fa-screwdriver-wrench' : 'fa-arrows-rotate';
                                ?>
                                <span class="badge <?= $badge_class ?> shadow-sm" style="font-size: 10px;">
                                    <i class="fa-solid <?= $icon ?> me-1"></i><?= $is_repair ? 'REPAIR' : 'REPLACE' ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                    $clean_status = str_replace('PENDING_', '', $status);
                                    $status_color = $is_pending ? 'text-warning' : 'text-success';
                                ?>
                                <div class="small fw-bold <?= $status_color ?>">
                                    <?= $is_pending ? '<i class="fa-solid fa-clock me-1"></i>' : '<i class="fa-solid fa-check-circle me-1"></i>' ?>
                                    <?= $clean_status ?>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <?php if ($is_pending): ?>
                                        <form method="POST" action="../src/php_script/process_damage.php">
                                            <input type="hidden" name="damage_id" value="<?= $row['damage_id'] ?>">
                                            <input type="hidden" name="resolve_damage" value="1">
                                            <button class="btn btn-sm btn-success shadow-sm" title="Mark as Fixed">
                                                <i class="fa-solid fa-check"></i> Resolve
                                            </button>
                                        </form>

                                        <?php if ($status === 'PENDING_REPAIR'): ?>
                                        <form method="POST" action="../src/php_script/process_damage.php">
                                            <input type="hidden" name="damage_id" value="<?= $row['damage_id'] ?>">
                                            <input type="hidden" name="switch_to_replace" value="1">
                                            <button class="btn btn-sm btn-outline-danger shadow-sm" title="Change to Replacement">
                                                <i class="fa-solid fa-arrows-rotate"></i> Replace
                                            </button>
                                        </form>
                                        <?php endif; ?>

                                    <?php else: ?>
                                        <span class="badge bg-light text-success border"><i class="fa-solid fa-check"></i> Completed</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
            </table>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
                <div class="text-muted small">
                    Page <?= $page ?> of <?= max(1, $total_pages) ?> (Total: <?= $total_items ?> records)
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= get_page_url($page - 1) ?>">Previous</a>
                        </li>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                <a class="page-link" href="<?= get_page_url($i) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= get_page_url($page + 1) ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('productSelect').addEventListener('change', function() {
            const qtyInput = document.getElementById('qtyInput');
            const warning = document.getElementById('stockWarning');
            
            // Get the data-stock attribute from the selected option
            const selectedOption = this.options[this.selectedIndex];
            const stockAvailable = selectedOption.getAttribute('data-stock');

            if (stockAvailable) {
                qtyInput.max = stockAvailable;
                qtyInput.placeholder = "Max: " + stockAvailable;
            } else {
                qtyInput.max = "";
                qtyInput.placeholder = "";
            }
            
            // Reset validation state on change
            qtyInput.classList.remove('is-invalid');
            warning.style.display = 'none';
        });

        // Real-time visual feedback
        document.getElementById('qtyInput').addEventListener('input', function() {
            const max = parseInt(this.max);
            const val = parseInt(this.value);
            const warning = document.getElementById('stockWarning');

            if (val > max) {
                this.classList.add('is-invalid');
                warning.style.display = 'block';
            } else {
                this.classList.remove('is-invalid');
                warning.style.display = 'none';
            }
        });
    </script>
    </body>
</html>