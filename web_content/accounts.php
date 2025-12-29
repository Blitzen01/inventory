<?php
    session_start();
    include "../src/cdn/cdn_links.php";
    include "../render/connection.php";
    include "../render/modal.php";

    // 1. Pagination Logic
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // 2. Fetch Active Users
    $count_stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $total_users = $count_stmt->fetchColumn();
    $total_pages = ceil($total_users / $limit);

    $stmt = $pdo->prepare("SELECT * FROM users ORDER BY user_id DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll();

    // 3. Fetch Deleted Users
    $stmt_deleted = $pdo->prepare("SELECT * FROM deleted_users ORDER BY deleted_at DESC");
    $stmt_deleted->execute();
    $deleted_users = $stmt_deleted->fetchAll();
?> 

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>User Management</title>

        <style>
            :root { --glass-bg: rgba(255, 255, 255, 0.9); }
            body { background-color: #f4f7f6; padding-top: 80px; font-family: 'Segoe UI', sans-serif; }
            .main-card { border: none; border-radius: 0 0 15px 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); background: var(--glass-bg); backdrop-filter: blur(10px); }
            
            /* Tabs Styling */
            .nav-tabs { border: none; }
            .nav-tabs .nav-link { border: none; color: #888; font-weight: 600; padding: 12px 25px; border-radius: 12px 12px 0 0; margin-right: 5px; background: rgba(0,0,0,0.03); }
            .nav-tabs .nav-link.active { background: #fff; color: #0d6efd; box-shadow: 0 -5px 15px rgba(0,0,0,0.05); }

            .table-sm thead th { font-size: 0.65rem; text-transform: uppercase; color: #888; padding: 12px 10px; border-bottom: 2px solid #f1f1f1; }
            .avatar-wrapper { width: 32px; height: 32px; border-radius: 10px; overflow: hidden; display: flex; align-items: center; justify-content: center; }
            .initials-fallback { background: #2f2f30; color: white; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold; }
            .status-badge { padding: 4px 10px; border-radius: 6px; font-size: 0.65rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }
            
            /* Action Buttons */
            .action-btn { height: 30px; width: 30px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; border: 1px solid #eee; background: white; color: #555; transition: 0.2s; }
            .action-btn:hover { background: #f8f9fa; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }

            .confirm-verify-btn:disabled {
                background-color: #6c757d !important;
                border-color: #6c757d !important;
                color: white !important;
                opacity: 0.6;
                pointer-events: none;
                height: auto !important; /* This prevents the 30px height conflict */
                width: 50% !important;   /* This keeps it at half-width in the modal */
            }

            .verify-input {
                border: 2px solid #dee2e6 !important;
                transition: all 0.3s ease;
            }

            .toggle-password:hover {
                color: #212529 !important;
            }
        </style>
    </head>
    <body>

        <?php include "../nav/header.php"; ?> 

        <div class="container-fluid px-4">
            <div class="row align-items-center mb-4">
                <div class="col-md-6">
                    <h2 class="fw-bold text-dark m-0">User Management</h2>
                    <p class="text-muted small">Manage active users and archived records</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <button class="btn btn-primary px-4 py-2 shadow-sm rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fa-solid fa-user-plus me-2"></i> New User
                    </button>
                </div>
            </div>

            <ul class="nav nav-tabs" id="userTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active-content" type="button">
                        Active Accounts <span class="badge bg-primary ms-2"><?= $total_users ?></span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="deleted-tab" data-bs-toggle="tab" data-bs-target="#deleted-content" type="button">
                        Archive Log <span class="badge bg-danger ms-2"><?= count($deleted_users) ?></span>
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="active-content">
                    <div class="card main-card">
                        <div class="p-3 d-flex justify-content-between align-items-center border-bottom bg-white rounded-top">
                            <form method="GET" class="d-flex align-items-center">
                                <span class="small text-muted me-2">Show</span>
                                <select name="limit" onchange="this.form.submit()" class="form-select form-select-sm" style="width: auto;">
                                    <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                                    <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>20</option>
                                    <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                                </select>
                            </form>
                            <div class="text-muted small">Showing <?= count($users) ?> active users</div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-3">User Details</th>
                                        <th>ID</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th class="text-center">Manage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $row): 
                                        // 1. Basic variables
                                        $initial = strtoupper(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1));
                                        $isActive = (strtolower($row['status']) === 'active');

                                        // 2. Role Styling
                                        $roleName = strtolower($row['role']);
                                        $roleClass = match(true) {
                                            str_contains($roleName, 'super')     => 'bg-danger-subtle text-danger border-danger',
                                            str_contains($roleName, 'admin')     => 'bg-primary-subtle text-primary border-primary',
                                            str_contains($roleName, 'inventory')   => 'bg-info-subtle text-info border-info',
                                            str_contains($roleName, 'stock')       => 'bg-warning-subtle text-warning border-warning',
                                            str_contains($roleName, 'viewer')      => 'bg-secondary-subtle text-secondary border-secondary',
                                            default                                => 'bg-light text-dark border-dark-subtle',
                                        };
                                        
                                        $roleIcon = match(true) {
                                            str_contains($roleName, 'super')     => 'fa-shield-heart',
                                            str_contains($roleName, 'admin')     => 'fa-user-shield',
                                            str_contains($roleName, 'inventory')   => 'fa-boxes-stacked',
                                            str_contains($roleName, 'stock')       => 'fa-box-open',
                                            default                                => 'fa-user',
                                        };

                                        // 3. RESTRICTION LOGIC (Safe from Undefined Key errors)
                                        $myRole = strtolower($_SESSION['user_type'] ?? ''); 
                                        $targetRole = strtolower($row['role'] ?? '');
                                        $canEdit = false;

                                        if ($myRole === 'super administrator') {
                                            $canEdit = true; 
                                        } elseif ($myRole === 'administrator') {
                                            // Admin can only edit if target is NOT an admin/super admin
                                            if (!str_contains($targetRole, 'admin')) {
                                                $canEdit = true;
                                            }
                                        }
                                        
                                        // Safety: Cannot edit/delete yourself
                                        if ($row['user_id'] == ($_SESSION['user_id'] ?? 0)) {
                                            $canEdit = false;
                                        }
                                    ?>
                                        <tr>
                                            <td class="ps-3">
                                                <div class="d-flex align-items-center py-1">
                                                    <div class="avatar-wrapper me-2 shadow-sm">
                                                        <?php if (!empty($row['profile_image']) && file_exists($row['profile_image'])): ?>
                                                            <img src="<?= $row['profile_image']; ?>" style="width:100%; height:100%; object-fit:cover;">
                                                        <?php else: ?>
                                                            <div class="initials-fallback"><?= $initial; ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark mb-0 small"><?= htmlspecialchars($row['first_name'].' '.$row['last_name']); ?></div>
                                                        <div class="text-muted" style="font-size: 0.65rem;">@<?= htmlspecialchars($row['username']); ?></div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td><span class="text-muted font-monospace small">#<?= $row['role_id']; ?></span></td>

                                            <td>
                                                <span class="badge <?= $roleClass ?> border px-2 py-1 small fw-bold" style="font-size: 0.7rem;">
                                                    <i class="fa-solid <?= $roleIcon ?> me-1"></i>
                                                    <?= strtoupper(htmlspecialchars($row['role'])); ?>
                                                </span>
                                            </td>

                                            <td>
                                                <span class="status-badge <?= $isActive ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?>">
                                                    <i class="fa-solid <?= $isActive ? 'fa-check-circle' : 'fa-circle-xmark'; ?>"></i>
                                                    <?= $isActive ? 'ACTIVATED' : 'DEACTIVATED'; ?>
                                                </span>
                                            </td>

                                            <td>
                                                <div class="d-flex justify-content-center gap-1">
                                                    <button class="action-btn" data-bs-toggle="modal" data-bs-target="#viewUserModal_<?= $row['user_id']; ?>" title="View Profile">
                                                        <i class="fa-solid fa-expand text-primary"></i>
                                                    </button>

                                                    <?php if ($row['user_id'] != $_SESSION['user_id']): ?>
                                                        
                                                        <?php if ($canEdit): ?>
                                                            <button class="action-btn" data-bs-toggle="modal" data-bs-target="#confirmStatusModal<?= $row['user_id']; ?>" title="Change Status">
                                                                <i class="fa-solid fa-power-off <?= $isActive ? 'text-warning' : 'text-success'; ?>"></i>
                                                            </button>
                                                            <button class="action-btn" data-bs-toggle="modal" data-bs-target="#deleteUserModal<?= $row['user_id']; ?>" title="Move to Archive">
                                                                <i class="fa-solid fa-trash-can text-danger"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <button class="action-btn" style="opacity: 0.4; cursor: not-allowed;" title="Access Restricted" disabled>
                                                                <i class="fa-solid fa-lock text-muted"></i>
                                                            </button>
                                                            <button class="action-btn" style="opacity: 0.4; cursor: not-allowed;" title="Access Restricted" disabled>
                                                                <i class="fa-solid fa-ban text-muted"></i>
                                                            </button>
                                                        <?php endif; ?>

                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="deleted-content">
                    <div class="card main-card border-top border-danger border-4">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-3">Archived User</th>
                                        <th>Original ID</th>
                                        <th>Deleted Date</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($deleted_users)): ?>
                                        <tr><td colspan="4" class="text-center py-4 text-muted small">No archived accounts.</td></tr>
                                    <?php endif; ?>
                                    <?php foreach ($deleted_users as $drow): ?>
                                    <tr>
                                        <td class="ps-3 opacity-75">
                                            <div class="fw-bold text-dark small"><?= htmlspecialchars($drow['first_name'].' '.$drow['last_name']); ?></div>
                                            <div class="text-muted" style="font-size: 0.65rem;"><?= $drow['email']; ?></div>
                                        </td>
                                        <td><span class="text-muted font-monospace small">#<?= $drow['role_id']; ?></span></td>
                                        <td class="small text-danger fw-bold"><?= date("M d, Y", strtotime($drow['deleted_at'])); ?></td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-1">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <button class="action-btn border-success text-success" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#restoreUserModal<?= $drow['user_id']; ?>" 
                                                            title="Restore Account">
                                                        <i class="fa-solid fa-rotate-left"></i>
                                                    </button>
                                                    
                                                    <button class="action-btn border-danger text-danger" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#wipeUserModal<?= $drow['user_id']; ?>" 
                                                            title="Permanently Wipe">
                                                        <i class="fa-solid fa-circle-xmark"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link shadow-sm" href="?page=<?= $page - 1 ?>&limit=<?= $limit ?>">Previous</a>
                    </li>
                    <li class="page-item active"><a class="page-link shadow-sm" href="#"><?= $page ?></a></li>
                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link shadow-sm" href="?page=<?= $page + 1 ?>&limit=<?= $limit ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>

        <script src="../src/script/add_account_script.js"></script>
        <script>
            document.addEventListener('click', function(e) {
                // 1. Toggle Password Visibility
                if (e.target.classList.contains('toggle-password')) {
                    const input = e.target.closest('.position-relative').querySelector('input');
                    if (input.type === "password") {
                        input.type = "text";
                        e.target.classList.replace('fa-eye', 'fa-eye-slash');
                    } else {
                        input.type = "password";
                        e.target.classList.replace('fa-eye-slash', 'fa-eye');
                    }
                }
            });

            document.addEventListener('keyup', function(e) {
                if (e.target.classList.contains('verify-input')) {
                    const typedVal = e.target.value;
                    const modal = e.target.closest('.modal-content');
                    const sessionPass = modal.querySelector('.session-pass-verify').value;
                    const actionBtn = modal.querySelector('.confirm-verify-btn');

                    if (typedVal === sessionPass) {
                        // DIRECT ENABLE
                        actionBtn.disabled = false; 
                        actionBtn.removeAttribute('disabled'); 
                        
                        // Input border turns Green
                        e.target.style.border = "2px solid #198754";
                        e.target.style.boxShadow = "0 0 0 0.25rem rgba(25, 135, 84, 0.25)";
                    } else {
                        // DIRECT DISABLE
                        actionBtn.disabled = true;
                        
                        // Input border turns Red if typing, else Gray
                        if (typedVal.length > 0) {
                            e.target.style.border = "2px solid #dc3545";
                            e.target.style.boxShadow = "0 0 0 0.25rem rgba(220, 53, 69, 0.25)";
                        } else {
                            e.target.style.border = "2px solid #dee2e6";
                            e.target.style.boxShadow = "none";
                        }
                    }
                }
            });
            </script>
    </body>
</html>