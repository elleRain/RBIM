<?php
// Simple script to check if the resident table exists and display its structure
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once "../plugins/conn.php";

echo "<h1>Database Table Check</h1>";

// Check if database connection is successful
if (!isset($conn) || !$conn) {
    die("<p style='color:red'>Database connection failed: " . (isset($conn) ? mysqli_connect_error() : "Connection variable not set") . "</p>");
}
echo "<p style='color:green'>Database connection successful</p>";

// List all tables in the database
echo "<h2>All Tables in Database</h2>";
$result = mysqli_query($conn, "SHOW TABLES");
if ($result) {
    echo "<ul>";
    $tableExists = false;
    while ($row = mysqli_fetch_row($result)) {
        echo "<li>" . $row[0] . "</li>";
        if ($row[0] == 'resident') {
            $tableExists = true;
        }
    }
    echo "</ul>";
    mysqli_free_result($result);
    
    if ($tableExists) {
        echo "<p style='color:green'>✓ 'resident' table exists</p>";
    } else {
        echo "<p style='color:red'>✗ 'resident' table does NOT exist</p>";
        echo "<p>The table might be named differently. Check the list above.</p>";
    }
} else {
    echo "<p style='color:red'>Error listing tables: " . mysqli_error($conn) . "</p>";
}

// Check for tables that might contain resident data
echo "<h2>Possible Resident Tables</h2>";
$possibleTables = ['residents', 'resident', 'household_members', 'members', 'people', 'citizens'];
$foundTables = [];

foreach ($possibleTables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if ($result && mysqli_num_rows($result) > 0) {
        $foundTables[] = $table;
    }
    if ($result) mysqli_free_result($result);
}

if (count($foundTables) > 0) {
    echo "<p>Found these possible resident tables:</p>";
    echo "<ul>";
    foreach ($foundTables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Show structure of found tables
    foreach ($foundTables as $table) {
        echo "<h3>Structure of '$table' table</h3>";
        $result = mysqli_query($conn, "DESCRIBE $table");
        if ($result) {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . $row['Field'] . "</td>";
                echo "<td>" . $row['Type'] . "</td>";
                echo "<td>" . $row['Null'] . "</td>";
                echo "<td>" . $row['Key'] . "</td>";
                echo "<td>" . $row['Default'] . "</td>";
                echo "<td>" . $row['Extra'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Count records
            $countResult = mysqli_query($conn, "SELECT COUNT(*) as count FROM $table");
            if ($countResult) {
                $countRow = mysqli_fetch_assoc($countResult);
                echo "<p>Total records in '$table': " . $countRow['count'] . "</p>";
                mysqli_free_result($countResult);
            }
            
            mysqli_free_result($result);
        } else {
            echo "<p style='color:red'>Error describing table: " . mysqli_error($conn) . "</p>";
        }
    }
} else {
    echo "<p style='color:red'>No resident-related tables found</p>";
    echo "<p>You may need to create a 'resident' table or check if it has a different name.</p>";
}

// Check the code in super_admin_dashboard.php
echo "<h2>Code Check</h2>";
echo "<p>The code in super_admin_dashboard.php should include:</p>";
echo "<pre>
\$check_table = mysqli_query(\$conn, \"SHOW TABLES LIKE 'resident'\");
if (\$check_table && mysqli_num_rows(\$check_table) > 0) {
    \$sql = \"SELECT COUNT(*) as total FROM resident\";
    \$result = mysqli_query(\$conn, \$sql);
    if (\$result) {
        \$row = mysqli_fetch_assoc(\$result);
        \$stats['total_residents'] = \$row['total'];
        mysqli_free_result(\$result);
    }
    mysqli_free_result(\$check_table);
}
</pre>";

echo "<p>And in the HTML section:</p>";
echo "<pre>
&lt;div class=\"stat-card bg-green-500\"&gt;
    &lt;div class=\"stat-card-info\"&gt;
        &lt;div class=\"stat-card-info-number\"&gt;&lt;?php echo number_format(\$stats['total_residents']); ?&gt;&lt;/div&gt;
        &lt;div class=\"stat-card-info-text\"&gt;TOTAL RESIDENTS&lt;/div&gt;
    &lt;/div&gt;
    &lt;div class=\"stat-card-icon\"&gt;
        &lt;i class=\"fas fa-users\"&gt;&lt;/i&gt;
    &lt;/div&gt;
&lt;/div&gt;
</pre>";

echo "<p><a href='super_admin_dashboard.php' style='background-color:#2196F3;color:white;padding:10px 15px;text-decoration:none;border-radius:4px;display:inline-block;'>View Dashboard</a></p>";
?>