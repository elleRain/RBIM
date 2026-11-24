<?php
session_start();
require_once '../plugins/conn.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in - using the correct session variable names
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Additional check for admin role
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'super_admin')) {
    echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
    exit;
}

// Check if member_id is provided
if (!isset($_GET['member_id']) || empty($_GET['member_id'])) {
    echo json_encode(['success' => false, 'message' => 'Member ID is required']);
    exit;
}

$member_id = $_GET['member_id'];

try {
    // Check if database connection exists
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    // Prepare SQL query to fetch ALL available member details from actual database schema
    $query = "SELECT 
                member_id,
                household_number,
                session_id,
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
                place_of_school,
                q15_monthly_income,
                q15a_source_of_income,
                q15b_occupation,
                q17_work_status,
                q18_place_of_work,
                q19_place_of_delivery,
                q20_birth_attendant,
                q21_immunization,
                q22_living_children,
                q23_fp_method,
                q24_source_of_fp,
                q25_intention_to_use_fp,
                q26_health_insurance,
                q27_facility_visited,
                q28_reason_for_visit,
                q29_disability,
                q30_solo_parent_status,
                q31_registered_senior,
                q32_registered_voter,
                q33_previous_residence_5yrs,
                q34_previous_residence_6mos,
                q35_length_of_stay,
                q36_type_of_resident,
                q37_date_of_transfer,
                q38_reasons_for_leaving,
                q39_plan_to_return,
                q40_reasons_for_transferring,
                q41_duration_of_stay,
                q42a_ctc_possession,
                q42b_ctc_issued_in_barangay,
                q43_skills_training_interest,
                q44_skills,
                q45_housing_tenure,
                q46_lot_tenure,
                q47_fuel_for_lighting,
                q48_fuel_for_cooking,
                q49_drinking_water_source,
                q50a_garbage_disposal_method,
                q50b_garbage_segregation,
                q51_toilet_facility,
                q52_building_type,
                q53_outer_wall_material,
                q54_female_death_details,
                q55_child_death_details,
                q56_common_diseases,
                q57_primary_needs,
                q58_future_residence
              FROM register_members 
              WHERE member_id = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "i", $member_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $member = mysqli_fetch_assoc($result);
        
        // Format date if available
        if ($member['date_of_birth'] && $member['date_of_birth'] !== '0000-00-00') {
            $member['date_of_birth'] = date('F j, Y', strtotime($member['date_of_birth']));
        } else {
            $member['date_of_birth'] = null;
        }
        
        // Format income if available
        if ($member['q15_monthly_income'] && $member['q15_monthly_income'] > 0) {
            $member['q15_monthly_income'] = number_format($member['q15_monthly_income'], 2);
        }
        
        echo json_encode([
            'success' => true,
            'member' => $member
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Member not found'
        ]);
    }
    
    mysqli_stmt_close($stmt);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>