<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    include "../render/connection.php"; 
    include "../src/cdn/cdn_links.php";

    $error_message = "";

    if (!isset($conn) || $conn->connect_error) {
        die("Database connection error. Please check configuration.");
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_submit'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        if (empty($username) || empty($password)) {
            $error_message = "Please enter both username and password.";
        } else {
            $sql = "SELECT user_id, password, role, status FROM users WHERE username = ?";
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();
                    
                    if (password_verify($password, $user['password'])) {
                        if (strtolower($user['status']) !== 'active') {
                            $error_message = "Your account is deactivated. Please contact an administrator.";
                        } else {
                            $_SESSION['logged_in'] = true;
                            $_SESSION['user_id'] = $user['user_id'];
                            $_SESSION['username'] = $username;
                            $_SESSION['password'] = $password;
                            $_SESSION['user_type'] = $user['role']; 
                            
                            $sql_update_login = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
                            $stmt_update = $conn->prepare($sql_update_login);
                            $stmt_update->bind_param("i", $user['user_id']);
                            $stmt_update->execute();
                            $stmt_update->close();
                            
                            $redirect = ($_SESSION['user_type'] == 'Viewer') ? 'inventory.php' : 'dashboard.php';
                            header("Location: " . $redirect);
                            exit();
                        }
                    } else {
                        $error_message = "Invalid username or password.";
                    }
                } else {
                    $error_message = "Invalid username or password.";
                }
                $stmt->close();
            }
        }
    }
    $conn->close(); 
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Secure Access Portal</title>
    <style>
        :root {
            --primary-blue: #004d99;
            --dark-blue: #003366;
        }
        body { 
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }
        .login-card {
            max-width: 420px;
            width: 90%;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.8);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .logo-img {
            width: 140px;
            margin-bottom: 1.5rem;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.05));
        }
        .form-floating > .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.25rem rgba(0, 77, 153, 0.1);
        }
        .btn-login {
            background-color: var(--primary-blue);
            border: none;
            padding: 12px;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border-radius: 10px;
        }
        .btn-login:hover {
            background-color: var(--dark-blue);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 77, 153, 0.2);
        }
        .input-group-text {
            background: none;
            border-right: none;
            color: #adb5bd;
        }
        .form-control {
            border-left: none;
        }
        .login-footer {
            font-size: 0.85rem;
            color: #6c757d;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">

    <div class="card login-card p-4">
        <div class="card-body">
            <div class="text-center">
                <img src="../src/image/logo/varay_logo.png" alt="Logo" class="logo-img">
                <h4 class="fw-bold text-dark mb-1">Welcome Back</h4>
                <p class="text-muted small mb-4">Please enter your credentials to continue</p>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger border-0 small shadow-sm d-flex align-items-center" role="alert">
                    <i class="fa-solid fa-circle-exclamation me-2"></i>
                    <div><?= $error_message; ?></div>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="login_submit" value="1"> 
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase text-muted">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                        <input type="text" class="form-control" name="username" placeholder="Enter username" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-uppercase text-muted">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" class="form-control" name="password" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="d-grid mb-3">
                    <button class="btn btn-primary btn-login text-white" type="submit">
                        SECURE SIGN IN <i class="fa-solid fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </form>

            <div class="text-center mt-4 pt-3 border-top login-footer">
                &copy; <?= date("Y"); ?> Stock Focus System. All Rights Reserved.
            </div>
        </div>
    </div>

</body>
</html>