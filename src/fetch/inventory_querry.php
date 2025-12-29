<?php
    if (!isset($conn)) {
        die("Error: Database connection not established. Check connection.php.");
    }

    $user_type = $_SESSION['user_type'] ?? 'Viewer';

    // --- Fetch System Settings ---
    $system_settings = [];
    $sql_settings = "SELECT setting_key, setting_value FROM system_settings";
    $result_settings = $conn->query($sql_settings);
    if ($result_settings) {
        while ($row = $result_settings->fetch_assoc()) {
            $system_settings[$row['setting_key']] = $row['setting_value'];
        }
    }

    $liquidation_percent = isset($system_settings['liquidation_percentage']) ? floatval($system_settings['liquidation_percentage']) : 20;
    $eol_years = isset($system_settings['eol_duration_years']) ? intval($system_settings['eol_duration_years']) : 1;
    $liquidation_multiplier = (100 - $liquidation_percent) / 100;

    // --- Fetch Categories for dropdown ---
    $result_categories = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name ASC");

    // --- Filters Setup ---
    $search_term = trim($_GET['searchItem'] ?? '');
    $category_filter = $_GET['category_filter'] ?? '';
    $status_filter = $_GET['status_filter'] ?? '';
    $category_filter = is_numeric($category_filter) ? (int)$category_filter : null;

    $where_clauses = [];
    $params = [];
    $param_types = '';

    if (!empty($search_term)) {
        $search_columns = ['p.sku', 'p.product_name', 'p.brand', 'p.location', 'p.condition', 'p.remarks', 'c.category_name'];
        $like_clauses = [];
        foreach ($search_columns as $col) {
            $like_clauses[] = "$col LIKE ?";
            $params[] = "%$search_term%";
            $param_types .= 's';
        }
        $where_clauses[] = '(' . implode(' OR ', $like_clauses) . ')';
    }

    if ($category_filter !== null) {
        $where_clauses[] = "p.category_id = ?";
        $params[] = $category_filter;
        $param_types .= 'i';
    }

    if (!empty($status_filter)) {
        if ($status_filter === 'out_of_stock') $where_clauses[] = "p.stock_level <= 0";
        elseif ($status_filter === 'low_stock') $where_clauses[] = "p.stock_level > 0 AND p.stock_level <= p.min_threshold";
        elseif ($status_filter === 'in_stock') $where_clauses[] = "p.stock_level > p.min_threshold";
    }

    $where_sql = count($where_clauses) > 0 ? " WHERE " . implode(" AND ", $where_clauses) : "";

    // --- Pagination Calculation ---
    $limit = 10; 
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;
    $offset = ($page - 1) * $limit;

    // --- Get Total Count ---
    $sql_total_count = "SELECT COUNT(p.product_id) AS total_items FROM products p LEFT JOIN categories c ON p.category_id = c.category_id $where_sql";
    $stmt_count = $conn->prepare($sql_total_count);
    if (!empty($params)) {
        $stmt_count->bind_param($param_types, ...$params);
    }
    $stmt_count->execute();
    $total_items = $stmt_count->get_result()->fetch_assoc()['total_items'] ?? 0;
    $total_pages = ceil($total_items / $limit);

    // --- Get Paginated Products ---
    $sql_products = "
        SELECT p.*, c.category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        $where_sql
        ORDER BY p.product_name ASC
        LIMIT ? OFFSET ?
    ";

    $stmt_products = $conn->prepare($sql_products);
    $paginated_params = $params;
    $paginated_params[] = $limit;
    $paginated_params[] = $offset;
    $paginated_types = $param_types . "ii";
    $stmt_products->bind_param($paginated_types, ...$paginated_params);
    $stmt_products->execute();
    $result_products = $stmt_products->get_result();

    function get_page_url($p) {
        $params = $_GET;
        $params['page'] = $p;
        return "?" . http_build_query($params);
    }
?>