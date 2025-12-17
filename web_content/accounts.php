<?php
    include "../src/cdn/cdn_links.php";
    include "../render/connection.php";
    include "../render/modal.php";

    // Assume $pdo is the PDO connection object available from connection.php

    // Function to determine the badge class based on status or role
    function getStatusBadge($status) {
        switch (strtolower($status)) {
            case 'active':
                return 'text-bg-success';
            case 'inactive':
                return 'text-bg-secondary';
            case 'suspended':
                return 'text-bg-danger';
            default:
                return 'text-bg-info';
        }
    }

    // --- Database Query ---
    $sql = "SELECT 
                role_id, 
                first_name, 
                last_name, 
                username, 
                email, 
                phone,
                role, 
                status, 
                last_login,
                profile_image  -- <--- **FIX 1: ADDED PROFILE IMAGE PATH**
            FROM users 
            ORDER BY user_id DESC";
            
    // Check if $pdo exists and is an object before using it (good practice)
    if (isset($pdo) && is_object($pdo)) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(); // Fetch all results into an array
    } else {
        // Fallback if connection fails
        $users = [];
        // You might want to log this error or display a message
        // die("Database connection not available or \$pdo is not set.");
    }
?> 

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Accounts Management</title>
    
    <style>
        /* Fix for fixed-top Navbar */
        body {
            padding-top: 56px;
        }
        /* Style for actionable buttons in rows */
        .table-action-btns {
            min-width: 150px;
        }
        /* Style for the profile picture */
        .profile-picture-modal {
            width: 100px; 
            height: 100px; 
            border-radius: 50%;
            object-fit: cover; /* Ensures image covers the area without distortion */
            border: 3px solid #0d6efd; /* Highlight border */
        }
    </style>
</head>
<body class="bg-light <?php echo $body_class; ?>">

    <?php include "../nav/header.php"; ?> 

    <div class="container-fluid mt-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="fw-light text-dark"><i class="fa-solid fa-users-gear me-2"></i> User Accounts & Permissions</h1>
            <button class="btn btn-primary btn-lg shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fa-solid fa-user-plus me-2"></i> Add New User
            </button>
        </div>
        <hr>

        <div class="card shadow mb-5">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold text-muted">Showing <?php echo count($users); ?> Users</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Role ID</th>
                                <th scope="col">Name</th>
                                <th scope="col">Username</th>
                                <th scope="col">Email</th>
                                <th scope="col">Role</th>
                                <th scope="col">Status</th>
                                <th scope="col">Last Login</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $row): ?>
                                <tr>
                                    <td><?= $row['role_id']; ?></td>
                                    <td><?= $row['first_name'] . ' ' . $row['last_name']; ?></td>
                                    <td><?= $row['username']; ?></td>
                                    <td><a href="mailto:<?= htmlspecialchars($row['email']); ?>"><?= htmlspecialchars($row['email']); ?></a></td>

                                    <td>
                                        <?php
                                            $role = strtolower($row['role']);
                                            $badge_class = 'bg-secondary'; 
                                            // ... (Role Badge logic remains the same) ...
                                            switch ($role) {
                                                case 'administrator':
                                                    $badge_class = 'bg-danger'; 
                                                    break;
                                                case 'inventory manager':
                                                    $badge_class = 'bg-primary'; 
                                                    break;
                                                case 'stock handler':
                                                    $badge_class = 'bg-success'; 
                                                    break;
                                                case 'viewer':
                                                    $badge_class = 'bg-info text-dark'; 
                                                    break;
                                                default:
                                                    $badge_class = 'bg-light text-dark'; 
                                                    break;
                                            }
                                        ?>
                                        <span class="badge <?= $badge_class; ?>">
                                            <?= strtoupper(htmlspecialchars($row['role'])); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?php
                                            $status_class = 'bg-secondary';
                                            if ($row['status'] == 'active') {
                                                $status_class = 'bg-success';
                                            } elseif ($row['status'] == 'inactive') {
                                                $status_class = 'bg-warning text-dark';
                                            }
                                        ?>
                                        <span class="badge <?= $status_class; ?>">
                                            <?= htmlspecialchars($row['status']); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <span class="<?= empty($row['last_login']) ? 'text-danger' : 'text-muted'; ?>">
                                            <?= empty($row['last_login']) ? "Never" : $row['last_login']; ?>
                                        </span>
                                    </td>

                                    <td>
                                        <button class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#viewUserModal_<?= $row['role_id']; ?>"
                                            title="View Details">
                                            <i class="fa-solid fa-eye"></i> View
                                        </button>
                                        <?php
                                            if($row['status'] == "active") {
                                                ?>
                                                <button class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-circle"></i> In-Active</button>
                                                <?php
                                            } else {
                                                ?>
                                                <button class="btn btn-sm btn-outline-success"><i class="fa-solid fa-circle"></i> Active</button>
                                                <?php
                                            }

                                            if($row['role'] == "Administrator") {
                                                ?>
                                                <button class="btn btn-sm btn-outline-danger" disabled><i class="fa-regular fa-trash-can"></i> Delete</button>
                                                <?php
                                            } else {
                                                ?>
                                                <button class="btn btn-sm btn-outline-danger"><i class="fa-regular fa-trash-can"></i> Delete</button>
                                                <?php
                                            }
                                        ?>
                                    </td>
                                </tr>

                                <div class="modal fade" id="viewUserModal_<?= $row['role_id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">

                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title">
                                                    <i class="bi bi-person-circle me-2"></i> User Details — <?= htmlspecialchars($row['first_name']); ?>
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body">
                                                <div class="text-center mb-4">
                                                    
                                                    <?php 
                                                        $profile_image_path = htmlspecialchars($row['profile_image']);
                                                        // Fallback path if the database value is empty, null, or 'default.png'
                                                        $default_image = '../src/image/default_profile.png'; // <-- Adjust this if your default image is elsewhere
                                                        
                                                        // Determine the final image source
                                                        $image_src = (!empty($profile_image_path) && $profile_image_path !== 'src/image/profile_picture/') ? $profile_image_path : $default_image;
                                                    ?>

                                                    <img src="<?= $image_src; ?>" 
                                                         alt="<?= htmlspecialchars($row['username']); ?>'s Profile Picture" 
                                                         class="profile-picture-modal mx-auto d-block">

                                                    <h4 class="mt-2"><?= htmlspecialchars($row['first_name'].' '.$row['last_name']); ?></h4>
                                                    <span class="text-muted"><?= htmlspecialchars($row['username']); ?></span>
                                                </div>

                                                <ul class="list-group list-group-flush">
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <strong>Role ID:</strong>
                                                        <span>#<?= $row['role_id']; ?></span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <strong>Email:</strong>
                                                        <span><?= htmlspecialchars($row['email']); ?></span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <strong>Phone:</strong>
                                                        <span><?= htmlspecialchars($row['phone']); ?></span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <strong>Status:</strong>
                                                        <span class="badge <?= $status_class; ?>">
                                                            <?= htmlspecialchars($row['status']); ?>
                                                        </span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <strong>Role:</strong>
                                                        <span class="badge <?= $badge_class; ?>">
                                                            <?= strtoupper(htmlspecialchars($row['role'])); ?>
                                                        </span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <strong>Last Login:</strong>
                                                        <span class="<?= empty($row['last_login']) ? 'text-danger' : 'text-success'; ?>">
                                                            <?= empty($row['last_login']) ? "Never" : $row['last_login']; ?>
                                                        </span>
                                                    </li>
                                                </ul>
                                            </div>

                                            <div class="modal-footer">
                                                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
</body>
</html>