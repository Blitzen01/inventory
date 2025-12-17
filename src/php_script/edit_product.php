<?php
session_start();
include '../../render/connection.php';

$user_id = $_SESSION['user_id'] ?? 1;

/* -----------------------------
   Allow POST only
------------------------------ */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../web_content/inventory.php");
    exit;
}

/* -----------------------------
   1. Retrieve POST Data (FROM MODAL)
------------------------------ */
$productId      = (int)$_POST['productId'];
$productName    = trim($_POST['productName']);
$sku            = trim($_POST['sku']);
$brand          = trim($_POST['brand']);
$categoryId     = (int)$_POST['productCategory'];
$newStock       = (int)$_POST['stockLevel'];
$minThreshold   = (int)($_POST['minThreshold'] ?? 0);
$unitCost       = (float)$_POST['unitCost'];
$location       = trim($_POST['location']);
$status         = trim($_POST['status']);
$remarks        = trim($_POST['remarks']);

/* -----------------------------
   2. Validation (MATCH MODAL)
------------------------------ */
if (
    $productId <= 0 ||
    $categoryId <= 0 ||
    $productName === '' ||
    $sku === '' ||
    $brand === '' ||
    $location === '' ||
    $status === '' ||
    $remarks === ''
) {
    header("Location: ../../web_content/inventory.php?error=Missing required fields");
    exit;
}

/* -----------------------------
   3. Check Category Exists
------------------------------ */
$cat = $conn->prepare("SELECT category_id FROM categories WHERE category_id = ?");
$cat->bind_param("i", $categoryId);
$cat->execute();
$cat->store_result();

if ($cat->num_rows === 0) {
    header("Location: ../../web_content/inventory.php?error=Invalid category");
    exit;
}
$cat->close();

/* -----------------------------
   4. Get OLD Product Data
------------------------------ */
$oldStmt = $conn->prepare("
    SELECT product_name, stock_level, unit_cost, brand,
           min_threshold, location, status, remarks
    FROM products
    WHERE product_id = ?
");
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
   5. Build Change Log
------------------------------ */
$stockChange = $newStock - $old['stock_level'];
$changes = [];

if ($productName !== $old['product_name'])   $changes[] = "Name updated";
if ($brand !== $old['brand'])                 $changes[] = "Brand updated";
if ($unitCost != $old['unit_cost'])           $changes[] = "Unit cost updated";
if ($minThreshold != $old['min_threshold'])   $changes[] = "Min threshold updated";
if ($location !== $old['location'])           $changes[] = "Location updated";
if ($status !== $old['status'])               $changes[] = "Status updated";
if ($remarks !== $old['remarks'])             $changes[] = "Remarks updated";

if ($stockChange != 0) {
    $changes[] = $stockChange > 0
        ? "Stock increased by {$stockChange}"
        : "Stock decreased by " . abs($stockChange);
}

$logDetails = empty($changes)
    ? "Product updated"
    : implode(". ", $changes) . ".";

/* -----------------------------
   6. Transaction
------------------------------ */
mysqli_begin_transaction($conn);

try {

    // Update product (ALL MODAL FIELDS)
    $update = $conn->prepare("
        UPDATE products SET
            product_name  = ?,
            brand         = ?,
            category_id   = ?,
            stock_level   = ?,
            min_threshold = ?,
            unit_cost     = ?,
            location      = ?,
            status        = ?,
            remarks       = ?
        WHERE product_id = ?
    ");

    $update->bind_param(
        "ssiiddsssi",
        $productName,
        $brand,
        $categoryId,
        $newStock,
        $minThreshold,
        $unitCost,
        $location,
        $status,
        $remarks,
        $productId
    );

    $update->execute();
    $update->close();

    // Insert inventory log
    $log = $conn->prepare("
        INSERT INTO inventory_log
        (product_id, user_id, action_type, quantity_change, log_details, remarks)
        VALUES (?, ?, 'Product Edit', ?, ?, ?)
    ");

    $log->bind_param(
        "iiiss",
        $productId,
        $user_id,
        $stockChange,
        $logDetails,
        $remarks
    );

    $log->execute();
    $log->close();

    mysqli_commit($conn);

    header("Location: ../../web_content/inventory.php?success=Product updated successfully");
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    header("Location: ../../web_content/inventory.php?error=Update failed");
    exit;
}
