<?php
// Start session
session_start();
require_once "../plugins/conn.php";

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_page/index.php");
    exit;
}

// Check if user has admin role
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    // Redirect to appropriate page based on role
    if (isset($_SESSION["role"]) && $_SESSION["role"] === "super_admin") {
        header("location: ../super_admin/super_admin_dashboard.php");
    } else {
        header("location: ../index.php");
    }
    exit;
}

// Get the admin's assigned barangay from session
// If not in session, fetch from database as fallback
if (isset($_SESSION["barangay"]) && isset($_SESSION["barangay_id"])) {
    $admin_barangay = $_SESSION["barangay"];
    $admin_barangay_id = $_SESSION["barangay_id"];
} else {
    // Fallback to database query if session variables not set
    $admin_id = $_SESSION["id"] ?? 0;
    $barangay_query = "SELECT u.barangay, b.barangay_id 
                      FROM users u 
                      LEFT JOIN barangay b ON u.barangay = b.barangay 
                      WHERE u.admin_id = ?";
    $stmt = mysqli_prepare($conn, $barangay_query);
    mysqli_stmt_bind_param($stmt, "i", $admin_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $admin_data = mysqli_fetch_assoc($result);
    $admin_barangay = $admin_data['barangay'] ?? 'Sample Barangay'; // Default to Sample Barangay if not set
    $admin_barangay_id = $admin_data['barangay_id'] ?? 0; // Default to 0 if not set
    
    // Store in session for future use
    $_SESSION["barangay"] = $admin_barangay;
    $_SESSION["barangay_id"] = $admin_barangay_id;
}

// Set page title for navbar
$page_title = "Household List";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="../src/design-system.css">
    <link rel="stylesheet" href="../src/responsive-fixes.css">
    <link rel="stylesheet" href="../css/global-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Dashboard styles */
        
        /* Register Household Button */
        .register-household-btn button { 
            align-items: center; 
            color: #ffffff; 
            font-size: 14px; 
            background: hsl(197, 100.00%, 50.00%); 
            border: none !important; 
            padding: 6px 10px; 
            box-shadow: 0 3px 0 hsl(197, 100.00%, 50.00%); 
            border-radius: 8px; 
            cursor: pointer; 
            display: flex;
            align-items: center;
            white-space: nowrap;
        } 
        
        .register-household-btn button i {
            margin-right: 6px;
        }
        
        .register-household-btn button:active { 
            box-shadow: none; 
            transform: translate(0, 4px); 
        }
        
        /* View Details Button - Matching Register Household Button styling */
        .view-details-btn button { 
            align-items: center; 
            color: #ffffff; 
            font-size: 14px; 
            background: hsl(197, 100.00%, 50.00%); 
            border: none !important; 
            padding: 6px 10px; 
            box-shadow: 0 3px 0 hsl(197, 100.00%, 50.00%); 
            border-radius: 8px; 
            cursor: pointer; 
            display: flex;
            align-items: center;
            white-space: nowrap;
        } 
        
        .view-details-btn button i {
            margin-right: 6px;
        }
        
        .view-details-btn button:active { 
            box-shadow: none; 
            transform: translate(0, 4px); 
        }
        
        body {
            font-family: var(--font-family);
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: var(--neutral-900);
        }
        
        .main-content {
            margin-left: 220px;
            padding: var(--spacing-3) var(--spacing-6);
            transition: margin 0.3s ease;
            margin-top: 10px;
            background-color: #f5f5f5;
            min-height: calc(100vh - 40px); 
        }
        
        .main-content.expanded {
            margin-left: 70px;
            margin-top: 40px;
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: var(--spacing-2); /* Further reduced padding on mobile */
                margin-top: 40px; /* Reduced from 50px */
            }
            
            .main-content.expanded {
                margin-left: 0;
            }
            
            .main-content.sidebar-mobile-open {
                margin-left: 240px;
                margin-top: 40px; /* Reduced from 50px */
            }
        }
        
        .page-header {
            margin-bottom: var(--spacing-3); /* Reduced from spacing-4 */
            padding-top: var(--spacing-1); /* Reduced from spacing-2 */
            padding-bottom: var(--spacing-1); /* Reduced from spacing-2 */
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            margin-bottom: var(--spacing-1); /* Reduced from spacing-2 */
            font-size: var(--font-size-sm);
            color: var(--neutral-500);
            font-weight: var(--font-weight-medium);
        }
        
        .breadcrumb a {
            color: var(--primary-600);
            text-decoration: none;
            transition: color 0.2s ease;
        }
        
        .breadcrumb a:hover {
            color: var(--primary-700);
            text-decoration: underline;
        }
        
        .breadcrumb-separator {
            margin: 0 var(--spacing-2);
            color: var(--neutral-400);
        }
        
        .page-title {
            font-size: var(--font-size-3xl);
            font-weight: var(--font-weight-bold);
            color: var(--neutral-900);
            margin-bottom: var(--spacing-1); /* Reduced from spacing-2 */
            letter-spacing: -0.5px;
        }
        
        .page-subtitle {
            font-size: var(--font-size-lg);
            color: var(--neutral-600);
            margin-bottom: var(--spacing-4);
            font-weight: var(--font-weight-normal);
        }
        
        .page-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--spacing-2); /* Reduced from spacing-3 */
        }
        
        /* Search components removed */
        
        /* Action buttons removed */
        
        .card {
            background-color: var(--white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            padding: var(--spacing-3); /* Reduced from spacing-4 */
            margin-bottom: var(--spacing-5); /* Reduced from spacing-6 */
            transition: all 0.3s ease;
            border: 1px solid var(--neutral-100);
            position: relative;
            overflow: hidden;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, var(--primary), var(--primary-600));
            opacity: 0.8;
        }
        
        .card:hover {
            box-shadow: var(--shadow-xl);
            transform: translateY(-2px);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--spacing-2); /* Reduced from spacing-3 */
            padding-bottom: var(--spacing-2); /* Reduced from spacing-3 */
            border-bottom: 1px solid var(--neutral-100);
        }
        
        .card-title {
            font-size: var(--font-size-xl);
            font-weight: var(--font-weight-bold);
            color: var(--neutral-900);
            display: flex;
            align-items: center;
            letter-spacing: -0.3px;
        }
        
        .card-title i {
            margin-right: var(--spacing-3);
            color: var(--primary-600);
            font-size: 1.2em;
        }
        
        .card-actions {
            display: flex;
            gap: var(--spacing-2);
        }
        
        /* Card action buttons removed */
        
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: var(--spacing-5);
            box-shadow: var(--shadow-sm);
            border-radius: var(--radius-lg);
            overflow: hidden;
        }
        
        .data-table th {
            background-color: var(--neutral-100);
            padding: var(--spacing-4) var(--spacing-5);
            text-align: left;
            font-weight: var(--font-weight-bold);
            color: var(--neutral-800);
            border-bottom: 2px solid var(--neutral-200);
            position: relative;
            font-size: var(--font-size-sm);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .data-table th:first-child {
            border-top-left-radius: var(--radius-lg);
            padding-left: var(--spacing-6);
        }
        
        .data-table th:last-child {
            border-top-right-radius: var(--radius-lg);
            padding-right: var(--spacing-6);
        }
        
        .data-table td {
            padding: var(--spacing-4) var(--spacing-5);
            border-bottom: 1px solid var(--neutral-100);
            color: var(--neutral-800);
            font-size: var(--font-size-sm);
            transition: all 0.2s ease;
        }
        
        .data-table td:first-child {
            padding-left: var(--spacing-6);
            font-weight: var(--font-weight-medium);
        }
        
        .data-table td:last-child {
            padding-right: var(--spacing-6);
        }
        
        .data-table tr:last-child td:first-child {
            border-bottom-left-radius: var(--radius-lg);
        }
        
        .data-table tr:last-child td:last-child {
            border-bottom-right-radius: var(--radius-lg);
        }
        
        .data-table tr {
            transition: all 0.2s ease;
        }
        
        .data-table tr:hover {
            background-color: var(--neutral-50);
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }
        
        .data-table .status-badge {
            display: inline-flex;
            align-items: center;
            padding: var(--spacing-1) var(--spacing-3);
            border-radius: var(--radius-full);
            font-size: var(--font-size-xs);
            font-weight: var(--font-weight-semibold);
            position: relative;
            box-shadow: var(--shadow-xs);
        }
        
        .data-table .status-badge::before {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            margin-right: var(--spacing-2);
        }
        
        .data-table .status-badge.active {
            background-color: rgba(var(--success-rgb), 0.15);
            color: var(--success-dark);
        }
        
        .data-table .status-badge.active::before {
            background-color: var(--success);
            box-shadow: 0 0 0 2px rgba(var(--success-rgb), 0.2);
        }
        
        .data-table .status-badge.inactive {
            background-color: rgba(var(--warning-rgb), 0.15);
            color: var(--warning-dark);
        }
        
        .data-table .status-badge.inactive::before {
            background-color: var(--warning);
            box-shadow: 0 0 0 2px rgba(var(--warning-rgb), 0.2);
        }
        
        .data-table .status-badge.pending {
            background-color: rgba(var(--info-rgb), 0.15);
            color: var(--info-dark);
        }
        
        .data-table .status-badge.pending::before {
            background-color: var(--info);
            box-shadow: 0 0 0 2px rgba(var(--info-rgb), 0.2);
        }
        

        
        /* Icon buttons removed */
        
        /* View buttons removed */
        
        .no-households {
            text-align: center;
            padding: var(--spacing-8);
            color: var(--neutral-600);
            font-weight: var(--font-weight-medium);
            background-color: var(--neutral-50);
            border-radius: var(--radius-lg);
            border: 1px dashed var(--neutral-300);
            margin: var(--spacing-6) 0;
        }
        
        .no-households i {
            display: block;
            font-size: 2.5rem;
            color: var(--neutral-400);
            margin-bottom: var(--spacing-4);
        }
        
        /* Pagination removed */
         
         /* Pagination hover effect removed */
        
        /* Pagination active state removed */
        
        /* Search and Filter Styles removed */
        
        /* Modal Styles */
        .modal {
            display: none; /* Initially hidden, will be changed to flex when shown */
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s ease;
        }
        
        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background-color: var(--white);
            margin: 0;
            width: 90%;
            max-width: 1000px;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
            animation: slideDown 0.3s ease;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
            z-index: 1001; /* Higher than modal backdrop */
            outline: none; /* Remove default focus outline */
            transform-origin: center top;
        }
        
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: var(--spacing-6);
            border-bottom: 1px solid var(--neutral-200);
            background: linear-gradient(to right, var(--primary-100), var(--primary-50), var(--white));
            border-radius: var(--radius-xl) var(--radius-xl) 0 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .modal-header h2 {
            font-size: var(--font-size-xl);
            font-weight: var(--font-weight-bold);
            color: var(--neutral-900);
            margin: 0;
            display: flex;
            align-items: center;
            text-shadow: 0 1px 1px rgba(255, 255, 255, 0.8);
            letter-spacing: 0.5px;
        }
        
        .modal-header h2 i {
            margin-right: var(--spacing-3);
            color: var(--primary-600);
            font-size: 1.2em;
            filter: drop-shadow(0 1px 1px rgba(0, 0, 0, 0.1));
        }
        
        .modal-body {
            padding: var(--spacing-6);
            overflow-y: auto;
            flex: 1;
            background-color: var(--white);
            background-image: linear-gradient(to bottom, rgba(var(--primary-rgb), 0.02), rgba(var(--primary-rgb), 0));
        }
        
        .close {
            color: var(--neutral-500);
            font-size: 28px;
            font-weight: var(--font-weight-bold);
            cursor: pointer;
            transition: all 0.2s ease;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background-color: var(--neutral-50);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .close:hover {
            color: var(--danger);
            background-color: var(--white);
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        
        .close:active {
            transform: translateY(0);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: var(--spacing-5);
        }
        
        .form-group label {
            display: block;
            margin-bottom: var(--spacing-2);
            font-weight: var(--font-weight-medium);
            color: var(--neutral-800);
            font-size: var(--font-size-sm);
            position: relative;
            padding-left: var(--spacing-3);
        }
        
        .form-group label:before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background-color: var(--primary-400);
        }
        
        .section-header {
            margin-top: var(--spacing-6);
            margin-bottom: var(--spacing-4);
            border-bottom: 1px solid var(--neutral-200);
            padding-bottom: var(--spacing-2);
            position: relative;
        }
        
        .section-header:before {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--primary-500);
            border-radius: 3px;
        }
        
        .section-header h3 {
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-semibold);
            color: var(--primary-700);
            margin: 0;
            display: flex;
            align-items: center;
        }
        
        .section-header h3:before {
            content: '•';
            margin-right: var(--spacing-2);
            color: var(--primary-500);
            font-size: 1.2em;
        }
        
        .form-row {
            display: flex;
            gap: var(--spacing-5);
            margin-bottom: var(--spacing-5);
        }
        
        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }
        
        .form-row + .section-header {
            margin-top: var(--spacing-6);
        }
        

        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: var(--spacing-3) var(--spacing-4);
            border: 1px solid var(--neutral-200);
            border-radius: var(--radius-lg);
            font-size: var(--font-size-base);
            transition: all 0.2s ease;
            background-color: var(--white);
            color: var(--neutral-900);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        
        .form-group input:hover,
        .form-group select:hover,
        .form-group textarea:hover {
            border-color: var(--primary-300);
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-400);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.15);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .required {
            color: var(--danger);
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: var(--spacing-4);
            margin-top: var(--spacing-6);
            padding-top: var(--spacing-5);
            padding-bottom: var(--spacing-2);
            border-top: 1px solid var(--neutral-200);
            background-color: var(--neutral-50);
            padding-right: var(--spacing-5);
            margin-left: calc(-1 * var(--spacing-5));
            margin-right: calc(-1 * var(--spacing-5));
            border-radius: 0 0 var(--radius-xl) var(--radius-xl);
        }
        
        .submit-btn {
            background-color: var(--success);
            color: white;
            border: none;
            padding: var(--spacing-3) var(--spacing-6);
            border-radius: var(--radius-lg);
            font-weight: var(--font-weight-medium);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .submit-btn:hover {
            background-color: var(--success-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        .submit-btn:active {
            transform: translateY(0);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .cancel-btn {
            background-color: var(--neutral-200);
            color: var(--neutral-700);
            border: none;
            padding: var(--spacing-3) var(--spacing-6);
            border-radius: var(--radius-lg);
            font-weight: var(--font-weight-medium);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .cancel-btn:hover {
            background-color: var(--neutral-300);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .cancel-btn:active {
            transform: translateY(0);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        
        /* Animation keyframes */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideDown {
            from { transform: translateY(-30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        /* Responsive adjustments for the modal */
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                max-height: 95vh;
                border-radius: var(--radius-lg);
            }
            
            .modal-header {
                padding: var(--spacing-4);
            }
            
            .modal-body {
                padding: var(--spacing-4);
            }
            
            .form-row {
                flex-direction: column;
                gap: var(--spacing-3);
                margin-bottom: var(--spacing-4);
            }
            
            .form-row .form-group {
                margin-bottom: var(--spacing-3);
            }
            
            .form-actions {
                padding: var(--spacing-4);
                margin-top: var(--spacing-4);
                flex-direction: column;
                gap: var(--spacing-3);
            }
            
            .submit-btn, .cancel-btn, .auto-fill-btn {
                width: 100%;
                justify-content: center;
            }
        }
        
        /* Auto Fill Button */
        .auto-fill-btn {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: var(--spacing-3) var(--spacing-6);
            border-radius: var(--radius-lg);
            font-weight: var(--font-weight-medium);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .auto-fill-btn:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        .auto-fill-btn:active {
            transform: translateY(0);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        /* Ensure proper tab order and focus styles */
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus,
        .submit-btn:focus,
        .cancel-btn:focus,
        .auto-fill-btn:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.3);
            border-color: var(--primary-500);
        }
        
        /* Error state handling */
        .form-group.error input,
        .form-group.error select,
        .form-group.error textarea {
            border-color: var(--danger);
            background-color: rgba(var(--danger-rgb), 0.05);
        }
        
        .error-message {
            color: var(--danger);
            font-size: var(--font-size-sm);
            margin-top: var(--spacing-1);
            display: none;
        }
        
        .form-group.error .error-message {
            display: block;
        }
        
        /* Register Member Button */
        .register-member-btn {
            background-color: var(--success) !important;
            box-shadow: 0 3px 0 var(--success-dark) !important;
        }
        
        .register-member-btn:hover {
            background-color: var(--success-dark) !important;
        }
    </style>
</head>
<body>
    <?php
    // Include the sidebar
    include '../includes/sidebar.php';
    
    // Include the navbar
    include '../admin/admin_navbar.php';
    ?>
    
    <div class="main-content" id="main-content">
 
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success" style="margin-bottom: var(--spacing-6); padding: var(--spacing-5); background-color: var(--success-light); color: var(--success-dark); border-radius: var(--radius-lg); box-shadow: var(--shadow-md); position: relative; overflow: hidden; display: flex; align-items: center;">
            <div style="position: absolute; top: 0; left: 0; width: 4px; height: 100%; background-color: var(--success);"></div>
            <i class="fas fa-check-circle" style="margin-right: var(--spacing-3); font-size: 1.2em; color: var(--success);"></i>
            <div>
                <strong style="font-weight: var(--font-weight-bold); font-size: var(--font-size-lg); display: block; margin-bottom: var(--spacing-1);">Success!</strong>
                <span style="font-weight: var(--font-weight-medium);"><?php echo htmlspecialchars($_SESSION['success']); ?></span>
            </div>
        </div>
        <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error" style="margin-bottom: var(--spacing-6); padding: var(--spacing-5); background-color: #FEF2F2; color: #B91C1C; border-radius: var(--radius-lg); box-shadow: var(--shadow-md); position: relative; overflow: hidden; display: flex; align-items: center;">
            <div style="position: absolute; top: 0; left: 0; width: 4px; height: 100%; background-color: #DC2626;"></div>
            <i class="fas fa-exclamation-circle" style="margin-right: var(--spacing-3); font-size: 1.2em; color: #DC2626;"></i>
            <div>
                <strong style="font-weight: var(--font-weight-bold); font-size: var(--font-size-lg); display: block; margin-bottom: var(--spacing-1);">Error</strong>
                <span style="font-weight: var(--font-weight-medium);"><?php echo htmlspecialchars($_SESSION['error']); ?></span>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-home"></i> Registered Households in <?php echo htmlspecialchars($admin_barangay); ?></h2>
                <div class="card-actions">
                    <a href="add_household.php" class="register-household-btn">
                        <button><i class="fas fa-plus-circle"></i> Register Household</button>
                    </a>
                </div>
            </div>
            
            <!-- Search and filter container removed -->
            
            <?php
            // Pagination setup
            $records_per_page = 10;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $records_per_page;
            
            // Query to get households for the admin's barangay with barangay name from barangay table
            $query = "SELECT ss.*, b.barangay as barangay_name 
                      FROM survey_session ss 
                      LEFT JOIN barangay b ON ss.barangay_id = b.barangay_id 
                      WHERE ss.barangay = ? OR b.barangay = ? OR ss.barangay_id = ? 
                      ORDER BY ss.session_id DESC LIMIT ?, ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "ssiii", $admin_barangay, $admin_barangay, $admin_barangay_id, $offset, $records_per_page);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            // Count total records for pagination
            $count_query = "SELECT COUNT(*) as total 
                           FROM survey_session ss 
                           LEFT JOIN barangay b ON ss.barangay_id = b.barangay_id 
                           WHERE ss.barangay = ? OR b.barangay = ? OR ss.barangay_id = ?";
            $count_stmt = mysqli_prepare($conn, $count_query);
            mysqli_stmt_bind_param($count_stmt, "ssi", $admin_barangay, $admin_barangay, $admin_barangay_id);
            mysqli_stmt_execute($count_stmt);
            $count_result = mysqli_stmt_get_result($count_stmt);
            $count_data = mysqli_fetch_assoc($count_result);
            $total_records = $count_data['total'];
            $total_pages = ceil($total_records / $records_per_page);
            
            if (mysqli_num_rows($result) > 0) {
                echo '<table class="data-table" id="householdTable">';
                echo '<thead>';
                echo '<tr>';
                echo '<th>Household Number</th>';
                echo '<th>Household Head</th>';
                echo '<th>Address</th>';
                echo '<th>Total Members</th>';
                echo '<th>Visit Date</th>';
                echo '<th>Status</th>';
                echo '<th>Actions</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
                
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<tr>';
                    echo '<td>' . (isset($row['household_number']) ? '#' . htmlspecialchars(str_pad($row['household_number'], 6, '0', STR_PAD_LEFT)) : 'Not assigned') . '</td>';
                    echo '<td>' . htmlspecialchars($row['household_head']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['address']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['total_members']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['visit_date']) . '</td>';
                    echo '<td><span class="status-badge ' . (strtolower($row['result']) == 'complete' ? 'active' : 'inactive') . '">' . htmlspecialchars($row['result']) . '</span></td>';                    
                    echo '<td class="action-buttons" style="display: flex; gap: 8px;">';
                    echo '<a href="view_household_details.php?id=' . $row['session_id'] . '" class="view-details-btn">';
                    echo '<button><i class="fas fa-eye"></i> View Details</button>';
                    echo '</a>';
                    echo '<button class="view-details-btn register-member-btn" onclick="openMemberModal(' . $row['session_id'] . ')"><i class="fas fa-user-plus"></i> Register Members</button>';
                    echo '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody>';
                echo '</table>';
                
                // Pagination links removed
            } else {
                echo '<div class="no-households"><i class="fas fa-home"></i>No households registered in this barangay yet.</div>';
            }
            ?>
        </div>
    </div>
    
    <!-- Member Registration Modal -->
    <div id="memberModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-plus"></i> Register Household Member</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="memberRegistrationForm" method="post" action="process_member_registration.php">
                    <input type="hidden" id="household_id" name="household_id">
                    
                    <!-- Basic Information Section -->
                    <div class="section-header">
                        <h3>Basic Information</h3>
                    </div>
                    
                    <div class="form-group">
                        <label for="name">Full Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="relationship">Relationship to Household Head <span class="required">*</span></label>
                            <select id="relationship" name="relationship" required>
                                <option value="">Select Relationship</option>
                                <option value="01">01 = Head</option>
                                <option value="02">02 = Spouse</option>
                                <option value="03">03 = Son</option>
                                <option value="04">04 = Daughter</option>
                                <option value="05">05 = Stepson</option>
                                <option value="06">06 = Stepdaughter</option>
                                <option value="07">07 = Son-in-law</option>
                                <option value="08">08 = Daughter-in-law</option>
                                <option value="09">09 = Grandson</option>
                                <option value="10">10 = Granddaughter</option>
                                <option value="11">11 = Father</option>
                                <option value="12">12 = Mother</option>
                                <option value="13">13 = Brother</option>
                                <option value="14">14 = Sister</option>
                                <option value="15">15 = Uncle</option>
                                <option value="16">16 = Aunt</option>
                                <option value="17">17 = Nephew</option>
                                <option value="18">18 = Niece</option>
                                <option value="19">19 = Other relative</option>
                                <option value="20">20 = Non-relative</option>
                                <option value="21">21 = Boarder</option>
                                <option value="22">22 = Domestic helper</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="sex">Sex <span class="required">*</span></label>
                            <select id="sex" name="sex" required>
                                <option value="">Select Sex</option>
                                <option value="1">Male</option>
                                <option value="2">Female</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="age">Age <span class="required">*</span></label>
                            <input type="number" id="age" name="age" min="0" max="120" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="date_of_birth">Date of Birth <span class="required">*</span></label>
                            <input type="date" id="date_of_birth" name="date_of_birth" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="place_of_birth">Place of Birth</label>
                        <input type="text" id="place_of_birth" name="place_of_birth">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nationality">Nationality</label>
                            <input type="text" id="nationality" name="nationality" value="Filipino">
                        </div>
                        
                        <div class="form-group">
                            <label for="marital_status">Marital Status</label>
                            <select id="marital_status" name="marital_status">
                                <option value="">Select Status</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Living-in">Living-in</option>
                                <option value="Widowed">Widowed</option>
                                <option value="Separated">Separated</option>
                                <option value="Annulled">Annulled</option>
                                <option value="Unknown">Unknown</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="religion">Religion</label>
                            <input type="text" id="religion" name="religion">
                        </div>
                        
                        <div class="form-group">
                            <label for="ethnicity">Ethnicity</label>
                            <input type="text" id="ethnicity" name="ethnicity">
                        </div>
                    </div>
                    
                    <!-- Education Section -->
                    <div class="section-header">
                        <h3>Education</h3>
                    </div>
                    
                    <div class="form-group">
                        <label for="highest_education">Highest Educational Attainment</label>
                        <select id="highest_education" name="highest_education">
                            <option value="">Select Education Level</option>
                            <option value="No education">No education</option>
                            <option value="Pre-school">Pre-school</option>
                            <option value="Elementary level">Elementary level</option>
                            <option value="Elementary graduate">Elementary graduate</option>
                            <option value="High school level">High school level</option>
                            <option value="High school graduate">High school graduate</option>
                            <option value="Junior HS level">Junior HS level</option>
                            <option value="Junior HS graduate">Junior HS graduate</option>
                            <option value="Senior HS level">Senior HS level</option>
                            <option value="Senior HS graduate">Senior HS graduate</option>
                            <option value="Vocational/Tech">Vocational/Tech</option>
                            <option value="College level">College level</option>
                            <option value="College graduate">College graduate</option>
                            <option value="Post-graduate">Post-graduate</option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="currently_enrolled">Currently Enrolled?</label>
                            <select id="currently_enrolled" name="currently_enrolled" onchange="toggleSchoolFields()">
                                <option value="">Select Option</option>
                                <option value="Yes, public">Yes, public</option>
                                <option value="Yes, private">Yes, private</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="school_level">School Level (if enrolled)</label>
                            <select id="school_level" name="school_level" class="school-field">
                                <option value="">Select Level</option>
                                <option value="Pre-school">Pre-school</option>
                                <option value="Elementary">Elementary</option>
                                <option value="Junior High School">Junior High School</option>
                                <option value="Senior High School">Senior High School</option>
                                <option value="Vocational/Technical">Vocational/Technical</option>
                                <option value="College/University">College/University</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="place_of_school">Place of School</label>
                        <input type="text" id="place_of_school" name="place_of_school">
                    </div>
                    
                    <!-- Employment Section -->
                    <div class="section-header">
                        <h3>Employment & Income</h3>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="q15_monthly_income">Monthly Income</label>
                            <select id="q15_monthly_income" name="q15_monthly_income">
                                <option value="">Select Income Range</option>
                                <option value="Below 5,000">Below 5,000</option>
                                <option value="5,000 - 10,000">5,000 - 10,000</option>
                                <option value="10,001 - 15,000">10,001 - 15,000</option>
                                <option value="15,001 - 20,000">15,001 - 20,000</option>
                                <option value="20,001 - 30,000">20,001 - 30,000</option>
                                <option value="30,001 - 40,000">30,001 - 40,000</option>
                                <option value="40,001 - 50,000">40,001 - 50,000</option>
                                <option value="Above 50,000">Above 50,000</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="q15a_source_of_income">Source of Income</label>
                            <select id="q15a_source_of_income" name="q15a_source_of_income">
                                <option value="">Select Source</option>
                                <option value="Employment">Employment</option>
                                <option value="Business">Business</option>
                                <option value="Remittance">Remittance</option>
                                <option value="Investments">Investments</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="q15b_occupation">Occupation</label>
                            <input type="text" id="q15b_occupation" name="q15b_occupation">
                        </div>
                        
                        <div class="form-group">
                            <label for="q17_work_status">Work Status</label>
                            <select id="q17_work_status" name="q17_work_status">
                                <option value="">Select Status</option>
                                <option value="Permanent Work">Permanent Work</option>
                                <option value="Casual Work">Casual Work</option>
                                <option value="Contractual Work">Contractual Work</option>
                                <option value="Individually Owned Business">Individually Owned Business</option>
                                <option value="Shared/Partnership Business">Shared/Partnership Business</option>
                                <option value="Corporate Business">Corporate Business</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="q18_place_of_work">Place of Work</label>
                        <input type="text" id="q18_place_of_work" name="q18_place_of_work">
                    </div>
                    
                    <!-- Health Section -->
                    <div class="section-header">
                        <h3>Health Information</h3>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="q19_place_of_delivery">Place of Delivery (if applicable)</label>
                            <input type="text" id="q19_place_of_delivery" name="q19_place_of_delivery">
                        </div>
                        
                        <div class="form-group">
                            <label for="q20_birth_attendant">Birth Attendant</label>
                            <input type="text" id="q20_birth_attendant" name="q20_birth_attendant">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="q21_immunization">Immunization Status</label>
                            <input type="text" id="q21_immunization" name="q21_immunization">
                        </div>
                        
                        <div class="form-group">
                            <label for="q22_living_children">Number of Living Children</label>
                            <input type="text" id="q22_living_children" name="q22_living_children">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="q23_fp_method">Family Planning Method</label>
                            <input type="text" id="q23_fp_method" name="q23_fp_method">
                        </div>
                        
                        <div class="form-group">
                            <label for="q24_source_of_fp">Source of Family Planning</label>
                            <input type="text" id="q24_source_of_fp" name="q24_source_of_fp">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="q25_intention_to_use_fp">Intention to Use Family Planning</label>
                            <input type="text" id="q25_intention_to_use_fp" name="q25_intention_to_use_fp">
                        </div>
                        
                        <div class="form-group">
                            <label for="q26_health_insurance">Health Insurance</label>
                            <input type="text" id="q26_health_insurance" name="q26_health_insurance">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="q27_facility_visited">Health Facility Visited</label>
                            <input type="text" id="q27_facility_visited" name="q27_facility_visited">
                        </div>
                        
                        <div class="form-group">
                            <label for="q28_reason_for_visit">Reason for Visit</label>
                            <input type="text" id="q28_reason_for_visit" name="q28_reason_for_visit">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="q29_disability">Disability (if any)</label>
                        <input type="text" id="q29_disability" name="q29_disability">
                    </div>
                    
                    <!-- Additional Information Section -->
                    <div class="section-header">
                        <h3>Additional Information</h3>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="q30_solo_parent_status">Solo Parent Status</label>
                            <select id="q30_solo_parent_status" name="q30_solo_parent_status">
                                <option value="">Select Status</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="q31_registered_senior">Registered Senior Citizen</label>
                            <select id="q31_registered_senior" name="q31_registered_senior">
                                <option value="">Select Option</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                                <option value="Not Applicable">Not Applicable</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="q32_registered_voter">Registered Voter</label>
                        <input type="text" id="q32_registered_voter" name="q32_registered_voter">
                    </div>
                    
                    <!-- Residency Section -->
                    <div class="section-header">
                        <h3>Residency Information</h3>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="q33_previous_residence_5yrs">Previous Residence (5 years ago)</label>
                            <input type="text" id="q33_previous_residence_5yrs" name="q33_previous_residence_5yrs">
                        </div>
                        
                        <div class="form-group">
                            <label for="q34_previous_residence_6mos">Previous Residence (6 months ago)</label>
                            <input type="text" id="q34_previous_residence_6mos" name="q34_previous_residence_6mos">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="q35_length_of_stay">Length of Stay</label>
                            <input type="text" id="q35_length_of_stay" name="q35_length_of_stay">
                        </div>
                        
                        <div class="form-group">
                            <label for="q36_type_of_resident">Type of Resident</label>
                            <select id="q36_type_of_resident" name="q36_type_of_resident">
                                <option value="">Select Type</option>
                                <option value="Permanent">Permanent</option>
                                <option value="Temporary">Temporary</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="q37_date_of_transfer">Date of Transfer (if applicable)</label>
                        <input type="text" id="q37_date_of_transfer" name="q37_date_of_transfer">
                    </div>
                    
                    <div class="form-group">
                        <label for="q38_reasons_for_leaving">Reasons for Leaving</label>
                        <textarea id="q38_reasons_for_leaving" name="q38_reasons_for_leaving" rows="2"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="q39_plan_to_return">Plan to Return</label>
                        <textarea id="q39_plan_to_return" name="q39_plan_to_return" rows="2"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="q40_reasons_for_transferring">Reasons for Transferring</label>
                        <textarea id="q40_reasons_for_transferring" name="q40_reasons_for_transferring" rows="2"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="q41_duration_of_stay">Duration of Stay</label>
                        <input type="text" id="q41_duration_of_stay" name="q41_duration_of_stay">
                    </div>
                    
                    <!-- Other Information Section -->
                    <div class="section-header">
                        <h3>Other Information</h3>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="q42a_ctc_possession">CTC Possession</label>
                            <select id="q42a_ctc_possession" name="q42a_ctc_possession">
                                <option value="">Select Option</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="q42b_ctc_issued_in_barangay">CTC Issued in Barangay</label>
                            <select id="q42b_ctc_issued_in_barangay" name="q42b_ctc_issued_in_barangay">
                                <option value="">Select Option</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="q43_skills_training_interest">Skills Training Interest</label>
                        <textarea id="q43_skills_training_interest" name="q43_skills_training_interest" rows="2"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="q44_skills">Skills</label>
                        <textarea id="q44_skills" name="q44_skills" rows="2"></textarea>
                    </div>
                    
                    <!-- Housing Information Section -->
                    <div class="section-header">
                        <h3>Housing Information</h3>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="q45_housing_tenure">Housing Tenure</label>
                            <input type="text" id="q45_housing_tenure" name="q45_housing_tenure">
                        </div>
                        
                        <div class="form-group">
                            <label for="q46_lot_tenure">Lot Tenure</label>
                            <input type="text" id="q46_lot_tenure" name="q46_lot_tenure">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="q47_fuel_for_lighting">Fuel for Lighting</label>
                            <input type="text" id="q47_fuel_for_lighting" name="q47_fuel_for_lighting">
                        </div>
                        
                        <div class="form-group">
                            <label for="q48_fuel_for_cooking">Fuel for Cooking</label>
                            <input type="text" id="q48_fuel_for_cooking" name="q48_fuel_for_cooking">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="q49_drinking_water_source">Drinking Water Source</label>
                        <input type="text" id="q49_drinking_water_source" name="q49_drinking_water_source">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="q50a_garbage_disposal_method">Garbage Disposal Method</label>
                            <input type="text" id="q50a_garbage_disposal_method" name="q50a_garbage_disposal_method">
                        </div>
                        
                        <div class="form-group">
                            <label for="q50b_garbage_segregation">Garbage Segregation</label>
                            <select id="q50b_garbage_segregation" name="q50b_garbage_segregation">
                                <option value="">Select Option</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="q51_toilet_facility">Toilet Facility</label>
                            <input type="text" id="q51_toilet_facility" name="q51_toilet_facility">
                        </div>
                        
                        <div class="form-group">
                            <label for="q52_building_type">Building Type</label>
                            <input type="text" id="q52_building_type" name="q52_building_type">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="q53_outer_wall_material">Outer Wall Material</label>
                        <input type="text" id="q53_outer_wall_material" name="q53_outer_wall_material">
                    </div>
                    
                    <!-- Health and Needs Section -->
                    <div class="section-header">
                        <h3>Health and Needs</h3>
                    </div>
                    
                    <div class="form-group">
                        <label for="q54_female_death_details">Female Death Details (if applicable)</label>
                        <textarea id="q54_female_death_details" name="q54_female_death_details" rows="2"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="q55_child_death_details">Child Death Details (if applicable)</label>
                        <textarea id="q55_child_death_details" name="q55_child_death_details" rows="2"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="q56_common_diseases">Common Diseases</label>
                        <textarea id="q56_common_diseases" name="q56_common_diseases" rows="2"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="q57_primary_needs">Primary Needs</label>
                        <textarea id="q57_primary_needs" name="q57_primary_needs" rows="2"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="q58_future_residence">Future Residence Plans</label>
                        <textarea id="q58_future_residence" name="q58_future_residence" rows="2"></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="submit-btn"><i class="fas fa-save"></i> Save Member</button>
                        <button type="button" class="cancel-btn" onclick="closeMemberModal()"><i class="fas fa-times"></i> Cancel</button>
                        <button type="button" class="auto-fill-btn" onclick="autoFillTestData()"><i class="fas fa-magic"></i> Auto Fill (Testing)</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Modal functionality
        const modal = document.getElementById("memberModal");
        const closeBtn = document.getElementsByClassName("close")[0];
        const householdIdInput = document.getElementById("household_id");
        
        // Open modal with household ID
        function openMemberModal(householdId) {
            modal.classList.add("show");
            householdIdInput.value = householdId;
            document.body.style.overflow = "hidden"; // Prevent scrolling behind modal
            setTimeout(() => {
                document.getElementById("name").focus(); // Focus on first field for accessibility
            }, 300);
        }
        
        // Close modal
        function closeMemberModal() {
            modal.classList.remove("show");
            document.body.style.overflow = ""; // Restore scrolling
        }
        
        // Close modal when clicking on X
        closeBtn.onclick = function() {
            closeMemberModal();
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target == modal) {
                closeMemberModal();
            }
        }
        
        // Form validation
        document.getElementById("memberRegistrationForm").addEventListener("submit", function(event) {
            const name = document.getElementById("name");
            const relationship = document.getElementById("relationship");
            const sex = document.getElementById("sex");
            const age = document.getElementById("age");
            const dateOfBirth = document.getElementById("date_of_birth");
            
            // Reset previous error states
            const formGroups = document.querySelectorAll('.form-group');
            formGroups.forEach(group => {
                group.classList.remove('error');
                const errorMsg = group.querySelector('.error-message');
                if (errorMsg) errorMsg.remove();
            });
            
            let isValid = true;
            
            // Validate name
            if (name.value.trim() === "") {
                isValid = false;
                showError(name, "Full name is required.");
            }
            
            // Validate relationship
            if (relationship.value === "") {
                isValid = false;
                showError(relationship, "Relationship to household head is required.");
            }
            
            // Validate sex
            if (sex.value === "") {
                isValid = false;
                showError(sex, "Sex is required.");
            }
            
            // Validate age
            if (age.value === "" || isNaN(age.value) || parseInt(age.value) < 0) {
                isValid = false;
                showError(age, "Valid age is required.");
            }
            
            // Validate date of birth
            if (dateOfBirth.value === "") {
                isValid = false;
                showError(dateOfBirth, "Date of birth is required.");
            }
            
            if (!isValid) {
                event.preventDefault();
                // Scroll to the first error
                const firstError = document.querySelector('.form-group.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
        
        // Helper function to show error messages
        function showError(inputElement, message) {
            const formGroup = inputElement.closest('.form-group');
            formGroup.classList.add('error');
            
            const errorMessage = document.createElement('div');
            errorMessage.className = 'error-message';
            errorMessage.textContent = message;
            formGroup.appendChild(errorMessage);
            
            // Add shake animation for visual feedback
            formGroup.style.animation = 'shake 0.5s';
            setTimeout(() => {
                formGroup.style.animation = '';
            }, 500);
        }
        
        // Toggle school fields based on enrollment status
        function toggleSchoolFields() {
            const enrollmentStatus = document.getElementById("currently_enrolled").value;
            const schoolFields = document.querySelectorAll(".school-field");
            
            schoolFields.forEach(field => {
                const fieldGroup = field.closest('.form-group');
                if (enrollmentStatus === "No") {
                    fieldGroup.style.display = "none";
                } else {
                    fieldGroup.style.display = "";
                }
            });
        }
        
        // Initialize school fields visibility
        document.addEventListener("DOMContentLoaded", function() {
            toggleSchoolFields();
        });
        
        // Helper functions for random data generation
        function getRandomInt(min, max) {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }
        
        function getRandomElement(array) {
            return array[Math.floor(Math.random() * array.length)];
        }
        
        function getWeightedRandom(options) {
            const totalWeight = options.reduce((sum, option) => sum + (option.weight || 1), 0);
            let random = Math.random() * totalWeight;
            
            for (const option of options) {
                random -= (option.weight || 1);
                if (random <= 0) return option.value;
            }
            
            return options[0].value; // Fallback
        }
        
        function generateRandomName() {
            const firstNames = [
                "Juan", "Maria", "Pedro", "Ana", "Jose", "Rosa", "Antonio", "Lourdes", "Ricardo", "Elena",
                "Francisco", "Josefa", "Carlos", "Luisa", "Miguel", "Teresa", "Rafael", "Juana", "Eduardo", "Margarita",
                "Roberto", "Rosario", "Manuel", "Cristina", "Alejandro", "Angelica", "Fernando", "Pilar", "Rodrigo", "Dolores"
            ];
            
            const lastNames = [
                "Dela Cruz", "Santos", "Reyes", "Garcia", "Mendoza", "Ramos", "Flores", "Gonzales", "Bautista", "Villanueva",
                "Fernandez", "Cruz", "De Guzman", "Lopez", "Perez", "Castillo", "Francisco", "Rivera", "Aquino", "Castro",
                "Domingo", "Martinez", "Torres", "Rodriguez", "Salazar", "Hernandez", "Pascual", "Dizon", "Marquez", "Mercado"
            ];
            
            const middleInitial = String.fromCharCode(65 + Math.floor(Math.random() * 26));
            
            return `${getRandomElement(firstNames)} ${middleInitial}. ${getRandomElement(lastNames)}`;
        }
        
        function generateRandomBirthDate(minAge, maxAge) {
            const today = new Date();
            const years = getRandomInt(minAge, maxAge);
            const birthDate = new Date(today);
            
            // Randomize month and day as well
            birthDate.setFullYear(today.getFullYear() - years);
            birthDate.setMonth(getRandomInt(0, 11));
            birthDate.setDate(getRandomInt(1, 28)); // Using 28 to avoid invalid dates
            
            return {
                date: birthDate,
                age: years,
                formatted: birthDate.toISOString().split('T')[0]
            };
        }
        
        function generatePlaceOfBirth() {
            const cities = [
                "Bacolod City", "Iloilo City", "Cebu City", "Manila", "Quezon City", "Davao City", 
                "Cagayan de Oro", "Baguio City", "Tacloban City", "Dumaguete City", "Roxas City",
                "Kabankalan City", "Silay City", "Talisay City", "Sagay City", "Cadiz City"
            ];
            return getRandomElement(cities);
        }
        
        // Auto fill form with test data
        function autoFillTestData() {
            // Generate a profile for consistency
            const personProfile = {
                name: generateRandomName(),
                birth: generateRandomBirthDate(1, 90), // Age 1-90
                birthplace: generatePlaceOfBirth(),
                isMale: Math.random() > 0.5
            };
            
            // Determine relationship based on household context
            const relationships = [
                {value: "01 = Head", weight: 1},
                {value: "02 = Spouse", weight: 1},
                {value: "03 = Son/Daughter", weight: 3},
                {value: "04 = Son/Daughter-in-law", weight: 1},
                {value: "05 = Grandchild", weight: 2},
                {value: "06 = Parent", weight: 0.5},
                {value: "07 = Parent-in-law", weight: 0.5},
                {value: "08 = Brother/Sister", weight: 1},
                {value: "09 = Other relative", weight: 1},
                {value: "10 = Non-relative", weight: 0.5},
                {value: "11 = Boarder", weight: 0.3},
                {value: "12 = Domestic helper", weight: 0.3},
            ];
            
            // Basic information
            document.getElementById("name").value = personProfile.name;
            document.getElementById("relationship").value = getWeightedRandom(relationships);
            document.getElementById("sex").value = personProfile.isMale ? "1" : "2";
            
            // Birth information
            document.getElementById("date_of_birth").value = personProfile.birth.formatted;
            document.getElementById("age").value = personProfile.birth.age;
            document.getElementById("place_of_birth").value = personProfile.birthplace;
            
            // Nationality (weighted heavily toward Filipino)
            const nationalities = [
                {value: "Filipino", weight: 95},
                {value: "American", weight: 1},
                {value: "Chinese", weight: 1},
                {value: "Korean", weight: 1},
                {value: "Japanese", weight: 1},
                {value: "Other", weight: 1}
            ];
            document.getElementById("nationality").value = getWeightedRandom(nationalities);
            
            // Marital status based on age
            let maritalOptions;
            if (personProfile.birth.age < 18) {
                maritalOptions = [{value: "Single", weight: 10}];
            } else if (personProfile.birth.age < 25) {
                maritalOptions = [
                    {value: "Single", weight: 7},
                    {value: "Married", weight: 2},
                    {value: "Live-in", weight: 1}
                ];
            } else if (personProfile.birth.age < 60) {
                maritalOptions = [
                    {value: "Single", weight: 2},
                    {value: "Married", weight: 6},
                    {value: "Widowed", weight: 1},
                    {value: "Separated/Divorced", weight: 1},
                    {value: "Live-in", weight: 1}
                ];
            } else {
                maritalOptions = [
                    {value: "Single", weight: 1},
                    {value: "Married", weight: 4},
                    {value: "Widowed", weight: 4},
                    {value: "Separated/Divorced", weight: 1}
                ];
            }
            document.getElementById("marital_status").value = getWeightedRandom(maritalOptions);
            
            // Religion
            const religions = [
                {value: "Roman Catholic", weight: 8},
                {value: "Islam", weight: 1},
                {value: "Iglesia ni Cristo", weight: 1},
                {value: "Born Again Christian", weight: 2},
                {value: "Seventh Day Adventist", weight: 1},
                {value: "Baptist", weight: 1},
                {value: "Methodist", weight: 1},
                {value: "Other Christian", weight: 1}
            ];
            document.getElementById("religion").value = getWeightedRandom(religions);
            
            // Ethnicity
            const ethnicities = [
                {value: "Ilonggo", weight: 7},
                {value: "Cebuano", weight: 3},
                {value: "Tagalog", weight: 2},
                {value: "Waray", weight: 1},
                {value: "Ilocano", weight: 1},
                {value: "Bicolano", weight: 1},
                {value: "Maguindanaon", weight: 0.5},
                {value: "Tausug", weight: 0.5}
            ];
            document.getElementById("ethnicity").value = getWeightedRandom(ethnicities);
            
            // Education fields - based on age
            let educationOptions;
            let enrollmentStatus = "No";
            let schoolLevel = "";
            
            if (personProfile.birth.age < 5) {
                educationOptions = [{value: "No education/Not applicable", weight: 1}];
            } else if (personProfile.birth.age < 7) {
                educationOptions = [
                    {value: "No education/Not applicable", weight: 1},
                    {value: "Pre-school", weight: 4}
                ];
                if (Math.random() > 0.3) {
                    enrollmentStatus = "Yes";
                    schoolLevel = "Pre-school";
                }
            } else if (personProfile.birth.age < 13) {
                educationOptions = [
                    {value: "Pre-school", weight: 1},
                    {value: "Elementary (1-6)", weight: 4}
                ];
                if (Math.random() > 0.1) {
                    enrollmentStatus = "Yes";
                    schoolLevel = "Elementary";
                }
            } else if (personProfile.birth.age < 18) {
                educationOptions = [
                    {value: "Elementary (1-6)", weight: 2},
                    {value: "Elementary graduate", weight: 1},
                    {value: "High School (1-4)", weight: 4}
                ];
                if (Math.random() > 0.2) {
                    enrollmentStatus = "Yes";
                    schoolLevel = "High School";
                }
            } else if (personProfile.birth.age < 23) {
                educationOptions = [
                    {value: "Elementary graduate", weight: 1},
                    {value: "High School (1-4)", weight: 1},
                    {value: "High School graduate", weight: 2},
                    {value: "Vocational", weight: 2},
                    {value: "College (1-4)", weight: 3},
                    {value: "College graduate", weight: 1}
                ];
                if (Math.random() > 0.6) {
                    enrollmentStatus = "Yes";
                    schoolLevel = "College/University";
                }
            } else {
                educationOptions = [
                    {value: "Elementary (1-6)", weight: 1},
                    {value: "Elementary graduate", weight: 2},
                    {value: "High School (1-4)", weight: 1},
                    {value: "High School graduate", weight: 3},
                    {value: "Vocational", weight: 2},
                    {value: "College (1-4)", weight: 2},
                    {value: "College graduate", weight: 3},
                    {value: "Post Graduate", weight: 1}
                ];
            }
            
            document.getElementById("highest_education").value = getWeightedRandom(educationOptions);
            document.getElementById("currently_enrolled").value = enrollmentStatus;
            
            // School information
            const schoolLevels = ["Pre-school", "Elementary", "High School", "College/University", "Vocational/Technical", "Graduate School"];
            if (!schoolLevel) {
                schoolLevel = getRandomElement(schoolLevels);
            }
            document.getElementById("school_level").value = schoolLevel;
            
            const schools = [
                "University of St. La Salle", "University of Negros Occidental-Recoletos", "Carlos Hilado Memorial State University",
                "West Negros University", "La Consolacion College", "STI College", "AMA Computer College",
                "Bacolod City National High School", "Negros Occidental High School", "Domingo Lacson National High School",
                "Education and Training Center School", "Handumanan Elementary School", "Paglaum Village Elementary School",
                "Bacolod City Elementary School", "St. John's Institute", "Colegio San Agustin"
            ];
            document.getElementById("place_of_school").value = getRandomElement(schools);
            toggleSchoolFields(); // Update visibility of school fields
            
            // Employment & Income - based on age
            let incomeOptions, sourceOptions, occupationOptions, workStatusOptions;
            
            if (personProfile.birth.age < 15) {
                // Children don't work
                incomeOptions = [{value: "None", weight: 1}];
                sourceOptions = [{value: "None", weight: 1}];
                occupationOptions = [{value: "None/Not applicable", weight: 1}];
                workStatusOptions = [{value: "Not applicable", weight: 1}];
            } else if (personProfile.birth.age < 18) {
                // Teens mostly don't work
                incomeOptions = [
                    {value: "None", weight: 8},
                    {value: "Below 5,000", weight: 2}
                ];
                sourceOptions = [
                    {value: "None", weight: 8},
                    {value: "Part-time work", weight: 2}
                ];
                occupationOptions = [
                    {value: "None/Not applicable", weight: 8},
                    {value: "Service worker", weight: 2}
                ];
                workStatusOptions = [
                    {value: "Not applicable", weight: 8},
                    {value: "Temporary Work", weight: 2}
                ];
            } else if (personProfile.birth.age < 60) {
                // Working age adults
                incomeOptions = [
                    {value: "None", weight: 1},
                    {value: "Below 5,000", weight: 2},
                    {value: "5,001 - 10,000", weight: 3},
                    {value: "10,001 - 15,000", weight: 3},
                    {value: "15,001 - 20,000", weight: 2},
                    {value: "20,001 - 25,000", weight: 1},
                    {value: "Above 25,000", weight: 1}
                ];
                sourceOptions = [
                    {value: "Employment", weight: 6},
                    {value: "Business", weight: 3},
                    {value: "Remittance", weight: 2},
                    {value: "Pension", weight: 1},
                    {value: "None", weight: 1}
                ];
                occupationOptions = [
                    {value: "Professional", weight: 2},
                    {value: "Technician", weight: 1},
                    {value: "Clerical worker", weight: 2},
                    {value: "Service worker", weight: 3},
                    {value: "Farmer/Fisher", weight: 2},
                    {value: "Craft worker", weight: 1},
                    {value: "Machine operator", weight: 1},
                    {value: "Laborer", weight: 2},
                    {value: "Business owner", weight: 1},
                    {value: "Driver", weight: 2},
                    {value: "Teacher", weight: 1},
                    {value: "Nurse", weight: 1},
                    {value: "None/Not applicable", weight: 1}
                ];
                workStatusOptions = [
                    {value: "Permanent Work", weight: 4},
                    {value: "Temporary Work", weight: 3},
                    {value: "Self-employed", weight: 2},
                    {value: "Unemployed", weight: 1},
                    {value: "Not applicable", weight: 1}
                ];
            } else {
                // Seniors
                incomeOptions = [
                    {value: "None", weight: 2},
                    {value: "Below 5,000", weight: 3},
                    {value: "5,001 - 10,000", weight: 2},
                    {value: "10,001 - 15,000", weight: 1},
                    {value: "15,001 - 20,000", weight: 1}
                ];
                sourceOptions = [
                    {value: "Pension", weight: 5},
                    {value: "Remittance", weight: 3},
                    {value: "Business", weight: 1},
                    {value: "Employment", weight: 1}
                ];
                occupationOptions = [
                    {value: "None/Not applicable", weight: 7},
                    {value: "Business owner", weight: 1},
                    {value: "Farmer/Fisher", weight: 1},
                    {value: "Professional", weight: 1}
                ];
                workStatusOptions = [
                    {value: "Not applicable", weight: 7},
                    {value: "Self-employed", weight: 2},
                    {value: "Permanent Work", weight: 1}
                ];
            }
            
            document.getElementById("q15_monthly_income").value = getWeightedRandom(incomeOptions);
            document.getElementById("q15a_source_of_income").value = getWeightedRandom(sourceOptions);
            document.getElementById("q15b_occupation").value = getWeightedRandom(occupationOptions);
            document.getElementById("q17_work_status").value = getWeightedRandom(workStatusOptions);
            
            // Place of work
            const workPlaces = [
                "Bacolod City", "Talisay City", "Silay City", "Bago City", "Murcia", "Metro Manila",
                "Cebu City", "Iloilo City", "Overseas", "Not applicable"
            ];
            document.getElementById("q18_place_of_work").value = getWeightedRandom([
                {value: "Not applicable", weight: personProfile.birth.age < 15 ? 10 : 1},
                {value: getRandomElement(workPlaces), weight: personProfile.birth.age >= 15 ? 10 : 0}
            ]);
            
            // Health Information - more relevant for women of childbearing age
            const deliveryPlaces = [
                "Bacolod Doctors Hospital", "Provincial Hospital", "City Health Center", "Barangay Health Center",
                "Home", "Private Hospital", "Public Hospital", "Not applicable"
            ];
            
            const birthAttendants = [
                "Doctor", "Nurse", "Midwife", "Traditional Birth Attendant (Hilot)", "None", "Not applicable"
            ];
            
            const immunizationStatus = ["Complete", "Incomplete", "None", "Not applicable"];
            
            // For women of childbearing age (15-49)
            if (!personProfile.isMale && personProfile.birth.age >= 15 && personProfile.birth.age <= 49) {
                document.getElementById("q19_place_of_delivery").value = getRandomElement(deliveryPlaces.filter(p => p !== "Not applicable"));
                document.getElementById("q20_birth_attendant").value = getRandomElement(birthAttendants.filter(a => a !== "Not applicable"));
                document.getElementById("q21_immunization").value = getRandomElement(immunizationStatus.filter(s => s !== "Not applicable"));
                document.getElementById("q22_living_children").value = getRandomInt(0, 5).toString();
                
                const fpMethods = [
                    "Pills", "IUD", "Injectables", "Condom", "Natural Family Planning", "Ligation", "Vasectomy", "None"
                ];
                document.getElementById("q23_fp_method").value = getRandomElement(fpMethods);
                
                const fpSources = [
                    "Health Center", "Public Hospital", "Private Hospital", "Pharmacy", "None"
                ];
                document.getElementById("q24_source_of_fp").value = getRandomElement(fpSources);
                document.getElementById("q25_intention_to_use_fp").value = getRandomElement(["Yes", "No"]);
            } else {
                // Not applicable for males or women outside childbearing age
                document.getElementById("q19_place_of_delivery").value = "Not applicable";
                document.getElementById("q20_birth_attendant").value = "Not applicable";
                document.getElementById("q21_immunization").value = "Not applicable";
                document.getElementById("q22_living_children").value = "0";
                document.getElementById("q23_fp_method").value = "None";
                document.getElementById("q24_source_of_fp").value = "None";
                document.getElementById("q25_intention_to_use_fp").value = "No";
            }
            
            // Housing Information
            const waterSources = [
                "Water District", "Deep Well", "Shallow Well", "Spring", "Rain", "River/Stream", "Peddler", "Bottled Water"
            ];
            document.getElementById("q49_drinking_water_source").value = getRandomElement(waterSources);
            
            const garbageMethods = [
                "Collected by Garbage Truck", "Burning", "Composting", "Burying", "Dumping in water bodies", "Open dumping"
            ];
            document.getElementById("q50a_garbage_disposal_method").value = getRandomElement(garbageMethods);
            document.getElementById("q50b_garbage_segregation").value = getRandomElement(["Yes", "No"]);
            
            const toiletFacilities = [
                "Water-sealed", "Closed pit", "Open pit", "None"
            ];
            document.getElementById("q51_toilet_facility").value = getRandomElement(toiletFacilities);
            
            const buildingTypes = [
                "Single House", "Duplex", "Multi-unit residential", "Commercial/Industrial/Agricultural", "Institutional", "Other"
            ];
            document.getElementById("q52_building_type").value = getRandomElement(buildingTypes);
            
            const wallMaterials = [
                "Concrete", "Wood", "Galvanized Iron/Aluminum", "Bamboo/Sawali/Cogon/Nipa", "Asbestos", "Glass", "Makeshift/Salvaged", "Other"
            ];
            document.getElementById("q53_outer_wall_material").value = getRandomElement(wallMaterials);
            
            // Health and Needs
            document.getElementById("q54_female_death_details").value = getRandomElement(["None", "1 case in the past year", "Not applicable"]);
            document.getElementById("q55_child_death_details").value = getRandomElement(["None", "1 case in the past year", "Not applicable"]);
            
            const diseases = [
                "None", "Common cold, flu", "Dengue", "Diarrhea", "Hypertension", "Diabetes", "Tuberculosis", 
                "Respiratory infections", "Skin diseases", "COVID-19", "Malaria", "Typhoid fever"
            ];
            document.getElementById("q56_common_diseases").value = getRandomElement(diseases);
            
            const primaryNeeds = [
                "Stable water supply", "Electricity", "Health services", "Education", "Livelihood", "Housing", 
                "Food security", "Road improvement", "Drainage system", "Waste management", "Security"
            ];
            document.getElementById("q57_primary_needs").value = getRandomElement(primaryNeeds);
            
            const futureResidence = [
                "Stay in current residence", "Move within the barangay", "Move to another barangay", 
                "Move to another city/municipality", "Move to another province", "Move abroad"
            ];
            document.getElementById("q58_future_residence").value = getRandomElement(futureResidence);
            
            // Show success notification
            const notification = document.createElement('div');
            notification.className = 'alert alert-success';
            notification.innerHTML = '<i class="fas fa-check-circle"></i> Form auto-filled with randomized test data!';
            notification.style.position = 'fixed';
            notification.style.top = '20px';
            notification.style.right = '20px';
            notification.style.zIndex = '9999';
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 500);
            }, 3000);
        }
        
        // Calculate age from date of birth
        document.getElementById("date_of_birth").addEventListener("change", function() {
            const dob = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - dob.getFullYear();
            const monthDiff = today.getMonth() - dob.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                age--;
            }
            
            document.getElementById("age").value = age;
        });
    </script>
    
 