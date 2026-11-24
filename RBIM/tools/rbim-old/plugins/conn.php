<?php
	# Define Database Connection
	$host = 'localhost';
	$username = 'root';
	$password = '';
	$database_name = 'rbim';

	// Set error reporting for debugging
	ini_set('display_errors', 0); // Don't show errors to users
	ini_set('log_errors', 1); // Log errors

	try {
		$conn = mysqli_connect($host, $username, $password, $database_name);

		if (!$conn) {
			error_log("Connection failed: " . mysqli_connect_error());
			throw new Exception("Database connection failed");
		}

		mysqli_set_charset($conn, "utf8mb4");
	} catch (Exception $e) {
		error_log("Database connection error: " . $e->getMessage());
		// Don't throw the exception here, let the calling code handle it
		$conn = null;
	}
?>