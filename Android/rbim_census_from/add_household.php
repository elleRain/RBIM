<?php
// Start session
session_start();
require_once "../plugins/conn.php";

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

// Get the admin's assigned barangay from session
// If not in session, fetch from database as fallback
if (isset($_SESSION["barangay"]) && isset($_SESSION["barangay_id"])) {
    $admin_barangay = $_SESSION["barangay"];
    $admin_barangay_id = $_SESSION["barangay_id"];
} else {
    // Fallback to database query if session variables not set
    $admin_id = $_SESSION["id"] ?? 0;
    $barangay_query = "SELECT u.barangay, b.barangay_id 
                      FROM users u 
                      LEFT JOIN barangay b ON u.barangay = b.barangay 
                      WHERE u.admin_id = ?";
    $stmt = mysqli_prepare($conn, $barangay_query);
    mysqli_stmt_bind_param($stmt, "i", $admin_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $admin_data = mysqli_fetch_assoc($result);
    $admin_barangay = $admin_data['barangay'] ?? 'Sample Barangay'; // Default to Sample Barangay if not set
    $admin_barangay_id = $admin_data['barangay_id'] ?? 0; // Default to 0 if not set
    
    // Store in session for future use
    $_SESSION["barangay"] = $admin_barangay;
    $_SESSION["barangay_id"] = $admin_barangay_id;
}

// Set page title for navbar
$page_title = "Add New Household";

// Initialize variables for form fields
$household_number = $address = "";
$province = "Negros Occidental"; // Static value
$city_municipality = "Bago City"; // Static value
$barangay = "";
$respondent_name = $household_head = $total_members = $visit_number = "";
$visit_date = $start_time = $end_time = $result = $next_visit_date = "";
$interviewer_name = $supervisor_name = $date_encoded = $encoder_name = $encoding_supervisor = "";

// Initialize error variables
$errors = [];

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input fields
    // Household number is auto-generated
    
    // Required fields validation
    $required_fields = [
        'address' => 'Address',
        'respondent_name' => 'Respondent Name',
        'household_head' => 'Household Head',
        'total_members' => 'Total Members',
        'visit_number' => 'Visit Number',
        'visit_date' => 'Visit Date',
        'result' => 'Result',
        'interviewer_name' => 'Interviewer Name'
    ];
    // Province and city/municipality are static values, no need to validate
    
    foreach ($required_fields as $field => $label) {
        if (empty(trim($_POST[$field]))) {
            $errors[] = "$label is required";
        }
    }
    
    // Validate numeric fields
    if (!empty($_POST['total_members']) && !is_numeric($_POST['total_members'])) {
        $errors[] = "Total Members must be a number";
    }
    
    if (!empty($_POST['visit_number']) && !is_numeric($_POST['visit_number'])) {
        $errors[] = "Visit Number must be a number";
    }
    
    // If no errors, process the form
    if (empty($errors)) {
        // Initialize form validation flag
        $form_valid = true;
        // Sanitize input data
        $province = mysqli_real_escape_string($conn, trim($_POST['province']));
        $city_municipality = mysqli_real_escape_string($conn, trim($_POST['city_municipality']));
        $barangay = $admin_barangay; // Use admin's assigned barangay
        $barangay_id = $admin_barangay_id; // Use admin's assigned barangay ID
        $address = mysqli_real_escape_string($conn, trim($_POST['address']));
        $respondent_name = mysqli_real_escape_string($conn, trim($_POST['respondent_name']));
        // Validate household head - ensure it's not empty
        $household_head = trim($_POST['household_head']);
        if (empty($household_head)) {
            $errors[] = "Household Head cannot be empty";
            // Don't proceed with form processing
            $form_valid = false;
        } else {
        $household_head = mysqli_real_escape_string($conn, $household_head);
        }
        $total_members = (int)$_POST['total_members'];
        $visit_number = (int)$_POST['visit_number'];
        $visit_date = mysqli_real_escape_string($conn, trim($_POST['visit_date']));
        $start_time = !empty($_POST['start_time']) ? mysqli_real_escape_string($conn, trim($_POST['start_time'])) : null;
        $end_time = !empty($_POST['end_time']) ? mysqli_real_escape_string($conn, trim($_POST['end_time'])) : null;
        $result = mysqli_real_escape_string($conn, trim($_POST['result']));
        $next_visit_date = !empty($_POST['next_visit_date']) ? mysqli_real_escape_string($conn, trim($_POST['next_visit_date'])) : null;
        $interviewer_name = mysqli_real_escape_string($conn, trim($_POST['interviewer_name']));
        $supervisor_name = !empty($_POST['supervisor_name']) ? mysqli_real_escape_string($conn, trim($_POST['supervisor_name'])) : null;
        $date_encoded = date('Y-m-d'); // Current date
        $encoder_name = $_SESSION['username'] ?? 'System'; // Current user
        $encoding_supervisor = !empty($_POST['encoding_supervisor']) ? mysqli_real_escape_string($conn, trim($_POST['encoding_supervisor'])) : null;
        
        // Only proceed if form is valid
        if ($form_valid) {
            // Generate a unique household number (format: YYMMXX)
        // YY = Year (2 digits), MM = Month (2 digits), XX = Sequential counter (2 digits)
        $current_year = date('y'); // 2-digit year
        $current_month = date('m'); // 2-digit month
        
        // Get the highest counter for the current month and year
        $year_month_prefix = $current_year . $current_month; // Format: YYMM
        $query = "SELECT MAX(household_number) as max_number FROM survey_session 
                  WHERE household_number LIKE ?";
        $stmt = mysqli_prepare($conn, $query);
        $search_pattern = $year_month_prefix . '%';
        mysqli_stmt_bind_param($stmt, "s", $search_pattern);
        mysqli_stmt_execute($stmt);
        $result_max = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result_max);
        
        // Extract the counter part or default to 0
        $max_number = $row['max_number'] ?? 0;
        if ($max_number > 0) {
            // Extract the last 2 digits (counter part)
            $counter = (int)substr($max_number, -2);
            $counter++;
        } else {
            // First record of the month
            $counter = 1;
        }
        
        // Format the counter with leading zeros
        $counter_formatted = str_pad($counter, 2, '0', STR_PAD_LEFT);
        
        // Combine all parts to create the household number
        $household_number = (int)($year_month_prefix . $counter_formatted); // Convert to integer for database storage
        
        // Insert data into survey_session table
        $insert_query = "INSERT INTO survey_session (household_number, province, city_municipality, barangay, barangay_id, address, respondent_name, household_head, total_members, visit_number, visit_date, start_time, end_time, result, next_visit_date, interviewer_name, supervisor_name, date_encoded, encoder_name, encoding_supervisor) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $insert_query);
        
        mysqli_stmt_bind_param($stmt, "isssisssisssssssssss", 
                        $household_number, $province, $city_municipality, $barangay, $barangay_id, $address, 
                        $respondent_name, $household_head, $total_members, $visit_number, $visit_date, $start_time, $end_time, 
                        $result, $next_visit_date, $interviewer_name, $supervisor_name, $date_encoded, $encoder_name, $encoding_supervisor);
        
        if (mysqli_stmt_execute($stmt)) {
            // Set success flag and household number for modal display
            $success = true;
            $added_household_number = $household_number;
            // Keep the success message in session as well for fallback
            $_SESSION['success_message'] = "Household added successfully with Household Number: #" . str_pad($household_number, 6, '0', STR_PAD_LEFT);
        } else {
            $errors[] = "Error: " . mysqli_error($conn);
        }
        } else {
            // Form validation failed, errors already added to $errors array
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="../src/design-system.css">
    <link rel="stylesheet" href="../src/responsive-fixes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Dashboard styles */
        body {
            font-family: var(--font-family);
            margin: 0;
            padding: 0;
            background-color: var(--neutral-50);
            color: var(--neutral-900);
        }
        
        .main-content {
            margin-left: 220px;
            padding: var(--spacing-6);
            transition: all 0.3s ease;
            margin-top: 10px; /* Add top margin equal to navbar height */
            max-width: 1400px;
        }
        
        .main-content.expanded {
            margin-left: 60px;
            margin-top: 70px; /* Maintain top margin when sidebar is collapsed */
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: var(--spacing-4);
            }
        }
        
        .page-header {
            margin-bottom: var(--spacing-6);
            border-bottom: 1px solid var(--neutral-200);
            padding-bottom: var(--spacing-4);
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            font-size: var(--font-size-sm);
            color: var(--neutral-600);
            margin-bottom: var(--spacing-2);
        }
        
        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
            transition: color 0.2s ease;
        }
        
        .breadcrumb-separator {
            margin: 0 var(--spacing-2);
            color: var(--neutral-400);
        }
        
        .page-title {
            font-size: var(--font-size-2xl);
            font-weight: var(--font-weight-bold);
            color: var(--neutral-900);
            margin: 0 0 var(--spacing-2) 0;
        }
        
        .page-subtitle {
            font-size: var(--font-size-base);
            color: var(--neutral-600);
            margin-bottom: var(--spacing-4);
        }
        
        .card {
            background-color: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            padding: var(--spacing-6);
            margin-bottom: var(--spacing-6);
            transition: all 0.3s ease;
        }
        

        
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--spacing-5);
            padding-bottom: var(--spacing-4);
            border-bottom: 2px solid var(--neutral-100);
        }
        
        .card-title {
            font-size: var(--font-size-xl);
            font-weight: var(--font-weight-semibold);
            color: var(--neutral-900);
            display: flex;
            align-items: center;
        }
        
        .card-title i {
            margin-right: var(--spacing-3);
            color: var(--primary);
            font-size: 1.2em;
        }
        
        /* Form Styles */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: var(--spacing-5);
            align-items: start;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .form-group {
            margin-bottom: var(--spacing-5);
            position: relative;
        }
        
        .form-group.full-width {
            grid-column: span 2;
        }
        
        @media (max-width: 768px) {
            .form-group.full-width {
                grid-column: span 1;
            }
        }
        
        .form-label {
            display: block;
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-semibold);
            color: var(--neutral-700);
            margin-bottom: var(--spacing-2);
            letter-spacing: 0.01em;
        }
        
        .form-control {
            width: 100%;
            height: 42px;
            padding: var(--spacing-3) var(--spacing-4);
            font-size: var(--font-size-base);
            border: 1px solid var(--neutral-300);
            border-radius: var(--radius-md);
            background-color: var(--white);
            color: var(--neutral-900);
            transition: all 0.2s ease;
            box-sizing: border-box;
        }
        
        textarea.form-control {
            height: auto;
            min-height: 100px;
            resize: vertical;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.15);
        }
        
        .form-control.is-invalid {
            border-color: #dc3545;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        
        .form-control.is-invalid:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.25);
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .shake {
            animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
        }
        
        .form-control::placeholder {
            color: var(--neutral-400);
        }
        
        /* Input groups with icons */
        .input-group {
            position: relative;
            display: flex;
            width: 100%;
        }
        
        .input-group-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--neutral-500);
            z-index: 10;
            width: 16px;
            text-align: center;
        }
        
        .input-group .form-control {
            padding-left: 35px;
        }
        
        .form-text {
            font-size: var(--font-size-xs);
            color: var(--neutral-500);
            margin-top: var(--spacing-2);
            display: block;
        }
        
        .form-section {
            margin-bottom: var(--spacing-6);
            padding-bottom: var(--spacing-5);
            border-bottom: 1px solid var(--neutral-200);
        }
        
        .form-section:last-of-type {
            border-bottom: none;
        }
        
        .form-section-title {
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-semibold);
            color: var(--neutral-800);
            margin-bottom: var(--spacing-4);
            padding-left: var(--spacing-3);
            border-left: 3px solid var(--primary);
        }
        
        /* Button container and button styles */
        .btn-container {
            display: flex;
            justify-content: flex-end;
            gap: var(--spacing-3);
            margin-top: var(--spacing-6);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-3) var(--spacing-5);
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-medium);
            border-radius: var(--radius-md);
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            gap: var(--spacing-2);
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }
        
        .btn-secondary {
            background-color: var(--neutral-200);
            color: var(--neutral-700);
        }
        
        .btn-info {
            background-color: var(--info);
            color: var(--white);
        }
        
        .alert {
            padding: var(--spacing-4) var(--spacing-5);
            border-radius: var(--radius-md);
            margin-bottom: var(--spacing-5);
            font-size: var(--font-size-sm);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .alert-danger {
            background-color: rgba(var(--danger-rgb), 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }
        
        .alert-success {
            background-color: rgba(var(--success-rgb), 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .required-field::after {
            content: '*';
            color: var(--danger);
            margin-left: 4px;
        }
        
        /* Additional professional styling */
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236B7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
            padding-right: 40px;
        }
        
        input[type="date"].form-control,
        input[type="time"].form-control {
            min-height: 42px;
        }
        
        /* Improve readability of readonly fields */
        .form-control[readonly] {
            background-color: var(--neutral-100);
            border-color: var(--neutral-200);
            color: var(--neutral-700);
            cursor: not-allowed;
        }
        
        /* Removed hover effect from form controls */

        /* Consistent height for all form controls */
        input.form-control,
        select.form-control {
            height: 42px;
        }
        
        /* Popup Notification Styles */
         .popup-notification {
             position: fixed;
             top: 20px;
             right: 20px;
             z-index: 1000;
             max-width: 350px;
             animation: slideIn 0.5s ease-out;
         }
         
         .popup-content {
             background-color: #fff;
             border-left: 4px solid #007bff;
             border-radius: 4px;
             box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
             padding: 15px;
         }
         
         .popup-content h4 {
             margin-top: 0;
             margin-bottom: 10px;
             color: #343a40;
             font-size: 16px;
             display: flex;
             align-items: center;
             gap: 8px;
         }
         
         .popup-content p {
             margin-bottom: 15px;
             font-size: 14px;
             color: #6c757d;
         }
         
         /* Close popup button removed */
         
         /* Small button styles removed */
         
         @keyframes slideIn {
             from { transform: translateX(100%); opacity: 0; }
             to { transform: translateX(0); opacity: 1; }
         }
    </style>
</head>
<body>
    <?php
    // Include the sidebar
    include '../includes/sidebar.php';
    
    // Include the navbar
    include '../admin/admin_navbar.php';
    ?>
    
    <div class="main-content" id="main-content">

        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-home"></i> Household Information</h2>
            </div>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-section">
                    <h3 class="form-section-title"><i class="fas fa-map-marker-alt"></i> Location Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="province" class="form-label required-field">Province</label>
                            <input type="text" id="province" name="province" class="form-control" value="Negros Occidental" readonly>
                            <small class="form-text"><i class="fas fa-info-circle"></i> This field is pre-set</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="city_municipality" class="form-label required-field">City/Municipality</label>
                            <input type="text" id="city_municipality" name="city_municipality" class="form-control" value="Bago City" readonly>
                            <small class="form-text"><i class="fas fa-info-circle"></i> This field is pre-set</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="barangay" class="form-label">Barangay</label>
                            <input type="text" id="barangay" class="form-control" value="<?php echo htmlspecialchars($admin_barangay); ?>" readonly>
                            <small class="form-text"><i class="fas fa-info-circle"></i> This is your assigned barangay</small>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="address" class="form-label required-field">Complete Address</label>
                            <textarea id="address" name="address" class="form-control" rows="3" placeholder="Enter complete street address" required><?php echo htmlspecialchars($address); ?></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3 class="form-section-title"><i class="fas fa-users"></i> Household Details</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="respondent_name" class="form-label required-field">Respondent Name</label>
                            <input type="text" id="respondent_name" name="respondent_name" class="form-control" value="<?php echo htmlspecialchars($respondent_name); ?>" placeholder="Enter respondent's full name" required>
                        </div>
                        
                        <div class="form-group">
                <label for="household_head" class="form-label required-field">Household Head</label>
                <input type="text" id="household_head" name="household_head" class="form-control" value="<?php echo htmlspecialchars($household_head); ?>" placeholder="Enter household head's full name" required oninvalid="this.setCustomValidity('Household Head is required')" oninput="this.setCustomValidity('')">
                <small class="form-text"><i class="fas fa-exclamation-circle"></i> This field is mandatory and cannot be empty</small>
            </div>
                        
                        <div class="form-group">
                            <label for="total_members" class="form-label required-field">Total Members</label>
                            <input type="number" id="total_members" name="total_members" class="form-control" min="1" value="<?php echo htmlspecialchars($total_members); ?>" placeholder="Enter number of household members" required>
                            <small class="form-text"><i class="fas fa-info-circle"></i> Include all people living in this household</small>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3 class="form-section-title"><i class="fas fa-calendar-check"></i> Visit Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="visit_number" class="form-label required-field">Visit Number</label>
                            <input type="number" id="visit_number" name="visit_number" class="form-control" min="1" value="<?php echo htmlspecialchars($visit_number); ?>" placeholder="Enter visit number" required>
                            <small class="form-text"><i class="fas fa-info-circle"></i> Sequential number of this visit</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="visit_date" class="form-label required-field">Visit Date</label>
                            <input type="date" id="visit_date" name="visit_date" class="form-control" value="<?php echo htmlspecialchars($visit_date); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="start_time" class="form-label">Start Time</label>
                            <div class="input-group">
                                <span class="input-group-icon"><i class="fas fa-clock"></i></span>
                                <input type="time" id="start_time" name="start_time" class="form-control" value="<?php echo htmlspecialchars($start_time); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="end_time" class="form-label">End Time</label>
                            <div class="input-group">
                                <span class="input-group-icon"><i class="fas fa-clock"></i></span>
                                <input type="time" id="end_time" name="end_time" class="form-control" value="<?php echo htmlspecialchars($end_time); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="result" class="form-label required-field">Result</label>
                            <select id="result" name="result" class="form-control" required>
                                <option value="" disabled <?php echo empty($result) ? 'selected' : ''; ?>>Select result</option>
                                <option value="Complete" <?php echo $result === 'Complete' ? 'selected' : ''; ?>>Complete</option>
                                <option value="Incomplete" <?php echo $result === 'Incomplete' ? 'selected' : ''; ?>>Incomplete</option>
                                <option value="Refused" <?php echo $result === 'Refused' ? 'selected' : ''; ?>>Refused</option>
                                <option value="No Response" <?php echo $result === 'No Response' ? 'selected' : ''; ?>>No Response</option>
                            </select>
                        </div>
                        
                        <div class="form-group" id="next_visit_date_container" style="display: none;">
                            <label for="next_visit_date" class="form-label">Next Visit Date</label>
                            <div class="input-group">
                                <span class="input-group-icon"><i class="fas fa-calendar-alt"></i></span>
                                <input type="date" id="next_visit_date" name="next_visit_date" class="form-control" value="<?php echo htmlspecialchars($next_visit_date); ?>">
                            </div>
                            <small class="form-text"><i class="fas fa-exclamation-circle"></i> Required if result is Incomplete</small>
                        </div>
                        
                        <!-- Next Visit Date Popup Notification -->
                        <div id="next_visit_popup" class="popup-notification" style="display: none;">
                            <div class="popup-content">
                                <span class="close-popup">&times;</span>
                                <h4><i class="fas fa-calendar-alt"></i> Next Visit Required</h4>
                                <p>Please set a date for the next visit as the result is incomplete.</p>
                                <button id="set_next_visit_btn" class="btn btn-sm btn-primary">Set Next Visit Date</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3 class="form-section-title"><i class="fas fa-users"></i> Personnel Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="interviewer_name" class="form-label required-field">Interviewer Name</label>
                            <div class="input-group">
                                <span class="input-group-icon"><i class="fas fa-user"></i></span>
                                <input type="text" id="interviewer_name" name="interviewer_name" class="form-control" value="<?php echo htmlspecialchars($interviewer_name); ?>" placeholder="Enter interviewer's full name" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="supervisor_name" class="form-label">Supervisor Name</label>
                            <div class="input-group">
                                <span class="input-group-icon"><i class="fas fa-user-tie"></i></span>
                                <input type="text" id="supervisor_name" name="supervisor_name" class="form-control" value="<?php echo htmlspecialchars($supervisor_name); ?>" placeholder="Enter supervisor's full name">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="encoding_supervisor" class="form-label">Encoding Supervisor</label>
                            <div class="input-group">
                                <span class="input-group-icon"><i class="fas fa-user-check"></i></span>
                                <input type="text" id="encoding_supervisor" name="encoding_supervisor" class="form-control" value="<?php echo htmlspecialchars($encoding_supervisor); ?>" placeholder="Enter encoding supervisor's name">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="btn-container">
                    <a href="household_list.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                    <button type="button" id="auto-fill-btn" class="btn btn-info"><i class="fas fa-magic"></i> Auto-Fill</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Household</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Form validation and submission handling
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const resultSelect = document.getElementById('result');
            const nextVisitDateInput = document.getElementById('next_visit_date');
            const nextVisitDateContainer = document.getElementById('next_visit_date_container');
            const nextVisitPopup = document.getElementById('next_visit_popup');
            const closePopupBtn = document.querySelector('.close-popup');
            const setNextVisitBtn = document.getElementById('set_next_visit_btn');
            const submitBtn = document.querySelector('button[type="submit"]');
            const autoFillBtn = document.getElementById('auto-fill-btn');
            
            // Add loading state to submit button
            form.addEventListener('submit', function(e) {
                // Prevent multiple submissions
                if (form.classList.contains('is-submitting')) {
                    e.preventDefault();
                    return;
                }
                
                // Basic form validation
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                    // Add shake animation
                    field.classList.add('shake');
                    setTimeout(() => {
                        field.classList.remove('shake');
                    }, 500);
                    
                    // Special validation for household_head
                    if (field.id === 'household_head') {
                        alert('Household Head is a mandatory field and cannot be empty.');
                    }
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            // Extra validation for household_head
            const householdHeadField = document.getElementById('household_head');
            if (!householdHeadField.value.trim()) {
                isValid = false;
                householdHeadField.focus();
            }
                
                if (!isValid) {
                    e.preventDefault();
                    return;
                }
                
                // Show loading state
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                submitBtn.disabled = true;
                form.classList.add('is-submitting');
            });
            
            // Next visit date validation and popup handling
            function handleResultChange() {
                if (resultSelect.value === 'Incomplete') {
                    // Show the next visit date field
                    nextVisitDateContainer.style.display = 'block';
                    nextVisitDateInput.setAttribute('required', 'required');
                    nextVisitDateContainer.querySelector('.form-label').classList.add('required-field');
                    
                    // Show popup notification if next visit date is not set
                    if (!nextVisitDateInput.value) {
                        nextVisitPopup.style.display = 'block';
                    }
                } else {
                    // Hide the next visit date field
                    nextVisitDateContainer.style.display = 'none';
                    nextVisitDateInput.removeAttribute('required');
                    nextVisitDateContainer.querySelector('.form-label').classList.remove('required-field');
                    
                    // Hide popup
                    nextVisitPopup.style.display = 'none';
                }
            }
            
            // Initialize based on current selection
            handleResultChange();
            
            // Add event listener for result select change
            resultSelect.addEventListener('change', handleResultChange);
            
            // Close popup when X is clicked
            closePopupBtn.addEventListener('click', function() {
                nextVisitPopup.style.display = 'none';
            });
            
            // Set next visit button click handler
            setNextVisitBtn.addEventListener('click', function() {
                nextVisitPopup.style.display = 'none';
                nextVisitDateContainer.scrollIntoView({ behavior: 'smooth' });
                nextVisitDateInput.focus();
            });
            
            // Hide popup when date is selected
            nextVisitDateInput.addEventListener('change', function() {
                if (this.value) {
                    nextVisitPopup.style.display = 'none';
                }
            });
            
            // Set current date as default for visit date
            const visitDateInput = document.getElementById('visit_date');
            if (!visitDateInput.value) {
                const today = new Date();
                const year = today.getFullYear();
                let month = today.getMonth() + 1;
                let day = today.getDate();
                
                month = month < 10 ? '0' + month : month;
                day = day < 10 ? '0' + day : day;
                
                visitDateInput.value = `${year}-${month}-${day}`;
            }
            
            // Auto-fill button functionality with randomized test data
            autoFillBtn.addEventListener('click', function() {
                // Get current date and time for defaults
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const currentTime = `${hours}:${minutes}`;
                
                // Random data generation functions
                function getRandomInt(min, max) {
                    return Math.floor(Math.random() * (max - min + 1)) + min;
                }
                
                function getRandomStreetName() {
                    const streets = ['Main St.', 'Rizal St.', 'Mabini St.', 'Bonifacio St.', 'National Highway', 
                                    'Quezon Ave.', 'Magsaysay Blvd.', 'Aguinaldo St.', 'Laurel St.', 'Luna St.'];
                    return streets[Math.floor(Math.random() * streets.length)];
                }
                
                function getRandomName() {
                    const firstNames = ['Juan', 'Maria', 'Pedro', 'Ana', 'Jose', 'Rosa', 'Carlos', 'Sofia', 
                                      'Miguel', 'Elena', 'Antonio', 'Luisa', 'Ricardo', 'Teresa', 'Eduardo'];
                    const lastNames = ['Santos', 'Reyes', 'Cruz', 'Garcia', 'Mendoza', 'Torres', 'Ramos', 
                                     'Flores', 'Gonzales', 'Diaz', 'Aquino', 'Pascual', 'Robles', 'Bautista'];
                    
                    return `${firstNames[Math.floor(Math.random() * firstNames.length)]} ${lastNames[Math.floor(Math.random() * lastNames.length)]}`;
                }
                
                function getRandomHouseNumber() {
                    return getRandomInt(1, 999);
                }
                
                function getRandomTime(baseHour) {
                    const hour = getRandomInt(baseHour, baseHour + 2);
                    const minute = getRandomInt(0, 59);
                    return `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;
                }
                
                function getRandomDate(minusDays = 0) {
                    const date = new Date();
                    date.setDate(date.getDate() - minusDays);
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                }
                
                function getRandomResult() {
                    const results = ['Complete', 'Incomplete', 'Refused', 'No Response'];
                    const weights = [0.7, 0.2, 0.05, 0.05]; // 70% complete, 20% incomplete, 5% refused, 5% no response
                    
                    const random = Math.random();
                    let sum = 0;
                    for (let i = 0; i < results.length; i++) {
                        sum += weights[i];
                        if (random <= sum) return results[i];
                    }
                    return results[0]; // Default to Complete
                }
                
                // Generate random values for form fields
                const barangay = document.getElementById('barangay').value;
                const houseNumber = getRandomHouseNumber();
                const streetName = getRandomStreetName();
                const randomAddress = `${houseNumber} ${streetName}, ${barangay}`;
                const randomRespondent = getRandomName();
                const randomHouseholdHead = getRandomName();
                const randomTotalMembers = getRandomInt(1, 8);
                const randomVisitNumber = getRandomInt(1, 3);
                const randomVisitDate = getRandomDate(getRandomInt(0, 14)); // Random date within last 2 weeks
                const randomStartTime = getRandomTime(8); // Random time between 8-10 AM
                const randomEndTime = getRandomTime(10); // Random time between 10-12 PM
                const randomResult = getRandomResult();
                const randomNextVisitDate = randomResult === 'Incomplete' ? getRandomDate(-getRandomInt(1, 7)) : ''; // Future date if incomplete
                const randomInterviewer = getRandomName();
                const randomSupervisor = getRandomName();
                const randomEncodingSupervisor = getRandomName();
                
                // Predefined values for form fields with randomized data
                const randomValues = {
                    'address': randomAddress,
                    'respondent_name': randomRespondent,
                    'household_head': randomHouseholdHead,
                    'total_members': randomTotalMembers,
                    'visit_number': randomVisitNumber,
                    'visit_date': randomVisitDate,
                    'start_time': randomStartTime,
                    'end_time': randomEndTime,
                    'result': randomResult,
                    'next_visit_date': randomNextVisitDate,
                    'interviewer_name': randomInterviewer,
                    'supervisor_name': randomSupervisor,
                    'encoding_supervisor': randomEncodingSupervisor
                };
                
                // Populate form fields with randomized values
                for (const [fieldId, value] of Object.entries(randomValues)) {
                    const field = document.getElementById(fieldId);
                    if (field) {
                        field.value = value;
                        
                        // Trigger change event for select elements
                        if (field.tagName === 'SELECT') {
                            const event = new Event('change');
                            field.dispatchEvent(event);
                        }
                    }
                }
                
                // Show success notification
                const notification = document.createElement('div');
                notification.className = 'alert alert-success';
                notification.innerHTML = '<i class="fas fa-check-circle"></i> Form fields have been auto-filled with randomized test data. Please review and edit as needed.';
                
                // Insert notification at the top of the form
                const formSection = document.querySelector('.form-section');
                form.insertBefore(notification, formSection);
                
                // Remove notification after 5 seconds
                setTimeout(() => {
                    notification.style.opacity = '0';
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                }, 5000);
                
                // Add fade-out transition
                notification.style.transition = 'opacity 0.3s ease';
            });
        });
    </script>
    <!-- Success Modal -->
    <div id="successModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-check-circle" style="color: var(--success);"></i> Success</h2>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <p id="successMessage">Household added successfully!</p>
                <div class="household-number">
                    <span>Household Number:</span>
                    <strong id="householdNumber"></strong>
                </div>
            </div>
            <div class="modal-footer">
                <a href="household_list.php" class="btn-secondary">Back to List</a>
                <a id="registerMembersBtn" href="#" class="btn-primary">Register Household Members</a>
            </div>
        </div>
    </div>

    <style>
        /* Modal Styles */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        
        .modal.show {
            opacity: 1;
            visibility: visible;
        }
        
        .modal-content {
            background-color: white;
            border-radius: var(--radius-lg);
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            transform: translateY(-20px);
            transition: transform 0.3s ease;
        }
        
        .modal.show .modal-content {
            transform: translateY(0);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--spacing-4) var(--spacing-6);
            border-bottom: 1px solid var(--neutral-200);
        }
        
        .modal-header h2 {
            margin: 0;
            font-size: var(--font-size-xl);
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
        }
        
        .close-modal {
            font-size: 24px;
            cursor: pointer;
            color: var(--neutral-500);
            transition: color 0.2s ease;
        }
        
        /* Removed color change on hover */

        
        .modal-body {
            padding: var(--spacing-6);
        }
        
        .modal-body p {
            margin-top: 0;
            font-size: var(--font-size-lg);
            color: var(--neutral-700);
        }
        
        .household-number {
            background-color: var(--neutral-100);
            padding: var(--spacing-4);
            border-radius: var(--radius-md);
            margin-top: var(--spacing-4);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .household-number strong {
            font-size: var(--font-size-xl);
            color: var(--primary);
        }
        
        .modal-footer {
            padding: var(--spacing-4) var(--spacing-6);
            display: flex;
            justify-content: flex-end;
            gap: var(--spacing-3);
            border-top: 1px solid var(--neutral-200);
        }
    </style>

    <script>
        // Show success modal if household was added successfully
        <?php if (isset($success) && $success): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('successModal');
            const closeBtn = modal.querySelector('.close-modal');
            const householdNumber = document.getElementById('householdNumber');
            const registerMembersBtn = document.getElementById('registerMembersBtn');
            
            // Set household number in modal with proper formatting (6 digits with leading zeros and # prefix)
            householdNumber.textContent = '#<?php echo str_pad($added_household_number, 6, "0", STR_PAD_LEFT); ?>';
            
            // Configure register members button
            const memberRegistrationUrl = 'register_members.php?household_number=<?php echo $added_household_number; ?>&new=1';
            registerMembersBtn.href = memberRegistrationUrl;
            
            // Show modal
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
            
            // Close modal and redirect to household list
            closeBtn.addEventListener('click', function() {
                modal.classList.remove('show');
                setTimeout(() => {
                    modal.style.display = 'none';
                    window.location.href = 'household_list.php';
                }, 300);
            });
            
            // Automatically redirect to member registration after 3 seconds
            setTimeout(() => {
                modal.classList.remove('show');
                setTimeout(() => {
                    modal.style.display = 'none';
                    window.location.href = memberRegistrationUrl;
                }, 300);
            }, 3000);
            
            // Add click event to register members button
            registerMembersBtn.addEventListener('click', function(e) {
                e.preventDefault();
                modal.classList.remove('show');
                setTimeout(() => {
                    modal.style.display = 'none';
                    window.location.href = memberRegistrationUrl;
                }, 300);
            });
        });
        <?php endif; ?>
    </script>
</body>
</html>