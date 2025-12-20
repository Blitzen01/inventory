<?php
session_start();
require_once '../../render/connection.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $allocation_id = intval($_POST['allocation_id']);
    $product_id = intval($_POST['product_id']);
    $qty = intval($_POST['qty']);
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks'] ?? "Item returned to stock from allocated location"); 
    $user_id = $_SESSION['user_id'] ?? null; 

    mysqli_begin_transaction($conn);

    try {
        // 1. GET THE DEPARTMENT DATA BEFORE WE UPDATE ANYTHING
        // This ensures we know exactly which department is giving the item back.
        $fetch_sql = "SELECT location_name, category_group FROM product_allocations WHERE allocation_id = ?";
        $stmt_fetch = mysqli_prepare($conn, $fetch_sql);
        mysqli_stmt_bind_param($stmt_fetch, "i", $allocation_id);
        mysqli_stmt_execute($stmt_fetch);
        $alloc_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_fetch));

        if (!$alloc_data) throw new Exception("Allocation record not found.");

        $dept_name = $alloc_data['location_name']; // e.g. "Accounting"
        $cat_group = trim(strtoupper($alloc_data['category_group']));

        // 2. ADD STOCK BACK TO PRODUCTS
        $upd_stock = "UPDATE products SET stock_level = stock_level + ? WHERE product_id = ?";
        $stmt_upd = mysqli_prepare($conn, $upd_stock);
        mysqli_stmt_bind_param($stmt_upd, "ii", $qty, $product_id);
        mysqli_stmt_execute($stmt_upd);

        // 3. UPDATE ALLOCATION STATUS
        $upd_status = "UPDATE product_allocations SET status = 'Returned', quantity_allocated = 0 WHERE allocation_id = ?";
        $stmt_stat = mysqli_prepare($conn, $upd_status);
        mysqli_stmt_bind_param($stmt_stat, "i", $allocation_id);
        mysqli_stmt_execute($stmt_stat);

        // 4. LOG THE MOVEMENT
        // For a RETURN, the Origin is the DEPT and the Destination is EDD INVENTORY
        $origin = $dept_name; 
        $destination = "EDD Inventory"; 
        $action_type = "Return";

        $target_table = ($cat_group === 'HEAD OFFICE') ? "head_office_logs" : "branch_logs";

        $log_sql = "INSERT INTO $target_table 
                    (user_id, product_id, action_type, origin_branch, destination_branch, quantity, remarks) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt_log = mysqli_prepare($conn, $log_sql);
        if ($stmt_log) {
            mysqli_stmt_bind_param($stmt_log, "iisssis", 
                $user_id, 
                $product_id, 
                $action_type, 
                $origin,       // FROM: e.g. Accounting
                $destination,  // TO: EDD Inventory
                $qty, 
                $remarks
            );
            mysqli_stmt_execute($stmt_log);
        }

        mysqli_commit($conn);
        header("Location: ../../web_content/inventory.php?msg=success&text=Successfully returned to stock");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error_msg = urlencode($e->getMessage());
        header("Location: ../../web_content/inventory.php?msg=error&text=$error_msg");
        exit();
    }
}
?>