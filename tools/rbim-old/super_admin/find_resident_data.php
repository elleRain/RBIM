<?php
// Script to find where resident data is stored
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once "../plugins/conn.php";

echo "<h1>Database Table Explorer</h1>";

// Check if database connection is successful
if (!isset($conn) || !$conn) {
    die("<p style='color:red'>Database connection failed: " . (isset($conn) ? mysqli_connect_error() : "Connection variable not set") . "</p>");
}
echo "<p style='color:green'>Database connection successful</p>";

// Get all tables in the database
$tables = [];
$result = mysqli_query($conn, "SHOW TABLES");
if ($result) {
    while ($row = mysqli_fetch_row($result)) {
        $tables[] = $row[0];
    }
    mysqli_free_result($result);
}

echo "<h2>All Tables in Database</h2>";
echo "<ul>";
foreach ($tables as $table) {
    echo "<li>$table</li>";
}
echo "</ul>";

// Look for tables that might contain resident/member data
echo "<h2>Tables with Member/Resident Data</h2>";

foreach ($tables as $table) {
    // Get column names for each table
    $columns = [];
    $result = mysqli_query($conn, "SHOW COLUMNS FROM `$table`");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $columns[] = $row['Field'];
        }
        mysqli_free_result($result);
    }
    
    // Check if this table might contain resident data
    $potential_resident_columns = ['name', 'first_name', 'last_name', 'gender', 'age', 'birthdate', 'address'];
    $matches = array_intersect($columns, $potential_resident_columns);
    
    if (count($matches) >= 2) {
        echo "<h3>Table: $table</h3>";
        echo "<p>Potential resident data table (matched columns: " . implode(", ", $matches) . ")</p>";
        
        // Show table structure
        echo "<h4>Structure:</h4>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Column</th><th>Type</th></tr>";
        
        $result = mysqli_query($conn, "SHOW COLUMNS FROM `$table`");
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . $row['Field'] . "</td>";
                echo "<td>" . $row['Type'] . "</td>";
                echo "</tr>";
            }
            mysqli_free_result($result);
        }
        echo "</table>";
        
        // Count records
        $count_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM `$table`");
        if ($count_result) {
            $count_row = mysqli_fetch_assoc($count_result);
            echo "<p><strong>Record count:</strong> " . $count_row['count'] . "</p>";
            mysqli_free_result($count_result);
            
            // Show sample data (first 5 rows)
            if ($count_row['count'] > 0) {
                echo "<h4>Sample Data (first 5 rows):</h4>";
                $sample_result = mysqli_query($conn, "SELECT * FROM `$table` LIMIT 5");
                if ($sample_result) {
                    echo "<table border='1' cellpadding='5'>";
                    
                    // Table header
                    echo "<tr>";
                    foreach ($columns as $column) {
                        echo "<th>" . $column . "</th>";
                    }
                    echo "</tr>";
                    
                    // Table data
                    while ($row = mysqli_fetch_assoc($sample_result)) {
                        echo "<tr>";
                        foreach ($columns as $column) {
                            echo "<td>" . (isset($row[$column]) ? htmlspecialchars($row[$column]) : "NULL") . "</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</table>";
                    mysqli_free_result($sample_result);
                }
            }
        }
    }
}

// Check for survey_session and household_members tables specifically
echo "<h2>Household and Member Relationship</h2>";

// Check if survey_session exists
$survey_session_exists = in_array('survey_session', $tables);
echo "<p>survey_session table exists: " . ($survey_session_exists ? "Yes" : "No") . "</p>";

if ($survey_session_exists) {
    // Count households
    $count_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM survey_session");
    if ($count_result) {
        $count_row = mysqli_fetch_assoc($count_result);
        echo "<p>Total households (survey_session): " . $count_row['count'] . "</p>";
        mysqli_free_result($count_result);
    }
    
    // Check for related tables
    foreach ($tables as $table) {
        if (strpos($table, 'member') !== false || strpos($table, 'resident') !== false || 
            strpos($table, 'person') !== false || strpos($table, 'household') !== false) {
            
            echo "<h3>Related Table: $table</h3>";
            
            // Check for foreign key to survey_session
            $has_session_id = false;
            $result = mysqli_query($conn, "SHOW COLUMNS FROM `$table`");
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    if ($row['Field'] == 'session_id' || $row['Field'] == 'household_id' || 
                        $row['Field'] == 'survey_id' || $row['Field'] == 'survey_session_id') {
                        $has_session_id = true;
                        echo "<p>Has link to households via column: " . $row['Field'] . "</p>";
                    }
                }
                mysqli_free_result($result);
            }
            
            // Count records
            $count_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM `$table`");
            if ($count_result) {
                $count_row = mysqli_fetch_assoc($count_result);
                echo "<p>Record count: " . $count_row['count'] . "</p>";
                mysqli_free_result($count_result);
            }
        }
    }
}

echo "<p><a href='super_admin_dashboard.php'>Return to Dashboard</a></p>";
?>