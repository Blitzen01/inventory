<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: login.php");
        exit();
    }

    $current_user_id = $_SESSION['user_id'];
    include "../src/cdn/cdn_links.php";
    include "../render/connection.php"; 

    if (!isset($conn) || $conn->connect_error) {
        die("Error: Database connection not established.");
    }

    function time_ago($timestamp) {
        $time_difference = time() - strtotime($timestamp);
        if ($time_difference < 60) return max(1, $time_difference) . ' secs ago';
        if ($time_difference < 3600) return round($time_difference / 60) . ' mins ago';
        if ($time_difference < 8400) return round($time_difference / 3600) . ' hrs ago';
        return date('M j, Y', strtotime($timestamp));
    }

    $sql_profile = "SELECT first_name, last_name, username, email, phone, role, status, created_at, last_login, profile_image FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql_profile);
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $user_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user_data) die("Error: User profile not found.");

    $full_name = htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']);
    $has_profile_image = !empty($user_data['profile_image']);
    $profile_image_path = htmlspecialchars($user_data['profile_image'] ?? '');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $full_name; ?> | Profile</title>
    <style>
        body { background-color: #f4f7f6; padding-top: 80px; }
        .profile-card { border: none; border-radius: 15px; overflow: hidden; }
        .profile-avatar-container { position: relative; width: 140px; height: 140px; margin: 0 auto; transition: transform 0.3s ease; }
        .profile-avatar-container:hover { transform: scale(1.05); }
        .profile-avatar { width: 100%; height: 100%; object-fit: cover; border: 4px solid #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .default-avatar { width: 100%; height: 100%; background: #e9ecef; color: #adb5bd; display: flex; align-items: center; justify-content: center; font-size: 60px; border: 4px solid #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .edit-overlay { position: absolute; bottom: 5px; right: 5px; background: #0d6efd; color: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid #fff; cursor: pointer; }
        .info-label { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #888; font-weight: 700; margin-bottom: 2px; }
        .info-value { font-size: 15px; color: #333; font-weight: 500; }
        .card-header { background: #fff !important; border-bottom: 1px solid #eee !important; color: #333 !important; font-weight: 700; }
    </style>
</head>
<body>

<?php include "../nav/header.php"; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-4">
            <div class="card profile-card shadow-sm text-center p-4 mb-4">
                <div class="profile-avatar-container mb-3" data-bs-toggle="modal" data-bs-target="#profilePictureModal" style="cursor: pointer;">
                    <?php if ($has_profile_image): ?>
                        <img src="<?= $profile_image_path; ?>" class="profile-avatar rounded-circle">
                    <?php else: ?>
                        <div class="default-avatar rounded-circle"><i class="fa-solid fa-user"></i></div>
                    <?php endif; ?>
                    <div class="edit-overlay"><i class="fa-solid fa-camera fa-xs"></i></div>
                </div>
                <h4 class="fw-bold mb-1"><?= $full_name; ?></h4>
                <p class="text-muted small mb-3">@<?= htmlspecialchars($user_data['username']); ?></p>
                <div class="d-flex justify-content-center gap-2 mb-3">
                    <span class="badge bg-primary px-3 py-2 rounded-pill"><?= ucwords($user_data['role']); ?></span>
                    <span class="badge bg-success px-3 py-2 rounded-pill"><?= ucwords($user_data['status']); ?></span>
                </div>
                <hr>
                <div class="text-start">
                    <div class="mb-3">
                        <div class="info-label">Member Since</div>
                        <div class="info-value"><?= date('F j, Y', strtotime($user_data['created_at'])); ?></div>
                    </div>
                    <div class="mb-0">
                        <div class="info-label">Last Activity</div>
                        <div class="info-value"><?= $user_data['last_login'] ? time_ago($user_data['last_login']) : 'N/A'; ?></div>
                    </div>
                </div>
            </div>

            <button class="btn btn-dark w-100 rounded-pill mb-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                <i class="fa-solid fa-key me-2"></i> Security Settings
            </button>
        </div>

        <div class="col-lg-8">
            <div class="card profile-card shadow-sm mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0"><i class="fa-solid fa-id-card me-2 text-primary"></i> Account Details</h5>
                    <a href="edit_profile.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">Edit Profile</a>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <div class="info-label">Email Address</div>
                            <div class="info-value"><?= htmlspecialchars($user_data['email']); ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">Phone Number</div>
                            <div class="info-value"><?= htmlspecialchars($user_data['phone'] ?: 'No phone provided'); ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">First Name</div>
                            <div class="info-value"><?= htmlspecialchars($user_data['first_name']); ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label">Last Name</div>
                            <div class="info-value"><?= htmlspecialchars($user_data['last_name']); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card profile-card shadow-sm <?= ($_SESSION['user_type'] == 'Viewer') ? 'd-none' : '' ?>">
                <div class="card-header py-3">
                    <h5 class="m-0"><i class="fa-solid fa-bolt me-2 text-warning"></i> Recent Activity</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php
                        $sql_activity = "SELECT il.timestamp, il.action_type, il.quantity_change, p.product_name 
                                        FROM inventory_log il LEFT JOIN products p ON il.product_id = p.product_id 
                                        WHERE il.user_id = ? ORDER BY il.timestamp DESC LIMIT 5";
                        $stmt_act = $conn->prepare($sql_activity);
                        $stmt_act->bind_param("i", $current_user_id);
                        $stmt_act->execute();
                        $recent_activity = $stmt_act->get_result();

                        if ($recent_activity->num_rows > 0):
                            while ($act = $recent_activity->fetch_assoc()):
                                $color = 'text-secondary';
                                if($act['action_type'] == 'ADD') $color = 'text-success';
                                if($act['action_type'] == 'REMOVE') $color = 'text-danger';
                        ?>
                        <li class="list-group-item py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="<?= $color ?> fw-bold me-2"><?= $act['action_type'] ?></span>
                                    <span class="text-dark small"><?= htmlspecialchars($act['product_name'] ?? 'Unknown Item') ?> (<?= $act['quantity_change'] ?>)</span>
                                </div>
                                <span class="text-muted small"><?= time_ago($act['timestamp']) ?></span>
                            </div>
                        </li>
                        <?php endwhile; else: ?>
                            <li class="list-group-item text-center py-4 text-muted small">No recent activity logged.</li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="card-footer bg-white text-center border-0 py-3">
                    <a href="activity_log.php?user_id=<?= $current_user_id ?>" class="small fw-bold text-decoration-none">View Full Audit Trail <i class="fa-solid fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-lock me-2 text-dark"></i> Update Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../src/php_script/change_password.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">New Password</label>
                        <input type="password" name="new_password" class="form-control" required minlength="8">
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-4">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="profilePictureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Update Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../src/php_script/upload_profile_img.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body text-center">
                    <input type="file" name="profile_image" class="form-control mb-3" accept="image/*" required>
                    <p class="text-muted small">Square images (1:1) work best.</p>
                </div>
                <div class="modal-footer bg-light justify-content-center">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 w-100">Upload Image</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>