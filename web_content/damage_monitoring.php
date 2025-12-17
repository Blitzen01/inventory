<?php
    include "../src/cdn/cdn_links.php";
    include "../render/connection.php"; // must define $conn (mysqli)
    include "../render/modal.php";

    // Initialize message variable
    $message = "";

    // IMPORTANT: Replace with $_SESSION['user_id']
    $current_user_id = 1;

    // Safety check
    if (!isset($conn)) {
        die("Database connection not established.");
    }

    // -------------------------------
    // 1. HANDLE DAMAGE REPORT FORM
    // -------------------------------
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['report_damage'])) {

        // --- Input Validation & Sanitization ---
        $product_id = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);
        $quantity = filter_var($_POST['quantity_damaged'], FILTER_VALIDATE_INT);
        $reason = trim($_POST['reason']);
        $initial_status = 'PENDING_REPLACEMENT'; 

        if ($product_id === false || $quantity === false || $quantity <= 0 || empty($reason)) {
            $message = "<div class='alert alert-danger'>Error: Invalid product ID or quantity. Please check inputs.</div>";
        } else {
            // --- Check stock using Prepared Statement ---
            $sql_check = "SELECT stock_level FROM products WHERE product_id = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("i", $product_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            $current_stock = $result_check->fetch_assoc()['stock_level'] ?? 0;
            $stmt_check->close();

            if ($current_stock < $quantity) {
                $message = "<div class='alert alert-danger'>Error: Insufficient stock. Current stock: $current_stock.</div>";
            } else {
                
                // --- Start Transaction for Data Integrity ---
                $conn->begin_transaction();
                $success = true;
                
                try {
                    // 1. Log the damage in the damaged_products table (SECURE)
                    $sql_insert = "INSERT INTO damaged_products (product_id, quantity_damaged, reason, reported_by_user_id, status) VALUES (?, ?, ?, ?, ?)";
                    $stmt_insert = $conn->prepare($sql_insert);
                    $stmt_insert->bind_param("iisis", $product_id, $quantity, $reason, $current_user_id, $initial_status);
                    if (!$stmt_insert->execute()) $success = false;
                    $stmt_insert->close();

                    // 2. Deduct stock from the products table (SECURE)
                    $sql_update = "UPDATE products SET stock_level = stock_level - ? WHERE product_id = ?";
                    $stmt_update = $conn->prepare($sql_update);
                    $stmt_update->bind_param("ii", $quantity, $product_id);
                    if (!$stmt_update->execute()) $success = false;
                    $stmt_update->close();

                    // 3. Log replacement action in inventory_log (SECURE)
                    $log_details = "Deducted $quantity unit(s) for damage replacement.";
                    $action_type = 'REPLACEMENT_DEDUCTION';
                    $quantity_change_log = $quantity * -1; // Log as negative quantity change

                    $sql_log = "INSERT INTO inventory_log (product_id, user_id, action_type, quantity_change, log_details) VALUES (?, ?, ?, ?, ?)";
                    $stmt_log = $conn->prepare($sql_log);
                    $stmt_log->bind_param("iisss", $product_id, $current_user_id, $action_type, $quantity_change_log, $log_details);
                    if (!$stmt_log->execute()) $success = false;
                    $stmt_log->close();

                    // Finalize Transaction
                    if ($success) {
                        $conn->commit();
                        $message = "<div class='alert alert-success'>Damage logged and $quantity stock deducted for replacement.</div>";
                    } else {
                        $conn->rollback();
                        $message = "<div class='alert alert-danger'>Error processing damage report. Transaction failed.</div>";
                    }

                } catch (Exception $e) {
                    $conn->rollback();
                    $message = "<div class='alert alert-danger'>Database error during transaction.</div>";
                }
            }
        }
    }

    // -------------------------------
    // 2. HANDLE MARK AS REPLACED
    // -------------------------------
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['complete_replacement'])) {

        $damage_id = filter_var($_POST['damage_id'], FILTER_VALIDATE_INT);
        $new_status = 'REPLACED';

        if ($damage_id === false) {
                $message = "<div class='alert alert-danger'>Error: Invalid damage ID.</div>";
        } else {
            // Secure update using Prepared Statement
            $sql_update_status = "UPDATE damaged_products SET status = ? WHERE damage_id = ?";
            $stmt_update_status = $conn->prepare($sql_update_status);
            $stmt_update_status->bind_param("si", $new_status, $damage_id);

            if ($stmt_update_status->execute()) {
                $message = "<div class='alert alert-info'>Log #$damage_id marked as REPLACED.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error updating status.</div>";
            }
            $stmt_update_status->close();
        }
    }

    // -------------------------------
    // 3. FETCH PRODUCTS FOR DROPDOWN (MOVED INSIDE MAIN PHP BLOCK)
    // -------------------------------
    $sql_products = "SELECT product_id, product_name, stock_level FROM products ORDER BY product_name ASC";
    // Check if query succeeds before assigning $result_products
    $result_products = $conn->query($sql_products);
    if (!$result_products) {
        // Handle error if product list fetch fails
        $message .= "<div class='alert alert-warning'>Warning: Could not fetch product list.</div>";
    }

    // -------------------------------
    // 4. FETCH DAMAGE RECORDS (MOVED INSIDE MAIN PHP BLOCK)
    // -------------------------------
    $sql_damaged = "
        SELECT dp.damage_id, dp.quantity_damaged, dp.date_reported, dp.reason, dp.status,
            p.product_name, p.sku
        FROM damaged_products dp
        JOIN products p ON dp.product_id = p.product_id
        ORDER BY dp.date_reported DESC
    ";
    // Check if query succeeds before assigning $result_damaged
    $result_damaged = $conn->query($sql_damaged);
    if (!$result_damaged) {
            // Handle error if damage log fetch fails
        $message .= "<div class='alert alert-warning'>Warning: Could not fetch damaged items log.</div>";
    }

    // NOW THE PHP BLOCK ENDS AND THE HTML STARTS
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Damage Monitoring</title>
        <style>
            body { padding-top: 56px; }
            .table-row-pending { background: #fff4f4; }
            .table-row-replaced { background: #eaffea; }
        </style>
    </head>

    <body class="bg-light">

        <?php include "../nav/header.php"; ?> 

        <div class="container-fluid mt-4">

            <h1 class="fw-light text-dark mb-4">
                <i class="fa-solid fa-broken me-2"></i> Damaged Items & Replacements
            </h1>

            <?php if (isset($message)) echo $message; ?>

            <!-- =======================================================
                DAMAGE REPORT FORM
                ======================================================= -->
            <div class="card shadow mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Report Damaged Item</h5>
                </div>

                <div class="card-body">
                    <form id="damageReportForm" method="POST">
                        <input type="hidden" name="report_damage" value="1">

                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label class="form-label">Product</label>
                                <select class="form-select" name="product_id" id="product_id" required>
                                    <option value="" disabled selected>Select a product</option>
                                    <?php while($p = $result_products->fetch_assoc()): ?>
                                        <option value="<?= $p['product_id']; ?>">
                                            <?= $p['product_name']; ?> (Stock: <?= $p['stock_level']; ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Quantity Damaged</label>
                                <input type="number" class="form-control" name="quantity_damaged" id="quantity_damaged" min="1" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Reason</label>
                                <input type="text" class="form-control" name="reason" id="reason" required>
                            </div>
                        </div>

                        <button type="button" class="btn btn-danger mt-3" id="preSubmitDamageBtn">
                            <i class="fa-solid fa-minus-circle me-1"></i>
                            Log Damage & Deduct Stock
                        </button>
                    </form>
                </div>
            </div>

            <!-- =======================================================
                DAMAGE LIST TABLE
                ======================================================= -->
            <div class="card shadow mb-5">
                <div class="card-header bg-white">
                    <h5 class="fw-semibold text-danger mb-0">
                        <i class="fa-solid fa-list-check me-2"></i> Replacement Tracking
                    </h5>
                </div>

                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Date</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php 
                        if ($result_damaged && $result_damaged->num_rows > 0):
                            while($row = $result_damaged->fetch_assoc()):
                        ?>

                            <tr class="<?= $row['status'] == 'PENDING_REPLACEMENT' ? 'table-row-pending' : 'table-row-replaced'; ?>">
                                <td><?= $row['damage_id']; ?></td>
                                <td><?= $row['product_name']; ?> (<?= $row['sku']; ?>)</td>

                                <td class="fw-bold text-danger"><?= $row['quantity_damaged']; ?></td>

                                <td><?= date("Y-m-d", strtotime($row['date_reported'])); ?></td>
                                <td><?= $row['reason']; ?></td>

                                <td>
                                    <?= $row['status'] == 'PENDING_REPLACEMENT'
                                        ? '<span class="badge bg-warning text-dark">Pending</span>'
                                        : '<span class="badge bg-success">Replaced</span>'; ?>
                                </td>

                                <td>
                                    <?php if ($row['status'] == 'PENDING_REPLACEMENT'): ?>
                                        <form method="POST">
                                            <input type="hidden" name="damage_id" value="<?= $row['damage_id']; ?>">
                                            <input type="hidden" name="complete_replacement" value="1">

                                            <button type="submit" 
                                                    onclick="return confirm('Mark log #<?= $row['damage_id']; ?> as REPLACED?');"
                                                    class="btn btn-sm btn-outline-success">
                                                <i class="fa-solid fa-check"></i> Mark Replaced
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-light" disabled>Done</button>
                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php endwhile; else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No damaged items found.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script>
            document.getElementById('preSubmitDamageBtn').addEventListener('click', function () {

                const form = document.getElementById('damageReportForm');

                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                const productSelect = document.getElementById('product_id');
                const productName = productSelect.options[productSelect.selectedIndex].text;
                const quantity = document.getElementById('quantity_damaged').value;

                document.getElementById('confirmQty').textContent = quantity;
                document.getElementById('confirmProduct').textContent = productName;

                new bootstrap.Modal(document.getElementById('confirmDamageModal')).show();
            });

            document.getElementById('submitDamageButton').addEventListener('click', function () {
                bootstrap.Modal.getInstance(document.getElementById('confirmDamageModal')).hide();
                document.getElementById('damageReportForm').submit();
            });
        </script>

    </body>
</html>
