<?php
    // =======================================================
    // 💥 SESSION START & SECURITY CHECK 💥
    // =======================================================
    // 1. Start or resume the session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // 2. Check for login status and redirect if not logged in
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: login.php");
        exit(); 
    }
    
    // Enforce editing the logged-in user's profile
    $user_id_to_edit = $_SESSION['user_id'];
    
    // Include necessary files
    include "../src/cdn/cdn_links.php";
    include "../render/connection.php"; // Assuming this defines $conn

    if (!isset($conn) || $conn->connect_error) {
        die("Error: Database connection not established.");
    }

    // Initialize messages
    $message = '';
    $message_type = '';

    // =======================================================
    // 1. HANDLE FORM SUBMISSION (UPDATE)
    // =======================================================
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        // --- Input Retrieval ---
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        // --- Validation ---
        if (empty($first_name) || empty($last_name) || empty($username) || empty($email)) {
            $message = "All fields (except Phone) are required.";
            $message_type = "danger";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Invalid email format.";
            $message_type = "danger";
        } else {
            // --- Database Update ---
            $sql_update = "UPDATE users SET first_name=?, last_name=?, username=?, email=?, phone=? WHERE user_id=?";
            
            $stmt_update = $conn->prepare($sql_update);
            
            if ($stmt_update) {
                // Bind parameters for the 5 text fields + the user ID
                $stmt_update->bind_param("sssssi", $first_name, $last_name, $username, $email, $phone, $user_id_to_edit);
                
                if ($stmt_update->execute()) {
                    // Update session variables if they changed
                    $_SESSION['username'] = $username;
                    
                    // ******************************************************
                    // ✅ FIXED: Redirect to profile.php instead of back to edit_profile.php
                    // ******************************************************
                    header("Location: profile.php?status=update_success"); 
                    exit();
                } else {
                    $message = "Error updating profile: " . $conn->error;
                    $message_type = "danger";
                }
                $stmt_update->close();
            } else {
                $message = "Database statement preparation failed: " . $conn->error;
                $message_type = "danger";
            }
        }
    }

    // =======================================================
    // 2. CHECK FOR REDIRECT STATUS MESSAGE (Only used if the POST fails locally)
    // =======================================================
    // This section is kept to catch error messages from the POST attempt 
    // before the redirect happens (if the DB fails)
    if (isset($_GET['status']) && $_GET['status'] === 'success') {
        $message = "Profile updated successfully!";
        $message_type = "success";
    }

    // =======================================================
    // 3. FETCH CURRENT USER DATA (FOR INITIAL FORM VALUES)
    // =======================================================
    $user_data = null; 
    $sql_fetch = "SELECT first_name, last_name, username, email, phone 
                  FROM users 
                  WHERE user_id = ?";
    
    $stmt_fetch = $conn->prepare($sql_fetch);
    if ($stmt_fetch) {
        $stmt_fetch->bind_param("i", $user_id_to_edit);
        $stmt_fetch->execute();
        $result = $stmt_fetch->get_result();
        $user_data = $result->fetch_assoc();
        $stmt_fetch->close();
    }

    if (!$user_data) {
        // If user data cannot be fetched, show an error
        die("Error: User profile data not found.");
    }
?> 

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Edit Profile - <?php echo htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']); ?></title>
        <style>
            /* Fix for fixed-top Navbar */
            body { padding-top: 56px; } 
        </style>
    </head>
    <body class="bg-light">

        <?php include "../nav/header.php"; // Include your header/navbar ?> 

        <div class="container-fluid mt-5">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-6">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h3 class="mb-0"><i class="fa-solid fa-user-edit me-2"></i> Edit Your Profile Information</h3>
                        </div>
                        <div class="card-body">
                            
                            <?php if (!empty($message)): // Display alert messages ?>
                            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php endif; ?>

                            <form method="POST" action="edit_profile.php">
                                <input type="hidden" name="user_id" value="<?php echo $user_id_to_edit; ?>">

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" 
                                               value="<?php echo htmlspecialchars($user_data['first_name']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" 
                                               value="<?php echo htmlspecialchars($user_data['last_name']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?php echo htmlspecialchars($user_data['username']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                                </div>

                                <div class="mb-4">
                                    <label for="phone" class="form-label">Phone (Optional)</label>
                                    <input type="text" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($user_data['phone']); ?>">
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="profile.php" class="btn btn-secondary me-md-2"><i class="fa-solid fa-times me-2"></i> Cancel</a>
                                    <button type="submit" class="btn btn-success"><i class="fa-solid fa-save me-2"></i> Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    <?php 
        // Close the connection AFTER all DB operations are complete.
        if (isset($conn)) {
            $conn->close();
        }
    ?>
</html>