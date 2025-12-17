<?php
session_start();
include '../../render/connection.php';

$user_id = $_SESSION['user_id'] ?? 1;

// Allow POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../web_content/inventory.php");
    exit;
}

/* -----------------------------
   1. Retrieve POST Data
------------------------------ */
$productName   = trim($_POST['productName']);
$brand         = trim($_POST['brand']);
$categoryId    = (int)$_POST['productCategory'];
$unitCost      = (float)$_POST['unitCost'];
$initialStock  = (int)$_POST['initialStock'];
$minThreshold  = (int)($_POST['minThreshold'] ?? 0);
$location      = trim($_POST['location']);   // ✅ ADDED
$status        = trim($_POST['status']);     // ✅ ADDED
$remarks       = trim($_POST['remarks']);

/* -----------------------------
   2. Validation
------------------------------ */
if (
    $productName === '' ||
    $brand === '' ||
    $location === '' ||
    $status === '' ||
    $categoryId <= 0
) {
    header("Location: ../../web_content/inventory.php?error=Missing required fields");
    exit;
}

/* -----------------------------
   3. Validate Category Exists
------------------------------ */
$check = $conn->prepare("SELECT category_id FROM categories WHERE category_id = ?");
$check->bind_param("i", $categoryId);
$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
    header("Location: ../../web_content/inventory.php?error=Invalid category");
    exit;
}
$check->close();

/* -----------------------------
   4. Generate SKU
------------------------------ */
$sku = strtoupper(substr(md5(uniqid('', true)), 0, 8));

/* -----------------------------
   5. Transaction
------------------------------ */
mysqli_begin_transaction($conn);

try {

    // Insert product (✅ location & status included)
    $stmt = $conn->prepare("
        INSERT INTO products
        (sku, product_name, brand, category_id, stock_level, min_threshold, unit_cost, location, status, remarks)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sssiiidsss",
        $sku,
        $productName,
        $brand,
        $categoryId,
        $initialStock,
        $minThreshold,
        $unitCost,
        $location,
        $status,
        $remarks
    );

    $stmt->execute();
    $productId = $stmt->insert_id;
    $stmt->close();

    // Insert inventory log
    $log = $conn->prepare("
        INSERT INTO inventory_log
        (product_id, user_id, action_type, quantity_change, log_details)
        VALUES (?, ?, 'Add Product', ?, 'Initial stock added')
    ");

    $log->bind_param("iii", $productId, $user_id, $initialStock);
    $log->execute();
    $log->close();

    mysqli_commit($conn);

    header("Location: ../../web_content/inventory.php?success=Product added successfully");
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    header("Location: ../../web_content/inventory.php?error=Database error");
    exit;
}
