<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_page/index.php");
    exit;
}

// Check if user has super_admin role
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "super_admin") {
    // Redirect to appropriate page based on role
    if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin") {
        header("location: ../admin/admin_dashboard.php");
    } else {
        header("location: ../index.php");
    }
    exit;
}

// Include database connection - moved up to ensure $conn is available for all code
require_once "../plugins/conn.php";

// Map-related columns check removed

// Initialize variables
$barangay_name = $barangay_id = "";
$barangay_name_err = "";
$success_message = $error_message = "";

// Handle AJAX search request
if (isset($_GET['ajax_search'])) {
    // Make sure we have a database connection
    require_once "../plugins/conn.php";
    
    $search_term = isset($_GET['term']) ? $_GET['term'] : '';
    $barangay_html = '';
    
    try {
        if (!empty($search_term)) {
            $search_param = "%" . $search_term . "%";
            $barangay_query = "SELECT barangay_id, barangay FROM barangay WHERE barangay LIKE ? ORDER BY barangay";
            $stmt = mysqli_prepare($conn, $barangay_query);
            mysqli_stmt_bind_param($stmt, "s", $search_param);
            mysqli_stmt_execute($stmt);
            $barangay_result = mysqli_stmt_get_result($stmt);
        } else {
            $barangay_query = "SELECT barangay_id, barangay FROM barangay ORDER BY barangay";
            $barangay_result = mysqli_query($conn, $barangay_query);
        }
        
        if ($barangay_result && mysqli_num_rows($barangay_result) > 0) {
            while ($barangay_row = mysqli_fetch_assoc($barangay_result)) {
                $barangay_name = htmlspecialchars($barangay_row['barangay']);
                $logo_path = "../images/barangay/" . strtolower(str_replace(' ', '_', $barangay_name)) . ".svg";
                $default_logo = "../images/barangay/default_barangay.svg";
                
                // Check if logo exists, otherwise use default
                $logo_to_use = file_exists($logo_path) ? $logo_path : $default_logo;
                
                $barangay_html .= '<div class="barangay-item">';
                $barangay_html .= '    <div class="barangay-logo">';
                $barangay_html .= '        <img src="' . $logo_to_use . '" alt="' . $barangay_name . ' Logo">';
                $barangay_html .= '    </div>';
                $barangay_html .= '    <div class="barangay-name">Barangay ' . $barangay_name . '</div>';
                $barangay_html .= '</div>';
            }
        } else {
            $barangay_html = '<div class="no-barangays">No barangays found</div>';
        }
        
        echo $barangay_html;
    } catch (Exception $e) {
        echo '<div class="error">Error: ' . $e->getMessage() . '</div>';
    }
    exit;
}

// Process add barangay form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_barangay"])) {
    // Validate barangay name
    if (empty(trim($_POST["barangay_name"]))) {
        $barangay_name_err = "Please enter a barangay name.";
    } else {
        $barangay_name = trim($_POST["barangay_name"]);
        
        // Check if barangay name already exists
        $sql_check = "SELECT barangay_id FROM barangay WHERE barangay = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql_check)) {
            mysqli_stmt_bind_param($stmt, "s", $barangay_name);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) > 0) {
                    $barangay_name_err = "This barangay name already exists.";
                }
            } else {
                $error_message = "Oops! Something went wrong. Please try again later.";
            }
            
            mysqli_stmt_close($stmt);
        }
    }
    
    // Check input errors before inserting into database
    if (empty($barangay_name_err)) {
        // Map-related form fields removed
        
        // Prepare an insert statement
        $sql = "INSERT INTO barangay (barangay) VALUES (?)";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $barangay_name);
            
            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Barangay added successfully!";
                // Clear the form
                $barangay_name = "";
            } else {
                $error_message = "Oops! Something went wrong. Please try again later.";
            }
            
            mysqli_stmt_close($stmt);
        }
    }
}

// Process update barangay form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_barangay"])) {
    $barangay_id = trim($_POST["barangay_id"]);
    
    // Validate barangay name
    if (empty(trim($_POST["edit_barangay_name"]))) {
        $barangay_name_err = "Please enter a barangay name.";
    } else {
        $barangay_name = trim($_POST["edit_barangay_name"]);
        
        // Check if barangay name already exists (excluding the current one)
        $sql_check = "SELECT barangay_id FROM barangay WHERE barangay = ? AND barangay_id != ?";
        
        if ($stmt = mysqli_prepare($conn, $sql_check)) {
            mysqli_stmt_bind_param($stmt, "si", $barangay_name, $barangay_id);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) > 0) {
                    $barangay_name_err = "This barangay name already exists.";
                }
            } else {
                $error_message = "Oops! Something went wrong. Please try again later.";
            }
            
            mysqli_stmt_close($stmt);
        }
    }
    
    // Check input errors before updating the database
    if (empty($barangay_name_err)) {
        // Map-related form fields removed
        
        // Prepare an update statement
        $sql = "UPDATE barangay SET barangay = ? WHERE barangay_id = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "si", $barangay_name, $barangay_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Barangay updated successfully!";
            } else {
                $error_message = "Oops! Something went wrong. Please try again later.";
            }
            
            mysqli_stmt_close($stmt);
        }
    }
}

// Process delete barangay
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_barangay"])) {
    $barangay_id = trim($_POST["barangay_id"]);
    
    // Check if barangay is being used by any barangay admin
    $sql_check = "SELECT barangay_admin_id FROM barangay_admin WHERE barangay_id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql_check)) {
        mysqli_stmt_bind_param($stmt, "i", $barangay_id);
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            
            if (mysqli_stmt_num_rows($stmt) > 0) {
                $error_message = "Cannot delete this barangay as it is assigned to one or more barangay admins.";
            } else {
                // Prepare a delete statement
                $sql = "DELETE FROM barangay WHERE barangay_id = ?";
                
                if ($stmt_delete = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt_delete, "i", $barangay_id);
                    
                    if (mysqli_stmt_execute($stmt_delete)) {
                        $success_message = "Barangay deleted successfully!";
                    } else {
                        $error_message = "Oops! Something went wrong. Please try again later.";
                    }
                    
                    mysqli_stmt_close($stmt_delete);
                }
            }
        } else {
            $error_message = "Oops! Something went wrong. Please try again later.";
        }
        
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Map functionality removed -->
    <style>
        /* General Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
        }
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: #ecf0f1;
            padding: 20px 0;
            position: fixed;
            height: 100%;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid #34495e;
        }
        
        .sidebar-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
        }
        
        .sidebar-menu li {
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
        
        .sidebar-menu li:hover {
            background-color: #34495e;
        }
        
        .sidebar-menu li.active {
            background-color: #3498db;
        }
        
        .sidebar-menu a {
            color: #ecf0f1;
            text-decoration: none;
            font-size: 1rem;
            display: block;
        }
        
        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 30px;
            background-color: #f8fafd;
            margin-left: 280px;
            transition: margin-left 0.3s ease;
        }
        
        .main-content.expanded {
            margin-left: 70px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background-color: #fff;
            padding: 15px 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 1.8rem;
            color: #2c3e50;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-info span {
            margin-right: 15px;
        }
        
        .logout-btn {
            background-color: #e74c3c;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        
        .logout-btn:hover {
            background-color: #c0392b;
        }
        
        /* Barangay Management Styles */
        .barangay-management {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .barangay-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .barangay-header h2 {
            color: #2c3e50;
            font-size: 1.5rem;
        }
        
        .barangay-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .search-container {
            position: relative;
        }
        
        .search-input {
            padding: 8px 15px;
            padding-right: 40px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9rem;
            width: 200px;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
            width: 250px;
        }
        
        .search-btn {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #7f8c8d;
            cursor: pointer;
            padding: 5px;
            transition: color 0.3s ease;
        }
        
        .search-btn:hover {
            color: #3498db;
        }
        
        .add-barangay-btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .add-barangay-btn:hover {
            background-color: #2980b9;
        }
        
        /* Table Styles */
        .data-table-container {
            overflow-x: auto;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th, .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .data-table th {
            background-color: #f9f9f9;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .data-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .edit-btn, .delete-btn {
            padding: 6px 10px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-size: 0.9rem;
            cursor: pointer;
        }
        
        .edit-btn {
            background-color: #2ecc71;
        }
        
        .edit-btn:hover {
            background-color: #27ae60;
        }
        
        .delete-btn {
            background-color: #e74c3c;
        }
        
        .delete-btn:hover {
            background-color: #c0392b;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal.show {
            display: block;
        }
        
        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border-radius: 5px;
            width: 50%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            position: relative;
        }
        
        .close-modal {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 1.5rem;
            cursor: pointer;
            color: #aaa;
        }
        
        .close-modal:hover {
            color: #555;
        }
        
        .modal-header {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .modal-header h2 {
            margin: 0;
            color: #2c3e50;
            font-size: 1.5rem;
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        .form-error {
            color: #e74c3c;
            font-size: 0.85rem;
            margin-top: 5px;
        }
        
        .form-submit {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
            margin-top: 10px;
        }
        
        .form-submit:hover {
            background-color: #2980b9;
        }
        
        /* Barangay Grid Styles */
        .barangay-grid-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            padding: 20px 0;
        }
        
        .barangay-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #fff;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        /* Map Container styles removed */
        
        .barangay-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .barangay-item.selected {
            border: 2px solid #4CAF50;
            background-color: rgba(76, 175, 80, 0.05);
        }
        
        .barangay-logo {
            width: 100px;
            height: 100px;
            margin-bottom: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .barangay-logo img {
            max-width: 100%;
            max-height: 100%;
            border-radius: 50%;
            object-fit: contain;
        }
        
        .barangay-name {
            font-weight: 600;
            text-align: center;
            margin-top: 10px;
            color: #2c3e50;
        }
        
        /* Barangay hall indicator styles removed */
        
        .no-barangays, .loading, .error {
            grid-column: 1 / -1;
            text-align: center;
            padding: 30px;
            background-color: #f8f9fa;
            border-radius: 8px;
            color: #6c757d;
            font-size: 1.1rem;
        }
        
        .loading {
            background-color: #e9f5ff;
            color: #0056b3;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        /* Alert Messages */
        .alert {
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php 
        // Set page title for the navbar
        $page_title = 'Barangay Management';
        
        // Include sidebar
        include 'superadmin_sidebar.php'; 
        ?>
        
        <div class="main-content">
            <?php 
            // Include navbar
            include 'superadmin_navbar.php'; 
            ?>
            
            <!-- Map containers removed -->
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Barangay List Section -->
            <div class="barangay-management">
                <div class="barangay-header">
                    <h2>Barangay List</h2>
                    <div class="barangay-actions">
                        <div class="search-container">
                            <input type="text" id="search-barangay" class="search-input" placeholder="Search barangay..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <button class="search-btn"><i class="fas fa-search"></i></button>
                        </div>
                        <button id="openAddBarangayModal" class="add-btn">Add Barangay</button>
                    </div>
                </div>
                
                <div class="barangay-grid-container">
                    <?php
                    // Only fetch barangays on initial page load (not for AJAX requests)
                    if (!isset($_GET['ajax_search']) || $_GET['ajax_search'] !== 'true') {
                        // Get barangays from the database with optional search filter
                        $search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
                        
                        if (!empty($search_term)) {
                            $search_term = '%' . mysqli_real_escape_string($conn, $search_term) . '%';
                            $barangay_query = "SELECT barangay_id, barangay FROM barangay WHERE barangay LIKE ? ORDER BY barangay";
                            $stmt = mysqli_prepare($conn, $barangay_query);
                            mysqli_stmt_bind_param($stmt, "s", $search_term);
                            mysqli_stmt_execute($stmt);
                            $barangay_result = mysqli_stmt_get_result($stmt);
                        } else {
                            $barangay_query = "SELECT barangay_id, barangay FROM barangay ORDER BY barangay";
                            $barangay_result = mysqli_query($conn, $barangay_query);
                        }
                        
                        if ($barangay_result && mysqli_num_rows($barangay_result) > 0) {
                            while ($barangay_row = mysqli_fetch_assoc($barangay_result)) {
                                $barangay_name = htmlspecialchars($barangay_row['barangay']);
                                $logo_path = "../images/barangay/" . strtolower(str_replace(' ', '_', $barangay_name)) . ".svg";
                                $default_logo = "../images/barangay/default_barangay.svg";
                                
                                // Check if logo exists, otherwise use default
                                $logo_to_use = file_exists($logo_path) ? $logo_path : $default_logo;
                                
                                // Map-related attributes removed
                                echo '<div class="barangay-item" data-id="' . $barangay_row['barangay_id'] . '" data-name="' . $barangay_name . '">';
                                echo '    <div class="barangay-logo">';
                                echo '        <img src="' . $logo_to_use . '" alt="' . $barangay_name . ' Logo">';
                                echo '    </div>';
                                echo '    <div class="barangay-name">Barangay ' . $barangay_name . '</div>';
                                echo '</div>';
                            }
                        } else {
                            echo '<div class="no-barangays">No barangays found</div>';
                        }
                    }
                    ?>
                </div>
            </div>
            
            <!-- Add Barangay Modal -->
            <div id="addBarangayModal" class="modal">
                <div class="modal-content">
                    <span class="close-modal">&times;</span>
                    <div class="modal-header">
                        <h2>Add Barangay</h2>
                    </div>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group">
                            <label for="barangay_name">Barangay Name:</label>
                            <input type="text" name="barangay_name" id="barangay_name" class="form-control" value="<?php echo htmlspecialchars($barangay_name); ?>">
                            <?php if (!empty($barangay_name_err)): ?>
                                <span class="form-error"><?php echo $barangay_name_err; ?></span>
                            <?php endif; ?>
                        </div>
                        <!-- Map-related form fields removed -->
                        <button type="submit" name="add_barangay" class="form-submit">Add Barangay</button>
                    </form>
                </div>
            </div>
            
            <!-- Edit Barangay Modal -->
            <div id="editBarangayModal" class="modal">
                <div class="modal-content">
                    <span class="close-modal">&times;</span>
                    <div class="modal-header">
                        <h2>Edit Barangay</h2>
                    </div>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <input type="hidden" name="barangay_id" id="edit_barangay_id">
                        <div class="form-group">
                            <label for="edit_barangay_name">Barangay Name:</label>
                            <input type="text" name="edit_barangay_name" id="edit_barangay_name" class="form-control">
                            <?php if (!empty($barangay_name_err)): ?>
                                <span class="form-error"><?php echo $barangay_name_err; ?></span>
                            <?php endif; ?>
                        </div>
                        <!-- Map-related form fields removed -->
                        <button type="submit" name="update_barangay" class="form-submit">Update Barangay</button>
                    </form>
                </div>
            </div>
            
           
    
    <?php
    // Close the database connection at the end of the page
    if (isset($conn) && $conn) {
        mysqli_close($conn);
    }
    ?>
    
    <script>
        // Enhanced client-side functionality
         document.addEventListener('DOMContentLoaded', function() {
            
            // Set active menu item based on current URL
            const currentLocation = window.location.href;
            const menuItems = document.querySelectorAll('.sidebar-menu a');
            
            menuItems.forEach(item => {
                if (item.href === currentLocation) {
                    item.parentElement.classList.add('active');
                } else {
                    item.parentElement.classList.remove('active');
                }
            });
            
            // Map initialization and interaction code removed
            
            // Add click event to barangay items
            const barangayItems = document.querySelectorAll('.barangay-item');
            barangayItems.forEach(item => {
                item.addEventListener('click', function() {
                    // Highlight selected barangay
                    barangayItems.forEach(item => item.classList.remove('selected'));
                    this.classList.add('selected');
                });
            });
            
            // Real-time search functionality
            const searchInput = document.getElementById('search-barangay');
            const barangayGridContainer = document.querySelector('.barangay-grid-container');
            let debounceTimer;
            
            // Function to perform search
            function performSearch() {
                const searchTerm = searchInput.value.trim();
                
                // Show loading message
                barangayGridContainer.innerHTML = '<div class="loading">Loading...</div>';
                
                // Create the URL for the AJAX request
                const url = 'barangay.php?ajax_search=1&term=' + encodeURIComponent(searchTerm);
                
                // Perform the AJAX request
                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.text();
                    })
                    .then(html => {
                        // Update the grid with the returned HTML
                        barangayGridContainer.innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        barangayGridContainer.innerHTML = '<div class="error">Error: ' + error.message + '</div>';
                    });
            }
            
            // Add event listener to search input
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(performSearch, 300); // 300ms debounce
            });
            
            // Also search on Enter key
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    performSearch();
                }
            });
            
            // Add event listener to search button
            const searchButton = document.querySelector('.search-btn');
            if (searchButton) {
                searchButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    performSearch();
                });
            }
            
            // Initial search to load all barangays
            performSearch();
            
            // Modal functionality
            const addModal = document.getElementById('addBarangayModal');
            const editModal = document.getElementById('editBarangayModal');
            const addModalBtn = document.getElementById('openAddBarangayModal');
            const closeButtons = document.querySelectorAll('.close-modal');
            
            // Debug information
            // Search functionality initialized
            
            // Open add modal
            addModalBtn.onclick = function() {
                addModal.classList.add('show');
            }
            
            // Close modals when X is clicked
            closeButtons.forEach(button => {
                button.onclick = function() {
                    addModal.classList.remove('show');
                    editModal.classList.remove('show');
                }
            });
            
            // Close modals when clicking outside
            window.onclick = function(event) {
                if (event.target === addModal) {
                    addModal.classList.remove('show');
                }
                if (event.target === editModal) {
                    editModal.classList.remove('show');
                }
            }
            
            // Edit button functionality
            const editButtons = document.querySelectorAll('.edit-btn');
            editButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const barangayId = this.getAttribute('data-id');
                    const barangayName = this.getAttribute('data-name');
                    // Map-related data attributes removed
                    
                    // Populate the edit form
                    document.getElementById('edit_barangay_id').value = barangayId;
                    document.getElementById('edit_barangay_name').value = barangayName;
                    // Map-related form field population removed
                    
                    // Show the edit modal
                    editModal.classList.add('show');
                });
            });
            
            // Delete button functionality
            const deleteButtons = document.querySelectorAll('.delete-btn');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const barangayId = this.getAttribute('data-id');
                    const barangayName = this.getAttribute('data-name');
                    
                    if (confirm('Are you sure you want to delete the barangay "' + barangayName + '"? This action cannot be undone.')) {
                        document.getElementById('delete_barangay_id').value = barangayId;
                        document.getElementById('deleteBarangayForm').submit();
                    }
                });
            });
        });
    </script>
</body>
</html>