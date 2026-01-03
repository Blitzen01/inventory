<?php
    session_start();

    include "../src/cdn/cdn_links.php";
    include "../render/connection.php";
    include "../render/modal.php";
    include "../src/fetch/inventory_querry.php";

?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Inventory Management</title>
        <style>
            body { padding-top: 60px; }
            .table-action-btns { min-width: 160px; }
            /* Matching the Damage Monitoring Look */
            .card { border: none; border-radius: 8px; }
            .table thead { background-color: #212529; color: white; }
            .badge { font-weight: 500; }
            .table-hover tbody tr:hover { background-color: #f8f9fa; }
            .btn-outline-dark.bg-white:hover {
                color: #f8f9fa !important; /* Forces text to stay dark on hover */
                background-color: #212529 !important; /* Gives a slight grey hint so the user knows they hovered */
            }
        </style>
    </head>
    <body class="bg-light">

    <?php include "../nav/header.php"; ?>

    <div class="container-fluid mt-4">

        <div class="d-flex justify-content-between align-items-center mb-4 px-2">
            <h3><i class="fa-solid fa-boxes-stacked me-2 text-primary"></i>Product Inventory</h3>
            <?php if($user_type != 'Viewer'): ?>
                <div class="btn-group shadow-sm">
                    <button class="btn btn-outline-dark bg-white" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="fa-solid fa-folder-plus"></i> New Category
                    </button>
                    <button class="btn btn-outline-dark bg-white" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal">
                        <i class="fa-solid fa-folder-minus"></i> Remove Category
                    </button>
                    <button class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="fa-solid fa-plus-circle"></i> Add New Item
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body bg-white rounded">
                <form class="row g-2" method="GET">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="searchItem" placeholder="Search SKU, Name..." value="<?= htmlspecialchars($search_term) ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="category_filter">
                            <option value="">All Categories</option>
                            <?php 
                            $result_categories->data_seek(0);
                            while($cat = $result_categories->fetch_assoc()): ?>
                                <option value="<?= $cat['category_id'] ?>" <?= $category_filter == $cat['category_id'] ? 'selected' : '' ?>>
                                    <?= $cat['category_name'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="status_filter">
                            <option value="">All Status</option>
                            <option value="in_stock" <?= $status_filter === 'in_stock' ? 'selected' : '' ?>>In Stock</option>
                            <option value="low_stock" <?= $status_filter === 'low_stock' ? 'selected' : '' ?>>Low Stock</option>
                            <option value="out_of_stock" <?= $status_filter === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-dark w-100"><i class="fa-solid fa-magnifying-glass me-1"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <!-- inventory table start -->
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <?php if($user_type != 'Viewer'): ?>
                                <th class="ps-3">Manage</th>
                            <?php endif; ?>
                            <th>SKU</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th class="text-center">Stock</th>
                            <th class="text-center text-primary small">Min.</th> <th>Status</th>
                            <th>Cost</th>
                            <th>Liq. Price</th>
                            <th>Location</th>
                            <th>Condition</th>
                            <th class="pe-3">Remarks</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white">
                            <?php if ($result_products->num_rows > 0): ?>
                                <?php while($row = $result_products->fetch_assoc()): 
                                    $s = $row['stock_level'];
                                    $t = $row['min_threshold'];
                                    
                                    // Logic for badges
                                    if ($s <= 0) {
                                        $badge = '<span class="badge bg-secondary">Out of Stock</span>';
                                        $s_class = 'text-muted fw-bold';
                                    } elseif ($s <= $t) {
                                        $badge = '<span class="badge bg-danger">Low Stock</span>';
                                        $s_class = 'text-danger fw-bold';
                                    } else {
                                        $badge = '<span class="badge bg-success">In Stock</span>';
                                        $s_class = 'text-success fw-bold';
                                    }

                                    $c = $row['condition'];
                                    $c_class = match($c) {
                                        'New', 'New/Replaced' => 'text-primary',
                                        'Old' => 'text-muted',
                                        'Repaired' => 'text-info',
                                        'Damage' => 'text-danger',
                                        default => ''
                                    };
                                ?>
                                    <tr>
                                    <?php if ($user_type != 'Viewer'): ?>
                                        <td class="ps-3">
                                            <div class="d-flex gap-1">
                                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#viewProductModal<?= $row['product_id'] ?>" title="View Details">
                                                    <i class="fa-solid fa-eye"></i>
                                                </button>

                                                <button class="btn btn-sm btn-outline-dark" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editProductModal<?= $row['product_id'] ?>" 
                                                        title="Edit Item">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>

                                                <button class="btn btn-sm btn-outline-secondary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#allocateModal<?= $row['product_id'] ?>" 
                                                        title="Allocate">
                                                    <i class="fa-solid fa-share-nodes"></i>
                                                </button>
                                            </div>
                                        </td>

                                        <!-- view product start -->
                                        <div class="modal fade" id="viewProductModal<?= $row['product_id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow-lg">
                                                    <div class="modal-header bg-dark text-white border-0 py-3">
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-primary rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                <i class="fa-solid fa-box text-white"></i>
                                                            </div>
                                                            <div>
                                                                <h6 class="modal-title mb-0">Item Specifications</h6>
                                                                <small class="text-white-50">SKU: <?= $row['sku'] ?></small>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <div class="modal-body p-4">
                                                        <h4 class="fw-bold text-dark mb-4"><?= htmlspecialchars($row['product_name']) ?></h4>

                                                        <div class="row g-3">
                                                            <div class="col-6">
                                                                <div class="p-3 border rounded bg-light-subtle">
                                                                    <small class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.7rem;">Category</small>
                                                                    <span class="fw-semibold text-dark"><?= $row['category_name'] ?></span>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="p-3 border rounded bg-light-subtle">
                                                                    <small class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.7rem;">Condition</small>
                                                                    <span class="<?= $c_class ?> fw-bold px-2 py-0 rounded bg-white border border-light shadow-sm" style="font-size: 0.9rem;">
                                                                        <?= $c ?>
                                                                    </span>
                                                                </div>
                                                            </div>

                                                            <div class="col-12">
                                                                <div class="p-3 border rounded d-flex justify-content-between align-items-center shadow-sm">
                                                                    <div>
                                                                        <small class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.7rem;">Stock Availability</small>
                                                                        <h3 class="mb-0 <?= $s_class ?>"><?= $s ?> <small class="fs-6 text-muted">units</small></h3>
                                                                    </div>
                                                                    <div class="text-end">
                                                                        <span class="d-block mb-1"><?= $badge ?></span>
                                                                        <small class="text-muted small">Threshold: <?= $t ?></small>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-6">
                                                                <div class="ps-2 border-start border-3 border-info">
                                                                    <small class="text-muted d-block">Location</small>
                                                                    <span class="fw-semibold"><i class="fa-solid fa-location-dot me-1 text-info"></i> <?= htmlspecialchars($row['location']) ?></span>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="ps-2 border-start border-3 border-success">
                                                                    <small class="text-muted d-block">Unit Cost</small>
                                                                    <span class="fw-bold text-success">₱<?= number_format($row['unit_cost'], 2) ?></span>
                                                                </div>
                                                            </div>

                                                            <div class="col-12 mt-4">
                                                                <label class="form-label fw-bold text-muted" style="font-size: 0.8rem;"><i class="fa-solid fa-comment-dots me-1"></i> ADMIN REMARKS</label>
                                                                <div class="p-3 rounded bg-light text-secondary small border-0" style="min-height: 60px; font-style: italic;">
                                                                    <?= !empty($row['remarks']) ? nl2br(htmlspecialchars($row['remarks'])) : 'No additional notes provided for this item.' ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer border-0 p-3">
                                                        <button type="button" class="btn btn-outline-dark px-4" data-bs-dismiss="modal">Close</button>
                                                        <?php if ($user_type != 'Viewer'): ?>
                                                        <button type="button" class="btn btn-primary px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#editProductModal<?= $row['product_id'] ?>">
                                                            <i class="fa-solid fa-pen-to-square me-2"></i>Edit Product
                                                        </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- view product end -->

                                        <!-- allocate modal start -->
                                        <div class="modal fade" id="allocateModal<?= $row['product_id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow-lg">
                                                    <div class="modal-header bg-dark text-white border-0 py-3">
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-primary rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                <i class="fa-solid fa-right-from-bracket text-white"></i>
                                                            </div>
                                                            <div>
                                                                <h4 class="modal-title mb-0">New Allocation Entry</h4>
                                                                <span class="text-light mb-0">
                                                                    <b><?= $row['product_name']; ?></b>
                                                                     | 
                                                                    <small class="text-white-50">Current Stock: <strong><?= $row['stock_level'] ?> Units</strong></small>
                                                                </span>
                                                                
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <form action="../src/php_script/process_allocation.php" method="POST">
                                                        <div class="modal-body p-4 bg-light-subtle">
                                                            <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">

                                                            <div class="row g-3">
                                                                <div class="col-12">
                                                                    <label class="form-label small fw-bold text-muted">DESTINATION LOCATION</label>
                                                                    <select class="form-select border-2 shadow-sm py-2 location-selector" 
                                                                            name="location_name" 
                                                                            data-target-group="category_group_<?= $row['product_id'] ?>" 
                                                                            required>
                                                                        <option value="" selected disabled>Select Target Destination...</option>
                                                                        
                                                                        <optgroup label="🏢 HEAD OFFICE">
                                                                            <?php 
                                                                            $ho_locs = ["Human Resource", "Admin", "Accounting", "Electronic Data", "Payroll", "Warehouse", "Leasing", "Boutique", "Concession", "Graphics", "Marketing", "Social Media", "Purchasing", "Online"];
                                                                            foreach($ho_locs as $loc): ?>
                                                                                <option value="<?= $loc ?>" data-group="HEAD OFFICE"><?= $loc ?></option>
                                                                            <?php endforeach; ?>
                                                                        </optgroup>

                                                                        <optgroup label="🏪 STORE LOCATIONS">
                                                                            <?php 
                                                                            $store_locs = ["JUAN LUNA", "TUTUBAN MALL", "3G/3H", "3H-168 POS", "3I-ANCHOR", "1X-168", "K7-QUIAPO", "158-BACLARAN", "FARMERS CUBAO", "EVER COMM", "RIVERBANKS", "KAWIT ANNEX", "SM MUNTINLUPA", "SM IMUS", "SM LEMERY", "VCM STA. ROSA", "STARMALL SAN JOSE", "PAVILION BINAN", "VISTAMALL BATAAN", "ROBINSONS GEN. TRIAS", "LIPA BATANGAS"];
                                                                            foreach($store_locs as $loc): ?>
                                                                                <option value="<?= $loc ?>" data-group="STORE"><?= $loc ?></option>
                                                                            <?php endforeach; ?>
                                                                        </optgroup>
                                                                    </select>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <label class="form-label small fw-bold text-muted">DETECTION GROUP</label>
                                                                    <div class="input-group">
                                                                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-sitemap text-muted"></i></span>
                                                                        <input type="text" 
                                                                            id="category_group_display_<?= $row['product_id'] ?>" 
                                                                            class="form-control border-start-0 bg-light fw-bold" 
                                                                            placeholder="Auto-detecting..." 
                                                                            readonly>
                                                                        <input type="hidden" name="category_group" id="category_group_<?= $row['product_id'] ?>">
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <label class="form-label small fw-bold text-muted">ALLOCATE QTY (Max: <?= $row['stock_level'] ?>)</label>
                                                                    <div class="input-group shadow-sm">
                                                                        <input type="number" id="qty_input_<?= $row['product_id'] ?>" name="quantity_allocated" class="form-control fw-bold border-primary" min="1" max="<?= $row['stock_level'] ?>" required>
                                                                        <button class="btn btn-primary btn-sm" type="button" onclick="document.getElementById('qty_input_<?= $row['product_id'] ?>').value = '<?= $row['stock_level'] ?>'">MAX</button>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 mt-2">
                                                                    <label class="form-label small fw-bold text-muted">SPECIFIC REMARKS / USE CASE</label>
                                                                    <textarea name="remarks" class="form-control border-2 shadow-sm" rows="2" placeholder="e.g. For the upcoming promotional event..."></textarea>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="modal-footer border-0 p-3 bg-white">
                                                            <button type="button" class="btn btn-outline-secondary px-4 border-0" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-primary px-5 fw-bold shadow">Record Allocation</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <!-- allocate modal end -->
                                    
                                    <td class="small fw-bold text-muted"><?= $row['sku'] ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($row['product_name']) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= $row['category_name'] ?></span></td>
                                    <td class="text-center <?= $s_class ?>"><?= $s ?></td>
                                    <td class="text-center text-muted small fw-bold"><?= $t ?></td> 
                                    <td><?= $badge ?></td>
                                    <td class="text-success small">₱<?= number_format($row['unit_cost'], 2) ?></td>
                                    <td class="text-warning small fw-bold">
                                        ₱<?= number_format($row['unit_cost'] * ($liquidation_multiplier ?? 1), 2) ?>
                                    </td>
                                    <td><i class="fa-solid fa-location-dot me-1 text-muted small"></i><?= htmlspecialchars($row['location']) ?></td>
                                    <td class="<?= $c_class ?> small fw-bold"><?= $c ?></td>
                                    <td class="text-truncate small pe-3" style="max-width: 150px;"><?= htmlspecialchars($row['remarks']) ?></td>
                                </tr>

                                <!-- edit product modal start -->
                                <?php if ($user_type != 'Viewer'): ?>
                                <div class="modal fade" id="editProductModal<?= $row['product_id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg">
                                            <div class="modal-header bg-dark text-white border-0 py-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-warning rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                        <i class="fa-solid fa-pen text-dark"></i>
                                                    </div>
                                                    <div>
                                                        <h5 class="modal-title mb-0">Modify Inventory Item</h5>
                                                        <small class="text-white-50">Updating SKU: <?= htmlspecialchars($row['sku']) ?></small>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>

                                            <form action="../src/php_script/edit_product.php" method="POST">
                                                <div class="modal-body p-4 bg-light-subtle">
                                                    <div class="alert alert-soft-warning border-0 shadow-sm d-flex align-items-start mb-4 bg-warning-subtle text-warning-emphasis" role="alert">
                                                        <i class="fa-solid fa-circle-exclamation fs-5 me-3 mt-1"></i>
                                                        <div class="small">
                                                            <strong>Inventory Notice:</strong> This area is for master data updates only. 
                                                            If you are moving items to a department, please use the <span class="badge bg-dark-subtle text-dark">Allocation</span> tool instead.
                                                        </div>
                                                    </div>

                                                    <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
                                                    
                                                    <div class="row g-4">
                                                        <div class="col-md-7">
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold text-muted">PRODUCT IDENTIFICATION</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-tag"></i></span>
                                                                    <input type="text" class="form-control border-start-0 ps-0" name="product_name" value="<?= htmlspecialchars($row['product_name']) ?>" required placeholder="Item Name">
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-barcode"></i></span>
                                                                    <input type="text" class="form-control border-start-0 ps-0" name="sku" value="<?= htmlspecialchars($row['sku']) ?>" required placeholder="SKU Number">
                                                                </div>
                                                            </div>

                                                            <div class="mb-0">
                                                                <label class="form-label small fw-bold text-muted">ADMINISTRATIVE NOTES</label>
                                                                <textarea class="form-control shadow-sm" name="remarks" rows="4" placeholder="Enter any internal notes here..."><?= htmlspecialchars($row['remarks']) ?></textarea>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                                                                <div class="card-body bg-white p-3">
                                                                    <div class="mb-3">
                                                                        <label class="form-label small fw-bold text-muted">CURRENT STOCK LEVEL</label>
                                                                        <div class="input-group">
                                                                            <input type="number" class="form-control border-end-0 fw-bold" name="stock_level" value="<?= $row['stock_level'] ?>" required>
                                                                            <span class="input-group-text bg-white border-start-0 text-muted small">Units</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label class="form-label small fw-bold text-muted">UNIT VALUATION (₱)</label>
                                                                        <input type="number" step="0.01" class="form-control fw-bold text-success" name="unit_cost" value="<?= $row['unit_cost'] ?>" required>
                                                                    </div>

                                                                    <div class="mb-0">
                                                                        <label class="form-label small fw-bold text-muted">ASSET CONDITION</label>
                                                                        <select class="form-select border-0 bg-light fw-semibold" name="condition">
                                                                            <option value="New" <?= $row['condition'] == 'New' ? 'selected' : '' ?>>New / Mint</option>
                                                                            <option value="Old" <?= $row['condition'] == 'Old' ? 'selected' : '' ?>>Old / Used</option>
                                                                            <option value="Repaired" <?= $row['condition'] == 'Repaired' ? 'selected' : '' ?>>Repaired</option>
                                                                            <option value="Damage" <?= $row['condition'] == 'Damage' ? 'selected' : '' ?>>Damaged / Scrap</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="modal-footer border-0 bg-white p-3 shadow-sm">
                                                    <button type="button" class="btn btn-link text-muted text-decoration-none me-auto ps-0" data-bs-dismiss="modal">Discard Changes</button>
                                                    <button type="submit" class="btn btn-dark px-4 py-2 rounded-pill shadow">
                                                        <i class="fa-solid fa-save me-2 text-warning"></i>Commit Update
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <!-- edit product modal end -->

                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="11" class="text-center py-5 text-muted">No products found matching your search.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <!-- inventory table end -->
            </div>

            <!-- pagination card start -->
            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3 border-top">
                <div class="text-muted small">
                    Showing <b><?= $result_products->num_rows ?></b> of <?= $total_items ?> items
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link text-dark" href="<?= get_page_url($page - 1) ?>">Prev</a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 1 && $i <= $page + 1)): ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                    <a class="page-link <?= ($page == $i) ? 'bg-dark border-dark' : 'text-dark' ?>" href="<?= get_page_url($i) ?>"><?= $i ?></a>
                                </li>
                            <?php elseif ($i == $page - 2 || $i == $page + 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                            <a class="page-link text-dark" href="<?= get_page_url($page + 1) ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
            <!-- pagination card end -->
        </div>

        <script>
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('location-selector')) {
                    const select = e.target;
                    const selectedOption = select.options[select.selectedIndex];
                    
                    // Use trim() to prevent hidden space issues
                    const groupValue = selectedOption.getAttribute('data-group');
                    const targetHiddenId = select.getAttribute('data-target-group');
                    const displayId = 'category_group_display_' + targetHiddenId.split('_').pop();

                    if (groupValue) {
                        // Update the hidden input for the database
                        const hiddenInput = document.getElementById(targetHiddenId);
                        hiddenInput.value = groupValue.trim();

                        // Update the visual display input
                        const displayInput = document.getElementById(displayId);
                        displayInput.value = groupValue.trim();
                        
                        // Visual feedback
                        if(groupValue.trim() === 'HEAD OFFICE') {
                            displayInput.style.color = '#0d6efd'; // Bootstrap Primary Blue
                        } else {
                            displayInput.style.color = '#198754'; // Bootstrap Success Green
                        }
                    }
                }
            });
        </script>
    </body>
</html>