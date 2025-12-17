<?php
// Database connection
include "../../render/connection.php";

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $category_name = trim($_POST['category_name']);
    $description   = trim($_POST['description']);

    // Validate
    if (empty($category_name)) {
        die("Category name is required.");
    }

    // Prepare SQL
    $stmt = $conn->prepare("INSERT INTO categories (category_name, description, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $category_name, $description);

    if ($stmt->execute()) {
        // Redirect back (optional)
        header("Location: ../../web_content/inventory.php?success=1");
        exit;
    } else {
        echo "Error inserting category: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
