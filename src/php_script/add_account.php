<?php

include "../../render/connection.php";

// Assuming $pdo object is correctly initialized in connection.php
// If not, you must initialize your database connection here.


// ----------------------------------------------------------------------
// 2. Form Submission and Validation
// ----------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize and collect user input
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name  = htmlspecialchars(trim($_POST['last_name']));
    $username   = htmlspecialchars(trim($_POST['username'])); // 👈 ADDED USERNAME COLLECTION
    $email      = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $role       = htmlspecialchars(trim($_POST['role']));
    $phone      = htmlspecialchars(trim($_POST['phone'] ?? '')); // Optional field
    $password   = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic validation (You should add more robust server-side validation here)
    if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($role) || empty($password) || empty($confirm_password)) {
        // Handle error: missing required fields. Added $username check.
        die("Error: Please fill in all required fields, including the Username.");
    }

    if ($password !== $confirm_password) {
        // Handle error: passwords do not match
        die("Error: Passwords do not match.");
    }
    
    // Hash the password securely
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);


    // ----------------------------------------------------------------------
    // 3. Unique Role ID Generation Logic
    // ----------------------------------------------------------------------

    /**
     * Extracts the first three consonants from a string.
     * @param string $role The role name (e.g., 'Inventory Manager').
     * @return string The 3-consonant code (e.g., 'NVN').
     */
    function getRoleConsonants($role) {
        // Remove vowels (A, E, I, O, U, case-insensitive)
        $consonants_only = preg_replace('/[aeiou\s]/i', '', $role);
        
        // Take the first 3 characters and convert to uppercase
        $code = strtoupper(substr($consonants_only, 0, 3));

        // If the role has fewer than 3 consonants, pad with a fallback character (like X)
        while (strlen($code) < 3) {
            $code .= 'X';
        }
        
        return $code;
    }

    // A. Generate Role Consonant Code (e.g., 'DMN' from 'Administrator')
    $role_code = getRoleConsonants($role); 

    // B. Get Date Components (e.g., '12' for month, '2025' for year)
    $current_month = date('m');
    $current_year  = date('Y');

    // C. Get the Next User ID Sequence (001, 002, 003, etc.)
    // NOTE: This assumes $pdo is available globally from connection.php
    $stmt_last_id = $pdo->query("SELECT user_id FROM users ORDER BY user_id DESC LIMIT 1");
    $last_user = $stmt_last_id->fetch();

    if ($last_user) {
        // If data exists, increment the last ID
        $next_sequence_number = $last_user['user_id'] + 1;
    } else {
        // If table is empty, start at 1
        $next_sequence_number = 1;
    }

    // Format the number to be 3 digits (e.g., 1 -> 001, 12 -> 012)
    $sequence_part = str_pad($next_sequence_number, 3, '0', STR_PAD_LEFT);

    // D. Combine all parts: DMN + 12 + 2025 + 001
    $final_role_id = $role_code . $current_month . $current_year . $sequence_part;


    // ----------------------------------------------------------------------
    // 4. Insert Data into Database
    // ----------------------------------------------------------------------

    // 💡 UPDATED SQL: Added 'username' column
    $sql = "INSERT INTO users (role_id, first_name, last_name, username, email, phone, role, password) 
            VALUES (:role_id, :first_name, :last_name, :username, :email, :phone, :role, :password)";
    
    $stmt = $pdo->prepare($sql);
    
    $success = $stmt->execute([
        'role_id'    => $final_role_id,
        'first_name' => $first_name,
        'last_name'  => $last_name,
        'username'   => $username,
        'email'      => $email,
        'phone'      => $phone,
        'role'       => $role,
        'password'   => $hashed_password
    ]);

    // ----------------------------------------------------------------------
    // 5. Success/Failure Feedback
    // ----------------------------------------------------------------------
    if ($success) {
        // Redirect to a success page or display a success message
        header("Location: ../../web_content/accounts.php?status=success&id=" . urlencode($final_role_id));
        exit();
    } else {
        // Handle DB insertion error
        die("Error: Could not create user account.");
    }
} else {
    // If accessed directly without POST data
    die("Invalid request method.");
}
?>