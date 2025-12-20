<?php
session_start();
require_once '../../render/connection.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $location_name = mysqli_real_escape_string($conn, $_POST['location_name']);
    $category_group = mysqli_real_escape_string($conn, $_POST['category_group']);
    $qty_to_allocate = intval($_POST['quantity_allocated']);
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']); 
    $user_id = $_SESSION['user_id'] ?? null; 
    $status = 'Deployed'; 

    mysqli_begin_transaction($conn);

    try {
        // 1. Verify Stock
        $check_sql = "SELECT stock_level FROM products WHERE product_id = ?";
        $stmt_check = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($stmt_check, "i", $product_id);
        mysqli_stmt_execute($stmt_check);
        $product = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_check));

        if (!$product) throw new Exception("Product not found.");
        if ($product['stock_level'] < $qty_to_allocate) throw new Exception("Insufficient stock.");

        // 2. Insert into product_allocations
        $ins_sql = "INSERT INTO product_allocations 
                    (product_id, category_group, location_name, quantity_allocated, status, remarks) 
                    VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_ins = mysqli_prepare($conn, $ins_sql);
        mysqli_stmt_bind_param($stmt_ins, "ississ", $product_id, $category_group, $location_name, $qty_to_allocate, $status, $remarks);
        mysqli_stmt_execute($stmt_ins);

        // 3. Deduct from Products stock
        $upd_sql = "UPDATE products SET stock_level = stock_level - ? WHERE product_id = ?";
        $stmt_upd = mysqli_prepare($conn, $upd_sql);
        mysqli_stmt_bind_param($stmt_upd, "ii", $qty_to_allocate, $product_id);
        mysqli_stmt_execute($stmt_upd);

        // 4. Determine Table and Log Details
        $clean_group = trim(strtoupper($category_group));
        
        // --- KEY CHANGE HERE ---
        $origin = "EDD Inventory"; 
        $transfer_action = "Transfer";
        $target_table = ($clean_group === 'HEAD OFFICE') ? "head_office_logs" : "branch_logs";

        $branch_sql = "INSERT INTO $target_table 
                    (user_id, product_id, action_type, origin_branch, destination_branch, quantity, remarks) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt_branch = mysqli_prepare($conn, $branch_sql);

        if ($stmt_branch) {
            mysqli_stmt_bind_param($stmt_branch, "iisssis", 
                $user_id, 
                $product_id, 
                $transfer_action, 
                $origin,          // "EDD Inventory"
                $location_name,   // e.g., "Concession"
                $qty_to_allocate, 
                $remarks
            );
            
            if (!mysqli_stmt_execute($stmt_branch)) {
                throw new Exception("Log Error: " . mysqli_stmt_error($stmt_branch));
            }
        }

        mysqli_commit($conn);
        header("Location: ../../web_content/inventory.php?msg=success&text=Allocated to $location_name");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error_msg = urlencode($e->getMessage());
        header("Location: ../../web_content/inventory.php?msg=error&text=$error_msg");
        exit();
    }
}