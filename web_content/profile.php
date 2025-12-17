<?php
    // 1. Start or resume the session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // 2. Check for login status and redirect if not logged in
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: login.php");
        exit();
    }

    // Get the ID of the logged-in user
    $current_user_id = $_SESSION['user_id'];

    // Include necessary files
    include "../src/cdn/cdn_links.php";
    include "../render/connection.php"; // Assuming this defines $conn

    // Safety check for connection
    if (!isset($conn) || $conn->connect_error) {
        die("Error: Database connection not established.");
    }

    // --- HELPER FUNCTIONS ---

    // Function to format the timestamp into a readable "time ago" string
    function time_ago($timestamp)
    {
        $time_difference = time() - strtotime($timestamp);
        if ($time_difference < 60) return max(1, $time_difference) . ' seconds ago';
        if ($time_difference < 3600) return round($time_difference / 60) . ' minutes ago';
        if ($time_difference < 86400) return round($time_difference / 3600) . ' hours ago';
        return date('M j, Y', strtotime($timestamp)); // Fallback to date
    }

    // --- 1. FETCH USER PROFILE DATA ---
    $user_data = null;
    // MODIFIED: Added profile_image to the SELECT statement
    $sql_profile = "SELECT user_id, first_name, last_name, username, email, phone, role, status, created_at, last_login, profile_image 
    FROM users 
    WHERE user_id = ?";

    $stmt = $conn->prepare($sql_profile);
    if ($stmt) {
        $stmt->bind_param("i", $current_user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        $stmt->close();
    }

    if (!$user_data) {
        // If user data cannot be fetched, redirect or show an error
        die("Error: User profile not found.");
    }

    // Assign variables for cleaner HTML rendering
    $full_name = htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']);
    $username = htmlspecialchars($user_data['username']);
    $role = htmlspecialchars(ucwords($user_data['role']));
    $status = htmlspecialchars(ucwords($user_data['status']));
    $email = htmlspecialchars($user_data['email']);
    $phone = htmlspecialchars($user_data['phone']);
    $member_since = date('F j, Y', strtotime($user_data['created_at']));
    $last_login_display = $user_data['last_login'] ? date('F j, Y, g:i A', strtotime($user_data['last_login'])) : 'Never logged in';

    // NEW: Check if the user has a profile image uploaded
    // Use the actual path if available, otherwise, it remains null/empty to trigger the icon fallback
    $profile_image_path = htmlspecialchars($user_data['profile_image'] ?? '');
    $has_profile_image = !empty($user_data['profile_image']);

    // Define the default image path for the MODAL (if you must display a picture in the modal when none is set)
    // NOTE: For the main display, we use the icon (see HTML below)
    $default_image_for_modal = '../src/images/default_avatar.png'; // Fallback image for modal only
    $modal_image_path = $has_profile_image ? $profile_image_path : $default_image_for_modal;

    // Determine status badge class
    $status_class = ($user_data['status'] === 'active') ? 'text-bg-success' : 'text-bg-warning';
    $role_class = ($user_data['role'] === 'administrator') ? 'text-bg-danger' : 'text-bg-primary';

    // --- 2. FETCH RECENT USER ACTIVITY ---
    $recent_activity = null;
    // ... rest of activity fetch code remains the same ...
    $sql_activity = "SELECT il.timestamp, il.action_type, il.quantity_change, p.product_name
    FROM inventory_log il
    LEFT JOIN products p ON il.product_id = p.product_id
    WHERE il.user_id = ?
    ORDER BY il.timestamp DESC
    LIMIT 5";
    $stmt_activity = $conn->prepare($sql_activity);
    if ($stmt_activity) {
        $stmt_activity->bind_param("i", $current_user_id);
        $stmt_activity->execute();
        $recent_activity = $stmt_activity->get_result();
        $stmt_activity->close();
    }
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo $full_name; ?> Profile - AppName</title>
        <style>
            body {
                padding-top: 56px;
            }

            .profile-avatar {
                width: 150px;
                height: 150px;
                object-fit: cover;
                border: 5px solid #fff;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }

            /* Style for the Modal image */
            .modal-profile-img {
                max-width: 100%;
                max-height: 300px;
                object-fit: cover;
            }
            
            /* FIX: Ensure the default icon container is styled properly */
            .default-avatar-container {
                /* Inherit size and appearance from profile-avatar, but add icon-specific styling */
                background-color: #e9ecef; /* Lighter gray background */
                color: #adb5bd; /* Medium gray icon color */
                display: flex; /* For centering the icon inside */
                align-items: center;
                justify-content: center;
            }

            .default-avatar-container i {
                /* Set a large size for the icon itself */
                font-size: 80px; 
            }
        </style>

    <body class="bg-light">
        <?php include "../nav/header.php"; ?>
        <div class="profile-header mt-3 mb-4">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-auto text-center">
                        <button type="button" class="btn p-0 border-0"
                            data-bs-toggle="modal"
                            data-bs-target="#profilePictureModal"
                            aria-label="View and Change Profile Picture">
                            
                            <?php if ($has_profile_image): ?>
                                <img src="<?php echo $profile_image_path; ?>"
                                    alt="<?php echo $username; ?> Profile"
                                    class="profile-avatar rounded-circle">
                            <?php else: ?>
                                <div class="profile-avatar rounded-circle default-avatar-container">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                            <?php endif; ?>
                            
                        </button>
                    </div>
                    <div class="col-md">
                        <?php if (isset($_GET['status']) && $_GET['status'] === 'update_success'): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                Profile updated successfully!
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        <h1 class="display-5 fw-bold text-dark mb-0"><?php echo $full_name; ?></h1>
                        <p class="lead text-muted"><?php echo $role; ?> | User ID: <?php echo $current_user_id; ?></p>
                    </div>
                    <div class="col-md-auto">
                        <a href="edit_profile.php" class="btn btn-primary btn-lg">
                            <i class="fa-solid fa-pen me-2"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fa-solid fa-info-circle me-2"></i> Contact and Personal Information</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <strong><i class="fa-solid fa-envelope fa-fw me-2"></i> Email:</strong> <?php echo $email; ?>
                                </li>
                                <li class="list-group-item">
                                    <strong><i class="fa-solid fa-user fa-fw me-2"></i> Username:</strong> <?php echo $username; ?>
                                </li>
                                <li class="list-group-item">
                                    <strong><i class="fa-solid fa-phone fa-fw me-2"></i> Phone:</strong> <?php echo $phone; ?>
                                </li>
                                <li class="list-group-item">
                                    <strong><i class="fa-solid fa-calendar-alt fa-fw me-2"></i> Member Since:</strong> <?php echo $member_since; ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="fa-solid fa-lock me-2"></i> Security and Access</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fa-solid fa-shield-alt fa-fw me-2"></i> Role:</span>
                                    <span class="badge fs-6 <?php echo $role_class; ?>"><?php echo $role; ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fa-solid fa-user-check fa-fw me-2"></i> Status:</span>
                                    <span class="badge fs-6 <?php echo $status_class; ?>"><?php echo $status; ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fa-solid fa-history fa-fw me-2"></i> Last Login:</span>
                                    <span><?php echo $last_login_display; ?></span>
                                </li>
                                <li class="list-group-item">
                                    <button class="btn btn-warning w-100 mt-2"><i class="fa-solid fa-key me-2"></i> Change Password</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="fa-solid fa-list-alt me-2"></i> Recent System Activity</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <?php
                                if ($recent_activity && $recent_activity->num_rows > 0) {
                                    while ($activity = $recent_activity->fetch_assoc()) {

                                        $icon = 'fa-solid fa-question-circle';
                                        $text_color = 'text-secondary';
                                        $product_name = htmlspecialchars($activity['product_name'] ?? 'N/A');
                                        $details = '';
                                        switch ($activity['action_type']) {
                                            case 'ADD':
                                                $icon = 'fa-solid fa-circle-plus';
                                                $text_color = 'text-success';
                                                $details = "Received **{$activity['quantity_change']}** units of **{$product_name}**.";
                                                break;
                                            case 'REMOVE':
                                                $icon = 'fa-solid fa-circle-minus';
                                                $text_color = 'text-danger';
                                                $details = "Deducted **{$activity['quantity_change']}** units of **{$product_name}**.";
                                                break;
                                            case 'UPDATE':
                                                $icon = 'fa-solid fa-check-circle';
                                                $text_color = 'text-info';
                                                $details = "Updated details for **{$product_name}**.";
                                                break;
                                            default:
                                                $details = "Performed action: " . htmlspecialchars($activity['action_type']);
                                        }
                                        echo '<li class="list-group-item">';
                                        echo "  <span class='{$text_color}'><i class='{$icon} me-2'></i></span> ";
                                        echo $details;
                                        echo '  <small class="text-muted float-end" title="' . htmlspecialchars($activity['timestamp']) . '">' . time_ago($activity['timestamp']) . '</small>';
                                        echo '</li>';
                                    }
                                } else {
                                    echo '<li class="list-group-item text-center text-muted">No recent system activity logged for this user.</li>';
                                }
                                ?>
                                <li class="list-group-item text-center">
                                    <a href="activity_log.php?user_id=<?php echo $current_user_id; ?>" class="text-decoration-none">View Full Audit Log <i class="fa-solid fa-chevron-right ms-1"></i></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>


<!-- profile picture modal start -->
<div class="modal fade" id="profilePictureModal" tabindex="-1" aria-labelledby="profilePictureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered modal-dialog-scrollable"> 
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="profilePictureModalLabel">Profile Picture Options</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Form for uploading image -->
            <form action="../src/php_script/profile_picture_upload.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body text-center">

                    <?php if ($has_profile_image): ?>
                        <img src="<?php echo $modal_image_path; ?>"
                            alt="Current Profile Picture"
                            class="modal-profile-img rounded-circle mb-3 border border-5 shadow-sm mx-auto"
                            style="width: 180px; height: 180px; object-fit: cover;">
                    <?php else: ?>
                        <div class="rounded-circle mb-3 border border-5 shadow-sm mx-auto"
                            style="width: 180px; height: 180px; background-color: #ced4da; color: #6c757d; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-user" style="font-size: 100px;"></i>
                        </div>
                    <?php endif; ?>

                    <p class="text-muted small">Current Image</p>

                    <!-- FILE INPUT -->
                    <input type="file" name="profile_image" accept="image/*" class="form-control mt-2" required>

                    <!-- Upload button -->
                    <button type="submit" class="btn btn-warning w-100 mt-3">
                        <i class="fa-solid fa-camera me-2"></i> Upload New Picture
                    </button>

                    <!-- Delete button -->
                    <button type="button"
                        class="btn btn-outline-danger btn-sm w-100 mt-2"
                        <?php echo $has_profile_image ? '' : 'disabled'; ?>
                        onclick="alert('Add delete functionality in your backend script.')">
                        <i class="fa-solid fa-trash-alt me-1"></i> Remove Picture
                    </button>
                    
                </div>
            </form>
        </div>
    </div>
</div>
<!-- profile picture modal end -->