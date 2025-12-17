<?php
include "../../render/connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['category_id']) || empty($_POST['category_id'])) {
        die("No category selected.");
    }

    $category_id = $_POST['category_id'];

    // OPTIONAL: prevent deleting categories used by products
    $check = $conn->prepare("SELECT * FROM products WHERE category_id = ?");
    $check->bind_param("i", $category_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // Category is used by products
        header("Location: ../../web_content/inventory.php?error=CategoryInUse");
        exit();
    }

    // Delete category
    $query = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
    $query->bind_param("i", $category_id);

    if ($query->execute()) {
        header("Location: ../../web_content/inventory.php?success=CategoryDeleted");
        exit();
    } else {
        echo "Error deleting category: " . $conn->error;
    }
}
?>
