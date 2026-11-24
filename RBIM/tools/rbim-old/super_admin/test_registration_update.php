<?php
// Include database connection
require_once('../plugins/conn.php');

// Set error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Registration Update Test</h1>";

// Test 1: Check if register_members table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'register_members'");
if (mysqli_num_rows($table_check) > 0) {
    echo "<p style='color:green'>✓ register_members table exists</p>";
} else {
    echo "<p style='color:red'>✗ register_members table does not exist</p>";
}

// Test 2: Count records in register_members table
$count_query = "SELECT COUNT(*) as total FROM register_members";
$count_result = mysqli_query($conn, $count_query);
if ($count_result) {
    $row = mysqli_fetch_assoc($count_result);
    echo "<p>Current register_members count: <strong>{$row['total']}</strong></p>";
} else {
    echo "<p style='color:red'>Error counting register_members: " . mysqli_error($conn) . "</p>";
}

// Test 3: Check if the dashboard query works
$dashboard_query = "SELECT COUNT(*) as total FROM register_members";
$dashboard_result = mysqli_query($conn, $dashboard_query);
if ($dashboard_result) {
    $row = mysqli_fetch_assoc($dashboard_result);
    echo "<p>Dashboard should show: <strong>{$row['total']}</strong> residents</p>";
} else {
    echo "<p style='color:red'>Dashboard query error: " . mysqli_error($conn) . "</p>";
}

// Test 4: Insert a test record to verify database updates work
echo "<h2>Testing Record Insertion</h2>";
echo "<p>Attempting to insert a test record...</p>";

// Begin transaction
mysqli_begin_transaction($conn);

try {
    // Create a test record
    $household_number = 999999;
    $session_id = 999999;
    $name = "Test Resident " . date('YmdHis');
    
    // Insert test record
    $insert_query = "INSERT INTO register_members (household_number, session_id, name, sex, age) 
                    VALUES (?, ?, ?, ?, ?)";
    
    $insert_stmt = mysqli_prepare($conn, $insert_query);
    if (!$insert_stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }
    
    $sex = 1;
    $age = 30;
    mysqli_stmt_bind_param($insert_stmt, "iisii", $household_number, $session_id, $name, $sex, $age);
    
    if (!mysqli_stmt_execute($insert_stmt)) {
        throw new Exception("Error executing statement: " . mysqli_stmt_error($insert_stmt));
    }
    
    // Get the newly inserted member ID
    $member_id = mysqli_insert_id($conn);
    
    if ($member_id <= 0) {
        throw new Exception("Failed to retrieve member ID after insertion");
    }
    
    // Commit the transaction
    mysqli_commit($conn);
    
    echo "<p style='color:green'>✓ Test record inserted successfully! Member ID: {$member_id}</p>";
    
    // Count records again to verify the increase
    $count_query = "SELECT COUNT(*) as total FROM register_members";
    $count_result = mysqli_query($conn, $count_query);
    if ($count_result) {
        $row = mysqli_fetch_assoc($count_result);
        echo "<p>Updated register_members count: <strong>{$row['total']}</strong></p>";
    }
    
    // Clean up the test record
    $delete_query = "DELETE FROM register_members WHERE member_id = ?";
    $delete_stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($delete_stmt, "i", $member_id);
    mysqli_stmt_execute($delete_stmt);
    echo "<p>Test record cleaned up.</p>";
    
} catch (Exception $e) {
    // Rollback the transaction on error
    mysqli_rollback($conn);
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

// Test 5: Check if the dashboard code is correct
echo "<h2>Dashboard Code Check</h2>";
echo "<pre>";
$dashboard_file = file_get_contents('super_admin_dashboard.php');
if ($dashboard_file) {
    // Extract the resident count code section
    if (preg_match('/\/\/ Total Residents.*?\$stats\[\'total_residents\'\].*?;/s', $dashboard_file, $matches)) {
        echo htmlspecialchars($matches[0]);
    } else {
        echo "Could not find resident count code in dashboard file.";
    }
}
echo "</pre>";

// Add a link to fix the dashboard if needed
echo "<h2>Fix Options</h2>";
echo "<p><a href='fix_dashboard_refresh.php' style='background-color:#4CAF50;color:white;padding:10px 15px;text-decoration:none;border-radius:4px;'>Fix Dashboard Refresh</a></p>";

// Add a link back to the dashboard
echo "<p><a href='super_admin_dashboard.php' style='background-color:#2196F3;color:white;padding:10px 15px;text-decoration:none;border-radius:4px;'>Return to Dashboard</a></p>";
?>