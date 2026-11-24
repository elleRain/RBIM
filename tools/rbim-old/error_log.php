<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log the error
$error_log = dirname(__FILE__) . '/error_log.txt';
file_put_contents($error_log, date('Y-m-d H:i:s') . " - Testing error logging\n", FILE_APPEND);

echo "Error logging is working. Check error_log.txt for details.";
?>