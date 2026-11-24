<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Include database connection
include '../plugins/conn.php';

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Get household ID from GET parameter
    $household_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($household_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid household ID']);
        exit();
    }
    
    // Get the household_number from survey_session table using session_id
    $household_query = "SELECT household_number FROM survey_session WHERE session_id = ?";
    $household_stmt = $conn->prepare($household_query);
    $household_stmt->bind_param("i", $household_id);
    $household_stmt->execute();
    $household_result = $household_stmt->get_result();
    
    if ($household_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'The specified household record could not be found in the system. Please verify the household ID or search criteria and try again.']);
        exit();
    }
    
    // Get the household_number to use for member lookup
    $household_data = $household_result->fetch_assoc();
    $household_number = $household_data['household_number'];
    
    // Fetch members data from register_members table using household_number
    $members_query = "SELECT 
        member_id,
        name,
        relationship,
        sex,
        age,
        date_of_birth,
        place_of_birth,
        nationality,
        marital_status,
        religion,
        ethnicity,
        highest_education,
        currently_enrolled,
        school_level,
        place_of_school
    FROM register_members 
    WHERE household_number = ? 
    ORDER BY member_id ASC";
    
    $members_stmt = $conn->prepare($members_query);
    $members_stmt->bind_param("s", $household_number);
    $members_stmt->execute();
    $members_result = $members_stmt->get_result();
    
    $members = [];
    while ($row = $members_result->fetch_assoc()) {
        // Format date of birth if available
        if ($row['date_of_birth'] && $row['date_of_birth'] !== '0000-00-00') {
            $row['date_of_birth'] = date('M d, Y', strtotime($row['date_of_birth']));
        } else {
            $row['date_of_birth'] = 'N/A';
        }
        
        $members[] = $row;
    }
    
    // Return success response with members data
    echo json_encode([
        'success' => true,
        'members' => $members,
        'total_members' => count($members),
        'household_number' => $household_data['household_number']
    ]);
    
} catch (Exception $e) {
    // Log error and return error response
    error_log("Error fetching household members: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} finally {
    // Close database connection
    if (isset($conn)) {
        $conn->close();
    }
}
?>