<?php
session_start();

include "../src/cdn/cdn_links.php";
include "../render/connection.php";
include "../render/modal.php";



if (!isset($conn)) {
    die("Error: Database connection not established. Check connection.php.");
}

$user_type = $_SESSION['user_type'];

// --- Fetch System Settings ---
$system_settings = [];
$sql_settings = "SELECT setting_key, setting_value FROM system_settings";
$result_settings = $conn->query($sql_settings);
if ($result_settings) {
    while ($row = $result_settings->fetch_assoc()) {
        $system_settings[$row['setting_key']] = $row['setting_value'];
    }
}

// Default values if not set
$liquidation_percent = isset($system_settings['liquidation_percentage']) ? floatval($system_settings['liquidation_percentage']) : 20;
$eol_years = isset($system_settings['eol_duration_years']) ? intval($system_settings['eol_duration_years']) : 1;
$liquidation_multiplier = (100 - $liquidation_percent) / 100;

// --- Fetch Categories ---
$sql_categories = "SELECT category_id, category_name FROM categories ORDER BY category_name ASC";
$result_categories = $conn->query($sql_categories);
$categories_array = [];
if ($result_categories) {
    while ($row = $result_categories->fetch_assoc()) {
        $categories_array[$row['category_id']] = $row['category_name'];
    }
}

// --- Filters ---
$search_term = trim($_GET['searchItem'] ?? '');
$category_filter = $_GET['category_filter'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';
$category_filter = is_numeric($category_filter) ? (int)$category_filter : null;

// --- Build SQL WHERE ---
$where_clauses = [];
$param_types = '';
$params = [];

if (!empty($search_term)) {
    $where_clauses[] = "(p.product_name LIKE ? OR p.sku LIKE ?)";
    $search_param = "%" . $search_term . "%";
    $param_types .= 'ss';
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($category_filter !== null) {
    $where_clauses[] = "p.category_id = ?";
    $param_types .= 'i';
    $params[] = $category_filter;
}

if (!empty($status_filter)) {
    if ($status_filter === 'out_of_stock') {
        $where_clauses[] = "p.stock_level <= 0";
    } elseif ($status_filter === 'low_stock') {
        $where_clauses[] = "p.stock_level > 0 AND p.stock_level <= p.min_threshold";
    } elseif ($status_filter === 'in_stock') {
        $where_clauses[] = "p.stock_level > p.min_threshold";
    }
}

$where_sql = count($where_clauses) > 0 ? " WHERE " . implode(" AND ", $where_clauses) : "";

// --- Products Query ---
$sql_products_base = "
    SELECT 
        p.*,
        c.category_name
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
";
$sql_products = $sql_products_base . $where_sql . " ORDER BY p.product_name ASC LIMIT 20";
$stmt_products = $conn->prepare($sql_products);
if (!empty($param_types)) $stmt_products->bind_param($param_types, ...$params);
$stmt_products->execute();
$result_products = $stmt_products->get_result();

// --- Total Count ---
$sql_total_count_base = "SELECT COUNT(p.product_id) AS total_items FROM products p";
$sql_total_count = $sql_total_count_base . $where_sql;
$stmt_count = $conn->prepare($sql_total_count);
if (!empty($param_types)) $stmt_count->bind_param($param_types, ...$params);
$stmt_count->execute();
$total_items = $stmt_count->get_result()->fetch_assoc()['total_items'] ?? 0;
$stmt_count->close();
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Inventory Management</title>
<style>
body { padding-top: 56px; }
.table-action-btns { min-width: 150px; }
.table-responsive { overflow-x: auto; }
</style>
</head>
<body class="bg-light">

<?php include "../nav/header.php"; ?>

<div class="container-fluid mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-light text-dark">📦 Product Inventory List</h1>
        <?php 
            if($user_type != 'viewer') {
                ?>
                <div class="btn-group" role="group">
                    <button class="btn btn-outline-info shadow-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal"><i class="fa-solid fa-tags me-2"></i> Add Category</button>
                    <button class="btn btn-outline-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal"><i class="fa-solid fa-trash-can me-2"></i> Delete Category</button>
                    <button class="btn btn-success btn-lg shadow-sm ms-3" data-bs-toggle="modal" data-bs-target="#addProductModal"><i class="fa-solid fa-plus-circle me-2"></i> Add New Item</button>
                </div>
                <?php
            }
        ?>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form class="row g-3 align-items-center" method="GET" action="">
                <div class="col-md-5">
                    <input type="text" class="form-control form-control-lg" id="searchItem" name="searchItem" placeholder="Search by name, ID, or SKU..." value="<?php echo htmlspecialchars($search_term); ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select form-select-lg" id="filterCategory" name="category_filter">
                        <option value="" <?php echo $category_filter === null ? 'selected' : ''; ?>>Filter by Category</option>
                        <?php
                        $result_categories->data_seek(0);
                        if($result_categories) while($row = $result_categories->fetch_assoc()) {
                            $selected = ($category_filter == $row['category_id']) ? 'selected' : '';
                            echo "<option value='{$row['category_id']}' $selected>{$row['category_name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-lg" id="filterStatus" name="status_filter">
                        <option value="" <?php echo empty($status_filter) ? 'selected' : ''; ?>>Filter by Status</option>
                        <option value="in_stock" <?php echo $status_filter === 'in_stock' ? 'selected' : ''; ?>>In Stock</option>
                        <option value="low_stock" <?php echo $status_filter === 'low_stock' ? 'selected' : ''; ?>>Low Stock</option>
                        <option value="out_of_stock" <?php echo $status_filter === 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fa-solid fa-filter me-1"></i> Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-5">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-semibold text-muted">
                <?php 
                $current_rows = $result_products->num_rows ?? 0;
                echo $total_items > 0 ? "Showing 1 - " . min($current_rows, $total_items) . " of " . $total_items . " Items" : "No Items Match Your Filter Criteria";
                ?>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0" style="overflow-x: auto; white-space: nowrap;">
                    <thead class="table-light">
                        <tr>
                            <?php
                                if($user_type != 'viewer') {
                                    ?>
                            <th>Actions</th>
                            <?php
                                }
                            ?>
                            <th>SKU</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th class="text-center">Stock Level</th>
                            <th>Status</th>
                            <th>Unit Cost</th>
                            <th>Liquidation Price</th>
                            <th>Date Added</th>
                            <th>EOL Date</th>
                            <th>Min. Threshold</th>
                            <th>Location</th>
                            <th>Condition</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($current_rows > 0) {
                            while($row = $result_products->fetch_assoc()) {
                                $stock_level = $row['stock_level'];
                                $min_threshold = $row['min_threshold'];
                                $unit_cost = number_format($row['unit_cost'], 2);
                                $status = $row['status'];

                                $liquidation_price = number_format(round($row['unit_cost'] * $liquidation_multiplier, 2), 2);
                                $eol_date = date("M d, Y", strtotime("+{$eol_years} years", strtotime($row['date_added'])));
                                $date_added = date("M d, Y", strtotime($row['date_added']));

                                if ($stock_level <= 0) {
                                    $status_badge = '<span class="badge text-bg-secondary">Out of Stock</span>';
                                    $stock_class = 'text-secondary fw-bold';
                                } elseif ($stock_level <= $min_threshold) {
                                    $status_badge = '<span class="badge text-bg-danger">Low Stock</span>';
                                    $stock_class = 'text-danger fw-bold';
                                } else {
                                    $status_badge = '<span class="badge text-bg-success">In Stock</span>';
                                    $stock_class = '';
                                }

                                if($status == "New") {
                                    $status_class = "text-primary";
                                } else if($status == "Old") {
                                    $status_class = "text-muted";
                                } else if($status == "Repaired") {
                                    $status_class = "text-info";
                                } else if($status == "Damage") {
                                    $status_class = "text-danger";
                                }

                                echo "<tr>";
                                if ($user_type != 'viewer') {
                                    echo "
                                        <td class='table-action-btns'>
                                            <button class='btn btn-sm btn-outline-primary m-1'
                                                data-bs-toggle='modal'
                                                data-bs-target='#editProductModal{$row['product_id']}'
                                                title='Edit Item'>
                                                <i class='fa-solid fa-pencil'></i>
                                            </button>

                                            <button class='btn btn-sm btn-outline-success m-1'
                                                title='Allocate to Branch'>
                                                <i class='fa-solid fa-arrow-right-arrow-left'></i>
                                            </button>

                                            <button class='btn btn-sm btn-outline-secondary m-1'
                                                title='Allocate to Warehouse'>
                                                <i class='fa-solid fa-warehouse'></i>
                                            </button>

                                            <button class='btn btn-sm btn-outline-info m-1'
                                                title='Temporary use'>
                                                <i class='fa-solid fa-hourglass'></i>
                                            </button>
                                        </td>";
                                }

                                echo "
                                    <td>{$row['sku']}</td>
                                    <td>{$row['product_name']}</td>
                                    <td>{$row['category_name']}</td>
                                    <td class='text-center {$stock_class}'>{$stock_level}</td>
                                    <td>{$status_badge}</td>
                                    <td class='fw-semibold text-success'>₱ {$unit_cost}</td>
                                    <td class='fw-semibold text-warning'>₱ {$liquidation_price}</td>
                                    <td>{$date_added}</td>
                                    <td>{$eol_date}</td>
                                    <td class='text-center'>{$min_threshold}</td>
                                    <td>{$row['location']}</td>
                                    <td class='{$status_class}'>{$status}</td>
                                    <td>{$row['remarks']}</td>
                                </tr>";

                            }
                        } else {
                            echo '<tr><td colspan="12" class="text-center text-muted py-5">No inventory items found. Please adjust your filters or add a new product.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
