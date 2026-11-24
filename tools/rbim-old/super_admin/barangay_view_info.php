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

// Check if barangay_id is provided
if (!isset($_GET['barangay_id']) || empty($_GET['barangay_id'])) {
    $_SESSION['error'] = "No barangay selected.";
    header("location: barangay_management.php");
    exit;
}

$barangay_id = intval($_GET['barangay_id']);

// Get barangay information
$barangay_name = "Unknown Barangay";
$barangay_status = "Active";
$last_updated = date("F d, Y");
$land_area = "5.2"; // Default land area in sq. km
$distance_from_city = "8.5"; // Default distance from city center in km

$sql = "SELECT barangay_name FROM barangay WHERE barangay_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $barangay_id);
    $stmt->execute();
    $stmt->bind_result($barangay_name);
    $stmt->fetch();
    $stmt->close();
}

// Get total households count - prioritize survey_session table for census data
$total_households = 0;

// First try to get count from survey_session table
$census_households = 0;
$sql = "SELECT COUNT(*) FROM survey_session WHERE barangay_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $barangay_id);
    if ($stmt->execute()) {
        $stmt->bind_result($census_households);
        $stmt->fetch();
        $total_households = $census_households; // Use this as primary source if available
    } else {
        // Log error or set error message
        error_log("Error executing survey_session count query: " . $stmt->error);
    }
    $stmt->close();
}

// If no results from survey_session, try register_members table
if ($total_households == 0) {
    // Get count from register_members table (distinct household numbers)
    $distinct_households = 0;
    $sql = "SELECT COUNT(DISTINCT household_number) FROM register_members WHERE barangay_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $barangay_id);
        if ($stmt->execute()) {
            $stmt->bind_result($distinct_households);
            $stmt->fetch();
            $total_households = $distinct_households;
        } else {
            // Log error or set error message
            error_log("Error executing distinct household count query: " . $stmt->error);
        }
        $stmt->close();
    }
}

// Fallback to households table if still no results
if ($total_households == 0) {
    $sql = "SELECT COUNT(*) FROM households WHERE barangay_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $barangay_id);
        if ($stmt->execute()) {
            $stmt->bind_result($total_households);
            $stmt->fetch();
        } else {
            // Log error or set error message
            error_log("Error executing household count query: " . $stmt->error);
        }
        $stmt->close();
    }
}

// Get total residents count
$total_residents = 0;

// First try to get total members from survey_session table
$census_residents = 0;
$sql = "SELECT SUM(total_members) FROM survey_session WHERE barangay_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $barangay_id);
    if ($stmt->execute()) {
        $stmt->bind_result($census_residents);
        $stmt->fetch();
        if ($census_residents !== null) {
            $total_residents = $census_residents;
        }
    } else {
        // Log error or set error message
        error_log("Error executing survey_session residents count query: " . $stmt->error);
    }
    $stmt->close();
}

// If no results from survey_session, fall back to register_members table
if ($total_residents == 0) {
    // Count all members in register_members table with valid data
    $sql = "SELECT COUNT(*) FROM register_members WHERE barangay_id = ? AND name IS NOT NULL";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $barangay_id);
        if ($stmt->execute()) {
            $stmt->bind_result($total_residents);
            $stmt->fetch();
        } else {
            // Log error or set error message
            error_log("Error executing resident count query: " . $stmt->error);
        }
        $stmt->close();
    }
}

// Get registered voters count
$registered_voters = 0;
$sql = "SELECT COUNT(*) FROM register_members WHERE barangay_id = ? AND voter_status = 'Yes'";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $barangay_id);
    if ($stmt->execute()) {
        $stmt->bind_result($registered_voters);
        $stmt->fetch();
    } else {
        error_log("Error executing registered voters query: " . $stmt->error);
    }
    $stmt->close();
}

// Get senior citizens count
$senior_citizens = 0;
$sql = "SELECT COUNT(*) FROM register_members WHERE barangay_id = ? AND age >= 60";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $barangay_id);
    if ($stmt->execute()) {
        $stmt->bind_result($senior_citizens);
        $stmt->fetch();
    } else {
        error_log("Error executing senior citizens query: " . $stmt->error);
    }
    $stmt->close();
}

// Get gender distribution
$gender_distribution = [
    'Male' => 0,
    'Female' => 0
];

// Get gender distribution in a single query
$sql = "SELECT 
    SUM(CASE WHEN sex = 1 THEN 1 ELSE 0 END) as male_count,
    SUM(CASE WHEN sex = 0 THEN 1 ELSE 0 END) as female_count
    FROM register_members 
    WHERE barangay_id = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $barangay_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $gender_distribution['Male'] = $row['male_count'];
            $gender_distribution['Female'] = $row['female_count'];
        }
    } else {
        error_log("Error executing gender distribution query: " . $stmt->error);
    }
    $stmt->close();
}

// Calculate gender ratio
$gender_ratio = 0;
if ($gender_distribution['Female'] > 0) {
    $gender_ratio = round($gender_distribution['Male'] / $gender_distribution['Female'], 2);
}

// Calculate average household size
$avg_household_size = 0;
if ($total_households > 0) {
    $avg_household_size = round($total_residents / $total_households, 2);
}

// Get age distribution data for chart
$age_groups = [
    '0-10' => 0,
    '11-20' => 0,
    '21-30' => 0,
    '31-40' => 0,
    '41-50' => 0,
    '51-60' => 0,
    '61+' => 0
];

$sql = "SELECT 
            CASE 
                WHEN age BETWEEN 0 AND 10 THEN '0-10'
                WHEN age BETWEEN 11 AND 20 THEN '11-20'
                WHEN age BETWEEN 21 AND 30 THEN '21-30'
                WHEN age BETWEEN 31 AND 40 THEN '31-40'
                WHEN age BETWEEN 41 AND 50 THEN '41-50'
                WHEN age BETWEEN 51 AND 60 THEN '51-60'
                ELSE '61+'
            END as age_group,
            COUNT(*) as count
        FROM register_members
        WHERE barangay_id = ? AND age IS NOT NULL
        GROUP BY age_group";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $barangay_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            if (isset($row['age_group']) && isset($age_groups[$row['age_group']])) {
                $age_groups[$row['age_group']] = (int)$row['count'];
            }
        }
    } else {
        error_log("Error executing age distribution query: " . $stmt->error);
    }
    $stmt->close();
}

// Get civil status distribution
$civil_status = [
    'Single' => 0,
    'Married' => 0,
    'Widowed' => 0,
    'Divorced' => 0,
    'Separated' => 0,
    'Other' => 0
];

$sql = "SELECT IFNULL(marital_status, 'Other') as status, COUNT(*) as count
        FROM register_members
        WHERE barangay_id = ? AND name IS NOT NULL
        GROUP BY status";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $barangay_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $status = $row['status'];
            if (isset($civil_status[$status])) {
                $civil_status[$status] = (int)$row['count'];
            } else {
                $civil_status['Other'] += (int)$row['count'];
            }
        }
    } else {
        error_log("Error executing civil status distribution query: " . $stmt->error);
    }
    $stmt->close();
}

// Get purok distribution
$purok_distribution = [];

$sql = "SELECT 
            IFNULL(purok, 'Unknown') as purok_name,
            COUNT(DISTINCT household_number) as households,
            COUNT(*) as population,
            SUM(CASE WHEN sex = 1 THEN 1 ELSE 0 END) as male,
            SUM(CASE WHEN sex = 0 THEN 1 ELSE 0 END) as female,
            SUM(CASE WHEN voter_status = 'Yes' THEN 1 ELSE 0 END) as voters,
            SUM(CASE WHEN age >= 60 THEN 1 ELSE 0 END) as seniors
        FROM register_members
        WHERE barangay_id = ? AND name IS NOT NULL
        GROUP BY purok_name
        ORDER BY purok_name";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $barangay_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $purok_distribution[] = $row;
        }
    } else {
        error_log("Error executing purok distribution query: " . $stmt->error);
    }
    $stmt->close();
}

// If no puroks found, add a default one for display purposes
if (empty($purok_distribution)) {
    $purok_distribution[] = [
        'purok_name' => 'No Data',
        'households' => 0,
        'population' => 0,
        'male' => 0,
        'female' => 0,
        'voters' => 0,
        'seniors' => 0
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Demographic Report - RBIM Super Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
            --light: #ecf0f1;
            --dark: #2c3e50;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary), #1a2530);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--secondary), #2980b9);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            font-weight: 600;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .stat-card {
            text-align: center;
            padding: 20px;
            border-radius: 10px;
            background: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }
        
        .stat-number {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--secondary);
            margin: 10px 0;
        }
        
        .stat-label {
            color: var(--dark);
            font-size: 0.95rem;
            font-weight: 500;
        }
        
        .stat-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 10px;
        }
        
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 20px;
        }
        
        .back-button {
            background: linear-gradient(135deg, var(--secondary), #2980b9);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s;
        }
        
        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .badge-status {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
        }
        
        .table th {
            background: linear-gradient(135deg, var(--primary), #1a2530);
            color: white;
            padding: 12px 15px;
        }
        
        .table td {
            padding: 12px 15px;
            vertical-align: middle;
        }
        
        .action-buttons .btn {
            border-radius: 6px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .action-buttons .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .summary-highlights {
            background: linear-gradient(135deg, #e8f4fc, #cce5ff);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .section-title {
            color: var(--primary);
            border-bottom: 2px solid var(--secondary);
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* NEW: Vertical Fast Facts Sidebar Styles */
        .main-content {
            display: flex;
            gap: 25px;
        }
        
        .content-primary {
            flex: 1;
        }
        
        .fast-facts-sidebar {
            width: 320px;
            flex-shrink: 0;
            background: linear-gradient(135deg, #fff, #f8f9fa);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
            border-left: 5px solid var(--secondary);
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        .fast-facts-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            color: var(--primary);
        }
        
        .fast-facts-header i {
            font-size: 1.8rem;
            margin-right: 10px;
            color: var(--secondary);
        }
        
        .fact-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            transition: transform 0.2s;
            margin-bottom: 12px;
        }
        
        .fact-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .fact-icon {
            width: 40px;
            height: 40px;
            background: var(--light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .fact-icon i {
            color: var(--secondary);
            font-size: 1.2rem;
        }
        
        .fact-content {
            flex-grow: 1;
        }
        
        .fact-title {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
            font-size: 0.95rem;
        }
        
        .fact-value {
            font-weight: 700;
            color: var(--primary);
            font-size: 1.1rem;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .main-content {
                flex-direction: column;
            }
            
            .fast-facts-sidebar {
                width: 100%;
                position: static;
            }
        }
        
        /* Improved header styles */
        .header-container {
            background: linear-gradient(135deg, var(--primary), #1a2530);
            color: white;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header-content {
            padding: 25px 30px;
            position: relative;
        }
        
        .header-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 8px 15px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .header-title {
            font-weight: 700;
            font-size: 2.2rem;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .header-subtitle {
            font-weight: 400;
            opacity: 0.85;
            font-size: 1.1rem;
            margin-bottom: 15px;
        }
        
        .header-meta {
            display: flex;
            gap: 20px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
            opacity: 0.9;
        }
        
        .breadcrumb-container {
            background: rgba(236, 240, 241, 0.7);
            padding: 12px 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        @media (max-width: 768px) {
            .header-title {
                font-size: 1.8rem;
            }
            
            .header-content {
                padding: 20px;
            }
            
            .header-badge {
                position: relative;
                top: 0;
                right: 0;
                margin-bottom: 15px;
                align-self: flex-start;
            }
            
            .header-meta {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Improved Header -->
        <div class="header-container">
            <div class="header-content d-flex flex-column">
                <span class="header-badge">
                    <i class="bi bi-check-circle"></i> Active Barangay
                </span>
                
                <h1 class="header-title">
                    <i class="bi bi-house-door"></i> <?php echo htmlspecialchars($barangay_name); ?> Demographic Report
                </h1>
                
                <p class="header-subtitle">RBIM Super Admin Dashboard - Bago City</p>
                
                <div class="header-meta">
                    <div class="meta-item">
                        <i class="bi bi-people"></i>
                        <span><?php echo number_format($total_residents); ?> Residents</span>
                    </div>
                    <div class="meta-item">
                        <i class="bi bi-house"></i>
                        <span><?php echo number_format($total_households); ?> Households</span>
                    </div>
                    <div class="meta-item">
                        <i class="bi bi-calendar-check"></i>
                        <span>Last updated: <?php echo $last_updated; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="breadcrumb-container">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="super_admin_dashboard.php"><i class="bi bi-house"></i> Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="barangay_management.php"><i class="bi bi-list-ul"></i> Barangays</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><i class="bi bi-bar-chart"></i> <?php echo htmlspecialchars($barangay_name); ?> Demographic Report</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <a href="barangay_management.php" class="back-button">
            <i class="bi bi-arrow-left me-2"></i> Back to Barangay List
        </a>
        
        <div class="main-content">
            <div class="content-primary">
                <!-- Barangay Info -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($barangay_name); ?></h2>
                    <div>
                        <span class="badge bg-primary me-2"><i class="bi bi-people"></i> <?php echo number_format($total_residents); ?> Residents</span>
                        <span class="badge bg-info"><i class="bi bi-house"></i> <?php echo number_format($total_households); ?> Households</span>
                    </div>
                </div>
                
                <!-- Summary Stats -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="bi bi-person"></i>
                            </div>
                            <div class="stat-number"><?php echo number_format($total_residents); ?></div>
                            <div class="stat-label">Total Population</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="bi bi-house"></i>
                            </div>
                            <div class="stat-number"><?php echo number_format($total_households); ?></div>
                            <div class="stat-label">Total Households</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="bi bi-check-square"></i>
                            </div>
                            <div class="stat-number"><?php echo number_format($registered_voters); ?></div>
                            <div class="stat-label">Registered Voters</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="bi bi-activity"></i>
                            </div>
                            <div class="stat-number"><?php echo number_format($senior_citizens); ?></div>
                            <div class="stat-label">Senior Citizens</div>
                        </div>
                    </div>
                </div>
                
                <!-- Summary Highlights -->
                <div class="summary-highlights">
                    <h4 class="section-title"><i class="bi bi-lightbulb"></i> Key Insights</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <p><i class="bi bi-arrow-up-right text-success"></i> <strong>Population Growth:</strong> Monitoring in progress</p>
                        </div>
                        <div class="col-md-4">
                            <p><i class="bi bi-gender-male text-primary"></i> <i class="bi bi-gender-female text-danger"></i> <strong>Gender Ratio:</strong> 
                                <?php 
                                $male_percent = ($total_residents > 0) ? round(($gender_distribution['Male'] / $total_residents) * 100, 1) : 0;
                                $female_percent = ($total_residents > 0) ? round(($gender_distribution['Female'] / $total_residents) * 100, 1) : 0;
                                echo $male_percent . '% Male, ' . $female_percent . '% Female'; 
                                ?>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p><i class="bi bi-house-up text-warning"></i> <strong>Average Household Size:</strong> <?php echo $avg_household_size; ?> persons per household</p>
                        </div>
                    </div>
                </div>
                
                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header"><i class="bi bi-bar-chart"></i> Population by Age Group</div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="ageGroupChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header"><i class="bi bi-pie-chart"></i> Gender Distribution</div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="genderChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Charts Row -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header"><i class="bi bi-venn"></i> Civil Status Distribution</div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="civilStatusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header"><i class="bi bi-grid-3x3"></i> Population by Purok</div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="purokChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Data Tables section removed as requested -->
            </div>
            
            <!-- Fast Facts Sidebar -->
            <div class="fast-facts-sidebar">
                <div class="fast-facts-header">
                    <i class="bi bi-info-circle"></i>
                    <h4>Fast Facts</h4>
                </div>
                
                <div class="fact-item">
                    <div class="fact-icon">
                        <i class="bi bi-gender-male"></i>
                    </div>
                    <div class="fact-content">
                        <div class="fact-title">Male Population</div>
                        <div class="fact-value"><?php echo number_format($gender_distribution['Male']); ?></div>
                    </div>
                </div>
                
                <div class="fact-item">
                    <div class="fact-icon">
                        <i class="bi bi-gender-female"></i>
                    </div>
                    <div class="fact-content">
                        <div class="fact-title">Female Population</div>
                        <div class="fact-value"><?php echo number_format($gender_distribution['Female']); ?></div>
                    </div>
                </div>
                
                <div class="fact-item">
                    <div class="fact-icon">
                        <i class="bi bi-house-up"></i>
                    </div>
                    <div class="fact-content">
                        <div class="fact-title">Avg. Household Size</div>
                        <div class="fact-value"><?php echo $avg_household_size; ?></div>
                    </div>
                </div>
                
                <div class="fact-item">
                    <div class="fact-icon">
                        <i class="bi bi-map"></i>
                    </div>
                    <div class="fact-content">
                        <div class="fact-title">Land Area</div>
                        <div class="fact-value"><?php echo $land_area; ?> sq. km</div>
                    </div>
                </div>
                
                <div class="fact-item">
                    <div class="fact-icon">
                        <i class="bi bi-signpost-2"></i>
                    </div>
                    <div class="fact-content">
                        <div class="fact-title">Distance from City</div>
                        <div class="fact-value"><?php echo $distance_from_city; ?> km</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Age Group Chart
        const ageGroupCtx = document.getElementById('ageGroupChart').getContext('2d');
        const ageGroupChart = new Chart(ageGroupCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($age_groups)); ?>,
                datasets: [{
                    label: 'Population',
                    data: <?php echo json_encode(array_values($age_groups)); ?>,
                    backgroundColor: 'rgba(52, 152, 219, 0.7)',
                    borderColor: 'rgba(52, 152, 219, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Gender Distribution Chart
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        const genderChart = new Chart(genderCtx, {
            type: 'pie',
            data: {
                labels: ['Male', 'Female'],
                datasets: [{
                    data: [<?php echo $gender_distribution['Male']; ?>, <?php echo $gender_distribution['Female']; ?>],
                    backgroundColor: [
                        'rgba(52, 152, 219, 0.7)',
                        'rgba(231, 76, 60, 0.7)'
                    ],
                    borderColor: [
                        'rgba(52, 152, 219, 1)',
                        'rgba(231, 76, 60, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
        
        // Civil Status Chart
        const civilStatusCtx = document.getElementById('civilStatusChart').getContext('2d');
        const civilStatusChart = new Chart(civilStatusCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_keys($civil_status)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($civil_status)); ?>,
                    backgroundColor: [
                        'rgba(52, 152, 219, 0.7)',
                        'rgba(46, 204, 113, 0.7)',
                        'rgba(155, 89, 182, 0.7)',
                        'rgba(231, 76, 60, 0.7)',
                        'rgba(241, 196, 15, 0.7)',
                        'rgba(149, 165, 166, 0.7)'
                    ],
                    borderColor: [
                        'rgba(52, 152, 219, 1)',
                        'rgba(46, 204, 113, 1)',
                        'rgba(155, 89, 182, 1)',
                        'rgba(231, 76, 60, 1)',
                        'rgba(241, 196, 15, 1)',
                        'rgba(149, 165, 166, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
        
        // Purok Chart
        const purokCtx = document.getElementById('purokChart').getContext('2d');
        const purokData = <?php 
            $purok_names = [];
            $purok_populations = [];
            foreach ($purok_distribution as $purok) {
                $purok_names[] = $purok['purok_name'];
                $purok_populations[] = $purok['population'];
            }
            echo json_encode(['names' => $purok_names, 'populations' => $purok_populations]);
        ?>;
        
        const purokChart = new Chart(purokCtx, {
            type: 'bar',
            data: {
                labels: purokData.names,
                datasets: [{
                    label: 'Population',
                    data: purokData.populations,
                    backgroundColor: 'rgba(46, 204, 113, 0.7)',
                    borderColor: 'rgba(46, 204, 113, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>