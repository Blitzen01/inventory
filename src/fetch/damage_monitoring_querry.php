<?php
    $message = "";
    if (isset($_SESSION['msg'])) {
        $alert_type = ($_SESSION['msg'] == "success") ? "alert-success" : "alert-danger";
        $message = "<div class='alert $alert_type alert-dismissible fade show' role='alert'>
                        {$_SESSION['text']}
                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                    </div>";
        unset($_SESSION['msg']); // Clear message so it doesn't show again
        unset($_SESSION['text']);
    }

    // FETCH DATA
    $products = $conn->query("SELECT product_id, product_name, stock_level FROM products ORDER BY product_name");
    $damaged = $conn->query("SELECT dp.*, p.product_name, p.sku FROM damaged_products dp JOIN products p ON dp.product_id = p.product_id ORDER BY dp.date_reported DESC");

    // --- PAGINATION SETTINGS ---
    $limit = 10; 
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;
    $offset = ($page - 1) * $limit;

    // 1. Get Total Count for Damaged Products
    $total_res = $conn->query("SELECT COUNT(*) as total FROM damaged_products");
    $total_items = $total_res->fetch_assoc()['total'];
    $total_pages = ceil($total_items / $limit);

    // 2. Fetch Products for the Dropdown (All products)
    $products = $conn->query("SELECT product_id, product_name, stock_level FROM products ORDER BY product_name");

    // 3. Fetch Damaged Products with LIMIT and OFFSET
    $damaged_sql = "
        SELECT dp.*, p.product_name, p.sku 
        FROM damaged_products dp 
        JOIN products p ON dp.product_id = p.product_id 
        ORDER BY dp.date_reported DESC 
        LIMIT $limit OFFSET $offset";
    $damaged = $conn->query($damaged_sql);

    // URL Helper
    function get_page_url($p) {
        $params = $_GET;
        $params['page'] = $p;
        return "?" . http_build_query($params);
    }
?>