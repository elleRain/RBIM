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
    $admin_barangay = $admin_data['barangay'] ?? 'Sample Barangay';
    $admin_barangay_id = $admin_data['barangay_id'] ?? 0;
    
    // Store in session for future use
    $_SESSION["barangay"] = $admin_barangay;
    $_SESSION["barangay_id"] = $admin_barangay_id;
}

// Error handling for missing or invalid household ID
$error_message = "";
$household_data = null;

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $error_message = "Household ID is required.";
} else {
    $household_id = intval($_GET['id']);
    
    // Fetch household details from survey_session table
    $sql = "SELECT * FROM survey_session WHERE session_id = ? AND barangay_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $household_id, $admin_barangay_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $household_data = mysqli_fetch_assoc($result);
        } else {
            $error_message = "Household not found or you don't have permission to view this household.";
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "Database error occurred while fetching household details.";
    }
}

// Set page title for navbar
$page_title = "Household Details";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Household Details - RBIM Census</title>

    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="../src/design-system.css">
    <link rel="stylesheet" href="../src/responsive-fixes.css">
    <link rel="stylesheet" href="../css/global-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Dashboard styles */
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
                padding: var(--spacing-2);
                margin-top: 40px;
            }
            
            .main-content.expanded {
                margin-left: 0;
            }
            
            .main-content.sidebar-mobile-open {
                margin-left: 240px;
                margin-top: 40px;
            }
        }
        
        .page-header {
            margin-bottom: var(--spacing-3);
            padding-top: var(--spacing-1);
            padding-bottom: var(--spacing-1);
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            margin-bottom: var(--spacing-1);
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
            margin-bottom: var(--spacing-1);
            letter-spacing: -0.5px;
        }
        
        .page-subtitle {
            font-size: var(--font-size-lg);
            color: var(--neutral-600);
            margin-bottom: var(--spacing-4);
            font-weight: var(--font-weight-normal);
        }
        
        .card {
            background-color: var(--white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            padding: var(--spacing-4);
            margin-bottom: var(--spacing-5);
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
            margin-bottom: var(--spacing-3);
            padding-bottom: var(--spacing-3);
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
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            color: var(--primary-600);
            text-decoration: none;
            font-weight: var(--font-weight-medium);
            padding: var(--spacing-2) var(--spacing-4);
            border-radius: var(--radius-lg);
            transition: all 0.2s ease;
            background-color: var(--primary-50);
            border: 1px solid var(--primary-200);
        }
        
        .back-btn:hover {
            background-color: var(--primary-100);
            color: var(--primary-700);
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }
        
        .back-btn i {
            margin-right: var(--spacing-2);
        }
        
        .details-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: var(--spacing-3);
            box-shadow: var(--shadow-sm);
            border-radius: var(--radius-lg);
            overflow: hidden;
        }
        
        .details-table th,
        .details-table td {
            padding: var(--spacing-4) var(--spacing-5);
            text-align: left;
            border-bottom: 1px solid var(--neutral-100);
        }
        
        .details-table th {
            background-color: var(--neutral-100);
            font-weight: var(--font-weight-bold);
            color: var(--neutral-800);
            font-size: var(--font-size-sm);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            width: 30%;
        }
        
        .details-table td {
            color: var(--neutral-700);
            font-size: var(--font-size-base);
        }
        
        .details-table tr:last-child th,
        .details-table tr:last-child td {
            border-bottom: none;
        }
        
        .details-table tr:hover {
            background-color: var(--neutral-50);
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: var(--spacing-1) var(--spacing-3);
            border-radius: var(--radius-full);
            font-size: var(--font-size-xs);
            font-weight: var(--font-weight-semibold);
            position: relative;
            box-shadow: var(--shadow-xs);
        }
        
        .status-badge::before {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            margin-right: var(--spacing-2);
        }
        
        .status-badge.complete {
            background-color: rgba(var(--success-rgb), 0.15);
            color: var(--success-dark);
        }
        
        .status-badge.complete::before {
            background-color: var(--success);
            box-shadow: 0 0 0 2px rgba(var(--success-rgb), 0.2);
        }
        
        .status-badge.incomplete {
            background-color: rgba(var(--warning-rgb), 0.15);
            color: var(--warning-dark);
        }
        
        .status-badge.incomplete::before {
            background-color: var(--warning);
            box-shadow: 0 0 0 2px rgba(var(--warning-rgb), 0.2);
        }
        
        .status-badge.pending {
            background-color: rgba(var(--info-rgb), 0.15);
            color: var(--info-dark);
        }
        
        .status-badge.pending::before {
            background-color: var(--info);
            box-shadow: 0 0 0 2px rgba(var(--info-rgb), 0.2);
        }
        
        .error-message {
            background-color: var(--danger-50);
            color: var(--danger-700);
            padding: var(--spacing-4);
            border-radius: var(--radius-lg);
            border: 1px solid var(--danger-200);
            margin-bottom: var(--spacing-4);
            display: flex;
            align-items: center;
        }
        
        .error-message i {
            margin-right: var(--spacing-3);
            color: var(--danger-600);
        }
        
        .section-divider {
            margin: var(--spacing-6) 0;
            border-bottom: 2px solid var(--neutral-200);
            position: relative;
        }
        
        .section-divider::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 2px;
            background-color: var(--primary-500);
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .details-table th,
            .details-table td {
                padding: var(--spacing-3);
                font-size: var(--font-size-sm);
            }
            
            .details-table th {
                width: 40%;
            }
            
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--spacing-3);
            }
        }
        
        @media (max-width: 480px) {
            .details-table {
                font-size: var(--font-size-xs);
            }
            
            .details-table th,
            .details-table td {
                padding: var(--spacing-2);
                display: block;
                width: 100%;
            }
            
            .details-table th {
                background-color: var(--primary-100);
                border-bottom: none;
                padding-bottom: var(--spacing-1);
            }
            
            .details-table td {
                border-bottom: 2px solid var(--neutral-200);
                padding-top: var(--spacing-1);
                padding-bottom: var(--spacing-3);
                font-weight: var(--font-weight-medium);
            }
        }
        
        /* Members Section Styles */
        .members-section {
            margin-top: var(--spacing-6);
        }
        
        .members-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-4);
            flex-wrap: wrap;
            gap: var(--spacing-3);
        }
        
        .members-header h3 {
            font-size: var(--font-size-xl);
            font-weight: var(--font-weight-bold);
            color: var(--neutral-900);
            margin: 0;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            padding: var(--spacing-3) var(--spacing-4);
            border: none;
            border-radius: var(--radius-lg);
            font-weight: var(--font-weight-medium);
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: var(--font-size-sm);
        }
        
        .btn-primary {
            background-color: var(--primary-600);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-700);
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }
        
        .btn i {
            margin-right: var(--spacing-2);
        }
        
        .members-container {
            margin-top: var(--spacing-4);
        }
        
        .loading-spinner {
            text-align: center;
            padding: var(--spacing-6);
            color: var(--primary-600);
            font-size: var(--font-size-lg);
        }
        
        .loading-spinner i {
            margin-right: var(--spacing-2);
        }
        
        .members-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            margin-top: var(--spacing-4);
        }
        
        .members-table th,
        .members-table td {
            padding: var(--spacing-3);
            text-align: left;
            border-bottom: 1px solid var(--neutral-200);
        }
        
        .members-table th {
            background-color: var(--primary-50);
            font-weight: var(--font-weight-semibold);
            color: var(--primary-900);
            font-size: var(--font-size-sm);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .members-table td {
            font-size: var(--font-size-sm);
            color: var(--neutral-700);
        }
        
        .members-table tbody tr:hover {
            background-color: var(--neutral-50);
        }
        
        .no-members {
            text-align: center;
            padding: var(--spacing-8);
            color: var(--neutral-500);
            font-style: italic;
        }
        
        /* View Details Button Styles */
        .btn-view-details {
            display: inline-flex;
            align-items: center;
            padding: var(--spacing-2) var(--spacing-3);
            background-color: var(--primary-600);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-size: var(--font-size-xs);
            font-weight: var(--font-weight-medium);
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        
        .btn-view-details:hover {
            background-color: var(--primary-700);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        .btn-view-details i {
            margin-right: var(--spacing-1);
            font-size: 0.8em;
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
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }
        
        .modal-content {
            background-color: var(--white);
            margin: 2% auto;
            padding: 0;
            border-radius: var(--radius-xl);
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-2xl);
            animation: modalSlideIn 0.3s ease-out;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .modal-header {
            padding: var(--spacing-6);
            border-bottom: 1px solid var(--neutral-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, var(--primary-50) 0%, var(--primary-100) 100%);
            border-radius: var(--radius-xl) var(--radius-xl) 0 0;
        }
        
        .modal-title {
            font-size: var(--font-size-xl);
            font-weight: var(--font-weight-bold);
            color: var(--primary-900);
            margin: 0;
            display: flex;
            align-items: center;
        }
        
        .modal-title i {
            margin-right: var(--spacing-3);
            color: var(--primary-600);
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: var(--font-size-xl);
            color: var(--neutral-500);
            cursor: pointer;
            padding: var(--spacing-2);
            border-radius: var(--radius-md);
            transition: all 0.2s ease;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-close:hover {
            background-color: var(--neutral-100);
            color: var(--neutral-700);
        }
        
        .modal-body {
            padding: var(--spacing-6);
        }
        
        .member-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--spacing-4);
        }
        
        .detail-section {
            background-color: var(--neutral-50);
            border-radius: var(--radius-lg);
            padding: var(--spacing-4);
            border: 1px solid var(--neutral-200);
        }
        
        .detail-section h4 {
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-semibold);
            color: var(--primary-800);
            margin: 0 0 var(--spacing-3) 0;
            display: flex;
            align-items: center;
        }
        
        .detail-section h4 i {
            margin-right: var(--spacing-2);
            color: var(--primary-600);
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: var(--spacing-2) 0;
            border-bottom: 1px solid var(--neutral-200);
        }
        
        .detail-item:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: var(--font-weight-medium);
            color: var(--neutral-700);
            font-size: var(--font-size-sm);
            flex: 0 0 40%;
        }
        
        .detail-value {
            color: var(--neutral-900);
            font-size: var(--font-size-sm);
            flex: 1;
            text-align: right;
            word-break: break-word;
        }
        
        .loading-modal {
            text-align: center;
            padding: var(--spacing-8);
            color: var(--primary-600);
        }
        
        .loading-modal i {
            font-size: var(--font-size-2xl);
            margin-bottom: var(--spacing-3);
        }
        
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 5% auto;
                max-height: 85vh;
            }
            
            .member-details-grid {
                grid-template-columns: 1fr;
            }
            
            .modal-header {
                padding: var(--spacing-4);
            }
            
            .modal-body {
                padding: var(--spacing-4);
            }
            
            .detail-item {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--spacing-1);
            }
            
            .detail-value {
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <!-- Include navbar -->
    <?php include '../admin/admin_navbar.php'; ?>
    
    <!-- Include sidebar -->
    <?php include '../admin/admin_sidebar.php'; ?>
    
    <div class="main-content" id="main-content">
        <div class="page-header">
            <nav class="breadcrumb">
                <a href="../admin/admin_dashboard.php">Dashboard</a>
                <span class="breadcrumb-separator">/</span>
                <a href="household_list.php">Household List</a>
                <span class="breadcrumb-separator">/</span>
                <span>Household Details</span>
            </nav>
            <h1 class="page-title">Household Details</h1>
            <p class="page-subtitle">Detailed information for the selected household</p>
        </div>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
            <div style="text-align: center; margin-top: var(--spacing-4);">
                <a href="household_list.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back to Household List
                </a>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-home"></i>
                        Household Information
                    </h2>
                    <a href="household_list.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i>
                        Back to List
                    </a>
                </div>
                
                <table class="details-table">
                    <tbody>
                        <tr>
                            <th>Session ID</th>
                            <td><?php echo htmlspecialchars($household_data['session_id']); ?></td>
                        </tr>
                        <tr>
                            <th>Household Number</th>
                            <td><?php echo isset($household_data['household_number']) ? '#' . htmlspecialchars(str_pad($household_data['household_number'], 6, '0', STR_PAD_LEFT)) : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <th>Province</th>
                            <td><?php echo htmlspecialchars($household_data['province'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>City/Municipality</th>
                            <td><?php echo htmlspecialchars($household_data['city_municipality'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>Barangay</th>
                            <td><?php echo htmlspecialchars($household_data['barangay'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <td><?php echo htmlspecialchars($household_data['address'] ?? 'N/A'); ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="section-divider"></div>
                
                <table class="details-table">
                    <tbody>
                        <tr>
                            <th>Respondent Name</th>
                            <td><?php echo htmlspecialchars($household_data['respondent_name'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>Household Head</th>
                            <td><?php echo htmlspecialchars($household_data['household_head'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>Total Members</th>
                            <td><?php echo htmlspecialchars($household_data['total_members'] ?? '0'); ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="section-divider"></div>
                
                <table class="details-table">
                    <tbody>
                        <tr>
                            <th>Visit Number</th>
                            <td><?php echo htmlspecialchars($household_data['visit_number'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>Visit Date</th>
                            <td><?php echo htmlspecialchars($household_data['visit_date'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>Start Time</th>
                            <td><?php echo htmlspecialchars($household_data['start_time'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>End Time</th>
                            <td><?php echo htmlspecialchars($household_data['end_time'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>Result</th>
                            <td>
                                <?php 
                                $result = $household_data['result'] ?? 'N/A';
                                $status_class = strtolower($result) == 'complete' ? 'complete' : 'incomplete';
                                echo '<span class="status-badge ' . $status_class . '">' . htmlspecialchars($result) . '</span>';
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Next Visit Date</th>
                            <td><?php echo htmlspecialchars($household_data['next_visit_date'] ?? 'N/A'); ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="section-divider"></div>
                
                <table class="details-table">
                    <tbody>
                        <tr>
                            <th>Interviewer Name</th>
                            <td><?php echo htmlspecialchars($household_data['interviewer_name'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>Supervisor Name</th>
                            <td><?php echo htmlspecialchars($household_data['supervisor_name'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>Date Encoded</th>
                            <td><?php echo htmlspecialchars($household_data['date_encoded'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>Encoder Name</th>
                            <td><?php echo htmlspecialchars($household_data['encoder_name'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>Encoding Supervisor</th>
                            <td><?php echo htmlspecialchars($household_data['encoding_supervisor'] ?? 'N/A'); ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- View Household Members Section -->
                <div class="section-divider"></div>
                <div class="members-section">
                    <div class="members-header">
                        <h3>Household Members</h3>
                        <button id="viewMembersBtn" class="btn btn-primary" onclick="toggleMembersView()">
                            <i class="fas fa-users"></i> View Household Members
                        </button>
                    </div>
                    
                    <div id="membersContainer" class="members-container" style="display: none;">
                        <div id="membersLoading" class="loading-spinner" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i> Loading members...
                        </div>
                        <div id="membersContent" class="members-content"></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Member Details Modal -->
    <div id="memberDetailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">
                    <i class="fas fa-user"></i>
                    <span id="modalMemberName">Member Details</span>
                </h2>
                <button class="modal-close" onclick="closeMemberModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <div class="loading-modal">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading member details...</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Sidebar toggle functionality
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            
            if (sidebar && mainContent) {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            }
        }
        
        // Toggle Members View functionality
        let membersLoaded = false;
        
        function toggleMembersView() {
            const container = document.getElementById('membersContainer');
            const btn = document.getElementById('viewMembersBtn');
            const loading = document.getElementById('membersLoading');
            const content = document.getElementById('membersContent');
            
            if (container.style.display === 'none') {
                container.style.display = 'block';
                btn.innerHTML = '<i class="fas fa-eye-slash"></i> Hide Household Members';
                
                if (!membersLoaded) {
                    loading.style.display = 'block';
                    content.innerHTML = '';
                    
                    // Get household ID from URL
                    const urlParams = new URLSearchParams(window.location.search);
                    const householdId = urlParams.get('id');
                    
                    // Fetch members data
                    fetch('fetch_household_members.php?id=' + householdId)
                        .then(response => response.json())
                        .then(data => {
                            loading.style.display = 'none';
                            
                            if (data.success) {
                                displayMembers(data.members);
                                membersLoaded = true;
                            } else {
                                content.innerHTML = '<div class="no-members">Error: ' + data.message + '</div>';
                            }
                        })
                        .catch(error => {
                            loading.style.display = 'none';
                            content.innerHTML = '<div class="no-members">Error loading members data.</div>';
                            console.error('Error:', error);
                        });
                }
            } else {
                container.style.display = 'none';
                btn.innerHTML = '<i class="fas fa-users"></i> View Household Members';
            }
        }
        
        function displayMembers(members) {
            const content = document.getElementById('membersContent');
            
            if (members.length === 0) {
                content.innerHTML = '<div class="no-members">No members found for this household.</div>';
                return;
            }
            
            let tableHTML = `
                <table class="members-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Relationship</th>
                            <th>Sex</th>
                            <th>Age</th>
                            <th>Date of Birth</th>
                            <th>Marital Status</th>
                            <th>Education</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            members.forEach(member => {
                const sex = member.sex == 1 ? 'Male' : (member.sex == 2 ? 'Female' : 'N/A');
                tableHTML += `
                    <tr>
                        <td>${member.name || 'N/A'}</td>
                        <td>${member.relationship || 'N/A'}</td>
                        <td>${sex}</td>
                        <td>${member.age || 'N/A'}</td>
                        <td>${member.date_of_birth || 'N/A'}</td>
                        <td>${member.marital_status || 'N/A'}</td>
                        <td>${member.highest_education || 'N/A'}</td>
                        <td>
                            <button class="btn-view-details" onclick="viewMemberDetails(${member.member_id})" title="View Full Details">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            tableHTML += `
                    </tbody>
                </table>
            `;
            
            content.innerHTML = tableHTML;
        }
        
        // Mobile sidebar functionality
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            
            if (sidebar && mainContent) {
                sidebar.classList.toggle('mobile-open');
                mainContent.classList.toggle('sidebar-mobile-open');
            }
        }
        
        // Close mobile sidebar when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const hamburger = document.querySelector('.hamburger-menu');
            
            if (sidebar && hamburger) {
                if (!sidebar.contains(event.target) && !hamburger.contains(event.target)) {
                    sidebar.classList.remove('mobile-open');
                    document.getElementById('main-content').classList.remove('sidebar-mobile-open');
                }
            }
        });
        
        // Member Details Modal Functions
        function viewMemberDetails(memberId) {
            const modal = document.getElementById('memberDetailsModal');
            const modalBody = document.getElementById('modalBody');
            const modalMemberName = document.getElementById('modalMemberName');
            
            // Show modal
            modal.style.display = 'block';
            
            // Reset modal content to loading state
            modalBody.innerHTML = `
                <div class="loading-modal">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading member details...</p>
                </div>
            `;
            modalMemberName.textContent = 'Member Details';
            
            // Fetch member details
            fetch('get_member_details.php?member_id=' + memberId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayMemberDetails(data.member);
                    } else {
                        modalBody.innerHTML = `
                            <div class="error-message">
                                <i class="fas fa-exclamation-triangle"></i>
                                Error: ${data.message || 'Failed to load member details'}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalBody.innerHTML = `
                        <div class="error-message">
                            <i class="fas fa-exclamation-triangle"></i>
                            Error loading member details. Please try again.
                        </div>
                    `;
                });
        }
        
        function displayMemberDetails(member) {
            const modalBody = document.getElementById('modalBody');
            const modalMemberName = document.getElementById('modalMemberName');
            
            // Update modal title with member name
            modalMemberName.textContent = member.name || 'Member Details';
            
            // Format sex display
            const sexDisplay = member.sex == 1 ? 'Male' : (member.sex == 2 ? 'Female' : 'N/A');
            
            // Format boolean values
            const formatBoolean = (value) => {
                if (value === '1' || value === 1) return 'Yes';
                if (value === '0' || value === 0) return 'No';
                return value || 'N/A';
            };
            
            // Create detailed view with all 64 fields organized by categories
            modalBody.innerHTML = `
                <div class="member-details-grid">
                    <div class="detail-section">
                        <h4><i class="fas fa-user"></i> Personal Information</h4>
                        <div class="detail-item">
                            <span class="detail-label">Full Name:</span>
                            <span class="detail-value">${member.name || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Relationship to Head:</span>
                            <span class="detail-value">${member.relationship || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Sex:</span>
                            <span class="detail-value">${sexDisplay}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Age:</span>
                            <span class="detail-value">${member.age || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Date of Birth:</span>
                            <span class="detail-value">${member.date_of_birth || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Place of Birth:</span>
                            <span class="detail-value">${member.place_of_birth || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Nationality:</span>
                            <span class="detail-value">${member.nationality || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Religion:</span>
                            <span class="detail-value">${member.religion || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Ethnicity:</span>
                            <span class="detail-value">${member.ethnicity || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Marital Status:</span>
                            <span class="detail-value">${member.marital_status || 'N/A'}</span>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h4><i class="fas fa-graduation-cap"></i> Education</h4>
                        <div class="detail-item">
                            <span class="detail-label">Highest Education:</span>
                            <span class="detail-value">${member.highest_education || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Currently Enrolled:</span>
                            <span class="detail-value">${formatBoolean(member.currently_enrolled)}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">School Level:</span>
                            <span class="detail-value">${member.school_level || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Place of School:</span>
                            <span class="detail-value">${member.place_of_school || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Q5 Literacy:</span>
                            <span class="detail-value">${member.q5_literacy || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Q6 Highest Grade:</span>
                            <span class="detail-value">${member.q6_highest_grade || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Q7 School Attendance:</span>
                            <span class="detail-value">${formatBoolean(member.q7_school_attendance)}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Q8 Grade Level:</span>
                            <span class="detail-value">${member.q8_grade_level || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Q9 School Type:</span>
                            <span class="detail-value">${member.q9_school_type || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Q10 School Location:</span>
                            <span class="detail-value">${member.q10_school_location || 'N/A'}</span>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h4><i class="fas fa-briefcase"></i> Employment & Economic</h4>
                        <div class="detail-item">
                            <span class="detail-label">Q11 Work Status:</span>
                            <span class="detail-value">${formatBoolean(member.q11_work_status)}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Q12 Job Search:</span>
                            <span class="detail-value">${formatBoolean(member.q12_job_search)}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Q13 Work Availability:</span>
                            <span class="detail-value">${formatBoolean(member.q13_work_availability)}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Q14 Work Last Week:</span>
                            <span class="detail-value">${formatBoolean(member.q14_work_last_week)}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Monthly Income:</span>
                            <span class="detail-value">${member.q15_monthly_income ? '₱' + parseFloat(member.q15_monthly_income).toLocaleString() : 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Work Status:</span>
                            <span class="detail-value">${member.q16_work_status || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Occupation:</span>
                            <span class="detail-value">${member.q17_occupation || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Work Location:</span>
                            <span class="detail-value">${member.q18_work_location || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Q19 Work Class:</span>
                            <span class="detail-value">${member.q19_work_class || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Q20 Basic Pay:</span>
                            <span class="detail-value">${member.q20_basic_pay ? '₱' + parseFloat(member.q20_basic_pay).toLocaleString() : 'N/A'}</span>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h4><i class="fas fa-heart"></i> Health & Disability</h4>
                        <div class="detail-item">
                            <span class="detail-label">Q21 Disability:</span>
                            <span class="detail-value">${formatBoolean(member.q21_disability)}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Q22 Disability Type:</span>
                            <span class="detail-value">${member.q22_disability_type || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Q23 Disability Cause:</span>
                            <span class="detail-value">${member.q23_disability_cause || 'N/A'}</span>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h4><i class="fas fa-globe"></i> Migration & Residence</h4>
                        <div class="detail-item">
                            <span class="detail-label">Q24 Residence 5 Years Ago:</span>
                            <span class="detail-value">${member.q24_residence_5_years_ago || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Q25 Previous Province:</span>
                            <span class="detail-value">${member.q25_previous_province || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Q26 Previous City:</span>
                            <span class="detail-value">${member.q26_previous_city || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Q27 Previous Barangay:</span>
                            <span class="detail-value">${member.q27_previous_barangay || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Q28 Previous Country:</span>
                            <span class="detail-value">${member.q28_previous_country || 'N/A'}</span>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h4><i class="fas fa-baby"></i> Maternal & Child Health</h4>
                        <div class="detail-item">
                            <span class="detail-label">Q29 Children Born Alive:</span>
                            <span class="detail-value">${member.q29_children_born_alive || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Q30 Children Still Alive:</span>
                            <span class="detail-value">${member.q30_children_still_alive || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Q31 Male Children Alive:</span>
                            <span class="detail-value">${member.q31_male_children_alive || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Q32 Female Children Alive:</span>
                            <span class="detail-value">${member.q32_female_children_alive || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Q33 Children Died:</span>
                            <span class="detail-value">${member.q33_children_died || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Q34 Male Children Died:</span>
                            <span class="detail-value">${member.q34_male_children_died || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Q35 Female Children Died:</span>
                            <span class="detail-value">${member.q35_female_children_died || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Q36 Last Birth Date:</span>
                            <span class="detail-value">${member.q36_last_birth_date || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Q37 Last Child Sex:</span>
                            <span class="detail-value">${member.q37_last_child_sex == 1 ? 'Male' : (member.q37_last_child_sex == 2 ? 'Female' : 'N/A')}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Q38 Last Child Alive:</span>
                            <span class="detail-value">${formatBoolean(member.q38_last_child_alive)}</span>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h4><i class="fas fa-id-card"></i> Identification & System</h4>
                        <div class="detail-item">
                            <span class="detail-label">Member ID:</span>
                            <span class="detail-value">${member.member_id || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Household Number:</span>
                            <span class="detail-value">${member.household_number || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Session ID:</span>
                            <span class="detail-value">${member.session_id || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Created At:</span>
                            <span class="detail-value">${member.created_at || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Updated At:</span>
                            <span class="detail-value">${member.updated_at || 'N/A'}</span>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function closeMemberModal() {
            const modal = document.getElementById('memberDetailsModal');
            modal.style.display = 'none';
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('memberDetailsModal');
            if (event.target == modal) {
                closeMemberModal();
            }
        }
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeMemberModal();
            }
        });
    </script>
</body>
</html>