<?php
session_start();
include "../../render/connection.php";

$current_user_id = $_SESSION['user_id'] ?? 1;

if (!$conn) { die("Database connection failed."); }


// --- 1. HANDLE DAMAGE REPORT ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_damage'])) {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $quantity   = filter_input(INPUT_POST, 'quantity_damaged', FILTER_VALIDATE_INT);
    $reason     = trim($_POST['reason'] ?? '');
    $action_req = $_POST['action_required']; 

    $status = ($action_req === 'REPAIR') ? 'PENDING_REPAIR' : 'PENDING_REPLACEMENT';

    if (!$product_id || !$quantity || $quantity <= 0 || empty($reason)) {
        $_SESSION['msg'] = "error";
        $_SESSION['text'] = "Invalid input data.";
    } else {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("SELECT stock_level FROM products WHERE product_id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $stock = $stmt->get_result()->fetch_assoc()['stock_level'] ?? 0;

            if ($stock < $quantity) {
                throw new Exception("Insufficient stock. Only $stock available.");
            }

            $stmt = $conn->prepare("INSERT INTO damaged_products (product_id, quantity_damaged, reason, reported_by_user_id, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisis", $product_id, $quantity, $reason, $current_user_id, $status);
            $stmt->execute();

            $stmt = $conn->prepare("UPDATE products SET stock_level = stock_level - ? WHERE product_id = ?");
            $stmt->bind_param("ii", $quantity, $product_id);
            $stmt->execute();

            $conn->commit();
            $_SESSION['msg'] = "success";
            $_SESSION['text'] = "Damage reported. Stock deducted.";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['msg'] = "error";
            $_SESSION['text'] = $e->getMessage();
        }
    }
    // REDIRECT back to the monitoring page
    header("Location: ../../web_content/damage_monitoring.php"); 
    exit();
}

// --- 2. HANDLE RESOLUTION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resolve_damage'])) {
    $damage_id = filter_input(INPUT_POST, 'damage_id', FILTER_VALIDATE_INT);

    if ($damage_id) {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("SELECT product_id, quantity_damaged, status FROM damaged_products WHERE damage_id = ?");
            $stmt->bind_param("i", $damage_id);
            $stmt->execute();
            $record = $stmt->get_result()->fetch_assoc();

            if ($record) {
                $isRepair = ($record['status'] === 'PENDING_REPAIR');
                $final_status = $isRepair ? 'REPAIRED' : 'REPLACED';
                $condition_val = $isRepair ? 'Repaired' : 'New/Replaced';

                $up1 = $conn->prepare("UPDATE damaged_products SET status = ? WHERE damage_id = ?");
                $up1->bind_param("si", $final_status, $damage_id);
                $up1->execute();

                $up2 = $conn->prepare("UPDATE products SET stock_level = stock_level + ?, `condition` = ? WHERE product_id = ?");
                $up2->bind_param("isi", $record['quantity_damaged'], $condition_val, $record['product_id']);
                $up2->execute();

                $conn->commit();
                $_SESSION['msg'] = "success";
                $_SESSION['text'] = "Stock restored successfully.";
            }
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['msg'] = "error";
            $_SESSION['text'] = $e->getMessage();
        }
    }
    header("Location: ../../web_content/damage_monitoring.php");
    exit();
}