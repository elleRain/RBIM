<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to users in production
ini_set('log_errors', 1); // Log errors instead

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

// Check if database connection is successful
if (!isset($conn) || !$conn) {
    error_log("Database connection failed: " . (isset($conn) ? mysqli_connect_error() : "Connection variable not set"));
    // Display a user-friendly error message
    $connection_error = "Unable to connect to the database. Please try again later or contact support.";
}

// Get system statistics
$stats = [];

// Initialize error tracking
$query_errors = [];

// Total barangays
$stats['total_barangays'] = 0;
$sql = "SELECT COUNT(*) as total FROM barangay";
$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['total_barangays'] = $row['total'];
    mysqli_free_result($result);
} else {
    error_log("Error in barangay count query: " . mysqli_error($conn));
    $query_errors[] = "barangay count";
}

// Total barangay admins
$stats['total_admins'] = 0;
$sql = "SELECT COUNT(*) as total FROM users WHERE role = 'admin' AND account_status = 'active'";
$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['total_admins'] = $row['total'];
    mysqli_free_result($result);
} else {
    error_log("Error in admin count query: " . mysqli_error($conn));
    $query_errors[] = "admin count";
}

// Total households
$stats['total_households'] = 0;
$sql = "SELECT COUNT(*) as total FROM survey_session";
$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['total_households'] = $row['total'];
    mysqli_free_result($result);
} else {
    error_log("Error in household count query: " . mysqli_error($conn));
    $query_errors[] = "household count";
}

// Total residents
$stats['total_residents'] = 0;
// Use register_members table for resident count - with cache busting
// Force cache refresh by adding timestamp
$cache_buster = time();
$sql = "SELECT COUNT(*) as total FROM register_members";
$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['total_residents'] = $row['total'];
    mysqli_free_result($result);
    // Log successful count for debugging
    error_log("Successfully counted {$stats['total_residents']} residents from register_members table");
} else {
    error_log("Error in resident count query: " . mysqli_error($conn));
    $query_errors[] = "resident count";
}

// Get gender distribution
$gender_distribution = [
    'Male' => 0,
    'Female' => 0
];

// Check if gender column exists in resident table
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM resident LIKE 'gender'");

if ($check_column) {
    if (mysqli_num_rows($check_column) > 0) {
        $sql = "SELECT gender, COUNT(*) as count FROM resident GROUP BY gender";
        $result = mysqli_query($conn, $sql);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                // Normalize gender values to handle case variations
                $gender = ucfirst(strtolower($row['gender']));
                if ($gender == 'Male' || $gender == 'Female') {
                    $gender_distribution[$gender] = (int)$row['count'];
                }
            }
            mysqli_free_result($result);
        } else {
            error_log("Error in gender distribution query: " . mysqli_error($conn));
            $query_errors[] = "gender distribution";
        }
    } else {
        error_log("Gender column not found in resident table");
    }
    mysqli_free_result($check_column);
} else {
    error_log("Error checking gender column: " . mysqli_error($conn));
    $query_errors[] = "gender column check";
}

// Get age distribution data for chart
// First check if 'age' column exists in resident table
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM resident LIKE 'age'");

$age_groups = [
    '0-10' => 0,
    '11-20' => 0,
    '21-30' => 0,
    '31-40' => 0,
    '41-50' => 0,
    '51-60' => 0,
    '61+' => 0
];

if ($check_column) {
    if (mysqli_num_rows($check_column) > 0) {
        // If age column exists, use it directly
        $sql = "SELECT 
                    CASE 
                        WHEN age BETWEEN 0 AND 10 THEN '0-10'
                        WHEN age BETWEEN 11 AND 20 THEN '11-20'
                        WHEN age BETWEEN 21 AND 30 THEN '21-30'
                        WHEN age BETWEEN 31 AND 40 THEN '31-40'
                        WHEN age BETWEEN 41 AND 50 THEN '41-50'
                        WHEN age BETWEEN 51 AND 60 THEN '51-60'
                        ELSE '61+'
                    END AS age_group,
                    COUNT(*) as count
                FROM resident
                GROUP BY age_group";
    } else {
        // If age column doesn't exist, use a placeholder query that returns empty result
        // This is a temporary solution until the database schema is updated
        $sql = "SELECT '0-10' as age_group, 0 as count WHERE 1=0";
        error_log("Age column not found in resident table");
    }
    mysqli_free_result($check_column);
} else {
    // Error checking for column existence
    error_log("Error checking age column: " . mysqli_error($conn));
    $query_errors[] = "age column check";
    $sql = "SELECT '0-10' as age_group, 0 as count WHERE 1=0"; // Fallback query
}

$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        if (isset($row['age_group']) && isset($age_groups[$row['age_group']])) {
            $age_groups[$row['age_group']] = (int)$row['count'];
        }
    }
    mysqli_free_result($result);
} else {
    error_log("Error in age distribution query: " . mysqli_error($conn));
    $query_errors[] = "age distribution";
}

// Get recent registrations (last 5 households)
$recent_households = [];
$sql = "SELECT ss.session_id, ss.household_number, ss.household_head, ss.barangay, ss.date_encoded 
       FROM survey_session ss 
       ORDER BY ss.date_encoded DESC LIMIT 5";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Sanitize data before adding to array
        foreach ($row as $key => $value) {
            $row[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        $recent_households[] = $row;
    }
    mysqli_free_result($result);
} else {
    error_log("Error in recent households query: " . mysqli_error($conn));
    $query_errors[] = "recent households";
}

// Get barangay distribution (for map or chart)
// Check connection before executing query
if (!isset($conn) || !$conn) {
    error_log("Database connection not available for barangay distribution query");
    $query_errors[] = "barangay distribution - connection error";
    $barangay_distribution = [];
} else {
    $sql = "SELECT b.barangay, COUNT(ss.session_id) as household_count 
           FROM barangay b 
           LEFT JOIN survey_session ss ON b.barangay = ss.barangay 
           GROUP BY b.barangay 
           ORDER BY household_count DESC";
    $result = mysqli_query($conn, $sql);
    $barangay_distribution = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Sanitize data before adding to array
            foreach ($row as $key => $value) {
                $row[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
            $barangay_distribution[] = $row;
        }
        mysqli_free_result($result);
    } else {
        error_log("Error in barangay distribution query: " . mysqli_error($conn));
        $query_errors[] = "barangay distribution";
    }
}

// Get system activity log (last 10 entries)
// This would typically come from a system_log table, but we'll simulate it for now
$activity_log = [
    ['timestamp' => date('Y-m-d H:i:s', strtotime('-1 hour')), 'user' => 'system', 'action' => 'Database backup completed'],
    ['timestamp' => date('Y-m-d H:i:s', strtotime('-3 hours')), 'user' => 'admin1', 'action' => 'New household registered'],
    ['timestamp' => date('Y-m-d H:i:s', strtotime('-1 day')), 'user' => 'admin2', 'action' => 'Updated resident information'],
    ['timestamp' => date('Y-m-d H:i:s', strtotime('-2 days')), 'user' => 'super_admin', 'action' => 'Added new barangay'],
    ['timestamp' => date('Y-m-d H:i:s', strtotime('-3 days')), 'user' => 'admin3', 'action' => 'Generated monthly report']
];

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
    <!-- Chart.js for data visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-rgb: 37, 99, 235;
            --secondary-rgb: 79, 70, 229;
            --success-rgb: 16, 185, 129;
            --info-rgb: 6, 182, 212;
            --warning-rgb: 245, 158, 11;
            --danger-rgb: 239, 68, 68;
            --neutral-rgb: 107, 114, 128;
        }
        
        body {
            font-family: 'Segoe UI', Roboto, -apple-system, BlinkMacSystemFont, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--neutral-50);
            color: var(--neutral-800);
        }
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }
        
        .main-content {
            flex: 1;
            padding: var(--spacing-6);
            background-color: var(--neutral-50);
            margin-left: 220px;
            transition: margin-left 0.3s ease;
        }
        
        .main-content.expanded {
            margin-left: 60px;
        }
        
        /* Page header */
        .page-header {
            margin-bottom: var(--spacing-6);
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            margin-bottom: var(--spacing-2);
            font-size: var(--font-size-sm);
            color: var(--neutral-500);
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
            margin: 0 0 var(--spacing-1);
        }
        
        .page-subtitle {
            font-size: var(--font-size-base);
            color: var(--neutral-600);
            margin: 0;
        }
        
        /* Dashboard grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: var(--spacing-6);
        }
        
        /* Stats section */
        .stats-section {
            grid-column: span 12;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: var(--spacing-4);
            margin-bottom: var(--spacing-6);
        }
        
        .stat-card {
            background-color: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            padding: var(--spacing-4);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }
        
        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-3);
        }
        
        .stat-card-title {
            color: var(--neutral-600);
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-medium);
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-card-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: var(--font-size-lg);
        }
        
        .stat-card-value {
            font-size: var(--font-size-3xl);
            font-weight: var(--font-weight-bold);
            color: var(--neutral-900);
            margin: 0 0 var(--spacing-1);
        }
        
        .stat-card-description {
            font-size: var(--font-size-sm);
            color: var(--neutral-500);
            margin: 0;
        }
        
        /* Charts section */
        .charts-section {
            grid-column: span 12;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: var(--spacing-6);
            margin-bottom: var(--spacing-6);
        }
        
        @media (max-width: 1024px) {
            .charts-section {
                grid-template-columns: 1fr;
            }
        }
        
        .chart-card {
            background-color: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            padding: var(--spacing-4);
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-4);
        }
        
        .chart-title {
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-semibold);
            color: var(--neutral-900);
            margin: 0;
        }
        
        .chart-container {
            height: 300px;
            position: relative;
        }
        
        /* Management sections */
        .management-section {
            grid-column: span 6;
            background-color: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            padding: var(--spacing-4);
            margin-bottom: var(--spacing-6);
        }
        
        @media (max-width: 1024px) {
            .management-section {
                grid-column: span 12;
            }
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-4);
            padding-bottom: var(--spacing-3);
            border-bottom: 1px solid var(--neutral-200);
        }
        
        .section-title {
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-semibold);
            color: var(--neutral-900);
            margin: 0;
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
        }
        
        .section-title i {
            color: var(--primary-500);
        }
        
        .section-actions {
            display: flex;
            gap: var(--spacing-2);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-2) var(--spacing-3);
            border-radius: var(--radius-md);
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-medium);
            text-decoration: none;
            transition: all 0.2s ease;
            gap: var(--spacing-2);
            cursor: pointer;
            border: none;
        }
        
        .btn-primary {
            background-color: rgba(var(--primary-rgb), 1);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background-color: rgba(var(--primary-rgb), 0.9);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--neutral-300);
            color: var(--neutral-700);
        }
        
        .btn-outline:hover {
            background-color: var(--neutral-100);
            border-color: var(--neutral-400);
        }
        
        /* Data tables */
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: var(--spacing-4);
        }
        
        .data-table th {
            text-align: left;
            padding: var(--spacing-3) var(--spacing-4);
            font-size: var(--font-size-xs);
            font-weight: var(--font-weight-semibold);
            color: var(--neutral-500);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--neutral-200);
        }
        
        .data-table td {
            padding: var(--spacing-3) var(--spacing-4);
            font-size: var(--font-size-sm);
            color: var(--neutral-700);
            border-bottom: 1px solid var(--neutral-100);
        }
        
        .data-table tr:last-child td {
            border-bottom: none;
        }
        
        .data-table tr:hover td {
            background-color: var(--neutral-50);
        }
        
        /* Activity log */
        .activity-log {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .activity-item {
            display: flex;
            align-items: flex-start;
            padding: var(--spacing-3) 0;
            border-bottom: 1px solid var(--neutral-100);
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: rgba(var(--primary-rgb), 0.1);
            color: rgba(var(--primary-rgb), 1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: var(--spacing-3);
            flex-shrink: 0;
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-action {
            font-size: var(--font-size-sm);
            color: var(--neutral-800);
            margin: 0 0 var(--spacing-1);
        }
        
        .activity-meta {
            display: flex;
            align-items: center;
            gap: var(--spacing-3);
            font-size: var(--font-size-xs);
            color: var(--neutral-500);
        }
        
        .activity-user {
            font-weight: var(--font-weight-medium);
        }
        
        /* Quick actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: var(--spacing-4);
        }
        
        .action-card {
            background-color: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            padding: var(--spacing-4);
            text-align: center;
            transition: all 0.2s ease;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-3);
        }
        
        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }
        
        .action-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background-color: rgba(var(--primary-rgb), 0.1);
            color: rgba(var(--primary-rgb), 1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--font-size-xl);
        }
        
        .action-title {
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-medium);
            color: var(--neutral-800);
            margin: 0;
        }
        
        .action-description {
            font-size: var(--font-size-xs);
            color: var(--neutral-500);
            margin: 0;
        }
        
        /* Alert messages */
        .alert {
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            font-size: var(--font-size-sm);
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

        <!-- Include sidebar and navbar -->
    <?php include "superadmin_sidebar.php"; ?>
    <?php include "superadmin_navbar.php"; ?>

    <div class="dashboard-container">
        <?php include "../includes/sidebar.php"; ?>
        
        <div class="main-content">
            <?php include "../includes/navbar.php"; ?>
            
            
            <?php if (isset($_SESSION['delete_error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['delete_error']; 
                    unset($_SESSION['delete_error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Stats Section -->
                <div class="stats-section">
                    <!-- Total Barangays -->
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">TOTAL BARANGAYS</h3>
                            <div class="stat-card-icon" style="background-color: rgba(var(--primary-rgb), 1);">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                        </div>
                        <h2 class="stat-card-value"><?php echo number_format($stats['total_barangays']); ?></h2>
                        <p class="stat-card-description">Registered barangays in the system</p>
                    </div>
                    
                    <!-- Total Admins -->
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">BARANGAY ADMINS</h3>
                            <div class="stat-card-icon" style="background-color: rgba(var(--secondary-rgb), 1);">
                                <i class="fas fa-user-shield"></i>
                            </div>
                        </div>
                        <h2 class="stat-card-value"><?php echo number_format($stats['total_admins']); ?></h2>
                        <p class="stat-card-description">Active barangay administrators</p>
                    </div>
                    
                    <!-- Total Households -->
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">TOTAL HOUSEHOLDS</h3>
                            <div class="stat-card-icon" style="background-color: rgba(var(--info-rgb), 1);">
                                <i class="fas fa-home"></i>
                            </div>
                        </div>
                        <h2 class="stat-card-value"><?php echo number_format($stats['total_households']); ?></h2>
                        <p class="stat-card-description">Registered households across all barangays</p>
                    </div>
                    
                    <!-- Total Residents -->
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">TOTAL RESIDENTS</h3>
                            <div class="stat-card-icon" style="background-color: rgba(var(--success-rgb), 1);">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <h2 class="stat-card-value"><?php echo number_format($stats['total_residents']); ?></h2>
                        <p class="stat-card-description">Total residents across all barangays</p>
                    </div>
                </div>
                
                <!-- Charts Section -->
                <div class="charts-section">
                    <!-- Age Distribution Chart -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">Age Distribution</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="ageDistributionChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Gender Distribution Chart -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">Gender Distribution</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="genderDistributionChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Barangay Distribution Section -->
                <div class="management-section" style="grid-column: span 12;">
                    <div class="section-header">
                        <h3 class="section-title"><i class="fas fa-chart-bar"></i> Barangay Distribution</h3>
                        <div class="section-actions">
                            <a href="barangay_management.php" class="btn btn-outline"><i class="fas fa-cog"></i> Manage Barangays</a>
                        </div>
                    </div>
                    <div class="chart-container" style="height: 400px;">
                        <canvas id="barangayDistributionChart"></canvas>
                    </div>
                </div>
                
                <!-- Recent Registrations Section -->
                <div class="management-section">
                    <div class="section-header">
                        <h3 class="section-title"><i class="fas fa-clipboard-list"></i> Recent Registrations</h3>
                        <div class="section-actions">
                            <a href="#" class="btn btn-outline"><i class="fas fa-search"></i> View All</a>
                        </div>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Household #</th>
                                <th>Household Head</th>
                                <th>Barangay</th>
                                <th>Date Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_households as $household): ?>
                            <tr>
                                <td><?php echo str_pad($household['household_number'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($household['household_head']); ?></td>
                                <td><?php echo htmlspecialchars($household['barangay']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($household['date_encoded'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recent_households)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">No recent registrations found.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- System Activity Log Section -->
                <div class="management-section">
                    <div class="section-header">
                        <h3 class="section-title"><i class="fas fa-history"></i> System Activity Log</h3>
                        <div class="section-actions">
                            <a href="#" class="btn btn-outline"><i class="fas fa-search"></i> View All</a>
                        </div>
                    </div>
                    <ul class="activity-log">
                        <?php foreach ($activity_log as $activity): ?>
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-circle-notch"></i>
                            </div>
                            <div class="activity-content">
                                <p class="activity-action"><?php echo htmlspecialchars($activity['action']); ?></p>
                                <div class="activity-meta">
                                    <span class="activity-user"><?php echo htmlspecialchars($activity['user']); ?></span>
                                    <span class="activity-time"><?php echo htmlspecialchars($activity['timestamp']); ?></span>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Quick Actions Section -->
                <div class="management-section" style="grid-column: span 12;">
                    <div class="section-header">
                        <h3 class="section-title"><i class="fas fa-bolt"></i> Quick Actions</h3>
                    </div>
                    <div class="quick-actions">
                        <a href="barangay_management.php" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <h4 class="action-title">Manage Barangays</h4>
                            <p class="action-description">Add, edit or remove barangays</p>
                        </a>
                        
                        <a href="admin_management.php" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <h4 class="action-title">Manage Admins</h4>
                            <p class="action-description">Manage barangay administrators</p>
                        </a>
                        
                        <a href="reports.php" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h4 class="action-title">Generate Reports</h4>
                            <p class="action-description">Create system-wide reports</p>
                        </a>
                        
                        <a href="system_settings.php" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-cogs"></i>
                            </div>
                            <h4 class="action-title">System Settings</h4>
                            <p class="action-description">Configure system parameters</p>
                        </a>
                        
                        <a href="backup.php" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-database"></i>
                            </div>
                            <h4 class="action-title">Database Backup</h4>
                            <p class="action-description">Backup system data</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set active menu item
        const currentPath = window.location.pathname;
        const menuItems = document.querySelectorAll('.nav-link');
        menuItems.forEach(item => {
            if (currentPath.includes(item.getAttribute('href'))) {
                item.classList.add('active');
            }
        });
        
        // Age Distribution Chart
        try {
            const ageCanvas = document.getElementById('ageDistributionChart');
            if (!ageCanvas) {
                console.error('Age distribution chart canvas not found');
            } else {
                const ageCtx = ageCanvas.getContext('2d');
                if (!ageCtx) {
                    console.error('Could not get 2D context for age distribution chart');
                } else {
                    const ageChart = new Chart(ageCtx, {
                        type: 'bar',
                        data: {
                            labels: <?php echo json_encode(array_keys($age_groups)); ?>,
                            datasets: [{
                                label: 'Number of Residents',
                                data: <?php echo json_encode(array_values($age_groups)); ?>,
                                backgroundColor: 'rgba(var(--primary-rgb), 0.7)',
                                borderColor: 'rgba(var(--primary-rgb), 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            }
                        }
                    });
                }
            }
        } catch (ageChartError) {
            console.error('Error initializing age distribution chart:', ageChartError);
        }
        
        // Gender Distribution Chart
        try {
            const genderCanvas = document.getElementById('genderDistributionChart');
            if (!genderCanvas) {
                console.error('Gender distribution chart canvas not found');
            } else {
                const genderCtx = genderCanvas.getContext('2d');
                if (!genderCtx) {
                    console.error('Could not get 2D context for gender distribution chart');
                } else {
                    const genderChart = new Chart(genderCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Male', 'Female'],
                datasets: [{
                    data: [<?php echo $gender_distribution['Male']; ?>, <?php echo $gender_distribution['Female']; ?>],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 99, 132, 0.8)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
}
} catch (genderChartError) {
console.error('Error initializing gender distribution chart:', genderChartError);
}

// Barangay Distribution Chart
try {
    const barangayCanvas = document.getElementById('barangayDistributionChart');
    if (!barangayCanvas) {
        console.error('Barangay distribution chart canvas not found');
    } else {
        const barangayCtx = barangayCanvas.getContext('2d');
        if (!barangayCtx) {
            console.error('Could not get 2D context for barangay distribution chart');
        } else {
            const barangayLabels = <?php echo json_encode(array_column($barangay_distribution, 'barangay')); ?>;
            const barangayData = <?php echo json_encode(array_column($barangay_distribution, 'household_count')); ?>;
            
            const barangayChart = new Chart(barangayCtx, {
                type: 'bar',
                data: {
                    labels: barangayLabels,
                    datasets: [{
                        label: 'Households',
                        data: barangayData,
                        backgroundColor: 'rgba(var(--secondary-rgb), 0.7)',
                        borderColor: 'rgba(var(--secondary-rgb), 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }
    }
} catch (barangayChartError) {
    console.error('Error initializing barangay distribution chart:', barangayChartError);
}
});
</script>
</body>
</html>