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
                            <input type="text" class="form-control bg-light border-0" id="gen_first_name" name="first_name" placeholder="John" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Last Name</label>
                            <input type="text" class="form-control bg-light border-0" name="last_name" placeholder="Doe" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Email Address</label>
                            <input type="email" class="form-control bg-light border-0" name="email" placeholder="john@example.com" required>
                        </div>
                        <div class="col-12 border-bottom pb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Assign Role</label>
                            <select class="form-select bg-light border-0" id="gen_role" name="role" required>
                                <option value="" selected disabled>Select Role...</option>
                                <option value="Administrator">Administrator</option>
                                <option value="Inventory Manager">Inventory Manager</option>
                                <option value="Stock Handler">Stock Handler</option>
                                <option value="Viewer">Viewer</option>
                            </select>
                        </div>
                        
                        <div class="col-12 mt-3">
                            <div class="p-3 border-0 rounded-4" style="background-color: #f0f7ff; border: 1px dashed #0d6efd !important;">
                                <label class="d-block small fw-bold text-primary text-uppercase mb-3">Login Credentials</label>
                                
                                <div class="mb-3">
                                    <small class="text-muted d-block mb-1">Username:</small>
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="username" id="display_username" class="form-control border-0 bg-white fw-bold" readonly value="---">
                                        <button class="btn btn-outline-primary border-0 bg-white" type="button" onclick="copyToClipboard('display_username', this)">
                                            <i class="fa-regular fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div>
                                    <small class="text-muted d-block mb-1">Temporary Password:</small>
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="password" id="display_password" class="form-control border-0 bg-white fw-bold" readonly value="---">
                                        <button class="btn btn-outline-primary border-0 bg-white" type="button" onclick="copyToClipboard('display_password', this)">
                                            <i class="fa-regular fa-copy"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary border-0 bg-white" type="button" onclick="generateNewPassword()">
                                            <i class="fa-solid fa-arrows-rotate"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">Save Account</button>
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


<!-- allocation modal start -->
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
<!-- allocation modal end -->


<!-- confirm status modal start -->
<?php
    $checkActiveUser = "SELECT * FROM users ORDER BY user_id DESC";
    $activeUserResult = mysqli_query($conn, $checkActiveUser);
    
    if (mysqli_num_rows($activeUserResult) > 0){
        while($userRow = mysqli_fetch_assoc($activeUserResult)){
            $isCurrentlyActive = ($userRow['status'] === 'active');
            $actionText = $isCurrentlyActive ? 'Deactivate' : 'Activate';
            
            // Dynamic UI colors based on status
            $themeColor = $isCurrentlyActive ? '#dc3545' : '#198754'; 
            $btnClass = $isCurrentlyActive ? 'btn-danger' : 'btn-success';
            
            $fNameInitial = !empty($userRow['first_name']) ? substr($userRow['first_name'], 0, 1) : 'U';
            $lNameInitial = !empty($userRow['last_name']) ? substr($userRow['last_name'], 0, 1) : 'N';
            $initials = strtoupper($fNameInitial . $lNameInitial);
            $profileImg = $userRow['profile_image'];
?>
<div class="modal fade" id="confirmStatusModal<?php echo $userRow['user_id']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"> 
        <div class="modal-content border-0 shadow-lg" style="border-radius: 25px; overflow: hidden;">
            <form action="../src/php_script/deactivate_user.php" method="POST">
                
                <div class="modal-header border-0 pt-5 px-4 position-relative" style="background: <?php echo $themeColor; ?>; min-height: 140px; align-items: flex-start;">
                    <h5 class="modal-title w-100 text-center fw-bold text-white" style="letter-spacing: 1px;">
                        <i class="fa-solid fa-user-shield me-2"></i>Account Access Control
                    </h5>
                    <button type="button" class="btn-close btn-close-white position-absolute" style="right: 25px; top: 25px;" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4 pt-0" style="margin-top: -60px;">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center text-white shadow-lg mb-3" 
                                style="width: 180px; height: 180px; border-radius: 40px; font-size: 4rem; font-weight: 700; border: 8px solid #fff; background: <?php echo $themeColor; ?>; overflow: hidden;">
                                <?php if (!empty($profileImg) && file_exists($profileImg)): ?>
                                <img src="<?= $profileImg; ?>" style="width:100%; height:100%; object-fit:cover;">
                                <?php else: ?>
                                <?php echo $initials; ?>
                                <?php endif; ?>
                        </div>
                        
                        <h2 class="mb-1 fw-bold text-dark">
                            <?php echo htmlspecialchars(($userRow['first_name'] ?? 'Unknown') . ' ' . ($userRow['last_name'] ?? 'User')); ?>
                        </h2>
                        <p class="text-muted fs-5 mb-4">@<?php echo htmlspecialchars($userRow['username'] ?? 'username'); ?></p>
                        
                        <div class="alert border-0 py-3 mb-4 rounded-4 shadow-sm mx-lg-5" style="background-color: #f8f9fa; border-left: 5px solid <?php echo $themeColor; ?> !important;">
                            <p class="mb-0 text-dark fw-semibold">
                                Confirming this will <span class="fw-bold" style="color: <?php echo $themeColor; ?>;"><?php echo strtolower($actionText); ?></span> this account.
                                <br>
                                <small class="text-muted fw-normal">User will <?php echo $isCurrentlyActive ? 'be immediately restricted from system access' : 'regain all system privileges'; ?>.</small>
                            </p>
                        </div>
                    </div>

                    <div class="mb-3 px-lg-5">
                        <label class="form-label small fw-bold text-uppercase text-muted d-block text-center mb-3" style="letter-spacing: 1px;">Admin Authorization</label>
                        <div class="input-group shadow-sm mx-auto" style="border-radius: 15px; overflow: hidden; max-width: 400px;">
                            <span class="input-group-text bg-white border-end-0 px-3"><i class="fa-solid fa-key text-muted"></i></span>
                            <input type="password" name="admin_password" class="form-control border-start-0 py-3 text-center bg-white" 
                                    style="font-size: 1rem; border: 2px solid #f1f1f1;" placeholder="Enter Admin Password" required>
                        </div>
                    </div>
                    
                    <input type="hidden" name="user_id" value="<?php echo $userRow['user_id']; ?>">
                    <input type="hidden" name="new_status" value="<?php echo $isCurrentlyActive ? 'inactive' : 'active'; ?>">
                </div>

                <div class="modal-footer border-0 justify-content-center pb-5 pt-0">
                    <button type="button" class="btn btn-light px-4 py-2 rounded-pill fw-bold me-2 border shadow-sm" data-bs-dismiss="modal">Go Back</button>
                    <button type="submit" name="update_status" class="btn <?php echo $btnClass; ?> px-5 py-2 rounded-pill fw-bold shadow-sm">
                        Confirm <?php echo $actionText; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
        }
    }
?>
<!-- confirm status modal end -->


<!-- delete user modal start -->
<?php
    $userQuery = "SELECT * FROM users ORDER BY user_id DESC";
    $userResult = mysqli_query($conn, $userQuery);
    
    while ($row = mysqli_fetch_assoc($userResult)):
        $initial = strtoupper(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1));
        $profileImg = $row['profile_image'];
?>
<div class="modal fade" id="deleteUserModal<?= $row['user_id']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"> 
        <div class="modal-content border-0 shadow-lg" style="border-radius: 25px; overflow: hidden;">
            <form action="../src/php_script/delete_user.php" method="POST">
                
                <div class="modal-header border-0 pt-5 px-4 position-relative" style="background: #dc3545; min-height: 140px; align-items: flex-start;">
                    <h5 class="modal-title w-100 text-center fw-bold text-white" style="letter-spacing: 1px;">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>Account Termination
                    </h5>
                    <button type="button" class="btn-close btn-close-white position-absolute" style="right: 25px; top: 25px;" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4 pt-0" style="margin-top: -60px;">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center text-white shadow-lg mb-3" 
                             style="width: 180px; height: 180px; border-radius: 40px; font-size: 4rem; font-weight: 700; border: 8px solid #fff; background: #dc3545; overflow: hidden;">
                             <?php if (!empty($profileImg) && file_exists($profileImg)): ?>
                                <img src="<?= $profileImg; ?>" style="width:100%; height:100%; object-fit:cover;">
                             <?php else: ?>
                                <?= $initial; ?>
                             <?php endif; ?>
                        </div>
                        
                        <h2 class="mb-1 fw-bold text-dark">
                            <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                        </h2>
                        <p class="text-muted fs-5 mb-4">@<?= htmlspecialchars($row['username']); ?></p>
                        
                        <div class="alert alert-danger border-0 py-3 mb-4 rounded-4 shadow-sm mx-lg-5" style="background-color: #fff5f5;">
                            <p class="mb-0 text-danger fw-bold">
                                <i class="fa-solid fa-circle-info me-2"></i>Warning: This user will be moved to the archive (Deleted Users).
                            </p>
                        </div>
                    </div>

                    <div class="mb-3 px-lg-5">
                        <label class="form-label small fw-bold text-uppercase text-muted d-block text-center mb-3" style="letter-spacing: 1px;">Admin Authorization Required</label>
                        <div class="input-group shadow-sm mx-auto" style="border-radius: 15px; overflow: hidden; max-width: 400px;">
                            <span class="input-group-text bg-white border-end-0 px-3"><i class="fa-solid fa-shield-halved text-danger"></i></span>
                            <input type="password" name="admin_password" class="form-control border-start-0 py-3 text-center bg-white" 
                                   style="font-size: 1rem; border: 2px solid #f1f1f1;" placeholder="Enter admin password" required>
                        </div>
                        <p class="text-center text-muted mt-2 mb-0" style="font-size: 0.8rem;">Confirming this will log your ID as the responsible admin.</p>
                    </div>
                    
                    <input type="hidden" name="user_id" value="<?= $row['user_id']; ?>">
                </div>

                <div class="modal-footer border-0 justify-content-center pb-5 pt-0">
                    <button type="button" class="btn btn-light px-4 py-2 rounded-pill fw-bold me-2 border shadow-sm" data-bs-dismiss="modal">Keep Account</button>
                    <button type="submit" name="confirm_delete" class="btn btn-danger px-5 py-2 rounded-pill fw-bold shadow-sm">
                        <i class="fa-solid fa-user-slash me-2"></i>Terminate User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endwhile; ?>
<!-- delete user modal end -->


<!-- view user modal start -->
<?php
    $viewQuery = "SELECT * FROM users ORDER BY user_id DESC";
    $viewResult = mysqli_query($conn, $viewQuery);
    
    while ($row = mysqli_fetch_assoc($viewResult)):
        $initial = strtoupper(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1));
        $isActive = (strtolower($row['status']) === 'active');
        $profileImg = $row['profile_image'];
?>
<div class="modal fade" id="viewUserModal_<?= $row['user_id']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"> 
        <div class="modal-content border-0 shadow-lg" style="border-radius: 25px; overflow: hidden;">
            
            <div class="modal-header border-0 pt-5 px-4 position-relative" style="background: #2f2f30; min-height: 140px; align-items: flex-start;">
                <h5 class="modal-title w-100 text-center fw-bold text-white" style="letter-spacing: 1px;">
                    <i class="fa-solid fa-address-card me-2"></i>User Profile Details
                </h5>
                <button type="button" class="btn-close btn-close-white position-absolute" style="right: 25px; top: 25px;" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 pt-0" style="margin-top: -60px;">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center text-white shadow-lg mb-3" 
                         style="width: 180px; height: 180px; border-radius: 40px; font-size: 4rem; font-weight: 700; border: 8px solid #fff; background: #2f2f30; overflow: hidden;">
                         <?php if (!empty($profileImg) && file_exists($profileImg)): ?>
                            <img src="<?= $profileImg; ?>" style="width:100%; height:100%; object-fit:cover;">
                         <?php else: ?>
                            <?= $initial; ?>
                         <?php endif; ?>
                    </div>
                    
                    <h2 class="mb-1 fw-bold text-dark">
                        <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                    </h2>
                    <p class="text-primary fs-5 fw-semibold mb-2">@<?= htmlspecialchars($row['username']); ?></p>
                    
                    <div>
                        <span class="badge rounded-pill <?= $isActive ? 'bg-success text-white' : 'bg-danger text-white'; ?> px-4 py-2" style="font-size: 0.85rem;">
                            <i class="fa-solid fa-circle me-1" style="font-size: 0.6rem;"></i> <?= strtoupper($row['status']); ?>
                        </span>
                    </div>
                </div>

                <div class="row g-4 px-lg-5">
                    <div class="col-md-6">
                        <div class="p-3 border-0 rounded-4 bg-light text-center shadow-sm">
                            <label class="d-block text-muted small fw-bold text-uppercase mb-1">System ID</label>
                            <span class="fs-5 fw-bold text-dark">#<?= $row['role_id']; ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border-0 rounded-4 bg-light text-center shadow-sm">
                            <label class="d-block text-muted small fw-bold text-uppercase mb-1">User Role</label>
                            <span class="fs-5 fw-bold text-dark"><?= htmlspecialchars($row['role']); ?></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-4 border rounded-4 shadow-sm">
                            <div class="row align-items-center">
                                <div class="col-md-6 mb-3 mb-md-0 border-end">
                                    <label class="d-block text-muted small fw-bold text-uppercase mb-1">Email Address</label>
                                    <span class="fw-semibold fs-6 text-primary"><i class="fa-regular fa-envelope me-2"></i><?= htmlspecialchars($row['email']); ?></span>
                                </div>
                                <div class="col-md-6 ps-md-4">
                                    <label class="d-block text-muted small fw-bold text-uppercase mb-1">Phone Number</label>
                                    <span class="fw-semibold fs-6 text-dark"><i class="fa-solid fa-phone me-2 text-muted"></i><?= htmlspecialchars($row['phone'] ?: 'Not Provided'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 pt-3 border-top text-center">
                    <p class="text-muted mb-0">Member since: <strong><?= date("F d, Y", strtotime($row['created_at'])); ?></strong></p>
                </div>
            </div>

            <div class="modal-footer border-0 justify-content-center pb-5 pt-0">
                <button type="button" class="btn btn-dark px-5 py-2 rounded-pill fw-bold shadow-sm" data-bs-dismiss="modal">
                    Close Details
                </button>
            </div>
        </div>
    </div>
</div>
<?php endwhile; ?>
<!-- view user modal end -->


<!-- restore user modal start -->
<?php
    $archiveQuery = "SELECT * FROM deleted_users ORDER BY deleted_at DESC";
    $archiveResult = mysqli_query($conn, $archiveQuery);
    
    if ($archiveResult):
        while ($drow = mysqli_fetch_assoc($archiveResult)):
            $initials = strtoupper(substr($drow['first_name'], 0, 1) . substr($drow['last_name'], 0, 1));
?>
<div class="modal fade" id="restoreUserModal<?= $drow['user_id']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"> 
        <div class="modal-content border-0 shadow-lg" style="border-radius: 25px; overflow: hidden;">
            <input type="hidden" class="session-pass-verify" value="<?= $_SESSION['password']; ?>">
            
            <div class="modal-header border-0 pt-5 px-4 position-relative" style="background: #198754; min-height: 140px; align-items: flex-start;">
                <h5 class="modal-title w-100 text-center fw-bold text-white" style="letter-spacing: 1px;">
                    <i class="fa-solid fa-rotate-left me-2"></i>Restore User Account
                </h5>
                <button type="button" class="btn-close btn-close-white position-absolute" style="right: 25px; top: 25px;" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4 pt-0" style="margin-top: -60px;">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center text-white shadow-lg mb-3" 
                         style="width: 180px; height: 180px; border-radius: 40px; font-size: 4rem; font-weight: 700; border: 8px solid #fff; background: #198754; overflow: hidden;">
                         <?php if (!empty($drow['profile_image']) && file_exists($drow['profile_image'])): ?>
                            <img src="<?= $drow['profile_image']; ?>" style="width:100%; height:100%; object-fit:cover;">
                         <?php else: ?>
                            <?= $initials; ?>
                         <?php endif; ?>
                    </div>
                    
                    <h2 class="mb-1 fw-bold text-dark"><?= htmlspecialchars($drow['first_name'] . ' ' . $drow['last_name']); ?></h2>
                    <p class="text-muted fs-5 mb-4">@<?= htmlspecialchars($drow['username']); ?></p>

                    <div class="alert alert-success border-0 py-3 mb-4 rounded-4 shadow-sm mx-lg-5" style="background-color: #f0fff4;">
                        <p class="mb-0 text-success fw-bold">
                            <i class="fa-solid fa-circle-info me-2"></i>This will move the user back to the active accounts list.
                        </p>
                    </div>

                    <div class="mb-3 px-lg-5">
                        <label class="form-label small fw-bold text-uppercase text-muted d-block text-center mb-3">Confirm Admin Session Password</label>
                        <div class="position-relative mx-auto" style="max-width: 400px;">
                            <span class="position-absolute start-0 top-50 translate-middle-y ps-3 text-muted">
                                <i class="fa-solid fa-shield-check"></i>
                            </span>
                            <input type="password" class="form-control rounded-pill text-center verify-input py-3 shadow-sm" 
                                   placeholder="Enter password to unlock" 
                                   style="padding-right: 45px; padding-left: 45px; border: 2px solid #f1f1f1;">
                            <i class="fa-solid fa-eye position-absolute toggle-password" 
                               style="right: 20px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6c757d;"></i>
                        </div>
                    </div>
                </div>

                <div class="row g-3 px-lg-5 mb-2">
                    <div class="col-6">
                        <div class="p-3 border rounded-4 bg-light text-center small fw-bold text-muted shadow-sm">
                            ROLE: <?= strtoupper(htmlspecialchars($drow['role'])); ?>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 border rounded-4 bg-light text-center small fw-bold text-muted shadow-sm">
                            ID: #<?= $drow['role_id']; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 justify-content-center pb-5 pt-0 px-lg-5">
                <form action="../src/php_script/restore_user.php" method="POST" class="w-100 d-flex gap-3 px-lg-5">
                    <input type="hidden" name="user_id" value="<?= $drow['user_id']; ?>">
                    <button type="button" class="btn btn-light px-4 py-2 rounded-pill fw-bold w-50 border shadow-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="restore_user" class="btn btn-success px-4 py-2 rounded-pill fw-bold w-50 shadow-sm confirm-verify-btn" disabled>
                        <i class="fa-solid fa-trash-arrow-up me-2"></i>Restore Now
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- restore user modal end -->


<!-- wipe user modal start -->
<div class="modal fade" id="wipeUserModal<?= $drow['user_id']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"> 
        <div class="modal-content border-0 shadow-lg" style="border-radius: 25px; overflow: hidden;">
            <input type="hidden" class="session-pass-verify" value="<?= $_SESSION['password']; ?>">

            <div class="modal-header border-0 pt-5 px-4 position-relative" style="background: #b02a37; min-height: 140px; align-items: flex-start;">
                <h5 class="modal-title w-100 text-center fw-bold text-white" style="letter-spacing: 1px;">
                    <i class="fa-solid fa-skull-crossbones me-2"></i>Critical Action: Permanent Wipe
                </h5>
                <button type="button" class="btn-close btn-close-white position-absolute" style="right: 25px; top: 25px;" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4 pt-0" style="margin-top: -60px;">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center text-white shadow-lg mb-3" 
                         style="width: 180px; height: 180px; border-radius: 40px; font-size: 5rem; font-weight: 700; border: 8px solid #fff; background: #dc3545; overflow: hidden;">
                         <i class="fa-solid fa-user-slash"></i>
                    </div>
                    
                    <h2 class="mb-1 fw-bold text-dark"><?= htmlspecialchars($drow['first_name'] . ' ' . $drow['last_name']); ?></h2>
                    <p class="text-muted fs-5">Account ID: <span class="fw-bold">#<?= $drow['role_id']; ?></span></p>
                    
                    <div class="alert alert-danger border-0 py-3 mb-4 rounded-4 shadow-sm mx-lg-5" style="background-color: #fff5f5;">
                        <p class="mb-0 fw-bold">
                            <i class="fa-solid fa-circle-exclamation me-2"></i>THIS CANNOT BE UNDONE.
                        </p>
                        <small>This will permanently erase this record from the archive and audit trails.</small>
                    </div>

                    <div class="mb-3 px-lg-5">
                        <label class="form-label small fw-bold text-uppercase text-muted d-block text-center mb-3">Confirm Session Password to Unlock</label>
                        <div class="position-relative mx-auto" style="max-width: 400px;">
                            <span class="position-absolute start-0 top-50 translate-middle-y ps-3 text-muted">
                                <i class="fa-solid fa-key"></i>
                            </span>
                            <input type="password" class="form-control rounded-pill text-center verify-input py-3 shadow-sm" 
                                   placeholder="Enter your password" 
                                   style="padding-right: 45px; padding-left: 45px; border: 2px solid #f1f1f1;">
                            <i class="fa-solid fa-eye position-absolute toggle-password" 
                               style="right: 20px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6c757d;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 justify-content-center pb-5 pt-0 px-lg-5">
                <form action="../src/php_script/delete_user_permanently.php" method="POST" class="w-100 d-flex gap-3 px-lg-5">
                    <input type="hidden" name="user_id" value="<?= $drow['user_id']; ?>">
                    <button type="button" class="btn btn-light px-4 py-2 rounded-pill fw-bold w-50 border shadow-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="wipe_user" class="btn btn-danger px-4 py-2 rounded-pill fw-bold w-50 shadow-sm confirm-verify-btn" disabled>
                        Wipe Forever
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php 
        endwhile; 
    endif; 
?>
<!-- wipe user modal end -->

<!-- report damage modal start -->
<div class="modal fade" id="reportDamageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"> <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header bg-danger bg-gradient text-white py-3 border-0">
                <div class="d-flex align-items-center">
                    <div class="p-2 me-3">
                        <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Report Damage</h5>
                        <small class="opacity-75">Deduct items from active inventory</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <form method="POST" action="../src/php_script/process_damage.php" id="damageForm">
                    <input type="hidden" name="report_damage" value="1">
                    
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary small text-uppercase">Select Product</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-box-open text-muted"></i></span>
                                <select name="product_id" id="productSelect" class="form-select border-start-0 bg-light" required>
                                    <option value="">Choose a product...</option>
                                    <?php 
                                        if($products->num_rows > 0):
                                            $products->data_seek(0); 
                                            while ($p = $products->fetch_assoc()): 
                                    ?>
                                        <option value="<?= $p['product_id'] ?>" data-stock="<?= $p['stock_level'] ?>">
                                            <?= htmlspecialchars($p['product_name']) ?>
                                        </option>
                                    <?php 
                                            endwhile; 
                                        endif;
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary small text-uppercase d-flex justify-content-between">
                                Qty to Report
                                <span id="stockBadge" class="badge bg-secondary-subtle text-secondary fw-normal">Max: 0</span>
                            </label>
                            <input type="number" name="quantity_damaged" id="qtyInput" class="form-control form-control-lg bg-light" min="1" placeholder="0" disabled required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary small text-uppercase">Action</label>
                            <select name="action_required" class="form-select form-control-lg bg-light" required>
                                <option value="REPAIR">Repair Item</option>
                                <option value="REPLACE">Replace Item</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary small text-uppercase">Reason for Damage</label>
                            <textarea name="reason" class="form-control bg-light" rows="2" placeholder="Describe how it was damaged..." required></textarea>
                        </div>
                    </div>

                    <div class="mt-4 pt-2 d-flex gap-2">
                        <button type="button" class="btn btn-link text-muted text-decoration-none w-100" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="submitDamage" class="btn btn-danger py-2 w-100 fw-bold shadow-sm">
                            Submit Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- report damage modal end -->