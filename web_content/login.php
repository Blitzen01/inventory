<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    include "../render/connection.php"; 
    include "../src/cdn/cdn_links.php";

    $error_message = "";

    if (!isset($conn) || $conn->connect_error) {
        $error_message = "Database connection error. Please check configuration.";
        die($error_message);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_submit'])) {
        
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        if (empty($username) || empty($password)) {
            $error_message = "<div class='alert alert-warning'>Please enter both username and password.</div>";
        } else {
            $sql = "SELECT user_id, password, role FROM users WHERE username = ?";
            
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                $error_message = "<div class='alert alert-danger'>Database query error.</div>";
            } else {
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();
                    $hashed_password = $user['password'];
                    
                    // Verify the password hash
                    if (password_verify($password, $hashed_password)) {
                        $_SESSION['logged_in'] = true;
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['username'] = $username;
                        $_SESSION['user_type'] = $user['role']; 
                        
                        $sql_update_login = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
                        $stmt_update = $conn->prepare($sql_update_login);
                        $stmt_update->bind_param("i", $user['user_id']);
                        $stmt_update->execute();
                        $stmt_update->close();
                        
                        if($_SESSION['user_type'] == 'viewer') {
                            header("Location: inventory.php");
                        } else {
                            header("Location: dashboard.php");
                        }
                        exit();
                        
                    } else {
                        $error_message = "<div class='alert alert-danger'>Invalid username or password.</div>";
                    }
                } else {
                    $error_message = "<div class='alert alert-danger'>Invalid username or password.</div>";
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
        <title>Secure Access Portal</title>
        <?php include "../src/cdn/cdn_links.php" ?>

        <style>
            /* Custom styles for professional aesthetic */
            .login-card {
                max-width: 450px;
                width: 100%;
                border-radius: 1rem;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
                border: none;
            }
            .btn-primary {
                background-color: #004d99; /* Deep Professional Blue */
                border-color: #004d99;
                transition: background-color 0.3s ease;
            }
            .btn-primary:hover {
                background-color: #003366;
                border-color: #003366;
            }
        </style>
    </head>
    
    <body class="bg-light d-flex align-items-center justify-content-center vh-100">

        <div class="card login-card p-4 p-md-5">
            <div class="card-body">
                <div class="text-center mb-2">
                    <img src="../src/image/logo/mventory_logo_no_bg.png" alt="" srcset="" style="width: 10rem;">
                </div>
                <h2 class="h3 fw-bold text-center mb-4">Login</h2>

                <?php if (!empty($error_message)) echo $error_message; ?>

                <form method="POST">
                    <input type="hidden" name="login_submit" value="1"> 
                    
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="floatingInput" name="username" placeholder="Username" required>
                        <label for="floatingInput">Username</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="password" class="form-control" id="floatingPassword" name="password" placeholder="Password" required>
                        <label for="floatingPassword">Password</label>
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-lg btn-primary fw-semibold" type="submit">Secure Sign In</button>
                    </div>
                </form>
            </div>
        </div>
    </body>
</html>