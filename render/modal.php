<!-- add category modal start -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="addCategoryModalLabel"><i class="fa-solid fa-tags me-2"></i> Add New Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="../src/php_script/add_category.php">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category Name</label>
                        <input type="text" class="form-control" name="category_name" id="categoryName" required>
                    </div>

                    <div class="mb-3">
                        <label for="categoryDescription" class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="categoryDescription" rows="3"></textarea>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info text-white">
                            <i class="fa-solid fa-plus me-1"></i> Create Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- add category modal end -->

<!-- delete category modal start -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteCategoryModalLabel"><i class="fa-solid fa-trash-can me-2"></i> Delete Inventory Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning" role="alert">
                    <strong>Warning:</strong> Deleting a category may affect products currently assigned to it.
                </div>
                <form method="POST" action="../src/php_script/delete_category.php" id="deleteCategoryForm">
                    <div class="mb-3">
                        <label for="selectCategoryToDelete" class="form-label">Select Category to Delete</label>
                        <select class="form-select" id="selectCategoryToDelete" name="category_id" required>
                            <option value="" selected>Select Category...</option>
                            <?php
                                $category_list = "SELECT * FROM categories";
                                $result_category_list = mysqli_query($conn, $category_list);

                                if($result_category_list) {
                                    while($row = mysqli_fetch_assoc($result_category_list)) {
                                        ?>
                                        <option value="<?php echo $row['category_id']; ?>" 
                                                title="<?php echo $row["description"]; ?>">
                                                <?php echo $row['category_name']; ?>
                                        </option>
                                        <?php
                                    }
                                }
                            ?>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fa-solid fa-trash me-1"></i> Confirm Deletion
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- delete category modal end -->

<!-- add product modal start -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fa-solid fa-box-open me-2"></i> Add New Inventory Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form method="POST" action="../src/php_script/add_product.php">

                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" class="form-control" name="productName" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Brand</label>
                        <input type="text" class="form-control" name="brand" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="productCategory" required>
                            <option value="">Select...</option>

                            <?php
                                $categories = mysqli_query($conn, "SELECT * FROM categories");
                                while ($row = mysqli_fetch_assoc($categories)) {
                                    echo '<option value="'.$row['category_id'].'">'.$row['category_name'].'</option>';
                                }
                            ?>

                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Unit Cost (₱)</label>
                        <input type="number" step="0.01" min="0" name="unitCost" class="form-control" value="0.00" required>
                    </div>

                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Initial Stock</label>
                            <input type="number" name="initialStock" class="form-control" value="0" required>
                        </div>

                        <div class="col mb-3">
                            <label class="form-label">Min. Threshold</label>
                            <input type="number" name="minThreshold" class="form-control" value="10" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" value="" required>
                        </div>

                        <div class="col mb-3">
                            <label class="form-label">Status</label>
                            <input type="text" name="status" class="form-control" value="" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remarks" rows="4"></textarea>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fa-solid fa-save me-1"></i> Save Product
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>
<!-- add product modal end -->

<!-- confirm damage loging modal start -->
<div class="modal fade" id="confirmDamageModal" tabindex="-1" aria-labelledby="confirmDamageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmDamageModalLabel"><i class="fa-solid fa-triangle-exclamation me-2"></i> Confirm Stock Deduction</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>You are about to log **<span id="confirmQty" class="fw-bold">0</span>** unit(s) of **<span id="confirmProduct" class="fw-bold"></span>** as damaged.</p>
                <p class="text-danger fw-bold">This action will immediately REDUCE the available stock level by the reported quantity to account for the replacement unit.</p>
                <p>Are you sure you want to proceed?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="submitDamageButton">
                    <i class="fa-solid fa-check me-1"></i> Confirm & Deduct Stock
                </button>
            </div>
        </div>
    </div>
</div>
<!-- confirm damage loging modal end -->

<!-- add account modal start -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addUserModalLabel"><i class="fa-solid fa-user-plus me-2"></i> Create New User Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="addUserForm" method="POST" action="../src/php_script/add_account.php">
                <div class="modal-body">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="userFirstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="userFirstName" name="first_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="userLastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="userLastName" name="last_name" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="userNameInput" class="form-label">**Username**</label>
                        <input type="text" class="form-control" id="userNameInput" name="username" placeholder="Unique login name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="userEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="userEmail" name="email" placeholder="user@domain.com" required>
                    </div>
                    <div class="mb-3">
                        <label for="userPhone" class="form-label">Phone Number (Optional)</label>
                        <input type="tel" class="form-control" id="userPhone" name="phone">
                    </div>
                    
                    <div class="mb-3">
                        <label for="userRole" class="form-label">Assign Role</label>
                        <select class="form-select" id="userRole" name="role" required>
                            <option value="" selected disabled>Select Role...</option>
                            <option value="Administrator">Administrator</option>
                            <option value="Inventory Manager">Inventory Manager</option>
                            <option value="Stock Handler">Stock Handler</option>
                            <option value="Viewer">Viewer</option>
                        </select>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label for="initialPassword" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="initialPassword" name="password" required onkeyup="checkPasswordStrength(this.value)">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fa-solid fa-eye-slash" id="toggleIcon"></i>
                            </button>
                        </div>
                        <div class="mt-1">
                            <div class="progress" style="height: 5px;">
                                <div id="passwordStrengthBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small id="passwordFeedback" class="form-text text-muted"></small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required 
                            onkeyup="checkPasswordMatch()">
                        <small id="passwordMatchFeedback" class="form-text"></small>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-2"></i> Create Account</button>
                </div>
            </form>
            
        </div>
    </div>
</div>

<script>
    const passwordSettings = {
        minLength: 12, 
        requireUppercase: true,
        requireSymbol: true,
        requireNumber: true
    };
    
    function checkPasswordMatch() {
        const password = document.getElementById('initialPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const feedback = document.getElementById('passwordMatchFeedback');

        // Reset classes
        feedback.classList.remove('text-success', 'text-danger');
        feedback.textContent = '';
        
        // Only check if both fields have content
        if (password.length === 0 && confirmPassword.length === 0) {
            return;
        }

        if (password === confirmPassword && confirmPassword.length > 0) {
            feedback.classList.add('text-success');
            feedback.textContent = 'Passwords match!';
        } else if (confirmPassword.length > 0) {
            feedback.classList.add('text-danger');
            feedback.textContent = 'Passwords do not match.';
        }
    }
    document.getElementById('togglePassword').addEventListener('click', function (e) {
        const passwordInput = document.getElementById('initialPassword');
        const toggleIcon = document.getElementById('toggleIcon');
        
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);

        if (type === 'text') {
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        } else {
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        }
    });
    function checkPasswordStrength(password) {
        const bar = document.getElementById('passwordStrengthBar');
        const feedback = document.getElementById('passwordFeedback');
        let score = 0;
        let feedbackText = '';
        let barClass = 'bg-danger'; 
        if (password.length === 0) {
            bar.style.width = '0%';
            bar.className = 'progress-bar';
            feedback.textContent = '';
            checkPasswordMatch(); 
            return;
        }

        // --- Custom Strength Checks (20 points each) ---
        
        // 1. Length Check (> 11 characters, meaning >= 12)
        if (password.length > 11) {
            score += 20;
        } else {
            feedbackText += 'Must be longer than 11 characters. ';
        }

        // 2. Uppercase Check (at least one)
        if (/[A-Z]/.test(password)) {
            score += 20;
        } else {
            feedbackText += 'Requires at least one uppercase letter. ';
        }

        // 3. Lowercase Check (at least one)
        if (/[a-z]/.test(password)) {
            score += 20;
        } else {
            feedbackText += 'Requires at least one lowercase letter. ';
        }

        // 4. Number Check (at least one)
        if (/[0-9]/.test(password)) {
            score += 20;
        } else {
            feedbackText += 'Requires at least one number. ';
        }
        
        // 5. Special Character Check (at least one symbol)
        if (/[^A-Za-z0-9\s]/.test(password)) {
            score += 20;
        } else {
            feedbackText += 'Requires at least one special character. ';
        }

        // --- Update UI Based on Score ---

        let strengthLabel = 'Very Weak';
        
        if (score === 100) {
            strengthLabel = 'Strong';
            barClass = 'bg-success';
            feedbackText = 'Strong password. All requirements met.';
        } else if (score >= 80) {
            strengthLabel = 'Good';
            barClass = 'bg-info';
        } else if (score >= 60) {
            strengthLabel = 'Moderate';
            barClass = 'bg-warning';
        } 

        // Apply visual updates
        bar.style.width = `${score}%`;
        bar.className = `progress-bar ${barClass}`;
        
        if (score < 100) {
             feedback.textContent = `${strengthLabel}. ${feedbackText.trim()}`;
        } else {
             feedback.textContent = feedbackText.trim();
        }
        checkPasswordMatch();
    }

</script>
<!-- add account modal end -->

<!-- edit product modal start -->
<?php
// Use long PHP tags for consistency and better compatibility
$edit_product = "SELECT * FROM products";
$result_edit_product = mysqli_query($conn, $edit_product);

if($result_edit_product) {
    while($row = mysqli_fetch_assoc($result_edit_product)) {
        ?>
        <div class="modal fade" id="editProductModal<?php echo $row['product_id']; ?>" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="editProductModalLabel"><i class="fa-solid fa-pencil me-2"></i> Edit Product Details</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="../src/php_script/edit_product.php" method="POST" id="editProductForm<?php echo $row['product_id']; ?>">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="edit_product">
                            <input type="hidden" name="productId" value="<?php echo $row['product_id']; ?>"> 

                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label for="edit_product_name<?php echo $row['product_id']; ?>" class="form-label">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_product_name<?php echo $row['product_id']; ?>" name="productName" value="<?php echo htmlspecialchars($row['product_name']); ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="edit_sku<?php echo $row['product_id']; ?>" class="form-label">SKU / Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_sku<?php echo $row['product_id']; ?>" name="sku" value="<?php echo htmlspecialchars($row['sku']); ?>" required readonly>
                                    <div class="form-text">Must be unique.</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="edit_brand<?php echo $row['product_id']; ?>" class="form-label">Brand <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_brand<?php echo $row['product_id']; ?>" name="brand" value="<?php echo htmlspecialchars($row['brand']); ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="edit_category_id<?php echo $row['product_id']; ?>" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select class="form-select" id="edit_category_id<?php echo $row['product_id']; ?>" name="productCategory" required>
                                        <option value="" disabled>Select Category</option>
                                        <?php
                                        $select_category = "SELECT * FROM categories ORDER BY category_name ASC";
                                        $result_select_category = mysqli_query($conn, $select_category);
                                        if ($result_select_category) {
                                            while ($category_row = mysqli_fetch_assoc($result_select_category)) {
                                                $selected = ($category_row['category_id'] == $row['category_id']) ? 'selected' : '';
                                                ?>
                                                <option value="<?php echo $category_row['category_id']; ?>" <?php echo $selected; ?>>
                                                    <?php echo htmlspecialchars($category_row['category_name']); ?>
                                                </option>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="edit_unit_cost<?php echo $row['product_id']; ?>" class="form-label">Unit Cost (₱) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="edit_unit_cost<?php echo $row['product_id']; ?>" name="unitCost" step="0.01" min="0" value="<?php echo $row['unit_cost']; ?>" required readonly>
                                </div>
                                <div class="col-md-4">
                                    <label for="edit_stock_level<?php echo $row['product_id']; ?>" class="form-label">Stock Level <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="edit_stock_level<?php echo $row['product_id']; ?>" name="stockLevel" min="0" value="<?php echo $row['stock_level']; ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="edit_min_threshold<?php echo $row['product_id']; ?>" class="form-label">Min. Threshold</label>
                                    <input type="number" class="form-control" id="edit_min_threshold<?php echo $row['product_id']; ?>" name="minThreshold" min="0" value="<?php echo $row['min_threshold']; ?>">
                                    <div class="form-text">Sets the level for low stock alerts.</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="location<?php echo $row['product_id']; ?>" class="form-label">Location <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="location<?php echo $row['product_id']; ?>" name="location" min="0" value="<?php echo $row['location']; ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="status<?php echo $row['product_id']; ?>" class="form-label">Status <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="status<?php echo $row['product_id']; ?>" name="status" value="<?php echo $row['status']; ?>">
                                </div>

                                <!-- REMARKS TEXTAREA IS NOW BLANK -->
                                <div class="col-12">
                                    <label for="edit_remarks<?php echo $row['product_id']; ?>" class="form-label">Remarks / Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="edit_remarks<?php echo $row['product_id']; ?>" name="remarks" rows="3" required><?php echo $row['remarks']; ?></textarea>
                                    <div class="form-text">Please enter what will happen to the item (required).</div>
                                </div>

                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-2"></i> Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
}
?>
<!-- edit product modal end -->


















































