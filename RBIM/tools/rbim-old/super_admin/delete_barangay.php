<?php
session_start();
require_once '../plugins/conn.php';

// Check if user is logged in and has super admin role
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== "super_admin") {
    $_SESSION['error'] = "You don't have permission to perform this action.";
    header("Location: ../login_page/index.php");
    exit();
}

// Check if barangay ID is provided
if (!isset($_POST['barangay_id']) || empty($_POST['barangay_id'])) {
    $_SESSION['error'] = "Barangay ID is required.";
    header("Location: barangay_management.php");
    exit();
}

$barangay_id = $_POST['barangay_id'];

try {
    // Get the barangay logo filename before deletion to delete the file
    $stmt = $conn->prepare("SELECT logo FROM barangay WHERE barangay_id = ?");
    $stmt->bind_param("i", $barangay_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $barangay = $result->fetch_assoc();
        $logo_filename = $barangay['logo'];
        
        // Delete the barangay record from the database
        $delete_stmt = $conn->prepare("DELETE FROM barangay WHERE barangay_id = ?");
        $delete_stmt->bind_param("i", $barangay_id);
        
        if ($delete_stmt->execute()) {
            // Delete the logo file if it exists and is not the default logo
            if (!empty($logo_filename) && $logo_filename !== 'default_logo.png') {
                $logo_path = "../images/barangay/" . $logo_filename;
                if (file_exists($logo_path)) {
                    unlink($logo_path);
                }
            }
            
            $_SESSION['success'] = "Barangay has been successfully deleted.";
        } else {
            $_SESSION['error'] = "Failed to delete barangay: " . $conn->error;
        }
        
        $delete_stmt->close();
    } else {
        $_SESSION['error'] = "Barangay not found.";
    }
    
    $stmt->close();
} catch (Exception $e) {
    $_SESSION['error'] = "An error occurred: " . $e->getMessage();
}

// Redirect back to the barangay management page
header("Location: barangay_management.php");
exit();
?>