<?php
require_once "conn.php";

// Check if remember_tokens table exists
$table_check = "SHOW TABLES LIKE 'remember_tokens'";
$result = mysqli_query($conn, $table_check);

if (mysqli_num_rows($result) == 0) {
    // Table doesn't exist, create it
    $create_table = "CREATE TABLE remember_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (mysqli_query($conn, $create_table)) {
        echo "Table 'remember_tokens' created successfully.";
    } else {
        echo "Error creating table: " . mysqli_error($conn);
    }
} else {
    echo "Table 'remember_tokens' already exists.";
}

// Close connection
mysqli_close($conn);
?>