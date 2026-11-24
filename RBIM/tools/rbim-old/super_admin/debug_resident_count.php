<?php
// Debug script to test resident count calculation
// This script validates the resident count logic from super_admin_dashboard.php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once "../plugins/conn.php";

// Function to output test results in a consistent format
function outputTestResult($test_name, $result, $message = '') {
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid " . ($result ? "#4CAF50" : "#F44336") . "; border-radius: 5px;'>";
    echo "<strong>" . $test_name . ":</strong> ";
    echo "<span style='color: " . ($result ? "green" : "red") . ";'>" . ($result ? "PASSED" : "FAILED") . "</span>";
    if ($message) {
        echo " - " . $message;
    }
    echo "</div>";
}

echo "<html><head><title>Debug Test: Total Resident Count</title>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;line-height:1.6;} h1{color:#2196F3;} table{border-collapse:collapse;width:100%;margin:15px 0;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background-color:#f2f2f2;}</style>";
echo "</head><body>";
echo "<h1>Debug Test: Total Resident Count Validation</h1>";

// Check database connection
if (!isset($conn) || !$conn) {
    outputTestResult("Database Connection", false, "Connection failed: " . (isset($conn) ? mysqli_connect_error() : "Connection variable not set"));
    echo "</body></html>";
    exit;
}
outputTestResult("Database Connection", true, "Successfully connected to database");

// Test 1: Check if resident table exists
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'resident'");
$table_exists = ($check_table && mysqli_num_rows($check_table) > 0);
outputTestResult("Resident Table Existence", $table_exists, $table_exists ? "Table exists" : "Table does not exist");

if ($check_table) {
    mysqli_free_result($check_table);
}

// Test 2: Extract the exact code from super_admin_dashboard.php for resident count
echo "<h2>Dashboard Code Logic Verification</h2>";

$stats = [];
$stats['total_residents'] = 0;

// This is the exact code from super_admin_dashboard.php for resident count
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'resident'");
if ($check_table && mysqli_num_rows($check_table) > 0) {
    $sql = "SELECT COUNT(*) as total FROM resident";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $stats['total_residents'] = $row['total'];
        mysqli_free_result($result);
        outputTestResult("Dashboard Resident Count Logic", true, "Successfully calculated total residents: " . number_format($stats['total_residents']));
    } else {
        outputTestResult("Dashboard Resident Count Logic", false, "Error in query: " . mysqli_error($conn));
    }
    mysqli_free_result($check_table);
} else {
    outputTestResult("Dashboard Resident Count Logic", false, "Resident table not found");
}

// Test 3: Edge case - Verify count matches sum of individual records
if ($table_exists) {
    echo "<h2>Edge Case Testing</h2>";
    
    // Get total count
    $sql = "SELECT COUNT(*) as total FROM resident";
    $result = mysqli_query($conn, $sql);
    $total_count = 0;
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $total_count = $row['total'];
        mysqli_free_result($result);
    }
    
    // Get individual records and count manually
    $sql = "SELECT id FROM resident";
    $result = mysqli_query($conn, $sql);
    $manual_count = 0;
    
    if ($result) {
        $manual_count = mysqli_num_rows($result);
        mysqli_free_result($result);
    }
    
    $counts_match = ($total_count == $manual_count);
    outputTestResult("Count Consistency", $counts_match, 
        "Total count ($total_count) " . ($counts_match ? "matches" : "does not match") . 
        " manual count ($manual_count)");
    
    // Test 4: Check for NULL values in critical fields
    $sql = "SELECT COUNT(*) as null_count FROM resident WHERE id IS NULL";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $null_count = $row['null_count'];
        outputTestResult("Data Integrity - NULL IDs", $null_count == 0, 
            $null_count == 0 ? "No NULL IDs found" : "Found $null_count records with NULL IDs");
        mysqli_free_result($result);
    }
}

// Test 5: Verify UI display
echo "<h2>UI Display Verification</h2>";
echo "<p>To verify the UI display:</p>";
echo "<ol>";
echo "<li>The dashboard should show a stat card labeled 'TOTAL RESIDENTS'</li>";
echo "<li>The value displayed should be: " . number_format($stats['total_residents']) . "</li>";
echo "<li>The card should be positioned adjacent to the 'Total Household' card</li>";
echo "</ol>";

echo "<p><a href='super_admin_dashboard.php' style='background-color:#2196F3;color:white;padding:10px 15px;text-decoration:none;border-radius:4px;display:inline-block;'>View Dashboard</a></p>";

echo "<h2>Summary</h2>";
echo "<p>The resident count calculation logic has been tested.</p>";
echo "<p>Total residents: <strong>" . number_format($stats['total_residents']) . "</strong></p>";

echo "</body></html>";
?>