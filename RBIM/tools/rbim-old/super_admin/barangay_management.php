<?php
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

// Include database connection
require_once "../plugins/conn.php";

// Set page title for navbar
$page_title = "Barangay Management";

// Fetch all barangays from database in alphabetical order
$barangays = [];
$sql = "SELECT barangay_id, barangay FROM barangay ORDER BY barangay ASC";
$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $barangays[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="../src/design-system.css">
    <link rel="stylesheet" href="../src/responsive-fixes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/global-styles.css">
    <style>
        /* Dashboard styles */
        
        body {
            font-family: 'Segoe UI', Roboto, -apple-system, BlinkMacSystemFont, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8fafd;
            color: #333;
        }
        
        .main-content {
            margin-left: 260px;
            padding: 30px;
            transition: margin-left 0.3s ease;
        }
        
        body.sidebar-collapsed .main-content {
            margin-left: 70px;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.06);
        }
        
        .dashboard-title {
            font-size: 26px;
            font-weight: 700;
            color: #1a202c;
            margin: 0;
            position: relative;
        }
        
        .dashboard-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 40px;
            height: 3px;
            background: linear-gradient(135deg, #4285F4, #34A853);
            border-radius: 3px;
        }
        
        .dashboard-subtitle {
            font-size: 15px;
            color: #718096;
            margin: 12px 0 0 0;
            line-height: 1.4;
        }
        
        .search-container {
            margin-bottom: 30px;
            width: 100%;
            position: relative;
        }
        
        .search-container::before {
            content: '\f002';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #718096;
            font-size: 16px;
            z-index: 1;
        }
        
        .search-input {
            width: 100%;
            max-width: 450px;
            padding: 12px 15px 12px 45px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            outline: none;
            transition: all 0.25s ease;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            background-color: #fff;
        }
        
        .search-input:focus {
            border-color: #4285F4;
            box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.15);
        }
        
        .barangay-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 30px;
            width: 100%;
            max-width: 1600px;
            margin-left: auto;
            margin-right: auto;
            padding: 10px;
            box-sizing: border-box;
        }
        
        .barangay-card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 22px 18px;
            position: relative;
            width: 100%;
            min-height: 260px;
            max-width: 100%;
            box-sizing: border-box;
            justify-content: space-between;
        }
        
        .menu-icon {
            position: absolute;
            top: 15px;
            right: 15px;
            cursor: pointer;
            font-size: 1.2rem;
            color: #555;
            z-index: 10;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.2s ease;
            background-color: rgba(245, 245, 245, 0.7);
        }
        
        .menu-icon:hover {
            background-color: #f0f0f0;
        }
        
        .dropdown-menu {
            position: absolute;
            top: 55px;
            right: 15px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
            display: none;
            flex-direction: column;
            min-width: 160px;
            z-index: 100;
            overflow: hidden;
            animation: fadeIn 0.2s ease;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 480px) {
            .dropdown-menu {
                right: 5px;
                min-width: 130px;
            }
        }
        
        .dropdown-menu.show {
            display: flex;
        }
        
        .dropdown-item {
            padding: 12px 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #333;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .dropdown-item:last-child {
            border-bottom: none;
        }
        
        .dropdown-item:hover {
            background-color: #f5f5f5;
            color: #4285F4;
        }
        
        .dropdown-item.delete-barangay:hover {
            color: #EA4335;
        }
        
        .dropdown-item i {
            width: 18px;
            font-size: 15px;
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
            background-color: rgba(0, 0, 0, 0.6);
        }
        
        .modal-content {
            background-color: #fff;
            margin: 8% auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.25);
            width: 90%;
            max-width: 520px;
            position: relative;
            animation: modalFadeIn 0.3s ease;
        }
        
        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .close-modal {
            position: absolute;
            right: 25px;
            top: 20px;
            font-size: 24px;
            font-weight: bold;
            color: #888;
            cursor: pointer;
            transition: color 0.2s ease;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .close-modal:hover {
            color: #333;
        }
        
        .modal h2 {
            margin-top: 0;
            margin-bottom: 25px;
            color: #333;
            font-size: 1.6rem;
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: #444;
            font-size: 15px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            background-color: #fafafa;
        }
        
        .form-control:focus {
            border-color: #4285F4;
            box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.1);
            outline: none;
        }
        
        .form-text {
            display: block;
            margin-top: 5px;
            font-size: 12px;
            color: #888;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
        }
        
        .save-btn {
            background: linear-gradient(135deg, #4285F4, #34A853);
            color: white;
        }
        
        .cancel-btn {
            background-color: #f5f5f5;
            color: #555;
        }
        
        .delete-modal {
            max-width: 480px;
        }
        
        .delete-message {
            font-size: 17px;
            margin-bottom: 15px;
            color: #333;
            line-height: 1.5;
        }
        
        .delete-warning {
            color: #EA4335;
            font-size: 15px;
            margin-bottom: 25px;
            padding: 10px 15px;
            background-color: rgba(234, 67, 53, 0.08);
            border-radius: 6px;
            border-left: 4px solid #EA4335;
        }
        
        .delete-confirm-btn {
            background: linear-gradient(135deg, #EA4335, #DB4437);
            color: white;
        }
        
        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 10px;
            font-size: 15px;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            position: relative;
        }
        
        .alert::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            border-radius: 4px 0 0 4px;
        }
        
        .alert-success {
            background-color: #e6f4ea;
            color: #34A853;
        }
        
        .alert-success::before {
            background-color: #34A853;
        }
        
        .alert-error {
            background-color: #fce8e6;
            color: #EA4335;
        }
        
        .alert-error::before {
            background-color: #EA4335;
        }
        
        .barangay-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }
        
        .barangay-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            object-fit: cover;
            background-color: #f8fafd;
            border: 3px solid rgba(66, 133, 244, 0.1);
            flex-shrink: 0;
        }
        
        .barangay-info {
            text-align: center;
            margin-bottom: 15px;
            width: 100%;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 0 5px;
        }
        
        .barangay-name {
            margin: 0 0 6px 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            line-height: 1.3;
            word-wrap: break-word;
        }
        
        .barangay-id {
            margin: 0 0 10px 0;
            font-size: 0.85rem;
            color: #888;
        }
        
        .barangay-badge {
            display: inline-block;
            background-color: #e6f3ff;
            color: #4285F4;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 12px;
            box-shadow: 0 2px 4px rgba(66, 133, 244, 0.1);
        }
        
        .barangay-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            width: 100%;
            margin-top: auto;
            padding-top: 12px;
            flex-shrink: 0;
        }
        
        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s ease;
            flex: 1;
            max-width: 100px;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .btn i {
            margin-right: 5px;
        }
        
        .btn-primary, .edit-btn {
            color: #fff;
            background-color: #4285F4;
        }
        
        .btn-primary:hover, .edit-btn:hover {
            background-color: #3367d6;
        }
        
        .btn-secondary, .view-btn {
            color: #fff;
            background-color: #34A853;
        }
        
        .btn-secondary:hover, .view-btn:hover {
            background-color: #2d9249;
        }
        
        .delete-btn {
            color: #fff;
            background-color: #EA4335;
        }
        
        .delete-btn:hover {
            background-color: #d33426;
        }
        
        /* Responsive adjustments - Updated to prevent overlapping */
        @media (min-width: 1600px) {
            .barangay-grid {
                grid-template-columns: repeat(4, 1fr);
                max-width: 1600px;
            }
        }
        
        @media (min-width: 1200px) and (max-width: 1599px) {
            .barangay-grid {
                grid-template-columns: repeat(4, 1fr);
                max-width: 1400px;
                gap: 20px;
            }
        }
        
        @media (min-width: 992px) and (max-width: 1199px) {
            .barangay-grid {
                grid-template-columns: repeat(3, 1fr);
                max-width: 1000px;
                gap: 20px;
            }
            
            .barangay-card {
                min-height: 270px;
                padding: 22px 18px;
            }
        }
        
        @media (min-width: 768px) and (max-width: 991px) {
            .barangay-grid {
                grid-template-columns: repeat(2, 1fr);
                max-width: 700px;
                gap: 20px;
            }
            
            .barangay-logo {
                width: 85px;
                height: 85px;
                margin-bottom: 18px;
            }
        }
        
        @media (min-width: 576px) and (max-width: 767px) {
            .main-content {
                margin-left: 0;
                padding: 25px 20px;
            }
            
            .barangay-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 18px;
                padding: 0;
                max-width: 100%;
            }
            
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                margin-bottom: 25px;
            }
            
            .dashboard-header > div:last-child {
                margin-top: 20px;
                width: 100%;
            }
            
            .search-container {
                margin-bottom: 25px;
            }
            
            .barangay-card {
                min-height: 260px;
                padding: 20px 15px;
            }
        }
        
        @media (max-width: 575px) {
            .barangay-grid {
                grid-template-columns: 1fr;
                gap: 18px;
                padding: 0;
                max-width: 100%;
            }
            
            .barangay-card {
                min-height: 230px;
                padding: 20px 15px;
                max-width: 100%;
            }
            
            .main-content {
                padding: 20px 15px;
            }
            
            .dashboard-title {
                font-size: 22px;
            }
            
            .search-input {
                padding: 10px 12px;
                max-width: 100%;
            }
        }
        
        /* Remove the placeholder card styles as they're not needed with proper grid */
        .placeholder-card {
            display: none;
        }
        
        /* Ensure dropdown menus work properly on smaller screens */
        @media (max-width: 480px) {
            .dropdown-menu {
                right: 5px;
                min-width: 130px;
                max-width: 150px;
            }
        }
        
        /* Fix for search container on mobile */
        @media (max-width: 576px) {
            .search-container {
                width: 100%;
            }
            
            .search-input {
                width: 100%;
                box-sizing: border-box;
            }
        }
    </style>
</head>
<body>
    <!-- Include sidebar and navbar -->
    <?php include "superadmin_sidebar.php"; ?>
    <?php include "superadmin_navbar.php"; ?>

    <div class="main-content">
        <div class="dashboard-header">
            <div>
                <h1 class="dashboard-title">Barangay Management</h1>
                <p class="dashboard-subtitle">Manage all barangays in the system</p>
            </div>
            <div>
                <button class="btn btn-primary" style="max-width: 180px;" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Add New Barangay
                </button>
            </div>
        </div>
        
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success']; 
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Search Bar -->
        <div class="search-container">
            <input type="text" id="barangaySearch" class="search-input" placeholder="Search barangays..." onkeyup="searchBarangays()">
        </div>

        <!-- Barangay Grid -->
        <div class="barangay-grid" id="barangayGrid">
            <?php if (count($barangays) > 0): ?>
                <?php foreach ($barangays as $barangay): ?>
                    <?php 
                    // Format barangay name for image filename
                    $barangay_name = $barangay['barangay'];
                    $image_name = 'Barangay-' . str_replace(' ', '-', strtolower($barangay_name)) . '.png';
                    $image_path = "../images/barangay/" . $image_name;
                    
                    // Check if image exists, otherwise use default
                    if (!file_exists($image_path)) {
                        $image_path = "../images/rbim_logo.jpg";
                    }
                    ?>
                    <div class="barangay-card">
                        <div class="menu-icon" onclick="toggleDropdown(this, event)">
                            <i class="fas fa-ellipsis-v"></i>
                        </div>
                        <div class="dropdown-menu">
                            <div class="dropdown-item edit-barangay" onclick="event.stopPropagation(); openEditModal(<?php echo $barangay['barangay_id']; ?>, '<?php echo addslashes(htmlspecialchars($barangay_name)); ?>')">
                                <i class="fas fa-edit"></i> Edit
                            </div>
                            <div class="dropdown-item delete-barangay" onclick="event.stopPropagation(); confirmDelete(<?php echo $barangay['barangay_id']; ?>, '<?php echo addslashes(htmlspecialchars($barangay_name)); ?>')">
                                <i class="fas fa-trash-alt"></i> Delete
                            </div>
                        </div>
                        <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($barangay_name); ?> Logo" class="barangay-logo">
                        <div class="barangay-info">
                            <h3 class="barangay-name"><?php echo htmlspecialchars($barangay_name); ?></h3>
                            <p class="barangay-id">ID: <?php echo $barangay['barangay_id']; ?></p>
                            <span class="barangay-badge">Active</span>
                        </div>
                        <div class="barangay-actions">
                            <a href="barangay_view_info.php?barangay_id=<?php echo $barangay['barangay_id']; ?>" class="btn view-btn">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-data">
                    <p>No barangays found in the system.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Search functionality
        function searchBarangays() {
            const input = document.getElementById('barangaySearch');
            const filter = input.value.toUpperCase();
            const grid = document.getElementById('barangayGrid');
            const cards = grid.getElementsByClassName('barangay-card');
            let visibleCount = 0;
            
            for (let i = 0; i < cards.length; i++) {
                const name = cards[i].getElementsByClassName('barangay-name')[0];
                const txtValue = name.textContent || name.innerText;
                
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    cards[i].style.display = "";
                    visibleCount++;
                } else {
                    cards[i].style.display = "none";
                }
            }
            
            // Add empty placeholder cards to maintain grid layout if needed
            const existingPlaceholders = grid.querySelectorAll('.placeholder-card');
            existingPlaceholders.forEach(placeholder => placeholder.remove());
            
            if (visibleCount > 0 && visibleCount % 3 !== 0) {
                const placeholdersNeeded = 3 - (visibleCount % 3);
                for (let i = 0; i < placeholdersNeeded; i++) {
                    const placeholder = document.createElement('div');
                    placeholder.className = 'placeholder-card';
                    grid.appendChild(placeholder);
                }
            }
        }
        
        // Toggle dropdown menu
        function toggleDropdown(element, event) {
            // Close all other open dropdowns first
            const allDropdowns = document.querySelectorAll('.dropdown-menu');
            allDropdowns.forEach(dropdown => {
                if (dropdown !== element.nextElementSibling) {
                    dropdown.classList.remove('show');
                }
            });
            
            // Toggle the clicked dropdown
            const dropdown = element.nextElementSibling;
            dropdown.classList.toggle('show');
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function closeDropdown(e) {
                if (!element.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.remove('show');
                    document.removeEventListener('click', closeDropdown);
                }
            });
            
            // Prevent the click from propagating to the document
            if (event) {
                event.stopPropagation();
            }
        }
        
        // Modal functionality
        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            window.editModal = document.getElementById('editBarangayModal');
            window.deleteModal = document.getElementById('deleteBarangayModal');
            window.addModal = document.getElementById('addBarangayModal');
            window.editCloseBtn = window.editModal ? window.editModal.querySelector('.close-modal') : null;
            window.deleteCloseBtn = window.deleteModal ? window.deleteModal.querySelector('.close-modal') : null;
            window.addCloseBtn = window.addModal ? window.addModal.querySelector('.close-modal') : null;
            
            // Initialize event listeners
            if (window.editCloseBtn) {
                window.editCloseBtn.addEventListener('click', closeEditModal);
            }
            
            if (window.deleteCloseBtn) {
                window.deleteCloseBtn.addEventListener('click', closeDeleteModal);
            }
            
            if (window.addCloseBtn) {
                window.addCloseBtn.addEventListener('click', closeAddModal);
            }
            
            // Close modals when clicking outside
            window.addEventListener('click', (event) => {
                if (event.target === window.editModal) {
                    closeEditModal();
                }
                if (event.target === window.deleteModal) {
                    closeDeleteModal();
                }
                if (event.target === window.addModal) {
                    closeAddModal();
                }
            });
        });
        
        // Open edit modal with barangay data
        function openEditModal(barangayId, barangayName) {
            document.getElementById('edit_barangay_id').value = barangayId;
            document.getElementById('edit_barangay_name').value = barangayName;
            if (window.editModal) {
                window.editModal.style.display = 'block';
                
                // Close any open dropdowns
                document.querySelectorAll('.dropdown-menu').forEach(dropdown => {
                    dropdown.classList.remove('show');
                });
            } else {
                console.error('Edit modal element not found');
            }
        }
        
        // Close edit modal
        function closeEditModal() {
            if (window.editModal) {
                window.editModal.style.display = 'none';
            }
        }
        
        // Open delete confirmation modal
        function confirmDelete(barangayId, barangayName) {
            document.getElementById('delete_barangay_id').value = barangayId;
            document.getElementById('delete_barangay_name').textContent = barangayName;
            if (window.deleteModal) {
                window.deleteModal.style.display = 'block';
                
                // Close any open dropdowns
                document.querySelectorAll('.dropdown-menu').forEach(dropdown => {
                    dropdown.classList.remove('show');
                });
            } else {
                console.error('Delete modal element not found');
            }
        }
        
        // Close delete modal
        function closeDeleteModal() {
            if (window.deleteModal) {
                window.deleteModal.style.display = 'none';
            }
        }
        
        // Open add modal
        function openAddModal() {
            if (window.addModal) {
                window.addModal.style.display = 'block';
            } else {
                console.error('Add modal element not found');
            }
        }
        
        // Close add modal
        function closeAddModal() {
            if (window.addModal) {
                window.addModal.style.display = 'none';
            }
        }
        
        // Event listeners are now added in the DOMContentLoaded event
    </script>
    <!-- Edit Barangay Modal -->
    <div id="editBarangayModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Edit Barangay</h2>
            <form id="editBarangayForm" method="post" action="update_barangay.php" enctype="multipart/form-data">
                <input type="hidden" id="edit_barangay_id" name="barangay_id">
                
                <div class="form-group">
                    <label for="edit_barangay_name">Barangay Name:</label>
                    <input type="text" id="edit_barangay_name" name="barangay_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="barangay_logo">Upload New Logo:</label>
                    <input type="file" id="barangay_logo" name="barangay_logo" class="form-control" accept="image/*">
                    <small class="form-text">Leave empty to keep current logo</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn save-btn">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <button type="button" class="btn cancel-btn" onclick="closeEditModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteBarangayModal" class="modal">
        <div class="modal-content delete-modal">
            <span class="close-modal" onclick="closeDeleteModal()">&times;</span>
            <h2>Delete Barangay</h2>
            <p class="delete-message">Are you sure you want to delete <span id="delete_barangay_name"></span>?</p>
            <p class="delete-warning">This action cannot be undone.</p>
            
            <form id="deleteBarangayForm" method="post" action="delete_barangay.php">
                <input type="hidden" id="delete_barangay_id" name="barangay_id">
                
                <div class="form-actions">
                    <button type="submit" class="btn delete-confirm-btn">
                        <i class="fas fa-trash-alt"></i> Delete
                    </button>
                    <button type="button" class="btn cancel-btn" onclick="closeDeleteModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Add Barangay Modal -->
    <div id="addBarangayModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Add New Barangay</h2>
            <form id="addBarangayForm" method="post" action="add_barangay.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="barangay_name">Barangay Name:</label>
                    <input type="text" id="barangay_name" name="barangay_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="add_barangay_logo">Upload Logo:</label>
                    <input type="file" id="add_barangay_logo" name="barangay_logo" class="form-control" accept="image/*">
                    <small class="form-text">Optional. Default logo will be used if none provided.</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn save-btn">
                        <i class="fas fa-plus"></i> Add Barangay
                    </button>
                    <button type="button" class="btn cancel-btn" onclick="closeAddModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>