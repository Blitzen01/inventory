<?php
    session_start();
    include "../src/cdn/cdn_links.php";
    include "../render/connection.php";
    include "../src/fetch/allocation_querry.php";
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Asset Distribution | Inventory System</title>
        <style>
            body { padding-top: 70px; background-color: #f8f9fa; }
            .table thead { background-color: #212529; color: white; }
            .card { border: none; border-radius: 10px; }
            .location-badge { font-size: 0.85rem; padding: 0.5em 0.8em; }
        </style>
    </head>
    <body>

    <?php include "../nav/header.php"; ?>

    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4 px-2">
            <div>
                <h3><i class="fa-solid fa-map-location-dot me-2 text-primary"></i>Asset Distribution</h3>
                <p class="text-muted small mb-0">Track and manage items currently deployed to Head Office and Store locations.</p>
            </div>
            <a href="inventory.php" class="btn btn-outline-dark btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Inventory
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Product & SKU</th>
                            <th>Type</th>
                            <th>Current Location</th>
                            <th class="text-center">Qty Deployed</th>
                            <th>Date Deployed</th>
                            <th class="text-center pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <?php if ($res_active->num_rows > 0): ?>
                            <?php while($item = $res_active->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-3">
                                    <strong><?= htmlspecialchars($item['brand'] . ' ' . $item['product_name']) ?></strong><br>
                                    <small class="text-muted fw-bold"><?= $item['sku'] ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= htmlspecialchars($item['category_group']) ?>
                                    </span>
                                </td>
                                <td>
                                    <i class="fa-solid fa-location-dot text-danger me-1"></i> 
                                    <span class="fw-semibold"><?= htmlspecialchars($item['location_name']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill bg-primary"><?= $item['quantity_allocated'] ?></span>
                                </td>
                                <td class="small text-muted">
                                    <?= date('M d, Y', strtotime($item['date_allocated'])) ?>
                                </td>
                                <td class="text-center pe-3">
                                    <button class="btn btn-sm btn-outline-danger px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#returnModal<?= $item['allocation_id'] ?>">
                                        <i class="fa-solid fa-rotate-left me-1"></i> Return to Stock
                                    </button>
                                </td>
                            </tr>

                            <div class="modal fade" id="returnModal<?= $item['allocation_id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-sm modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <form action="../src/php_script/process_return.php" method="POST">
                                            <div class="modal-body text-center p-4">
                                                <input type="hidden" name="allocation_id" value="<?= $item['allocation_id'] ?>">
                                                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                                <input type="hidden" name="qty" value="<?= $item['quantity_allocated'] ?>">
                                                
                                                <i class="fa-solid fa-warehouse text-dark mb-3" style="font-size: 2.5rem;"></i>
                                                <h5 class="fw-bold">Return Item?</h5>
                                                <p class="text-muted small">Move <b><?= $item['quantity_allocated'] ?></b> units from <b><?= $item['location_name'] ?></b> back to main inventory?</p>
                                                
                                                <div class="d-grid gap-2">
                                                    <button type="submit" class="btn btn-dark">Confirm Return</button>
                                                    <button type="button" class="btn btn-link text-muted btn-sm text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-box-open d-block mb-2 fs-2"></i>
                                    No assets are currently deployed to any locations.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top py-3 text-muted small">
                Total active allocations: <b><?= $res_active->num_rows ?></b>
            </div>
        </div>
    </div>

    </body>
</html>