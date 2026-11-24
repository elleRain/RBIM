<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to users in production
ini_set('log_errors', 1); // Log errors instead
ini_set('error_log', '../error_log.txt'); // Set error log path

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../index.php");
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

// Include database connection
require_once "../plugins/conn.php";

// Check if database connection is successful
if (!$conn) {
    error_log("Database connection failed in admin dashboard");
    die("Database connection error. Please try again later.");
}

// Function to log errors
function logError($message) {
    error_log(date('Y-m-d H:i:s') . " - " . $message);
}

// Get barangay information for the admin
$barangay_id = $_SESSION["barangay_id"] ?? 0;
$barangay_name = "Unknown Barangay";

if ($barangay_id > 0) {
    $sql = "SELECT barangay_name FROM barangay WHERE barangay_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $barangay_id);
        $stmt->execute();
        $stmt->bind_result($barangay_name);
        $stmt->fetch();
        $stmt->close();
    }
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

// Get total residents count across ALL barangays
$total_residents = 0;

// First try to get total members from survey_session table (all barangays)
$census_residents = 0;
$sql = "SELECT SUM(total_members) FROM survey_session";
if ($stmt = $conn->prepare($sql)) {
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

// If no results from survey_session, fall back to register_members table (all barangays)
if ($total_residents == 0) {
    // Count all members in register_members table with valid data across all barangays
    $sql = "SELECT COUNT(*) FROM register_members WHERE name IS NOT NULL";
    if ($stmt = $conn->prepare($sql)) {
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

// Get gender distribution counts for the current barangay
$male_residents = 0;
$female_residents = 0;

// Get gender counts from register_members table joined with survey_session for the current barangay
// Note: sex = 1 for male, sex = 2 for female (based on actual database values)
$sql = "SELECT 
    SUM(CASE WHEN rm.sex = 1 THEN 1 ELSE 0 END) as male_count,
    SUM(CASE WHEN rm.sex = 2 THEN 1 ELSE 0 END) as female_count
    FROM register_members rm 
    JOIN survey_session ss ON rm.session_id = ss.session_id
    WHERE ss.barangay_id = ? AND rm.name IS NOT NULL";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $barangay_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $male_residents = (int)($row['male_count'] ?? 0);
            $female_residents = (int)($row['female_count'] ?? 0);
        }
    } else {
        error_log("Error executing gender distribution query: " . $stmt->error);
    }
    $stmt->close();
}

// DEMOGRAPHIC FIT ANALYSIS - New Feature Implementation
// Get comprehensive demographic data for the current barangay

// Age Group Distribution
$age_groups = [
    'children' => 0,    // 0-17
    'adults' => 0,      // 18-59
    'seniors' => 0      // 60+
];

$sql = "SELECT 
    SUM(CASE WHEN rm.age BETWEEN 0 AND 17 THEN 1 ELSE 0 END) as children,
    SUM(CASE WHEN rm.age BETWEEN 18 AND 59 THEN 1 ELSE 0 END) as adults,
    SUM(CASE WHEN rm.age >= 60 THEN 1 ELSE 0 END) as seniors
    FROM register_members rm 
    JOIN survey_session ss ON rm.session_id = ss.session_id
    WHERE ss.barangay_id = ? AND rm.name IS NOT NULL AND rm.age IS NOT NULL";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $barangay_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $age_groups['children'] = (int)($row['children'] ?? 0);
            $age_groups['adults'] = (int)($row['adults'] ?? 0);
            $age_groups['seniors'] = (int)($row['seniors'] ?? 0);
        }
    } else {
        error_log("Error executing age group query: " . $stmt->error);
    }
    $stmt->close();
}

// Education Level Distribution
$education_levels = [
    'elementary' => 0,
    'high_school' => 0,
    'college' => 0,
    'no_education' => 0
];

$sql = "SELECT 
    SUM(CASE WHEN rm.highest_education LIKE '%Elementary%' THEN 1 ELSE 0 END) as elementary,
    SUM(CASE WHEN rm.highest_education LIKE '%High%' OR rm.highest_education LIKE '%Secondary%' THEN 1 ELSE 0 END) as high_school,
    SUM(CASE WHEN rm.highest_education LIKE '%College%' OR rm.highest_education LIKE '%University%' THEN 1 ELSE 0 END) as college,
    SUM(CASE WHEN rm.highest_education IS NULL OR rm.highest_education = '' OR rm.highest_education LIKE '%No%' THEN 1 ELSE 0 END) as no_education
    FROM register_members rm 
    JOIN survey_session ss ON rm.session_id = ss.session_id
    WHERE ss.barangay_id = ? AND rm.name IS NOT NULL";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $barangay_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $education_levels['elementary'] = (int)($row['elementary'] ?? 0);
            $education_levels['high_school'] = (int)($row['high_school'] ?? 0);
            $education_levels['college'] = (int)($row['college'] ?? 0);
            $education_levels['no_education'] = (int)($row['no_education'] ?? 0);
        }
    } else {
        error_log("Error executing education level query: " . $stmt->error);
    }
    $stmt->close();
}

// Marital Status Distribution
$marital_status = [
    'single' => 0,
    'married' => 0,
    'widowed' => 0,
    'separated' => 0
];

$sql = "SELECT 
    SUM(CASE WHEN rm.marital_status LIKE '%Single%' THEN 1 ELSE 0 END) as single,
    SUM(CASE WHEN rm.marital_status LIKE '%Married%' THEN 1 ELSE 0 END) as married,
    SUM(CASE WHEN rm.marital_status LIKE '%Widow%' THEN 1 ELSE 0 END) as widowed,
    SUM(CASE WHEN rm.marital_status LIKE '%Separated%' OR rm.marital_status LIKE '%Divorced%' THEN 1 ELSE 0 END) as separated
    FROM register_members rm 
    JOIN survey_session ss ON rm.session_id = ss.session_id
    WHERE ss.barangay_id = ? AND rm.name IS NOT NULL AND rm.age >= 18";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $barangay_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $marital_status['single'] = (int)($row['single'] ?? 0);
            $marital_status['married'] = (int)($row['married'] ?? 0);
            $marital_status['widowed'] = (int)($row['widowed'] ?? 0);
            $marital_status['separated'] = (int)($row['separated'] ?? 0);
        }
    } else {
        error_log("Error executing marital status query: " . $stmt->error);
    }
    $stmt->close();
}

// Ethnicity Distribution
$ethnicity_data = [];
$sql = "SELECT rm.ethnicity, COUNT(*) as count
    FROM register_members rm 
    JOIN survey_session ss ON rm.session_id = ss.session_id
    WHERE ss.barangay_id = ? AND rm.name IS NOT NULL AND rm.ethnicity IS NOT NULL AND rm.ethnicity != ''
    GROUP BY rm.ethnicity
    ORDER BY count DESC
    LIMIT 5";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $barangay_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $ethnicity_data[] = $row;
        }
    } else {
        error_log("Error executing ethnicity query: " . $stmt->error);
    }
    $stmt->close();
}

// Household Composition Analysis
$household_composition = [
    'average_household_size' => 0,
    'single_person_households' => 0,
    'nuclear_families' => 0,
    'extended_families' => 0
];

$sql = "SELECT 
    AVG(ss.total_members) as avg_size,
    SUM(CASE WHEN ss.total_members = 1 THEN 1 ELSE 0 END) as single_person,
    SUM(CASE WHEN ss.total_members BETWEEN 2 AND 4 THEN 1 ELSE 0 END) as nuclear,
    SUM(CASE WHEN ss.total_members > 4 THEN 1 ELSE 0 END) as extended
    FROM survey_session ss
    WHERE ss.barangay_id = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $barangay_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $household_composition['average_household_size'] = round($row['avg_size'] ?? 0, 1);
            $household_composition['single_person_households'] = (int)($row['single_person'] ?? 0);
            $household_composition['nuclear_families'] = (int)($row['nuclear'] ?? 0);
            $household_composition['extended_families'] = (int)($row['extended'] ?? 0);
        }
    } else {
        error_log("Error executing household composition query: " . $stmt->error);
    }
    $stmt->close();
}

// Calculate Demographic Fit Score (0-100)
$total_population = $male_residents + $female_residents;
$demographic_fit_score = 0;

if ($total_population > 0) {
    // Age diversity score (balanced age distribution gets higher score)
    $total_age_population = $age_groups['children'] + $age_groups['adults'] + $age_groups['seniors'];
    $age_balance_score = 0;
    if ($total_age_population > 0) {
        $children_ratio = $age_groups['children'] / $total_age_population;
        $adults_ratio = $age_groups['adults'] / $total_age_population;
        $seniors_ratio = $age_groups['seniors'] / $total_age_population;
        
        // Ideal ratios: 30% children, 60% adults, 10% seniors
        $age_balance_score = 100 - (
            abs($children_ratio - 0.30) * 100 +
            abs($adults_ratio - 0.60) * 100 +
            abs($seniors_ratio - 0.10) * 100
        ) / 3;
    }
    
    // Gender balance score
    $gender_balance_score = 0;
    if ($total_population > 0) {
        $male_ratio = $male_residents / $total_population;
        $gender_balance_score = 100 - abs($male_ratio - 0.50) * 200; // Closer to 50-50 is better
    }
    
    // Education diversity score
    $total_education = array_sum($education_levels);
    $education_score = 0;
    if ($total_education > 0) {
        $college_ratio = $education_levels['college'] / $total_education;
        $education_score = min($college_ratio * 200, 100); // Higher education ratio is better
    }
    
    // Household composition score
    $household_score = 0;
    if ($household_composition['average_household_size'] > 0) {
        // Ideal household size is around 3-4 members
        $ideal_size = 3.5;
        $size_diff = abs($household_composition['average_household_size'] - $ideal_size);
        $household_score = max(0, 100 - ($size_diff * 20));
    }
    
    // Calculate overall demographic fit score
    $demographic_fit_score = round(($age_balance_score + $gender_balance_score + $education_score + $household_score) / 4, 1);
}

// End of Demographic Fit Analysis

// Get recent households (last 5) - First try from survey_session table (census form data)
$recent_households = [];
$sql = "SELECT 
            ss.session_id, 
            ss.household_number, 
            ss.household_head, 
            ss.address, 
            ss.visit_date as date_added,
            ss.total_members
        FROM survey_session ss 
        WHERE ss.barangay_id = ? 
        ORDER BY ss.date_encoded DESC LIMIT 5";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $barangay_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $recent_households[] = $row;
    }
    $stmt->close();
}

// If no results from survey_session, try register_members table
if (empty($recent_households)) {
    $sql = "SELECT 
                rm.household_number, 
                MAX(CASE WHEN rm.relationship_to_head = 'Head' THEN CONCAT(rm.first_name, ' ', rm.last_name) ELSE NULL END) as household_head,
                MAX(rm.address) as address, 
                MAX(rm.date_registered) as date_added,
                COUNT(*) as total_members
            FROM register_members rm
            WHERE rm.barangay_id = ? AND rm.first_name IS NOT NULL
            GROUP BY rm.household_number
            ORDER BY MAX(rm.date_registered) DESC LIMIT 5";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $barangay_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $recent_households[] = $row;
        }
        $stmt->close();
    }
}

// If still no results, fall back to households table
if (empty($recent_households)) {
    $sql = "SELECT 
               h.id as household_id, 
               h.household_head, 
               h.address, 
               h.date_added,
               0 as total_members 
           FROM households h 
           WHERE h.barangay_id = ? 
           ORDER BY h.date_added DESC LIMIT 5";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $barangay_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $recent_households[] = $row;
        }
        $stmt->close();
    }
}










?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RBIM Admin Dashboard</title>
    <link rel="stylesheet" href="../src/design-system.css">
    <link rel="stylesheet" href="../src/output.css">
    <!-- Chart.js for data visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Dashboard specific styles */
        body {
            font-family: var(--font-family);
            background-color: #f5f5f5;
            color: var(--neutral-900);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .main-content {
            margin-left: 200px; /* Changed from 220px to match sidebar width */
            padding: var(--spacing-2) var(--spacing-6);
            transition: margin 0.3s ease;
            margin-top: 10px;
            min-height: calc(100vh - 40px);
        }
        
        body.sidebar-collapsed .main-content {
            margin-left: 60px; /* Changed from 70px to match collapsed sidebar width */
            margin-top: 40px;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-2); /* Reduced from spacing-3 */
        }
        
        .dashboard-title {
            color: var(--neutral-900);
            font-size: var(--font-size-2xl);
            font-weight: var(--font-weight-bold);
            margin: 0;
        }
        
        .dashboard-subtitle {
            color: var(--neutral-600);
            font-size: var(--font-size-md);
            margin: var(--spacing-1) 0 var(--spacing-3) 0; /* Reduced top and bottom margin */
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: var(--spacing-2); /* Reduced from spacing-3 */
            margin-bottom: var(--spacing-3); /* Reduced from spacing-4 */
        }
        
        /* Responsive adjustments for stats grid */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (min-width: 769px) and (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (min-width: 1025px) {
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        
        .stat-card {
            background-color: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            padding: var(--spacing-2); /* Reduced from spacing-3 */
            border-left: 4px solid var(--primary-500);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        
        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px; /* Reduced from 16px */
        }
        
        .stat-card-title {
            color: var(--neutral-600);
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-medium);
            margin: 0;
        }
        
        .stat-card-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
        }
        
        .stat-card-value {
            font-size: var(--font-size-3xl);
            font-weight: var(--font-weight-bold);
            margin: 0;
            color: var(--neutral-900);
        }
        
        .stat-card-description {
            color: var(--neutral-500);
            font-size: var(--font-size-xs);
            margin: 8px 0 0 0;
        }
        
        .dashboard-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-3); /* Reduced from spacing-4 */
            margin-bottom: var(--spacing-3); /* Reduced from spacing-4 */
        }
        
        @media (max-width: 992px) {
            .dashboard-row {
                grid-template-columns: 1fr;
            }
        }
        
        .chart-card, .table-card {
            background-color: var(--white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            padding: var(--spacing-2); /* Reduced from spacing-3 */
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 1px solid var(--neutral-100);
        }
        
        .chart-card-header, .table-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px; /* Reduced from 16px */
        }
        
        .chart-card-title, .table-card-title {
            color: var(--neutral-800);
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-semibold);
            margin: 0;
        }
        
        .chart-container {
            height: 280px; /* Reduced from 300px */
            position: relative;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .dashboard-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .dashboard-table th, .dashboard-table td {
            padding: var(--spacing-1) var(--spacing-2); /* Reduced vertical padding */
            text-align: left;
            border-bottom: 1px solid var(--neutral-100);
        }
        
        .dashboard-table th {
            background-color: var(--neutral-100);
            color: var(--neutral-700);
            font-weight: var(--font-weight-semibold);
            font-size: var(--font-size-sm);
        }
        
        .dashboard-table tr {
            border-bottom: 1px solid var(--neutral-200);
        }
        
        .dashboard-table tr:last-child {
            border-bottom: none;
        }
        
        .dashboard-table td {
            color: var(--neutral-800);
            font-size: var(--font-size-sm);
        }
        
        .dashboard-table tr:hover {
            background-color: var(--neutral-50);
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: var(--border-radius-full);
            font-size: var(--font-size-xs);
            font-weight: var(--font-weight-medium);
        }
        
        .badge-success {
            background-color: var(--success);
            color: var(--white);
        }
        
        .badge-warning {
            background-color: var(--warning);
            color: var(--white);
        }
        
        .badge-danger {
            background-color: var(--danger);
            color: var(--white);
        }
        
        .badge-info {
            background-color: var(--info);
            color: var(--white);
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            border-radius: var(--border-radius-md);
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-medium);
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-secondary {
            background-color: var(--neutral-200);
            color: var(--neutral-700);
        }
        
        .btn-secondary:hover {
            background-color: var(--neutral-300);
        }
        
        .btn-sm {
            padding: 4px 8px;
            font-size: var(--font-size-xs);
        }
        
        .btn-icon {
            width: 32px;
            height: 32px;
            padding: 0;
            border-radius: var(--border-radius-full);
        }
        
        .config-card {
            background-color: var(--white);
            border-radius: var(--border-radius-lg);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 24px;
        }
        
        .config-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        
        .config-card-title {
            color: var(--neutral-800);
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-semibold);
            margin: 0;
        }
        
        .config-form-group {
            margin-bottom: 16px;
        }
        
        .config-form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--neutral-700);
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-medium);
        }
        
        .config-form-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--neutral-300);
            border-radius: var(--border-radius-md);
            font-size: var(--font-size-sm);
            color: var(--neutral-800);
            transition: border-color 0.2s ease;
        }
        
        .config-form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px var(--primary-light);
        }
        
        .config-form-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--neutral-300);
            border-radius: var(--border-radius-md);
            font-size: var(--font-size-sm);
            color: var(--neutral-800);
            background-color: var(--white);
            transition: border-color 0.2s ease;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236b7280' viewBox='0 0 16 16'%3E%3Cpath d='M8 11.5l-5-5h10l-5 5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
        }
        
        .config-form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px var(--primary-light);
        }
        
        .config-form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 20px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }

        /* DEMOGRAPHIC FIT ANALYSIS STYLES */
        .demographic-section {
            margin: var(--spacing-4) 0;
            background-color: var(--white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            padding: var(--spacing-4);
            border: 1px solid var(--neutral-100);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-4);
            padding-bottom: var(--spacing-3);
            border-bottom: 2px solid var(--neutral-100);
        }

        .section-title {
            color: var(--neutral-800);
            font-size: var(--font-size-2xl);
            font-weight: var(--font-weight-bold);
            margin: 0;
        }

        .demographic-score {
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
        }

        .score-label {
            color: var(--neutral-600);
            font-size: var(--font-size-md);
            font-weight: var(--font-weight-medium);
        }

        .score-value {
            font-size: var(--font-size-xl);
            font-weight: var(--font-weight-bold);
            padding: 8px 16px;
            border-radius: var(--radius-lg);
            color: var(--white);
        }

        .score-good {
            background-color: var(--success);
        }

        .score-average {
            background-color: var(--warning);
        }

        .score-poor {
            background-color: var(--danger);
        }

        .demographic-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: var(--spacing-3);
        }

        .demographic-card {
            background-color: var(--neutral-50);
            border-radius: var(--radius-lg);
            padding: var(--spacing-3);
            border: 1px solid var(--neutral-200);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .demographic-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-3);
        }

        .card-title {
            color: var(--neutral-800);
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-semibold);
            margin: 0;
        }

        .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: var(--font-size-lg);
        }

        .chart-container {
            height: 200px;
            margin-bottom: var(--spacing-3);
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--white);
            border-radius: var(--radius-md);
            border: 1px solid var(--neutral-200);
        }

        .chart-legend {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: var(--font-size-sm);
            color: var(--neutral-700);
        }

        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            flex-shrink: 0;
        }

        .household-stats {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-2);
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background-color: var(--white);
            border-radius: var(--radius-md);
            border: 1px solid var(--neutral-200);
        }

        .stat-label {
            color: var(--neutral-600);
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-medium);
        }

        .stat-value {
            color: var(--neutral-800);
            font-size: var(--font-size-md);
            font-weight: var(--font-weight-bold);
        }

        .ethnicity-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .ethnicity-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background-color: var(--white);
            border-radius: var(--radius-md);
            border: 1px solid var(--neutral-200);
        }

        .ethnicity-name {
            color: var(--neutral-700);
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-medium);
        }

        .ethnicity-count {
            color: var(--neutral-800);
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-bold);
            background-color: var(--primary);
            color: var(--white);
            padding: 4px 8px;
            border-radius: var(--radius-full);
        }

        .insights-card {
            grid-column: 1 / -1;
        }

        .insights-content {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-2);
        }

        .insight-item {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            padding: 12px;
            background-color: var(--white);
            border-radius: var(--radius-md);
            border-left: 4px solid var(--primary);
            margin: 0;
            color: var(--neutral-700);
            font-size: var(--font-size-sm);
            line-height: 1.5;
        }

        .insight-item i {
            color: var(--primary);
            margin-top: 2px;
            flex-shrink: 0;
        }

        .no-data {
            text-align: center;
            color: var(--neutral-500);
            font-style: italic;
            padding: var(--spacing-3);
            margin: 0;
        }

        /* Responsive adjustments for demographic section */
        @media (max-width: 768px) {
            .demographic-grid {
                grid-template-columns: 1fr;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--spacing-2);
            }
            
            .demographic-score {
                align-self: flex-end;
            }
        }

        @media (max-width: 480px) {
            .demographic-section {
                padding: var(--spacing-2);
            }
            
            .demographic-card {
                padding: var(--spacing-2);
            }
        }
    </style>
</head>
<body>
    <!-- Include sidebar and navbar -->
    <?php include "admin_sidebar.php"; ?>
    <?php include "admin_navbar.php"; ?>

    <div class="main-content">

        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-header">
                    <h3 class="stat-card-title">TOTAL HOUSEHOLDS</h3>
                    <div class="stat-card-icon" style="background-color: var(--primary);">
                        <i class="fas fa-home"></i>
                    </div>
                </div>
                <h2 class="stat-card-value"><?php echo number_format($total_households); ?></h2>
                <p class="stat-card-description">Registered households in the barangay</p>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <h3 class="stat-card-title">TOTAL RESIDENTS</h3>
                    <div class="stat-card-icon" style="background-color: var(--secondary);">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <h2 class="stat-card-value"><?php echo number_format($total_residents); ?></h2>
                <p class="stat-card-description">Total population in the barangay</p>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <h3 class="stat-card-title">MALE RESIDENTS</h3>
                    <div class="stat-card-icon" style="background-color: #3B82F6;">
                        <i class="fas fa-male"></i>
                    </div>
                </div>
                <h2 class="stat-card-value"><?php echo number_format($male_residents); ?></h2>
                <p class="stat-card-description">Male population in the barangay</p>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <h3 class="stat-card-title">FEMALE RESIDENTS</h3>
                    <div class="stat-card-icon" style="background-color: #EC4899;">
                        <i class="fas fa-female"></i>
                    </div>
                </div>
                <h2 class="stat-card-value"><?php echo number_format($female_residents); ?></h2>
                <p class="stat-card-description">Female population in the barangay</p>
            </div>


        </div>

        <!-- DEMOGRAPHIC FIT ANALYSIS SECTION -->
        <div class="demographic-section">
            <div class="section-header">
                <h2 class="section-title">Demographic Fit Analysis</h2>
                <div class="demographic-score">
                    <span class="score-label">Overall Fit Score:</span>
                    <span class="score-value <?php echo $demographic_fit_score >= 70 ? 'score-good' : ($demographic_fit_score >= 50 ? 'score-average' : 'score-poor'); ?>">
                        <?php echo $demographic_fit_score; ?>%
                    </span>
                </div>
            </div>

            <!-- Age Distribution Chart -->
            <div class="demographic-grid">
                <div class="demographic-card">
                    <div class="card-header">
                        <h3 class="card-title">Age Distribution</h3>
                        <div class="card-icon">
                            <i class="fas fa-birthday-cake"></i>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="ageChart" width="300" height="200"></canvas>
                    </div>
                    <div class="chart-legend">
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: #3B82F6;"></span>
                            <span>Children (0-17): <?php echo $age_groups['children']; ?></span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: #10B981;"></span>
                            <span>Adults (18-59): <?php echo $age_groups['adults']; ?></span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: #F59E0B;"></span>
                            <span>Seniors (60+): <?php echo $age_groups['seniors']; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Education Level Chart -->
                <div class="demographic-card">
                    <div class="card-header">
                        <h3 class="card-title">Education Levels</h3>
                        <div class="card-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="educationChart" width="300" height="200"></canvas>
                    </div>
                    <div class="chart-legend">
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: #EF4444;"></span>
                            <span>No Education: <?php echo $education_levels['no_education']; ?></span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: #F59E0B;"></span>
                            <span>Elementary: <?php echo $education_levels['elementary']; ?></span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: #3B82F6;"></span>
                            <span>High School: <?php echo $education_levels['high_school']; ?></span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: #10B981;"></span>
                            <span>College: <?php echo $education_levels['college']; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Marital Status Chart -->
                <div class="demographic-card">
                    <div class="card-header">
                        <h3 class="card-title">Marital Status (Adults)</h3>
                        <div class="card-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="maritalChart" width="300" height="200"></canvas>
                    </div>
                    <div class="chart-legend">
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: #3B82F6;"></span>
                            <span>Single: <?php echo $marital_status['single']; ?></span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: #10B981;"></span>
                            <span>Married: <?php echo $marital_status['married']; ?></span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: #F59E0B;"></span>
                            <span>Widowed: <?php echo $marital_status['widowed']; ?></span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: #EF4444;"></span>
                            <span>Separated: <?php echo $marital_status['separated']; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Household Composition -->
                <div class="demographic-card">
                    <div class="card-header">
                        <h3 class="card-title">Household Composition</h3>
                        <div class="card-icon">
                            <i class="fas fa-home"></i>
                        </div>
                    </div>
                    <div class="household-stats">
                        <div class="stat-item">
                            <span class="stat-label">Average Household Size:</span>
                            <span class="stat-value"><?php echo $household_composition['average_household_size']; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Single Person Households:</span>
                            <span class="stat-value"><?php echo $household_composition['single_person_households']; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Nuclear Families (2-4 members):</span>
                            <span class="stat-value"><?php echo $household_composition['nuclear_families']; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Extended Families (5+ members):</span>
                            <span class="stat-value"><?php echo $household_composition['extended_families']; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Ethnicity Distribution -->
                <div class="demographic-card">
                    <div class="card-header">
                        <h3 class="card-title">Top Ethnicities</h3>
                        <div class="card-icon">
                            <i class="fas fa-globe"></i>
                        </div>
                    </div>
                    <div class="ethnicity-list">
                        <?php if (empty($ethnicity_data)): ?>
                            <p class="no-data">No ethnicity data available</p>
                        <?php else: ?>
                            <?php foreach ($ethnicity_data as $index => $ethnicity): ?>
                                <div class="ethnicity-item">
                                    <span class="ethnicity-name"><?php echo htmlspecialchars($ethnicity['ethnicity']); ?></span>
                                    <span class="ethnicity-count"><?php echo $ethnicity['count']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Demographic Insights -->
                <div class="demographic-card insights-card">
                    <div class="card-header">
                        <h3 class="card-title">Demographic Insights</h3>
                        <div class="card-icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                    </div>
                    <div class="insights-content">
                        <?php
                        $insights = [];
                        
                        // Age distribution insights
                        $total_age_pop = $age_groups['children'] + $age_groups['adults'] + $age_groups['seniors'];
                        if ($total_age_pop > 0) {
                            $children_percentage = round(($age_groups['children'] / $total_age_pop) * 100, 1);
                            $adults_percentage = round(($age_groups['adults'] / $total_age_pop) * 100, 1);
                            $seniors_percentage = round(($age_groups['seniors'] / $total_age_pop) * 100, 1);
                            
                            if ($children_percentage > 40) {
                                $insights[] = "High youth population ({$children_percentage}%) indicates potential for future growth.";
                            }
                            if ($seniors_percentage > 15) {
                                $insights[] = "Significant senior population ({$seniors_percentage}%) may require enhanced healthcare services.";
                            }
                            if ($adults_percentage > 70) {
                                $insights[] = "Strong working-age population ({$adults_percentage}%) supports economic development.";
                            }
                        }
                        
                        // Education insights
                        $total_education = array_sum($education_levels);
                        if ($total_education > 0) {
                            $college_percentage = round(($education_levels['college'] / $total_education) * 100, 1);
                            if ($college_percentage > 30) {
                                $insights[] = "High college education rate ({$college_percentage}%) indicates skilled workforce.";
                            } elseif ($college_percentage < 10) {
                                $insights[] = "Low college education rate ({$college_percentage}%) suggests need for educational programs.";
                            }
                        }
                        
                        // Gender balance insight
                        if ($total_population > 0) {
                            $male_percentage = round(($male_residents / $total_population) * 100, 1);
                            if (abs($male_percentage - 50) < 5) {
                                $insights[] = "Well-balanced gender distribution ({$male_percentage}% male, " . (100 - $male_percentage) . "% female).";
                            }
                        }
                        
                        // Household size insight
                        if ($household_composition['average_household_size'] > 5) {
                            $insights[] = "Large average household size ({$household_composition['average_household_size']}) may indicate extended family structures.";
                        } elseif ($household_composition['average_household_size'] < 2.5) {
                            $insights[] = "Small average household size ({$household_composition['average_household_size']}) indicates nuclear family preference.";
                        }
                        ?>
                        
                        <?php if (empty($insights)): ?>
                            <p class="insight-item">Demographic data is being analyzed. More insights will be available as data grows.</p>
                        <?php else: ?>
                            <?php foreach ($insights as $insight): ?>
                                <p class="insight-item"><i class="fas fa-arrow-right"></i> <?php echo $insight; ?></p>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        

        


        <!-- Recent Households Table -->
        <div class="table-card">
            <div class="table-card-header">
                <h3 class="table-card-title">Recent Households</h3>
                <a href="../rbim_census_from/household_list.php" class="btn btn-secondary btn-sm">View All</a>
            </div>
            <div class="table-responsive">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Household #</th>
                            <th>Household Head</th>
                            <th>Address</th>
                            <th>Total Members</th>
                            <th>Date Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_households)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">No households found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_households as $household): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($household['household_number']); ?></td>
                                    <td><?php echo htmlspecialchars($household['household_head']); ?></td>
                                    <td><?php echo htmlspecialchars($household['address']); ?></td>
                                    <td><?php echo htmlspecialchars($household['total_members']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($household['date_added'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <?php 
                                            // Determine the correct view URL based on data source
                                            $view_url = isset($household['session_id']) ? 
                                                "../rbim_census_from/view_household.php?session_id=" . $household['session_id'] : 
                                                "../rbim_census_from/view_household.php?id=" . $household['household_number'];
                                            
                                            // Determine the correct edit URL based on data source
                                            $edit_url = isset($household['session_id']) ? 
                                                "../rbim_census_from/edit_household.php?session_id=" . $household['session_id'] : 
                                                "../rbim_census_from/edit_household.php?id=" . $household['household_number'];
                                            ?>
                                            <a href="<?php echo $view_url; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo $edit_url; ?>" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>


    </div>

    <!-- JavaScript for Charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Toggle sidebar collapse
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize demographic charts
            initializeDemographicCharts();
        });

        function initializeDemographicCharts() {
            // Age Distribution Chart
            const ageCtx = document.getElementById('ageChart');
            if (ageCtx) {
                new Chart(ageCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Children (0-17)', 'Adults (18-59)', 'Seniors (60+)'],
                        datasets: [{
                            data: [
                                <?php echo $age_groups['children']; ?>,
                                <?php echo $age_groups['adults']; ?>,
                                <?php echo $age_groups['seniors']; ?>
                            ],
                            backgroundColor: ['#3B82F6', '#10B981', '#F59E0B'],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                        return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Education Level Chart
            const educationCtx = document.getElementById('educationChart');
            if (educationCtx) {
                new Chart(educationCtx, {
                    type: 'bar',
                    data: {
                        labels: ['No Education', 'Elementary', 'High School', 'College'],
                        datasets: [{
                            data: [
                                <?php echo $education_levels['no_education']; ?>,
                                <?php echo $education_levels['elementary']; ?>,
                                <?php echo $education_levels['high_school']; ?>,
                                <?php echo $education_levels['college']; ?>
                            ],
                            backgroundColor: ['#EF4444', '#F59E0B', '#3B82F6', '#10B981'],
                            borderWidth: 1,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ' + context.parsed.y + ' people';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }

            // Marital Status Chart
            const maritalCtx = document.getElementById('maritalChart');
            if (maritalCtx) {
                new Chart(maritalCtx, {
                    type: 'pie',
                    data: {
                        labels: ['Single', 'Married', 'Widowed', 'Separated'],
                        datasets: [{
                            data: [
                                <?php echo $marital_status['single']; ?>,
                                <?php echo $marital_status['married']; ?>,
                                <?php echo $marital_status['widowed']; ?>,
                                <?php echo $marital_status['separated']; ?>
                            ],
                            backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444'],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                        return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }
    </script>
</body>
</html>