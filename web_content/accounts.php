<?php
    session_start();

    include "../src/cdn/cdn_links.php";
    include "../render/connection.php";
    include "../render/modal.php";

    // Function to determine the badge class based on status
    function getStatusBadge($status) {
        return match (strtolower($status)) {
            'active'    => 'text-bg-success',
            'inactive'  => 'text-bg-secondary',
            'suspended' => 'text-bg-danger',
            default     => 'text-bg-info',
        };
    }

    // --- Database Query ---
    $sql = "SELECT user_id, role_id, first_name, last_name, username, email, phone, role, status, last_login, created_at, profile_image 
        FROM users 
        ORDER BY user_id DESC";

    $users = [];
    if (isset($pdo) && is_object($pdo)) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll();
    }
?> 

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>User Accounts Management</title>
        <style>
            body { padding-top: 56px; }
            .table-action-btns { min-width: 180px; }
            .profile-picture-modal {
                width: 100px; height: 100px; border-radius: 50%;
                object-fit: cover; border: 3px solid #0d6efd;
            }
            .avatar-sm {
                width: 32px; height: 32px; border-radius: 50%;
                object-fit: cover; margin-right: 10px;
            }
        </style>
    </head>
    <body class="bg-light">

        <?php include "../nav/header.php"; ?> 

        <div class="container-fluid mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="fw-light text-dark"><i class="fa-solid fa-users-gear me-2"></i> User Management</h1>
                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fa-solid fa-user-plus me-2"></i> Add New User
                </button>
            </div>

            <div class="card shadow border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold text-muted">Registered Users (<?= count($users); ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr><td colspan="7" class="text-center py-5 text-muted">No users found.</td></tr>
                                <?php endif; ?>

                                <?php foreach ($users as $row): 
                                    // Image Logic
                                    $default_image = '../src/image/default_profile.png';
                                    $img_path = $row['profile_image'];
                                    $image_src = (!empty($img_path) && $img_path !== 'src/image/profile_picture/') ? $img_path : $default_image;
                                    
                                    // Role Badge Logic
                                    $role_class = match(strtolower($row['role'])) {
                                        'administrator' => 'bg-danger',
                                        'inventory manager' => 'bg-primary',
                                        'stock handler' => 'bg-success',
                                        default => 'bg-secondary'
                                    };
                                ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?= $image_src; ?>" class="avatar-sm" alt="profile">
                                                <span class="fw-bold"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></span>
                                            </div>
                                        </td>
                                        <td><code class="text-primary"><?= htmlspecialchars($row['username']); ?></code></td>
                                        <td><small><a href="mailto:<?= htmlspecialchars($row['email']); ?>" class="text-decoration-none"><?= htmlspecialchars($row['email']); ?></a></small></td>
                                        <td><span class="badge <?= $role_class; ?>"><?= strtoupper(htmlspecialchars($row['role'])); ?></span></td>
                                        <td>
                                            <span class="badge <?= $row['status'] === 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?= ucfirst(htmlspecialchars($row['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="<?= empty($row['last_login']) ? 'text-danger' : 'text-muted'; ?>">
                                                <?= empty($row['last_login']) ? "Never" : date("M d, Y H:i", strtotime($row['last_login'])); ?>
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group shadow-sm">
                                                <button class="btn btn-sm btn-white border" data-bs-toggle="modal" data-bs-target="#viewUserModal_<?= $row['user_id']; ?>" title="View">
                                                    <i class="fa-solid fa-eye text-primary"></i>
                                                </button>

                                                <?php if ($row['user_id'] != $_SESSION['user_id']): ?>
                                                    <button class="btn btn-sm btn-white border" 
                                                            data-bs-toggle="modal" data-bs-target="#confirmStatusModal" 
                                                            data-user-id="<?= $row['user_id']; ?>" 
                                                            data-action="<?= $row['status'] === 'active' ? 'inactive' : 'active'; ?>"
                                                            title="<?= $row['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>">
                                                        <i class="fa-solid fa-power-off <?= $row['status'] === 'active' ? 'text-warning' : 'text-success'; ?>"></i>
                                                    </button>
                                                    
                                                    <button class="btn btn-sm btn-white border" 
                                                            <?= ($row['role'] == "Administrator") ? 'disabled' : ''; ?>
                                                            data-bs-toggle="modal" data-bs-target="#deleteUserModal" 
                                                            data-user-id="<?= $row['user_id']; ?>" title="Delete">
                                                        <i class="fa-regular fa-trash-can text-danger"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-white border" disabled title="Current User">
                                                        <i class="fa-solid fa-user-check text-muted"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="viewUserModal_<?= $row['user_id']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow-lg">
                                                <div class="modal-header bg-primary text-white border-0">
                                                    <h5 class="modal-title"><i class="fa-solid fa-address-card me-2"></i>User Profile</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                
                                                <div class="modal-body text-center p-4">
                                                    <div class="mb-4">
                                                        <img src="<?= $image_src; ?>" class="rounded-circle shadow-sm mb-3 border border-3 border-light" alt="Profile" style="width: 100px; height: 100px; object-fit: cover;">
                                                        <h4 class="mb-0 fw-bold"><?= htmlspecialchars($row['first_name'].' '.$row['last_name']); ?></h4>
                                                        <p class="text-info fw-semibold small">@<?= htmlspecialchars($row['username']); ?></p>
                                                    </div>
                                                    
                                                    <div class="list-group list-group-flush text-start border rounded-3 overflow-hidden">
                                                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                                            <span class="text-muted small"><i class="fa-solid fa-envelope me-2"></i>Email</span>
                                                            <span class="fw-medium"><?= htmlspecialchars($row['email']); ?></span>
                                                        </div>
                                                        
                                                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                                            <span class="text-muted small"><i class="fa-solid fa-phone me-2"></i>Phone</span>
                                                            <span class="fw-medium"><?= htmlspecialchars($row['phone'] ?: 'Not Provided'); ?></span>
                                                        </div>
                                                        
                                                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                                            <span class="text-muted small"><i class="fa-solid fa-user-shield me-2"></i>Access Role</span>
                                                            <span class="badge <?= $role_class; ?> rounded-pill px-3"><?= strtoupper($row['role']); ?></span>
                                                        </div>

                                                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                                            <span class="text-muted small"><i class="fa-solid fa-circle-check me-2"></i>Status</span>
                                                            <?php 
                                                                $status = $row['status'] ?? 'Active';
                                                                $statusColor = ($status === 'Active') ? 'text-success' : 'text-success';
                                                            ?>
                                                            <span class="<?= $statusColor; ?> small fw-bold">
                                                                <i class="fa-solid fa-circle fa-2xs me-1"></i> <?= $status; ?>
                                                            </span>
                                                        </div>

                                                        <div class="list-group-item d-flex justify-content-between align-items-center py-3 bg-light">
                                                            <span class="text-muted small"><i class="fa-solid fa-calendar-day me-2"></i>Member Since</span>
                                                            <div class="text-end">
                                                                <?php if (isset($row['created_at']) && !empty($row['created_at'])): ?>
                                                                    <div class="fw-medium small"><?= date('F j, Y', strtotime($row['created_at'])); ?></div>
                                                                    <div class="text-muted" style="font-size: 0.75rem;"><?= date('g:i A', strtotime($row['created_at'])); ?></div>
                                                                <?php else: ?>
                                                                    <div class="text-muted small">Not Available</div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-outline-secondary w-100 rounded-pill" data-bs-dismiss="modal">Close Details</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const statusModal = document.getElementById('confirmStatusModal');
                if(statusModal) {
                    statusModal.addEventListener('show.bs.modal', function (event) {
                        const button = event.relatedTarget;
                        const action = button.getAttribute('data-action');
                        
                        document.getElementById('targetUserId').value = button.getAttribute('data-user-id');
                        document.getElementById('newStatus').value = action;
                        
                        const isDeactivating = action === 'inactive';
                        document.getElementById('statusModalTitle').textContent = isDeactivating ? "Confirm Deactivation" : "Confirm Activation";
                        document.getElementById('confirmStatusBtn').className = isDeactivating ? "btn btn-danger" : "btn btn-success";
                    });
                }
            });
        </script>

        <div class="modal fade" id="confirmStatusModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form action="../src/php_script/deactivate_user.php" method="POST">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold" id="statusModalTitle">Confirm Action</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center py-4">
                            <i class="fa-solid fa-circle-exclamation fa-3x text-warning mb-3"></i>
                            <p class="mb-0">Are you sure you want to change this user's status?</p>
                            
                            <input type="hidden" name="user_id" id="targetUserId">
                            <input type="hidden" name="new_status" id="newStatus">
                        </div>
                        <div class="modal-footer border-0 pt-0 justify-content-center">
                            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="update_status" id="confirmStatusBtn" class="btn px-4">Confirm</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>