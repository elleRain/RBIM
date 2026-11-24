<?php
session_start();
require_once('../plugins/conn.php');

// Check if user is logged in as super admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== "super_admin") {
    $_SESSION['error'] = "You don't have permission to perform this action.";
    header('Location: ../login_page/index.php');
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $barangay_name = trim($_POST['barangay_name']);
    
    // Validate input
    if (empty($barangay_name)) {
        $_SESSION['error'] = "Barangay name is required.";
        header('Location: barangay_management.php');
        exit();
    }
    
    try {
        // Check if barangay already exists
        $check_stmt = $conn->prepare("SELECT barangay_id FROM barangay WHERE barangay = ?");
        $check_stmt->bind_param("s", $barangay_name);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $_SESSION['error'] = "A barangay with this name already exists.";
            header('Location: barangay_management.php');
            exit();
        }
        $check_stmt->close();
        
        // Set default logo
        $logo_filename = 'default_logo.png';
        
        // Handle logo upload if provided
        if (isset($_FILES['barangay_logo']) && $_FILES['barangay_logo']['error'] === UPLOAD_ERR_OK) {
            // Get file info
            $file_name = $_FILES['barangay_logo']['name'];
            $file_tmp = $_FILES['barangay_logo']['tmp_name'];
            $file_size = $_FILES['barangay_logo']['size'];
            $file_type = $_FILES['barangay_logo']['type'];
            
            // Validate file type
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
            if (!in_array($file_type, $allowed_types)) {
                $_SESSION['error'] = "Only JPG, JPEG, PNG, and GIF files are allowed.";
                header('Location: barangay_management.php');
                exit();
            }
            
            // Validate file size (max 2MB)
            if ($file_size > 2097152) {
                $_SESSION['error'] = "File size must be less than 2MB.";
                header('Location: barangay_management.php');
                exit();
            }
            
            // Format barangay name for filename
            $formatted_name = str_replace(' ', '-', $barangay_name);
            $logo_filename = "Barangay-{$formatted_name}.png";
            $upload_path = "../images/barangay/{$logo_filename}";
            
            // Create directory if it doesn't exist
            $upload_dir = "../images/barangay";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Move uploaded file
            if (!move_uploaded_file($file_tmp, $upload_path)) {
                $_SESSION['error'] = "Failed to upload logo. Please try again.";
                header('Location: barangay_management.php');
                exit();
            }
        }
        
        // Insert new barangay into database
        $insert_stmt = $conn->prepare("INSERT INTO barangay (barangay, logo) VALUES (?, ?)");
        $insert_stmt->bind_param("ss", $barangay_name, $logo_filename);
        
        if ($insert_stmt->execute()) {
            $_SESSION['success'] = "Barangay added successfully.";
        } else {
            $_SESSION['error'] = "Failed to add barangay: " . $conn->error;
            
            // Delete uploaded logo if insert failed
            if ($logo_filename !== 'default_logo.png') {
                $logo_path = "../images/barangay/{$logo_filename}";
                if (file_exists($logo_path)) {
                    unlink($logo_path);
                }
            }
        }
        
        $insert_stmt->close();
    } catch (Exception $e) {
        $_SESSION['error'] = "An error occurred: " . $e->getMessage();
    }
    
    // Redirect back to barangay management page
    header('Location: barangay_management.php');
    exit();
}

// If not a POST request, redirect to barangay management page
header('Location: barangay_management.php');
exit();
?>