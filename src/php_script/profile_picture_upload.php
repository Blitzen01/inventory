<?php
// Include database connection
include "../../render/connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Check for the uploaded file
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        
        // Define the upload directory (server path, relative to this script)
        $uploadDir = '../../src/image/profile_picture/';
        
        $fileTmpPath = $_FILES['profile_image']['tmp_name'];
        $fileName = basename($_FILES['profile_image']['name']);

        // Generate a unique and sanitized filename
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $safeFileName = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', pathinfo($fileName, PATHINFO_FILENAME)) . 
                         '_' . time() . '.' . $fileExtension;
        
        $targetFilePath = $uploadDir . $safeFileName;
        
        // Define the path to store in the database (web path, relative to the web root)
        // NOTE: Changed from '../src/image/...' to 'src/image/...'
        // The path should be consistent for HTML <img src="..."> usage, typically starting from the root or relative to the page displaying it.
        $databasePath = '../src/image/profile_picture/' . $safeFileName; 

        // Ensure the upload directory exists
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                 die("Failed to create upload directory: " . $uploadDir);
            }
        }

        session_start();
        $username = $_SESSION['username']; 

        // --- UNLINK LOGIC START ---
        
        // FIX 1: The SELECT query MUST pull the column where the profile picture path is stored.
        // Assuming your column is named 'profile_image' (matching your UPDATE statement) in the 'users' table.
        $fetchSql = "SELECT profile_image FROM users WHERE username = ?"; 
        $fetchStmt = $conn->prepare($fetchSql);
        $fetchStmt->bind_param("s", $username);
        $fetchStmt->execute();
        $result = $fetchStmt->get_result();
        $row = $result->fetch_assoc();
        $fetchStmt->close(); 

        // Check if an old profile picture path exists
        // FIX 2: Check the column name you actually selected: 'profile_image'
        if ($row && !empty($row['profile_image'])) {
            $oldProfilePicturePathDB = $row['profile_image']; 

            // IMPORTANT: Convert the database path (web path) back to the server path for deletion.
            // Assuming your database path is 'src/image/profile_picture/filename.jpg'
            // We need to strip the common web directory portion to get the filename.
            
            // Extract just the filename from the database path:
            $oldProfilePictureName = basename($oldProfilePicturePathDB);
            
            // Reconstruct the full server path for deletion using the $uploadDir
            $oldProfilePictureFullPath = $uploadDir . $oldProfilePictureName;

            // Delete the old profile picture if it exists
            if (file_exists($oldProfilePictureFullPath)) {
                 // Security check to ensure the file being deleted is within the expected upload directory
                 if (strpos(realpath($oldProfilePictureFullPath), realpath($uploadDir)) === 0) {
                     // *** This is the line that unlinks (deletes) the file ***
                     unlink($oldProfilePictureFullPath); 
                     // *** End of unlink ***
                 }
            }
        }

        // --- UNLINK LOGIC END ---

        // Move the uploaded file to the target directory
        if (move_uploaded_file($fileTmpPath, $targetFilePath)) {
            // Update the database with the web-accessible path/filename
            $sql = "UPDATE users SET profile_image = ? WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $databasePath, $username);

            if ($stmt->execute()) {
                // SUCCESS
                echo '<script type="text/javascript">';
                echo 'window.location.href = "../../web_content/profile.php";'; 
                echo '</script>';
            } else {
                // Handle database error
                echo "Failed to update profile picture in the database: " . $conn->error;
            }
            $stmt->close();
        } else {
            // Handle file upload error
            echo "Failed to upload the file. Check directory permissions (755).";
        }
    } else {
        // ... (Error handling block remains the same) ...
        $errorCode = $_FILES['profile_image']['error'] ?? 'N/A';
        $error_message = 'No file uploaded or an error occurred. Error Code: ' . $errorCode;

        // Check for specific common file upload errors
        if ($errorCode == UPLOAD_ERR_INI_SIZE) {
            $error_message .= " (File size exceeds upload_max_filesize directive in php.ini)";
        } elseif ($errorCode == UPLOAD_ERR_FORM_SIZE) {
            $error_message .= " (File size exceeds MAX_FILE_SIZE directive in HTML form)";
        } elseif ($errorCode == UPLOAD_ERR_NO_FILE) {
             $error_message = "No file was selected for upload.";
        }
        
        echo $error_message;
        if (empty($_FILES) && $_SERVER['REQUEST_METHOD'] === 'POST' && strtolower(substr($_SERVER['CONTENT_TYPE'], 0, 19)) === 'multipart/form-data') {
             echo "<br>CRITICAL: PHP did not process the multipart form data. Check post_max_size in php.ini.";
        }
    }
} else {
    echo "Invalid request method.";
}
?>