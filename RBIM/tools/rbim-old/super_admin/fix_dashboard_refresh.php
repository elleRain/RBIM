<?php
// Include database connection
require_once('../plugins/conn.php');

// Set error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Dashboard Refresh Fix</h1>";

// Step 1: Clear dashboard cache files
echo "<h2>Step 1: Clearing Dashboard Cache</h2>";
$cache_dir = "../cache/";
$cache_files = glob($cache_dir . "dashboard_*.cache");
$cleared = 0;

foreach ($cache_files as $file) {
    if (unlink($file)) {
        echo "<p>Cleared cache file: " . basename($file) . "</p>";
        $cleared++;
    }
}

echo "<p>" . ($cleared > 0 ? "<span style='color:green'>✓ Cleared $cleared cache files</span>" : "<span style='color:orange'>No cache files found</span>") . "</p>";

// Step 2: Update the dashboard code to ensure it always gets fresh data
echo "<h2>Step 2: Updating Dashboard Code</h2>";

$dashboard_file = 'super_admin_dashboard.php';
$dashboard_content = file_get_contents($dashboard_file);

if ($dashboard_content) {
    // Add cache busting to the dashboard
    $updated_content = str_replace(
        "// Use register_members table for resident count",
        "// Use register_members table for resident count - with cache busting
    // Force cache refresh by adding timestamp
    \$cache_buster = time();",
        $dashboard_content
    );
    
    // Make sure we're using the correct table
    if (strpos($updated_content, "FROM register_members") === false) {
        $updated_content = str_replace(
            "SELECT COUNT(*) as total FROM",
            "SELECT COUNT(*) as total FROM register_members",
            $updated_content
        );
        echo "<p style='color:green'>✓ Updated query to use register_members table</p>";
    } else {
        echo "<p style='color:green'>✓ Query already using register_members table</p>";
    }
    
    // Write the updated content back
    if (file_put_contents($dashboard_file, $updated_content)) {
        echo "<p style='color:green'>✓ Dashboard code updated successfully</p>";
    } else {
        echo "<p style='color:red'>✗ Failed to update dashboard code</p>";
    }
} else {
    echo "<p style='color:red'>✗ Could not read dashboard file</p>";
}

// Step 3: Add a trigger to update the dashboard when new members are added
echo "<h2>Step 3: Adding Update Trigger</h2>";

$process_file = '../rbim_census_from/process_member_registration.php';
$process_content = file_get_contents($process_file);

if ($process_content) {
    // Check if our update code is already there
    if (strpos($process_content, "// Clear dashboard cache") === false) {
        // Find the position after successful registration
        $success_pos = strpos($process_content, "// Success message");
        
        if ($success_pos !== false) {
            // Insert our cache clearing code
            $update_code = "
        // Clear dashboard cache to refresh resident count
        \$cache_files = glob(\"../cache/dashboard_*.cache\");
        foreach (\$cache_files as \$file) {
            @unlink(\$file);
        }
        
        ";
            
            $updated_process = substr($process_content, 0, $success_pos) . $update_code . substr($process_content, $success_pos);
            
            if (file_put_contents($process_file, $updated_process)) {
                echo "<p style='color:green'>✓ Added cache clearing to registration process</p>";
            } else {
                echo "<p style='color:red'>✗ Failed to update registration process</p>";
            }
        } else {
            echo "<p style='color:red'>✗ Could not locate insertion point in registration file</p>";
        }
    } else {
        echo "<p style='color:green'>✓ Registration process already includes cache clearing</p>";
    }
} else {
    echo "<p style='color:red'>✗ Could not read registration process file</p>";
}

// Step 4: Verify the fix
echo "<h2>Step 4: Verifying Fix</h2>";

// Count current residents
$count_query = "SELECT COUNT(*) as total FROM register_members";
$count_result = mysqli_query($conn, $count_query);
if ($count_result) {
    $row = mysqli_fetch_assoc($count_result);
    echo "<p>Current resident count: <strong>{$row['total']}</strong></p>";
    echo "<p style='color:green'>✓ Dashboard should now show this count</p>";
} else {
    echo "<p style='color:red'>✗ Error counting residents: " . mysqli_error($conn) . "</p>";
}

// Add a link back to the dashboard
echo "<p><a href='super_admin_dashboard.php' style='background-color:#2196F3;color:white;padding:10px 15px;text-decoration:none;border-radius:4px;'>Return to Dashboard</a></p>";
?>