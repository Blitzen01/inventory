<!-- add category modal start -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-tags me-2 text-info"></i> Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="../src/php_script/add_category.php">
                <div class="modal-body px-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Category Name</label>
                        <input type="text" class="form-control bg-light border-0" name="category_name" placeholder="e.g. Office Supplies" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-muted text-uppercase">Description</label>
                        <textarea class="form-control bg-light border-0" name="description" rows="3" placeholder="Briefly describe what items belong here..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info text-white rounded-pill px-4 shadow-sm">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- add category modal end -->

<!-- delete category modal start -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pt-4 px-4 text-center">
                <h5 class="modal-title fw-bold w-100 text-danger"><i class="fa-solid fa-trash-can me-2"></i> Delete Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../src/php_script/delete_category.php">
                <div class="modal-body px-4">
                    <div class="alert alert-danger border-0 rounded-4 py-3 small shadow-sm">
                        <i class="fa-solid fa-circle-exclamation me-2"></i>
                        <strong>Warning:</strong> This may affect products currently assigned to this category.
                    </div>
                    <div class="mb-3 mt-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Select Category to Remove</label>
                        <select class="form-select bg-light border-0" name="category_id" required>
                            <option value="" selected disabled>Select Category...</option>
                            <?php
                                $result_category_list = mysqli_query($conn, "SELECT * FROM categories");
                                while($row = mysqli_fetch_assoc($result_category_list)):
                            ?>
                                <option value="<?= $row['category_id']; ?>"><?= $row['category_name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm">Confirm Deletion</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- delete category modal end -->

<!-- add product modal start -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-box-open me-2 text-success"></i> New Inventory Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../src/php_script/add_product.php">
                <div class="modal-body px-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Product Name</label>
                            <input type="text" class="form-control bg-light border-0" name="productName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Brand</label>
                            <input type="text" class="form-control bg-light border-0" name="brand" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Category</label>
                            <select class="form-select bg-light border-0" name="productCategory" required>
                                <option value="">Select...</option>
                                <?php
                                    $categories = mysqli_query($conn, "SELECT * FROM categories");
                                    while ($row = mysqli_fetch_assoc($categories)) {
                                        echo '<option value="'.$row['category_id'].'">'.$row['category_name'].'</option>';
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Unit Cost (₱)</label>
                            <input type="number" step="0.01" name="unitCost" class="form-control bg-light border-0" value="0.00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted text-uppercase">Initial Stock</label>
                            <input type="number" name="initialStock" class="form-control bg-light border-0" value="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted text-uppercase">Min. Threshold</label>
                            <input type="number" name="minThreshold" class="form-control bg-light border-0" value="10" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted text-uppercase">Location</label>
                            <input type="text" name="location" class="form-control bg-light border-0" placeholder="Aisle/Rack" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Condition</label>
                            <select class="form-select bg-light border-0" name="status" required>
                                <option value="New">New</option>
                                <option value="Old">Old</option>
                                <option value="Repaired">Repaired</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Remarks</label>
                            <textarea class="form-control bg-light border-0" name="remarks" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- add product modal end -->

<!-- add user modal start -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-primary"><i class="fa-solid fa-user-plus me-2"></i> Create Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addUserForm" method="POST" action="../src/php_script/add_account.php">
                <div class="modal-body px-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">First Name</label>
                            <input type="text" class="form-control bg-light border-0" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Last Name</label>
                            <input type="text" class="form-control bg-light border-0" name="last_name" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Username</label>
                            <input type="text" class="form-control bg-light border-0" name="username" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Email Address</label>
                            <input type="email" class="form-control bg-light border-0" name="email" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Assign Role</label>
                            <select class="form-select bg-light border-0" name="role" required>
                                <option value="" selected disabled>Select Role...</option>
                                <option value="Administrator">Administrator</option>
                                <option value="Inventory Manager">Inventory Manager</option>
                                <option value="Stock Handler">Stock Handler</option>
                                <option value="Viewer">Viewer</option>
                            </select>
                        </div>
                        
                        <hr class="my-3 opacity-25">
                        
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control bg-light border-0" id="initialPassword" name="password" required onkeyup="checkPasswordStrength(this.value)">
                                <button class="btn btn-light border-0" type="button" id="togglePassword"><i class="fa-solid fa-eye-slash" id="toggleIcon"></i></button>
                            </div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div id="passwordStrengthBar" class="progress-bar" role="progressbar" style="width: 0%;"></div>
                            </div>
                            <small id="passwordFeedback" class="text-muted" style="font-size: 10px;"></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- add user modal end -->

<!-- confirm damage modal start -->
<div class="modal fade" id="confirmDamageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg text-center p-4" style="border-radius: 20px;">
            <div class="modal-body">
                <i class="fa-solid fa-triangle-exclamation fa-4x text-danger mb-4"></i>
                <h4 class="fw-bold">Confirm Stock Deduction</h4>
                <p class="text-muted">You are about to log <span id="confirmQty" class="fw-bold text-dark">0</span> units of <span id="confirmProduct" class="fw-bold text-dark"></span> as damaged.</p>
                <div class="alert alert-light border-0 small text-danger fw-bold">
                    This will immediately REDUCE available stock.
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-light w-100 rounded-pill fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger w-100 rounded-pill fw-bold shadow-sm" id="submitDamageButton">Confirm & Deduct</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- confirm damage modal end -->

<div class="modal fade" id="allocateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="../render/process_allocation.php" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Assign Item to Location</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Destination</label>
                        <select class="form-select" name="location_name" required>
                            <optgroup label="HEAD OFFICE LOCATIONS">
                                <option value="HR">HR</option>
                                <option value="Admin">Admin</option>
                                <option value="Accounting">Accounting</option>
                                <option value="Electronic Data">Electronic Data</option>
                                <option value="Payroll">Payroll</option>
                                <option value="Warehouse">Warehouse</option>
                                <option value="Leasing">Leasing</option>
                                <option value="Boutique">Boutique</option>
                                <option value="Concession">Concession</option>
                                <option value="Graphics">Graphics</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Social Media">Social Media</option>
                                <option value="Purchasing">Purchasing</option>
                                <option value="Online">Online</option>
                            </optgroup>
                            <optgroup label="STORE LOCATIONS">
                                <option value="JUAN LUNA">JUAN LUNA</option>
                                <option value="TUTUBAN MALL">TUTUBAN MALL</option>
                                <option value="3G/3H">3G/3H</option>
                                <option value="3H-168 POS">3H-168 POS</option>
                                <option value="3I-ANCHOR">3I-ANCHOR</option>
                                <option value="1X-168">1X-168</option>
                                <option value="K7-QUIAPO">K7-QUIAPO</option>
                                <option value="158-BACLARAN">158-BACLARAN</option>
                                <option value="FARMERS CUBAO">FARMERS CUBAO</option>
                                <option value="EVER COMM">EVER COMM</option>
                                <option value="RIVERBANKS">RIVERBANKS</option>
                                <option value="KAWIT ANNEX">KAWIT ANNEX</option>
                                <option value="SM MUNTINLUPA">SM MUNTINLUPA</option>
                                <option value="SM IMUS">SM IMUS</option>
                                <option value="SM LEMERY">SM LEMERY</option>
                                <option value="VCM STA. ROSA">VCM STA. ROSA</option>
                                <option value="STARMALL SAN JOSE">STARMALL SAN JOSE</option>
                                <option value="PAVILION BINAN">PAVILION BINAN</option>
                                <option value="VISTAMALL BATAAN">VISTAMALL BATAAN</option>
                                <option value="ROBINSONS GEN. TRIAS">ROBINSONS GEN. TRIAS</option>
                                <option value="LIPA BATANGAS">LIPA BATANGAS</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Quantity to Deploy</label>
                        <input type="number" name="qty" class="form-control" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Process Allocation</button>
                </div>
            </form>
        </div>
    </div>
</div>