<?php
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