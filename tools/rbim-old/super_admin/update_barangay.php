<?php
session_start();
require_once('../plugins/conn.php');

// Check if user is logged in as super admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "super_admin") {
    header('Location: ../login_page/index.php');
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $barangay_id = $_POST['barangay_id'];
    $barangay_name = $_POST['barangay_name'];
    
    // Validate input
    if (empty($barangay_id) || empty($barangay_name)) {
        $_SESSION['error'] = "Barangay ID and name are required.";
        header('Location: barangay_management.php');
        exit();
    }
    
    // Update barangay name in database
    $stmt = $conn->prepare("UPDATE barangay SET barangay = ? WHERE barangay_id = ?");
    if ($stmt === false) {
        $_SESSION['error'] = "Prepare failed: " . $conn->error;
        header('Location: barangay_management.php');
        exit();
    }
    $stmt->bind_param("si", $barangay_name, $barangay_id);
    $name_updated = $stmt->execute();
    
    // Handle logo upload if provided
    $logo_updated = false;
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
        $new_file_name = "Barangay-{$formatted_name}.png";
        $upload_path = "../images/barangay/{$new_file_name}";
        
        // Move uploaded file
        if (move_uploaded_file($file_tmp, $upload_path)) {
            $logo_updated = true;
        } else {
            $_SESSION['error'] = "Failed to upload logo. Please try again.";
            header('Location: barangay_management.php');
            exit();
        }
    }
    
    // Set success message
    if ($name_updated || $logo_updated) {
        $_SESSION['success'] = "Barangay updated successfully.";
    } else {
        $_SESSION['error'] = "No changes were made.";
    }
    
    // Redirect back to barangay management page
    header('Location: barangay_management.php');
    exit();
} else {
    // If not POST request, redirect to barangay management page
    header('Location: barangay_management.php');
    exit();
}
?>