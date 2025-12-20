<?php
session_start();
include '../../render/connection.php';

$user_id = $_SESSION['user_id'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../web_content/inventory.php");
    exit;
}

/* -----------------------------
   1. Retrieve POST Data (Matching HTML names)
------------------------------ */
$productId    = (int)$_POST['product_id'];
$productName  = trim($_POST['product_name']);
$sku          = trim($_POST['sku']);
$newStock     = (int)$_POST['stock_level'];
$unitCost     = (float)$_POST['unit_cost'];
$condition    = trim($_POST['condition']);
$remarks      = trim($_POST['remarks']);

// These weren't in your HTML form, so we provide defaults or handle them
$brand        = trim($_POST['brand'] ?? 'N/A'); 
$categoryId   = (int)($_POST['product_category'] ?? 1); 
$location     = trim($_POST['location'] ?? 'Warehouse');
$minThreshold = (int)($_POST['min_threshold'] ?? 5);

/* -----------------------------
   2. Validation (Check only essential fields)
------------------------------ */
if ($productId <= 0 || empty($productName) || empty($sku)) {
    header("Location: ../../web_content/inventory.php?error=Missing required fields");
    exit;
}

/* -----------------------------
   3. Get OLD Product Data
------------------------------ */
$oldStmt = $conn->prepare("SELECT product_name, stock_level, unit_cost, brand FROM products WHERE product_id = ?");
$oldStmt->bind_param("i", $productId);
$oldStmt->execute();
$result = $oldStmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ../../web_content/inventory.php?error=Product not found");
    exit;
}
$old = $result->fetch_assoc();
$oldStmt->close();

/* -----------------------------
   4. Build Log Logic
------------------------------ */
$stockChange = $newStock - $old['stock_level'];
$logDetails = "Product updated by " . $_SESSION['username'];

/* -----------------------------
   5. Database Transaction
------------------------------ */
mysqli_begin_transaction($conn);

try {
    // Update product
    $update = $conn->prepare("
        UPDATE products SET
            product_name  = ?,
            sku           = ?,
            stock_level   = ?,
            unit_cost     = ?,
            `condition`   = ?, 
            remarks       = ?
        WHERE product_id = ?
    ");

    $update->bind_param("ssidssi", $productName, $sku, $newStock, $unitCost, $condition, $remarks, $productId);
    $update->execute();

    // Insert inventory log
    $log = $conn->prepare("
        INSERT INTO inventory_log (product_id, user_id, action_type, quantity_change, log_details, remarks)
        VALUES (?, ?, 'Product Edit', ?, ?, ?)
    ");

    $log->bind_param("iiiss", $productId, $user_id, $stockChange, $logDetails, $remarks);
    $log->execute();

    mysqli_commit($conn);
    header("Location: ../../web_content/inventory.php?success=Product updated successfully");

} catch (Exception $e) {
    mysqli_rollback($conn);
    header("Location: ../../web_content/inventory.php?error=Update failed: " . $e->getMessage());
}
?>