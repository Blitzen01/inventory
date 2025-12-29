<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: login.php");
        exit(); 
    }
    
    $user_id_to_edit = $_SESSION['user_id'];
    include "../src/cdn/cdn_links.php";
    include "../render/connection.php"; 
    include "../src/fetch/edit_profile_querry.php";
?> 

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Profile | Stock Focus</title>
    <style>
        body { background-color: #f4f7f6; padding-top: 80px; }
        .card { border: none; border-radius: 15px; }
        .form-label { font-size: 12px; font-weight: 700; text-transform: uppercase; color: #666; letter-spacing: 0.5px; }
        .form-control { border-radius: 8px; padding: 10px 12px; border: 1px solid #ddd; }
        .form-control:focus { box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15); border-color: #0d6efd; }
        .input-group-text { background-color: #f8f9fa; border-radius: 8px 0 0 8px; color: #adb5bd; }
        .btn { border-radius: 8px; padding: 10px 20px; font-weight: 600; }
    </style>
</head>
<body>

<?php include "../nav/header.php"; ?> 

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="mb-4">
                <a href="profile.php" class="text-decoration-none small fw-bold text-muted">
                    <i class="fa-solid fa-arrow-left me-1"></i> BACK TO PROFILE
                </a>
                <h3 class="fw-bold text-dark mt-2">Edit Account Information</h3>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4 p-md-5">
                    
                    <?php if (!empty($message)): ?>
                    <div class="alert alert-<?= $message_type; ?> alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i> <?= $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="edit_profile.php">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($user_data['first_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($user_data['last_name']); ?>" required>
                            </div>

                            <div class="col-12 mt-3">
                                <label class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-at"></i></span>
                                    <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($user_data['username']); ?>" required>
                                </div>
                            </div>

                            <div class="col-12 mt-3">
                                <label class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user_data['email']); ?>" required>
                                </div>
                            </div>

                            <div class="col-12 mt-3 mb-4">
                                <label class="form-label">Phone Number (Optional)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                                    <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($user_data['phone']); ?>">
                                </div>
                            </div>

                            <hr class="text-muted opacity-25">

                            <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                                <a href="profile.php" class="btn btn-light px-4">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                    <i class="fa-solid fa-check me-2"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <p class="text-center text-muted small mt-4">
                To update your password or profile picture, please use the security options on the <a href="profile.php" class="text-decoration-none">main profile page</a>.
            </p>
        </div>
    </div>
</div>

</body>
<?php if (isset($conn)) { $conn->close(); } ?>
</html>