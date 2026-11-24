<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../plugins/conn.php";

/**
 * Ensure the activity_logs table exists for audit logging
 * 
 * @param mysqli $conn Database connection
 * @return bool True if table exists or was created successfully
 */
function ensureActivityLogsTableExists($conn) {
    // Check if activity_logs table exists
    $table_exists = false;
    $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'activity_logs'");
    if (mysqli_num_rows($check_table) > 0) {
        return true;
    }
    
    // Create table if it doesn't exist
    $create_table_sql = "CREATE TABLE IF NOT EXISTS activity_logs (
        id INT(11) NOT NULL AUTO_INCREMENT,
        user_id INT(11) NOT NULL DEFAULT 0,
        username VARCHAR(50) NOT NULL,
        action VARCHAR(50) NOT NULL,
        details TEXT,
        ip_address VARCHAR(45) NOT NULL,
        user_agent VARCHAR(255) NOT NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY idx_user_id (user_id),
        KEY idx_action (action),
        KEY idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $result = mysqli_query($conn, $create_table_sql);
    if (!$result) {
        error_log("Failed to create activity_logs table: " . mysqli_error($conn));
        return false;
    }
    
    return true;
}

/**
 * Validate and sanitize form input
 * 
 * @param string $input The input to validate and sanitize
 * @param string $type The type of validation to perform (text, number, date, select)
 * @param mysqli $conn Database connection for escaping
 * @return string|null The sanitized input or null if invalid
 */
function validateAndSanitize($input, $type, $conn) {
    if (empty($input) && $input !== '0') {
        return null;
    }
    
    $sanitized = null;
    
    switch ($type) {
        case 'text':
            // Remove any potentially harmful characters and trim
            $sanitized = mysqli_real_escape_string($conn, trim($input));
            break;
            
        case 'number':
            // Ensure it's a valid number
            if (is_numeric($input)) {
                $sanitized = mysqli_real_escape_string($conn, $input);
            }
            break;
            
        case 'date':
            // Validate date format (YYYY-MM-DD)
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $input)) {
                $date_parts = explode('-', $input);
                if (checkdate($date_parts[1], $date_parts[2], $date_parts[0])) {
                    $sanitized = mysqli_real_escape_string($conn, $input);
                }
            }
            break;
            
        case 'select':
            // For dropdown selections, just escape
            $sanitized = mysqli_real_escape_string($conn, $input);
            break;
            
        default:
            // Default basic sanitization
            $sanitized = mysqli_real_escape_string($conn, trim($input));
    }
    
    return $sanitized;
}

/**
 * Log member registration activities for security and audit purposes
 * 
 * @param string $action The action being performed (success, error, validation_error)
 * @param string $household_id The household ID
 * @param string $message The log message
 * @param mysqli $conn Database connection
 * @return void
 */
function logMemberRegistration($action, $household_id, $message, $conn) {
    // Get user information
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'unknown';
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    // Current timestamp
    $timestamp = date('Y-m-d H:i:s');
    
    // Ensure activity_logs table exists
    $table_exists = ensureActivityLogsTableExists($conn);
    
    // Log to database if table exists
    if ($table_exists) {
        $log_sql = "INSERT INTO activity_logs (user_id, username, action, details, ip_address, user_agent, created_at) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)"; 
        $log_stmt = mysqli_prepare($conn, $log_sql);
        
        if ($log_stmt) {
            $details = "Household ID: $household_id, Message: $message";
            mysqli_stmt_bind_param($log_stmt, "issssss", $user_id, $username, $action, $details, $ip_address, $user_agent, $timestamp);
            mysqli_stmt_execute($log_stmt);
            mysqli_stmt_close($log_stmt);
        }
    }
    
    // Always log to error_log as backup
    $log_message = "[$timestamp] [$action] User: $username (ID: $user_id), IP: $ip_address, Household ID: $household_id, $message";
    error_log($log_message);
}

// The duplicate function declaration has been completely removed

require_once '../plugins/session_check.php';

// Session check is already included from plugins directory
// require_once("../includes/session_check.php");

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_page/index.php");
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

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get household ID from form
    $household_id = isset($_POST['household_id']) ? intval($_POST['household_id']) : 0;
    
    if ($household_id <= 0) {
        // Invalid household ID
        $_SESSION['error'] = "Invalid household ID provided.";
        header("location: household_list.php");
        exit;
    }
    
    // Get household number from database
    $query = "SELECT household_number FROM survey_session WHERE session_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $household_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        // Household not found
        $_SESSION['error'] = "Household not found.";
        header("location: household_list.php");
        exit;
    }
    
    $household_data = mysqli_fetch_assoc($result);
    $household_number = $household_data['household_number'];
    
    // Validate required fields
    $required_fields = ['name', 'relationship', 'sex', 'age', 'date_of_birth'];
    $errors = [];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required.";
        }
    }
    
    // Additional validation
    if (!empty($_POST['age']) && (!is_numeric($_POST['age']) || $_POST['age'] < 0 || $_POST['age'] > 120)) {
        $errors[] = "Age must be a valid number between 0 and 120.";
    }
    
    if (!empty($_POST['date_of_birth'])) {
        $date_of_birth = $_POST['date_of_birth'];
        $date_regex = '/^\d{4}-\d{2}-\d{2}$/'; // YYYY-MM-DD format
        
        if (!preg_match($date_regex, $date_of_birth)) {
            $errors[] = "Date of birth must be in YYYY-MM-DD format.";
        } else {
            $dob_parts = explode('-', $date_of_birth);
            if (!checkdate($dob_parts[1], $dob_parts[2], $dob_parts[0])) {
                $errors[] = "Date of birth is not a valid date.";
            } else if (strtotime($date_of_birth) > time()) {
                $errors[] = "Date of birth cannot be in the future.";
            }
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        
        // Log validation errors
        $error_message = implode(", ", $errors);
        logMemberRegistration('validation_error', $household_id, $error_message, $conn);
        
        header("location: household_list.php?household_id=" . $household_id);
        exit;
    }
    
    // Sanitize and prepare data for insertion using the validation function
    $name = validateAndSanitize($_POST['name'], 'text', $conn);
    $relationship = validateAndSanitize($_POST['relationship'], 'select', $conn);
    $sex = isset($_POST['sex']) ? intval($_POST['sex']) : null;
    $age = isset($_POST['age']) ? intval($_POST['age']) : null;
    $date_of_birth = validateAndSanitize($_POST['date_of_birth'], 'date', $conn);
    $place_of_birth = validateAndSanitize($_POST['place_of_birth'] ?? null, 'text', $conn);
    $nationality = validateAndSanitize($_POST['nationality'] ?? null, 'text', $conn);
    $marital_status = validateAndSanitize($_POST['marital_status'] ?? null, 'select', $conn);
    $religion = validateAndSanitize($_POST['religion'] ?? null, 'text', $conn);
    $ethnicity = validateAndSanitize($_POST['ethnicity'] ?? null, 'text', $conn);
    $highest_education = validateAndSanitize($_POST['highest_education'] ?? null, 'select', $conn);
    
    // Education fields
    $currently_enrolled = validateAndSanitize($_POST['currently_enrolled'] ?? null, 'select', $conn);
    $school_level = validateAndSanitize($_POST['school_level'] ?? null, 'select', $conn);
    $place_of_school = validateAndSanitize($_POST['place_of_school'] ?? null, 'text', $conn);
    
    // Employment & Income fields
    $monthly_income = validateAndSanitize($_POST['q15_monthly_income'] ?? null, 'select', $conn);
    $source_of_income = validateAndSanitize($_POST['q15a_source_of_income'] ?? null, 'select', $conn);
    $occupation = validateAndSanitize($_POST['q15b_occupation'] ?? null, 'text', $conn);
    $work_status = validateAndSanitize($_POST['q17_work_status'] ?? null, 'select', $conn);
    $place_of_work = validateAndSanitize($_POST['q18_place_of_work'] ?? null, 'text', $conn);
    
    // Health Information fields
    $place_of_delivery = validateAndSanitize($_POST['q19_place_of_delivery'] ?? null, 'select', $conn);
    $birth_attendant = validateAndSanitize($_POST['q20_birth_attendant'] ?? null, 'select', $conn);
    $immunization = validateAndSanitize($_POST['q21_immunization'] ?? null, 'select', $conn);
    $living_children = validateAndSanitize($_POST['q22_living_children'] ?? null, 'number', $conn);
    $fp_method = validateAndSanitize($_POST['q23_fp_method'] ?? null, 'select', $conn);
    $source_of_fp = validateAndSanitize($_POST['q24_source_of_fp'] ?? null, 'select', $conn);
    $intention_to_use_fp = validateAndSanitize($_POST['q25_intention_to_use_fp'] ?? null, 'select', $conn);
    $health_insurance = validateAndSanitize($_POST['q26_health_insurance'] ?? null, 'select', $conn);
    $facility_visited = validateAndSanitize($_POST['q27_facility_visited'] ?? null, 'text', $conn);
    $reason_for_visit = validateAndSanitize($_POST['q28_reason_for_visit'] ?? null, 'text', $conn);
    $disability = validateAndSanitize($_POST['q29_disability'] ?? null, 'text', $conn);
    
    // Housing Information fields
    $q50a_garbage_disposal_method = validateAndSanitize($_POST['q50a_garbage_disposal_method'] ?? null, 'select', $conn);
    $q50b_garbage_segregation = validateAndSanitize($_POST['q50b_garbage_segregation'] ?? null, 'select', $conn);
    $q51_toilet_facility = validateAndSanitize($_POST['q51_toilet_facility'] ?? null, 'select', $conn);
    $q52_building_type = validateAndSanitize($_POST['q52_building_type'] ?? null, 'select', $conn);
    $q53_outer_wall_material = validateAndSanitize($_POST['q53_outer_wall_material'] ?? null, 'select', $conn);
    
    // Health and Needs fields
    $q54_female_death_details = validateAndSanitize($_POST['q54_female_death_details'] ?? null, 'text', $conn);
    $q55_child_death_details = validateAndSanitize($_POST['q55_child_death_details'] ?? null, 'text', $conn);
    $q56_common_diseases = validateAndSanitize($_POST['q56_common_diseases'] ?? null, 'text', $conn);
    $q57_primary_needs = validateAndSanitize($_POST['q57_primary_needs'] ?? null, 'text', $conn);
    $q58_future_residence = validateAndSanitize($_POST['q58_future_residence'] ?? null, 'text', $conn);
    
    // No additional parameters needed
    
    // Validate required fields again before database insertion
    $validation_errors = [];
    
    // Check required fields
    if (!$name) $validation_errors[] = "Name is required";
    if (!$relationship) $validation_errors[] = "Relationship is required";
    if ($sex === null) $validation_errors[] = "Sex is required";
    if ($age === null) $validation_errors[] = "Age is required";
    if (!$date_of_birth) $validation_errors[] = "Date of birth is required";
    
    // Additional validation for specific fields
    if ($age !== null && ($age < 0 || $age > 120)) {
        $validation_errors[] = "Age must be between 0 and 120";
    }
    
    if ($date_of_birth) {
        $today = new DateTime();
        $dob = new DateTime($date_of_birth);
        if ($dob > $today) {
            $validation_errors[] = "Date of birth cannot be in the future";
        }
    }
    
    // If validation errors exist, redirect back with error messages
    if (!empty($validation_errors)) {
        $_SESSION['error'] = "Validation errors: " . implode(", ", $validation_errors);
        logMemberRegistration("validation_error", $household_id, $_SESSION['error'], $conn);
        header("Location: household_list.php?household_id=" . $household_id);
        exit();
    }
    
    // Insert member data into database
    $insert_query = "INSERT INTO register_members (household_number, session_id, name, relationship, sex, age, date_of_birth, 
                    place_of_birth, nationality, marital_status, religion, ethnicity, highest_education,
                    currently_enrolled, school_level, place_of_school, q15_monthly_income, q15a_source_of_income, q15b_occupation, 
                    q17_work_status, q18_place_of_work, q19_place_of_delivery, q20_birth_attendant, q21_immunization, 
                    q22_living_children, q23_fp_method, q24_source_of_fp, q25_intention_to_use_fp, q26_health_insurance, 
                    q27_facility_visited, q28_reason_for_visit, q29_disability, q50a_garbage_disposal_method, q50b_garbage_segregation, 
                    q51_toilet_facility, q52_building_type, q53_outer_wall_material, q54_female_death_details, 
                    q55_child_death_details, q56_common_diseases, q57_primary_needs, q58_future_residence) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    // Count the number of columns and parameters
    $columns = 39; // Count of columns in the INSERT statement
    $params = substr_count($insert_query, '?'); // Count of parameter placeholders
    error_log("Column count: $columns, Parameter count: $params");
    
    // Debug the column names
    $column_names = "household_number, session_id, name, relationship, sex, age, date_of_birth, 
                    place_of_birth, nationality, marital_status, religion, ethnicity, highest_education,
                    currently_enrolled, school_level, place_of_school, q15_monthly_income, q15a_source_of_income, q15b_occupation, 
                    q17_work_status, q18_place_of_work, q19_place_of_delivery, q20_birth_attendant, q21_immunization, 
                    q22_living_children, q23_fp_method, q24_source_of_fp, q25_intention_to_use_fp, q26_health_insurance, 
                    q27_facility_visited, q28_reason_for_visit, q29_disability, q50a_garbage_disposal_method, q50b_garbage_segregation, 
                    q51_toilet_facility, q52_building_type, q53_outer_wall_material, q54_female_death_details, 
                    q55_child_death_details, q56_common_diseases, q57_primary_needs, q58_future_residence";
    $column_array = explode(',', $column_names);
    error_log("Total columns in names: " . count($column_array));
    
    // The issue is that we have 42 column names but only 39 placeholders
    // Let's print each column name to identify the issue
    foreach($column_array as $index => $column) {
        error_log("Column $index: " . trim($column));
    }

    
    $insert_stmt = mysqli_prepare($conn, $insert_query);
    if (!$insert_stmt) {
        // Log the error
        $error_message = "Error preparing statement: " . mysqli_error($conn);
        logMemberRegistration('db_error', $household_id, $error_message, $conn);
        
        $_SESSION['error'] = "Unable to register member. Please try again or contact support.";
        header("location: household_list.php?household_id=" . $household_id);
        exit;
    }
    
    // Count the number of parameters and create the type string
    $param_count = 42; // Total number of parameters (matching the number of ? in the SQL query)
    $type_string = str_repeat('s', $param_count); // Default all to string
    $type_string[0] = 'i'; // household_number is integer
    $type_string[1] = 'i'; // household_id is integer
    $type_string[4] = 'i'; // sex is integer
    $type_string[5] = 'i'; // age is integer
    
    // Bind parameters for the query
    mysqli_stmt_bind_param($insert_stmt, $type_string, 
                        $household_number, $household_id, $name, $relationship, $sex, $age, $date_of_birth,
                        $place_of_birth, $nationality, $marital_status, $religion, $ethnicity, $highest_education,
                        $currently_enrolled, $school_level, $place_of_school, $monthly_income, $source_of_income, $occupation,
                        $work_status, $place_of_work, $place_of_delivery, $birth_attendant, $immunization,
                        $living_children, $fp_method, $source_of_fp, $intention_to_use_fp, $health_insurance,
                        $facility_visited, $reason_for_visit, $disability, $q50a_garbage_disposal_method, $q50b_garbage_segregation, $q51_toilet_facility,
                        $q52_building_type, $q53_outer_wall_material, $q54_female_death_details, $q55_child_death_details, $q56_common_diseases,
                        $q57_primary_needs, $q58_future_residence);
    
    // Begin transaction for data integrity
    mysqli_begin_transaction($conn);
    
    try {
        // Execute the insert statement
        if (!mysqli_stmt_execute($insert_stmt)) {
            throw new Exception("Error executing statement: " . mysqli_stmt_error($insert_stmt));
        }
        
        // Get the newly inserted member ID
        $member_id = mysqli_insert_id($conn);
        
        if ($member_id <= 0) {
            throw new Exception("Failed to retrieve member ID after insertion");
        }
        
        // Update total members count in survey_session table
        $update_query = "UPDATE survey_session SET total_members = total_members + 1 WHERE session_id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        
        if (!$update_stmt) {
            throw new Exception("Error preparing update statement: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($update_stmt, "i", $household_id);
        
        if (!mysqli_stmt_execute($update_stmt)) {
            throw new Exception("Error updating household member count: " . mysqli_stmt_error($update_stmt));
        }
        
        // Commit the transaction
        mysqli_commit($conn);
        
        // Log successful registration
        $success_message = "Member ID {$member_id} successfully registered for household #" . str_pad($household_number, 6, '0', STR_PAD_LEFT);
        logMemberRegistration('success', $household_id, $success_message, $conn);
        
        // Success message
        $_SESSION['success'] = "Member successfully registered!";
        header("location: household_list.php?household_id=" . $household_id);
        exit;
        
    } catch (Exception $e) {
        // Rollback the transaction on error
        mysqli_rollback($conn);
        
        // Log the error
        $error_message = "Member registration failed: " . $e->getMessage();
        logMemberRegistration('db_error', $household_id, $error_message, $conn);
        
        // Error message for user - show a user-friendly message
        $_SESSION['error'] = "Unable to register member. Please try again or contact support.";
        header("location: household_list.php?household_id=" . $household_id);
        exit;
    }
} else {
    // Not a POST request, redirect to household list
    header("location: household_list.php");
    exit;
}
?>