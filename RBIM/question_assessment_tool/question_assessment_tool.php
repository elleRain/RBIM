<?php
declare(strict_types=1);

/**
 * Question Assessment Tool — municipality-scoped question catalog.
 */

require_once __DIR__ . '/../login.php';

function qat_questions_catalog(): array
{
    return [
        'version' => 3,
        'source' => 'RBIM-Tool-Updated.docx',
        'updated_at' => null,
        'sections' => [
            [
                'id' => 'demographic_characteristics',
                'title' => 'Demographic Characteristics',
                'description' => 'Personal information, age, gender, education, etc.',
                'icon' => 'user',
                'questions' => [
                    qat_q('q1_name', 'Q1 Name', 'text', true),
                    qat_q('q2_relationship', 'Q2 Relationship to Household Head', 'select', true, [
                        '01 = Head', '02 = Spouse', '03 = Son', '04 = Daughter', '05 = Stepson',
                        '06 = Stepdaughter', '07 = Son-in-law', '08 = Daughter-in-law', '09 = Grandson',
                        '10 = Granddaughter', '11 = Father', '12 = Mother', '13 = Brother', '14 = Sister',
                        '15 = Uncle', '16 = Aunt', '17 = Nephew', '18 = Niece', '19 = Other relative',
                        '20 = Non-relative', '21 = Boarder', '22 = Domestic helper',
                    ]),
                    qat_q('q3_sex', 'Q3 Sex', 'select', true, ['1 = Male', '2 = Female']),
                    qat_q('q4_age', 'Q4 Age', 'number', true),
                    qat_q('q5_date_of_birth', 'Q5 Date of Birth', 'date', false),
                    qat_q('q6_place_of_birth', 'Q6 Place of Birth', 'text', false),
                    qat_q('q7_nationality', 'Q7 Nationality', 'text', false),
                    qat_q('q8_marital_status', 'Q8 Marital Status', 'select', false, [
                        '1 = Single', '2 = Married', '3 = Living-in', '4 = Widowed',
                        '5 = Separated', '6 = Annulled', '7 = Unknown',
                    ]),
                    qat_q('q9_religion', 'Q9 Religion', 'text', false),
                    qat_q('q10_ethnicity', 'Q10 Ethnicity', 'text', false),
                    qat_q('q11_highest_education', 'Q11 Highest Level of Education Completed', 'select', false, [
                        '01 = No education / Not applicable', '02 = Pre-school', '03 = Elementary graduate',
                        '04 = Elementary level', '05 = High school graduate', '06 = Junior HS level',
                        '07 = Junior HS graduate', '08 = Senior HS level', '09 = Senior HS graduate',
                        '10 = Vocational/Tech', '11 = College level', '12 = College graduate',
                        '13 = Post-graduate', '14 = Master\'s Graduate', '15 = Doctorate Graduate',
                    ]),
                    qat_q('q12_currently_enrolled', 'Q12 Currently Enrolled', 'select', false, [
                        '1 = Yes, public', '2 = Yes, private', '3 = No',
                    ]),
                    qat_q('q13_school_level', 'Q13 School Level', 'select', false, [
                        '1 = Pre-school', '2 = Elementary', '3 = Junior High School',
                        '4 = Senior High School', '5 = Vocational/Technical', '6 = College/University',
                    ]),
                    qat_q('q14_place_of_school', 'Q14 Place of School', 'text', false),
                ],
            ],
            [
                'id' => 'economic_activity',
                'title' => 'Economic Activity',
                'description' => 'Employment, income, occupation, work status',
                'icon' => 'briefcase',
                'questions' => [
                    qat_q('q15_monthly_income', 'Q15 Monthly Income', 'text', false),
                    qat_q('q16a_source_of_income', 'Q16a Source of Income', 'select', false, [
                        '01 = Employment', '02 = Business', '03 = Remittance', '04 = Investments', '05 = Others',
                    ]),
                    qat_q('q16b_occupation', 'Q16b Occupation / Position', 'text', false),
                    qat_q('q17_work_status', 'Q17 Status of Work', 'select', false, [
                        '1 = Permanent Work', '2 = Casual Work', '3 = Contractual Work',
                        '4 = Individually Owned Business', '5 = Shared/Partnership Business', '6 = Corporate Business',
                    ]),
                    qat_q('q18_place_of_work', 'Q18 Place of Work', 'text', false),
                ],
            ],
            [
                'id' => 'health_information',
                'title' => 'Health Information',
                'description' => 'Health insurance, disability, facility visits, and medical status',
                'icon' => 'heart',
                'questions' => [
                    qat_q('q26_health_insurance', 'Q26 Health Insurance', 'select', false, [
                        '1 = PhilHealth paying member',
                        '2 = PhilHealth dependent of paying member',
                        '3 = PhilHealth indigent member',
                        '4 = PhilHealth dependent of indigent member',
                        '5 = Others / None',
                    ]),
                    qat_q('q26a_nutritional_status', 'Q26a Nutritional Status', 'text', false),
                    qat_q('q27_facility_visited', 'Q27 Facility Visited in Past 12 Months', 'select', false, [
                        '1 = Government hospital', '2 = RHU/Health center', '3 = Brgy. Health Station',
                        '4 = Private hospital', '5 = Private clinic', '6 = Others',
                    ]),
                    qat_q('q28_reason_for_visit', 'Q28 Reason for Visit in Health Facility', 'select', false, [
                        '1 = Sick/Injured', '2 = Prenatal/Postnatal', '3 = Gave birth',
                        '4 = Dental', '5 = Medical check-up', '6 = Others',
                    ]),
                    qat_q('q29_disability', 'Q29 Disability', 'select', false, [
                        'None', '1 = Sensory', '2 = Physical', '3 = Mental', '4 = Others',
                    ]),
                ],
            ],
            [
                'id' => 'maternal_child_health',
                'title' => 'Maternal & Child Health',
                'description' => 'Pregnancy, delivery, immunization, family planning',
                'icon' => 'stethoscope',
                'questions' => [
                    qat_q('q19_place_of_delivery', 'Q19 Place of Delivery', 'select', false, [
                        '1 = Public hospital', '2 = Private hospital', '3 = Brgy. Health Station',
                        '4 = Home', '5 = Others',
                    ]),
                    qat_q('q20_birth_attendant', 'Q20 Person Who Assisted in the Delivery', 'select', false, [
                        '1 = Doctor', '2 = Nurse', '3 = Midwife', '4 = Hilot', '5 = Others',
                    ]),
                    qat_q('q21_immunization', 'Q21 Immunization (Last Vaccine Received)', 'text', false),
                    qat_q('q22_living_children', 'Q22 Living Children', 'text', false),
                    qat_q('q23_fp_use', 'Q23 Family Planning (FP) Use', 'select', false, [
                        '01 = Female sterilization/Ligation', '02 = Male sterilization/Vasectomy',
                        '03 = IUD', '04 = Injectables', '05 = Pills', '06 = Condom',
                        '07 = Natural / Rhythm', '08 = Withdrawal', '09 = None', '10 = Others',
                    ]),
                    qat_q('q24_source_of_fp', 'Q24 Source of FP Method', 'select', false, [
                        '1 = Government hospital', '2 = RHU/Health center', '3 = Brgy. Health Station',
                        '4 = Private hospital/clinic', '5 = Pharmacy', '6 = Others',
                    ]),
                    qat_q('q25_intention_to_use_fp', 'Q25 Intention to Use FP', 'select', false, [
                        '1 = Yes', '2 = No', '3 = Already using', '4 = Not applicable',
                    ]),
                ],
            ],
            [
                'id' => 'socio_civic_migration',
                'title' => 'Socio-Civic Participation & Migration',
                'description' => 'Voter registration, residency, transfer, CTC, skills training',
                'icon' => 'map',
                'questions' => [
                    qat_q('q30_solo_parent', 'Q30 Solo Parent', 'select', false, ['1 = Yes', '2 = No']),
                    qat_q('q31_registered_senior', 'Q31 Registered Senior Citizen', 'select', false, [
                        '1 = Yes', '2 = No', '99 = Not applicable',
                    ]),
                    qat_q('q32_registered_voter', 'Q32 Registered Voter', 'select', false, [
                        '1 = Yes', '2 = No', '99 = Not applicable',
                    ]),
                    qat_q('q33_previous_residence_5yrs', 'Q33 Previous Residence (5 years ago)', 'text', false),
                    qat_q('q34_previous_residence', 'Q34 Previous Residence', 'text', false),
                    qat_q('q35_length_of_stay', 'Q35 Length of Stay in the Barangay', 'text', false),
                    qat_q('q36_type_of_resident', 'Q36 Type of Resident', 'select', false, [
                        '1 = Non-Migrant (Length of Stay is over 5 years)',
                        '2 = Migrant (Length of Stay is between 6 months and 1 day to 5 years)',
                    ]),
                    qat_q('q37_date_of_transfer', 'Q37 Date of Transfer', 'text', false),
                    qat_q('q38a_reason_leaving', 'Q38A Reason for Leaving Previous Residence (1)', 'text', false),
                    qat_q('q38b_reason_leaving', 'Q38B Reason for Leaving Previous Residence (2)', 'text', false),
                    qat_q('q38c_reason_leaving', 'Q38C Reason for Leaving Previous Residence (3)', 'text', false),
                    qat_q('q39_return_previous', 'Q39 Return to Previous Residence', 'text', false),
                    qat_q('q40a_reason_transferring', 'Q40A Reason for Transferring in this Barangay (1)', 'text', false),
                    qat_q('q40b_reason_transferring', 'Q40B Reason for Transferring in this Barangay (2)', 'text', false),
                    qat_q('q40c_reason_transferring', 'Q40C Reason for Transferring in this Barangay (3)', 'text', false),
                    qat_q('q41_duration_of_stay', 'Q41 Duration of Stay in Current Barangay', 'text', false),
                    qat_q('q42a_ctc_possession', 'Q42A CTC Information (Possession)', 'select', false, [
                        '1 = Yes', '2 = No', '99 = Not applicable',
                    ]),
                    qat_q('q42b_ctc_issued_in_barangay', 'Q42B CTC Information (Issued in Barangay)', 'select', false, [
                        '1 = Yes', '2 = No', '99 = Not applicable',
                    ]),
                    qat_q('q43_skills_training', 'Q43 Skills Development Training Interest', 'textarea', false),
                    qat_q('q44_skills', 'Q44 Skills', 'textarea', false),
                ],
            ],
            [
                'id' => 'housing_environment',
                'title' => 'Housing & Environment',
                'description' => 'Housing ownership, water source, toilet facilities, building materials',
                'icon' => 'home',
                'questions' => [
                    qat_q('q45_housing_tenure', 'Q45 Housing Unit Tenure', 'select', false, [
                        'Owned/being amortized', 'Rented',
                        'Rent-free with consent of owner', 'Rent-free without consent of owner',
                    ]),
                    qat_q('q46_lot_tenure', 'Q46 Lot Tenure', 'select', false, [
                        'Owned/being amortized', 'Rented',
                        'Rent-free with consent of owner', 'Rent-free without consent of owner',
                    ]),
                    qat_q('q47_fuel_for_lighting', 'Q47 Fuel for Lighting', 'text', false),
                    qat_q('q48_fuel_for_cooking', 'Q48 Fuel for Cooking', 'text', false),
                    qat_q('q49_drinking_water_source', 'Q49 Main Source of Drinking Water', 'select', false, [
                        '01 = Lake, river, rain, others', '02 = Dug well', '03 = Unprotected spring',
                        '04 = Protected spring', '05 = Peddler', '06 = Tubed/Piped shallow well',
                        '07 = Shared, tubed/piped deep well', '08 = Own use, tubed/piped deep well',
                        '09 = Shared, faucet community water system',
                        '10 = Own use, faucet community water system',
                        '11 = Bottled water', '12 = Others, please specify',
                    ]),
                    qat_q('q50a_garbage_disposal', 'Q50a Kitchen Garbage Disposal Method', 'text', false),
                    qat_q('q50b_garbage_segregation', 'Q50b Garbage Segregation', 'select', false, [
                        '1 = Yes', '2 = No',
                    ]),
                    qat_q('q51_toilet_facility', 'Q51 Toilet Facility', 'select', false, [
                        'None', 'Open pit', 'Closed pit',
                        'Water-sealed, other depository, shared',
                        'Water-sealed, other depository, exclusive',
                        'Water-sealed, sewer septic tank, shared',
                        'Water-sealed, sewer septic tank, exclusive',
                        'Others, specify',
                    ]),
                    qat_q('q52_building_type', 'Q52 Type of Building/House', 'select', false, [
                        'Single house', 'Duplex', 'Multi-unit residential (three units or more)',
                        'Commercial/industrial/agricultural',
                        'Institutional living quarter (hotel, hospital)',
                        'Other housing units (boat, cave, others)',
                    ]),
                    qat_q('q53_outer_wall_material', 'Q53 Construction Materials of the Outer Wall', 'select', false, [
                        'No walls', 'Makeshift/salvaged/improvised materials', 'Glass', 'Asbestos',
                        'Bamboo/Sawali/Cogon/Nipa', 'Galvanized iron/aluminum',
                        'Half concrete/brick/stone and half wood', 'Wood',
                        'Concrete/brick/stone', 'Others, please specify',
                    ]),
                ],
            ],
            [
                'id' => 'mortality_community_needs',
                'title' => 'Mortality & Community Needs',
                'description' => 'Deaths, common diseases, urgent needs, barangay help',
                'icon' => 'archive',
                'questions' => [
                    qat_q(
                        'q54_household_death',
                        'Q54 Has any member of the household died in the past 12 months? If yes, age at death and cause?',
                        'textarea',
                        false
                    ),
                    qat_q(
                        'q55_child_death',
                        'Q55 Do you have a child HH member below 5 years old who died in the past 12 months? Age and cause?',
                        'textarea',
                        false
                    ),
                    qat_q(
                        'q56_common_diseases',
                        'Q56 What are the common diseases that cause death in this barangay?',
                        'textarea',
                        false
                    ),
                    qat_q(
                        'q57_primary_needs',
                        'Q57 What do you think are the primary needs of this barangay?',
                        'textarea',
                        false
                    ),
                    qat_q(
                        'q58_future_residence',
                        'Q58 Where does your household intend to stay five years from now?',
                        'textarea',
                        false
                    ),
                    qat_q(
                        'q59_hunger_experience',
                        'Q59 Did anyone in your family ever experience hunger?',
                        'select',
                        false,
                        ['1 = Yes', '2 = No']
                    ),
                    qat_q(
                        'q60_cannot_read',
                        'Q60 Do you have a family member ages 8 years old and above who can\'t read?',
                        'select',
                        false,
                        ['1 = Yes', '2 = No']
                    ),
                    qat_q(
                        'q61_evacuation_center',
                        'Q61 Do you know where the evacuation center in your area is?',
                        'select',
                        false,
                        ['1 = Yes', '2 = No']
                    ),
                    qat_q(
                        'q62_emergency_kit',
                        'Q62 Do you have an emergency kit/bag in your household?',
                        'select',
                        false,
                        ['1 = Yes', '2 = No']
                    ),
                ],
            ],
        ],
    ];
}

function qat_q(string $key, string $label, string $type, bool $required, array $options = []): array
{
    return [
        'id' => $key,
        'key' => $key,
        'label' => $label,
        'type' => $type,
        'required' => $required,
        'enabled' => true,
        'options' => array_values($options),
        'help_text' => '',
        'show_when' => null,
    ];
}

/**
 * @param mixed $raw
 * @return array<string, mixed>|null
 */
function qat_normalize_show_when($raw): ?array
{
    if (!is_array($raw)) {
        return null;
    }

    $field = preg_replace('/[^a-zA-Z0-9_]/', '_', trim((string) ($raw['field'] ?? '')));
    if ($field === '') {
        return null;
    }

    $operator = (string) ($raw['operator'] ?? 'equals');
    $allowed = ['equals', 'not_equals', 'in', 'not_in', 'empty', 'not_empty'];
    if (!in_array($operator, $allowed, true)) {
        $operator = 'equals';
    }

    $showWhen = [
        'field' => $field,
        'operator' => $operator,
    ];

    if (!in_array($operator, ['empty', 'not_empty'], true)) {
        $value = $raw['value'] ?? '';
        if (is_array($value)) {
            $value = implode(', ', array_map('strval', $value));
        }
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $showWhen['value'] = $value;
    }

    return $showWhen;
}

function qat_shared_data_path(): string
{
    return __DIR__ . '/data/questions.json';
}

function qat_muni_data_path(int $muniId): string
{
    return __DIR__ . '/data/questions-muni-' . $muniId . '.json';
}

function qat_decode_questions_file(string $path): ?array
{
    if (!is_file($path)) {
        return null;
    }
    $raw = file_get_contents($path);
    $data = json_decode($raw ?: '', true);
    if (is_array($data) && !empty($data['sections'])) {
        return $data;
    }
    return null;
}

function qat_default_catalog(): array
{
    $shared = qat_decode_questions_file(qat_shared_data_path());
    return $shared ?? qat_questions_catalog();
}

function qat_question_count(array $data): int
{
    $count = 0;
    foreach ($data['sections'] ?? [] as $section) {
        $count += count($section['questions'] ?? []);
    }
    return $count;
}

function qat_save_questions_for_muni(int $muniId, array $data): bool
{
    if ($muniId <= 0) {
        return false;
    }

    $path = qat_muni_data_path($muniId);
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $data['city_municipality_id'] = $muniId;
    $data['updated_at'] = date('c');
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }

    return file_put_contents($path, $json . PHP_EOL) !== false;
}

function qat_load_questions_for_muni(int $muniId): array
{
    $existing = qat_decode_questions_file(qat_muni_data_path($muniId));
    if ($existing !== null) {
        return $existing;
    }

    $catalog = qat_default_catalog();
    $catalog['city_municipality_id'] = $muniId;
    return $catalog;
}

$user = require_role('super_admin', 'muni_admin');
$role = (string) ($user['role'] ?? '');
$isSuperAdmin = $role === 'super_admin';
$isMuniAdmin = $role === 'muni_admin';
$userMuniId = (int) ($user['city_municipality_id'] ?? 0);

$page_title = 'Question Assessment Tool';
$role_label = role_label($role);
$user_name = user_display_name($user);
$user_initials = user_initials($user);
$logo = app_url('assets/img/system-logo.jpg');
$css = app_url('assets/css/dashboard.css');
$qatCss = app_url('assets/css/qat.css');
$logout = app_url('logout.php');
$activeNav = 'question_assessment';
$pageHeading = 'Question Assessment Tool';
$pageSubheading = 'Province of Siquijor · CPD – Negros Island Region';

$municipalityLogos = [
    'enrique villanueva' => 'Enrique Villanueva.jpg',
    'larena' => 'Larena.jpg',
    'lazi' => 'lazi.jpg',
    'maria' => 'maria.jpg',
    'san juan' => 'sanjuan.jpg',
    'siquijor' => 'Siquijor.png',
];

$municipalities = [];
$selectedMuni = null;
$questionsData = ['sections' => []];
$filterMuniId = $isSuperAdmin ? max(0, (int) ($_GET['muni'] ?? 0)) : $userMuniId;
$showMuniPicker = $isSuperAdmin && $filterMuniId <= 0;

try {
    $pdo = db();
    $municipalities = $pdo->query(
        'SELECT city_municipality_id, name, type
         FROM city_municipality
         ORDER BY name ASC'
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $municipalities = [];
}

if ($filterMuniId > 0) {
    foreach ($municipalities as $mun) {
        if ((int) $mun['city_municipality_id'] === $filterMuniId) {
            $selectedMuni = $mun;
            break;
        }
    }
    if ($selectedMuni === null && $isMuniAdmin && $userMuniId > 0) {
        $selectedMuni = [
            'city_municipality_id' => $userMuniId,
            'name' => (string) ($user['municipality'] ?? 'Municipality'),
        ];
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    $payload = json_decode((string) file_get_contents('php://input'), true);
    $saveMuniId = (int) ($payload['city_municipality_id'] ?? $filterMuniId);
    if ($isMuniAdmin) {
        $saveMuniId = $userMuniId;
    }

    $allowed = false;
    if ($saveMuniId > 0) {
        foreach ($municipalities as $mun) {
            if ((int) $mun['city_municipality_id'] === $saveMuniId) {
                $allowed = true;
                break;
            }
        }
    }

    if (
        !is_array($payload)
        || ($payload['action'] ?? '') !== 'save'
        || $saveMuniId <= 0
        || !$allowed
        || !is_array($payload['questions'] ?? null)
        || empty($payload['questions']['sections'])
    ) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Unable to save questions.']);
        exit;
    }

    $ok = qat_save_questions_for_muni($saveMuniId, $payload['questions']);
    echo json_encode(['ok' => $ok]);
    exit;
}

if ($isSuperAdmin && $filterMuniId > 0 && $selectedMuni === null) {
    redirect(app_url('question_assessment_tool/question_assessment_tool.php'));
}

if ($showMuniPicker) {
    $pageSubheading = 'Select a municipality to view its question assessment tool';
    foreach ($municipalities as &$mun) {
        $muniId = (int) $mun['city_municipality_id'];
        $data = qat_decode_questions_file(qat_muni_data_path($muniId)) ?? qat_default_catalog();
        $mun['question_count'] = qat_question_count($data);
        $mun['customized'] = is_file(qat_muni_data_path($muniId));
    }
    unset($mun);
} else {
    if ($filterMuniId <= 0) {
        $questionsData = qat_default_catalog();
    } else {
        $questionsData = qat_load_questions_for_muni($filterMuniId);
        $muniName = (string) ($selectedMuni['name'] ?? 'Municipality');
        $pageSubheading = $muniName . ' · Question Assessment Tool';
        if ($isSuperAdmin) {
            $backUrl = app_url('question_assessment_tool/question_assessment_tool.php');
            $backLabel = 'Municipalities';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($page_title); ?> — RBIM</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo htmlspecialchars($css); ?>">
  <link rel="stylesheet" href="<?php echo htmlspecialchars($qatCss); ?>">
</head>
<body>
  <div class="overlay" id="sidebar-overlay" hidden></div>

  <div class="app">
    <?php require __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="main">
      <?php require __DIR__ . '/../includes/topbar.php'; ?>

      <main class="content">
        <?php if ($showMuniPicker): ?>
          <section class="mgmt-page">
            <div class="mgmt-toolbar">
              <div class="mgmt-search">
                <span class="mgmt-search-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                </span>
                <input
                  type="search"
                  id="qat-muni-search"
                  class="mgmt-search-input"
                  placeholder="Search municipalities..."
                  autocomplete="off"
                >
              </div>
            </div>

            <div class="mgmt-grid" id="qat-muni-grid" aria-label="Municipality assessment tools">
              <?php if (empty($municipalities)): ?>
                <p class="mgmt-empty" id="qat-muni-empty">No municipality records found.</p>
              <?php else: ?>
                <?php foreach ($municipalities as $mun): ?>
                  <?php
                    $muniKey = strtolower(trim((string) $mun['name']));
                    $logoFile = $municipalityLogos[$muniKey] ?? null;
                    $cityLogo = $logoFile
                        ? app_url('assets/img/Munacipality/' . rawurlencode($logoFile))
                        : $logo;
                  ?>
                  <article
                    class="mgmt-card"
                    data-name="<?php echo htmlspecialchars(strtolower((string) $mun['name'])); ?>"
                  >
                    <div class="mgmt-card-logo">
                      <img
                        src="<?php echo htmlspecialchars($cityLogo); ?>"
                        alt=""
                        width="96"
                        height="96"
                      >
                    </div>
                    <h3 class="mgmt-card-name"><?php echo htmlspecialchars((string) $mun['name']); ?></h3>
                    <span class="mgmt-badge"><?php echo !empty($mun['customized']) ? 'Custom tool' : 'Default tool'; ?></span>
                    <p class="mgmt-card-meta">
                      <?php echo number_format((int) ($mun['question_count'] ?? 0)); ?> questions
                    </p>
                    <a
                      class="mgmt-view-btn"
                      href="<?php echo htmlspecialchars(app_url('question_assessment_tool/question_assessment_tool.php?muni=' . (int) $mun['city_municipality_id'])); ?>"
                    >
                      <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                      </svg>
                      View tool
                    </a>
                  </article>
                <?php endforeach; ?>
                <p class="mgmt-empty" id="qat-muni-empty" hidden>No municipalities match your search.</p>
              <?php endif; ?>
            </div>
          </section>
        <?php else: ?>
        <div class="qat-page">
          <div class="qat-categories" id="category-list" aria-label="Question categories"></div>

          <section class="qat-workspace" aria-labelledby="active-title">
            <div class="qat-workspace-head">
              <div class="qat-workspace-title">
                <div class="qat-cat-icon" id="active-icon" aria-hidden="true"></div>
                <div>
                  <h2 id="active-title">Category</h2>
                  <p id="active-count">0 questions in this category</p>
                </div>
              </div>
              <div class="qat-workspace-tools">
                <label class="qat-search" for="q-search">
                  <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                  <input type="search" id="q-search" placeholder="Search questions…" autocomplete="off">
                </label>
                <button type="button" class="qat-btn qat-btn-primary" id="btn-add-question">
                  <svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                  Add Question
                </button>
              </div>
            </div>

            <div class="qat-table-panel">
              <div class="qat-table-wrap">
                <table class="qat-table">
                  <thead>
                    <tr>
                      <th class="qat-col-num" scope="col">No.</th>
                      <th scope="col">Question</th>
                      <th class="qat-col-field" scope="col">Field ID</th>
                      <th class="qat-col-type" scope="col">Type</th>
                      <th class="qat-col-req" scope="col">Required</th>
                      <th class="qat-col-status" scope="col">Status</th>
                      <th class="qat-col-action" scope="col">Actions</th>
                    </tr>
                  </thead>
                  <tbody id="question-rows"></tbody>
                </table>
              </div>
              <div class="qat-table-foot" id="table-foot">
                <span id="table-foot-text">Showing 0 questions</span>
              </div>
            </div>
          </section>
        </div>
        <?php endif; ?>
      </main>
    </div>
  </div>

  <?php if (!$showMuniPicker): ?>
  <div class="qat-modal-backdrop" id="question-modal" hidden>
    <div class="qat-modal qat-modal-lg" role="dialog" aria-modal="true" aria-labelledby="modal-title">
      <div class="qat-modal-head">
        <div class="qat-modal-head-copy">
          <p class="qat-modal-kicker" id="modal-kicker">Question editor</p>
          <h3 id="modal-title">Add Question</h3>
          <p class="qat-modal-sub" id="modal-sub">Configure field details, validation, and visibility rules.</p>
        </div>
        <button type="button" class="qat-icon-btn" id="modal-close" aria-label="Close">
          <svg viewBox="0 0 24 24"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>
        </button>
      </div>

      <div class="qat-modal-body">
        <section class="qat-form-section">
          <div class="qat-form-section-head">
            <span class="qat-form-step">1</span>
            <div>
              <h4>Question details</h4>
              <p>Label, identifier, and input type for this field.</p>
            </div>
          </div>
          <div class="qat-form-section-body">
            <div class="qat-field">
              <label for="q-label">Question label <span class="qat-req">*</span></label>
              <input type="text" id="q-label" maxlength="180" placeholder="e.g. Q3 Sex">
            </div>
            <div class="qat-field-row">
              <div class="qat-field">
                <label for="q-key">Field ID</label>
                <input type="text" id="q-key" maxlength="80" placeholder="e.g. q3_sex">
                <p class="qat-field-hint">Auto-generated from label if left blank.</p>
              </div>
              <div class="qat-field">
                <label for="q-type">Input type</label>
                <select id="q-type">
                  <option value="text">Text</option>
                  <option value="number">Number</option>
                  <option value="date">Date</option>
                  <option value="select">Select</option>
                  <option value="textarea">Textarea</option>
                </select>
              </div>
            </div>
            <div class="qat-field" id="options-field" hidden>
              <label for="q-options">Select options <span class="qat-req">*</span></label>
              <textarea id="q-options" rows="5" placeholder="1 = Male&#10;2 = Female"></textarea>
              <p class="qat-field-hint">Enter one option per line. Used only for Select fields.</p>
            </div>
            <div class="qat-field">
              <label for="q-help">Help text</label>
              <input type="text" id="q-help" maxlength="220" placeholder="Optional guidance shown below the field">
            </div>
          </div>
        </section>

        <section class="qat-form-section">
          <div class="qat-form-section-head">
            <span class="qat-form-step">2</span>
            <div>
              <h4>Field settings</h4>
              <p>Control whether this question is required and active on the form.</p>
            </div>
          </div>
          <div class="qat-form-section-body qat-toggles">
            <label class="qat-toggle">
              <input type="checkbox" id="q-required">
              <span class="qat-toggle-ui" aria-hidden="true"></span>
              <span class="qat-toggle-copy">
                <strong>Required field</strong>
                <small>Respondent must answer this question during encoding.</small>
              </span>
            </label>
            <label class="qat-toggle">
              <input type="checkbox" id="q-enabled" checked>
              <span class="qat-toggle-ui" aria-hidden="true"></span>
              <span class="qat-toggle-copy">
                <strong>Enabled</strong>
                <small>Include this question on the assessment form.</small>
              </span>
            </label>
          </div>
        </section>

        <section class="qat-form-section">
          <div class="qat-form-section-head">
            <span class="qat-form-step">3</span>
            <div>
              <h4>Conditional visibility</h4>
              <p>Hide this question when another field’s answer does not match.</p>
            </div>
          </div>
          <div class="qat-form-section-body">
            <label class="qat-toggle qat-toggle-inline">
              <input type="checkbox" id="q-cond-enabled">
              <span class="qat-toggle-ui" aria-hidden="true"></span>
              <span class="qat-toggle-copy">
                <strong>Enable conditional rule</strong>
                <small>Only show this question when the selected condition is met.</small>
              </span>
            </label>

            <div class="qat-cond-panel" id="cond-panel" hidden>
              <div class="qat-field-row qat-cond-row">
                <div class="qat-field">
                  <label for="q-cond-field">Depends on field</label>
                  <select id="q-cond-field">
                    <option value="">— Select a field —</option>
                  </select>
                </div>
                <div class="qat-field">
                  <label for="q-cond-operator">Show when</label>
                  <select id="q-cond-operator">
                    <option value="equals">Equals</option>
                    <option value="not_equals">Does not equal</option>
                    <option value="in">Is any of</option>
                    <option value="not_in">Is none of</option>
                    <option value="not_empty">Has a value</option>
                    <option value="empty">Is empty</option>
                  </select>
                </div>
              </div>
              <div class="qat-field" id="cond-value-wrap">
                <label for="q-cond-value">Expected value</label>
                <select id="q-cond-value-select" hidden></select>
                <input type="text" id="q-cond-value-text" placeholder="Enter the value that must match">
                <p class="qat-field-hint" id="cond-value-hint">Question is hidden when this condition is not satisfied.</p>
              </div>
              <div class="qat-cond-preview" id="cond-preview" hidden>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <span id="cond-preview-text"></span>
              </div>
            </div>
          </div>
        </section>
      </div>

      <div class="qat-modal-foot">
        <button type="button" class="qat-btn qat-btn-secondary" id="modal-cancel">Cancel</button>
        <button type="button" class="qat-btn qat-btn-primary" id="modal-save">
          <svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>
          Save question
        </button>
      </div>
    </div>
  </div>

  <div class="qat-toast" id="toast" role="status" aria-live="polite"></div>
  <?php endif; ?>

  <?php require __DIR__ . '/../includes/scripts.php'; ?>
  <?php if ($showMuniPicker): ?>
  <script>
    (function () {
      var input = document.getElementById('qat-muni-search');
      var cards = document.querySelectorAll('#qat-muni-grid .mgmt-card');
      var empty = document.getElementById('qat-muni-empty');
      if (!input) return;

      input.addEventListener('input', function () {
        var q = input.value.trim().toLowerCase();
        var visible = 0;
        cards.forEach(function (card) {
          var name = card.getAttribute('data-name') || '';
          var show = !q || name.indexOf(q) !== -1;
          card.hidden = !show;
          if (show) visible += 1;
        });
        if (empty) empty.hidden = visible > 0;
      });
    })();
  </script>
  <?php else: ?>
  <script>
    (function () {
      var state = <?php echo json_encode($questionsData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
      var muniId = <?php echo (int) $filterMuniId; ?>;
      var activeSectionId = (state.sections && state.sections[0]) ? state.sections[0].id : null;
      var editIndex = null;
      var searchQuery = '';

      var ICONS = {
        user: '<svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
        briefcase: '<svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>',
        heart: '<svg viewBox="0 0 24 24"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/><path d="M3.5 12h4l2 3 3-6 2 3h6"/></svg>',
        stethoscope: '<svg viewBox="0 0 24 24"><path d="M11 2v4"/><path d="M7 2v4"/><path d="M7 6a4 4 0 0 0 8 0"/><path d="M15 10v3a4 4 0 0 0 8 0v-1"/><circle cx="20" cy="18" r="2"/></svg>',
        map: '<svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
        home: '<svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>',
        archive: '<svg viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="5" rx="1"/><path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"/><path d="M10 12h4"/></svg>'
      };

      var categoryList = document.getElementById('category-list');
      var questionRows = document.getElementById('question-rows');
      var activeTitle = document.getElementById('active-title');
      var activeCount = document.getElementById('active-count');
      var activeIcon = document.getElementById('active-icon');
      var modal = document.getElementById('question-modal');
      var toast = document.getElementById('toast');
      var optionsField = document.getElementById('options-field');
      var searchInput = document.getElementById('q-search');
      var tableFootText = document.getElementById('table-foot-text');
      var condPanel = document.getElementById('cond-panel');
      var condValueWrap = document.getElementById('cond-value-wrap');
      var condValueSelect = document.getElementById('q-cond-value-select');
      var condValueText = document.getElementById('q-cond-value-text');
      var condPreview = document.getElementById('cond-preview');
      var condPreviewText = document.getElementById('cond-preview-text');

      var OPERATOR_LABELS = {
        equals: 'equals',
        not_equals: 'does not equal',
        in: 'is any of',
        not_in: 'is none of',
        not_empty: 'has a value',
        empty: 'is empty'
      };

      function allQuestions(excludeKey) {
        var list = [];
        (state.sections || []).forEach(function (section) {
          (section.questions || []).forEach(function (q) {
            var key = q.key || q.id || '';
            if (excludeKey && key === excludeKey) return;
            list.push({
              key: key,
              label: q.label || key,
              type: q.type || 'text',
              options: q.options || [],
              sectionTitle: section.title || ''
            });
          });
        });
        return list;
      }

      function findQuestionByKey(key) {
        if (!key) return null;
        var found = null;
        (state.sections || []).some(function (section) {
          return (section.questions || []).some(function (q) {
            if ((q.key || q.id) === key) {
              found = q;
              return true;
            }
            return false;
          });
        });
        return found;
      }

      function formatConditionSummary(showWhen) {
        if (!showWhen || !showWhen.field) return '';
        var parent = findQuestionByKey(showWhen.field);
        var parentLabel = parent ? (parent.label || showWhen.field) : showWhen.field;
        var op = OPERATOR_LABELS[showWhen.operator] || showWhen.operator;
        if (showWhen.operator === 'empty' || showWhen.operator === 'not_empty') {
          return 'Show when “' + parentLabel + '” ' + op;
        }
        var val = Array.isArray(showWhen.value) ? showWhen.value.join(', ') : (showWhen.value || '');
        return 'Show when “' + parentLabel + '” ' + op + ' “' + val + '”';
      }

      function populateCondFieldOptions(excludeKey) {
        var select = document.getElementById('q-cond-field');
        var current = select.value;
        select.innerHTML = '<option value="">— Select a field —</option>';
        allQuestions(excludeKey).forEach(function (q) {
          var opt = document.createElement('option');
          opt.value = q.key;
          opt.textContent = q.label + (q.sectionTitle ? ' · ' + q.sectionTitle : '');
          select.appendChild(opt);
        });
        if (current && select.querySelector('option[value="' + current + '"]')) {
          select.value = current;
        }
      }

      function toggleCondPanel() {
        var enabled = document.getElementById('q-cond-enabled').checked;
        condPanel.hidden = !enabled;
        if (enabled) updateCondValueInput();
      }

      function operatorNeedsValue(op) {
        return op !== 'empty' && op !== 'not_empty';
      }

      function updateCondValueInput() {
        var fieldKey = document.getElementById('q-cond-field').value;
        var operator = document.getElementById('q-cond-operator').value;
        var parent = findQuestionByKey(fieldKey);
        var needsValue = operatorNeedsValue(operator);

        condValueWrap.hidden = !needsValue;
        if (!needsValue) {
          updateCondPreview();
          return;
        }

        var hasOptions = parent && parent.type === 'select' && parent.options && parent.options.length;
        condValueSelect.hidden = !hasOptions;
        condValueText.hidden = !!hasOptions;

        if (hasOptions) {
          var prev = condValueSelect.value;
          condValueSelect.innerHTML = '';
          parent.options.forEach(function (opt) {
            var o = document.createElement('option');
            o.value = opt;
            o.textContent = opt;
            condValueSelect.appendChild(o);
          });
          if (prev) condValueSelect.value = prev;
          else if (condValueText.value) condValueSelect.value = condValueText.value;
        }

        updateCondPreview();
      }

      function updateCondPreview() {
        var enabled = document.getElementById('q-cond-enabled').checked;
        if (!enabled) {
          condPreview.hidden = true;
          return;
        }
        var field = document.getElementById('q-cond-field').value;
        if (!field) {
          condPreview.hidden = true;
          return;
        }
        var operator = document.getElementById('q-cond-operator').value;
        var value = '';
        if (operatorNeedsValue(operator)) {
          value = condValueSelect.hidden ? condValueText.value.trim() : condValueSelect.value;
        }
        var summary = formatConditionSummary({ field: field, operator: operator, value: value });
        condPreviewText.textContent = summary;
        condPreview.hidden = !summary;
      }

      function readShowWhenFromForm() {
        if (!document.getElementById('q-cond-enabled').checked) return null;
        var field = document.getElementById('q-cond-field').value;
        if (!field) return null;
        var operator = document.getElementById('q-cond-operator').value;
        var showWhen = { field: field, operator: operator };
        if (operatorNeedsValue(operator)) {
          var value = condValueSelect.hidden
            ? condValueText.value.trim()
            : condValueSelect.value;
          if (!value) return null;
          showWhen.value = value;
        }
        return showWhen;
      }

      function fillShowWhenForm(showWhen) {
        var enabled = !!(showWhen && showWhen.field);
        document.getElementById('q-cond-enabled').checked = enabled;
        document.getElementById('q-cond-field').value = enabled ? showWhen.field : '';
        document.getElementById('q-cond-operator').value = (showWhen && showWhen.operator) ? showWhen.operator : 'equals';
        var val = (showWhen && showWhen.value) ? String(showWhen.value) : '';
        condValueText.value = val;
        condValueSelect.value = val;
        toggleCondPanel();
        updateCondValueInput();
      }

      function slugify(value) {
        return String(value || '')
          .toLowerCase()
          .trim()
          .replace(/[^a-z0-9]+/g, '_')
          .replace(/^_+|_+$/g, '')
          .slice(0, 80) || ('field_' + Date.now());
      }

      function activeSection() {
        return (state.sections || []).find(function (s) { return s.id === activeSectionId; }) || null;
      }

      function showToast(message, type) {
        toast.textContent = message;
        toast.className = 'qat-toast show ' + (type || 'ok');
        clearTimeout(showToast._t);
        showToast._t = setTimeout(function () { toast.classList.remove('show'); }, 2800);
      }

      function iconHtml(name) {
        return ICONS[name] || ICONS.user;
      }

      function shortOptions(options) {
        if (!options || !options.length) return '';
        var shown = options.slice(0, 3);
        var text = shown.join(' · ');
        if (options.length > 3) text += ' · +' + (options.length - 3) + ' more';
        return text;
      }

      function matchesSearch(q) {
        if (!searchQuery) return true;
        var hay = [
          q.label || '',
          q.key || '',
          q.id || '',
          q.type || '',
          q.help_text || '',
          (q.options || []).join(' ')
        ].join(' ').toLowerCase();
        return hay.indexOf(searchQuery) !== -1;
      }

      function renderCategories() {
        categoryList.innerHTML = '';
        (state.sections || []).forEach(function (section) {
          var btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'qat-cat-card' + (section.id === activeSectionId ? ' active' : '');
          btn.innerHTML =
            '<div class="qat-cat-icon">' + iconHtml(section.icon || 'user') + '</div>' +
            '<strong></strong><span></span>';
          btn.querySelector('strong').textContent = section.title;
          btn.querySelector('span').textContent = section.description || '';
          btn.addEventListener('click', function () {
            activeSectionId = section.id;
            searchQuery = '';
            searchInput.value = '';
            render();
          });
          categoryList.appendChild(btn);
        });
      }

      function renderTable() {
        var section = activeSection();
        questionRows.innerHTML = '';

        if (!section) {
          questionRows.innerHTML = '<tr><td colspan="7"><div class="qat-empty"><strong>No category selected</strong><p>Choose a category card above to view questions.</p></div></td></tr>';
          tableFootText.textContent = 'No category selected';
          return;
        }

        var allCount = (section.questions || []).length;
        var filtered = (section.questions || []).filter(matchesSearch);
        var requiredCount = filtered.filter(function (q) { return !!q.required; }).length;

        activeTitle.textContent = section.title;
        activeCount.textContent = searchQuery
          ? (filtered.length + ' of ' + allCount + ' questions shown')
          : (allCount + ' questions in this category');
        activeIcon.innerHTML = iconHtml(section.icon || 'user');

        if (!allCount) {
          questionRows.innerHTML = '<tr><td colspan="7"><div class="qat-empty"><strong>No questions yet</strong><p>Click “Add Question” to create the first field in this category.</p></div></td></tr>';
          tableFootText.textContent = '0 questions';
          return;
        }

        if (!filtered.length) {
          questionRows.innerHTML = '<tr><td colspan="7"><div class="qat-empty"><strong>No matches found</strong><p>Try a different search term for this category.</p></div></td></tr>';
          tableFootText.textContent = '0 of ' + allCount + ' questions match your search';
          return;
        }

        filtered.forEach(function (q) {
          var index = section.questions.indexOf(q);
          var tr = document.createElement('tr');
          if (q.enabled === false) tr.classList.add('is-disabled');

          var optionsHtml = '';
          if (q.type === 'select' && q.options && q.options.length) {
            optionsHtml = '<div class="qat-q-options">' + escapeHtml(shortOptions(q.options)) + '</div>';
          }

          var helpHtml = '';
          if (q.help_text) {
            helpHtml = '<div class="qat-q-help"></div>';
          }

          var condHtml = '';
          if (q.show_when && q.show_when.field) {
            condHtml = '<div class="qat-q-cond"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg><span></span></div>';
          }

          tr.innerHTML =
            '<td class="qat-col-num"><span class="qat-index">' + (index + 1) + '</span></td>' +
            '<td class="qat-col-question">' +
              '<div class="qat-q-label"></div>' +
              helpHtml +
              condHtml +
              optionsHtml +
            '</td>' +
            '<td class="qat-col-field"><code class="qat-field-id"></code></td>' +
            '<td class="qat-col-type"><span class="qat-type-badge" data-type=""></span></td>' +
            '<td class="qat-col-req"><span class="qat-pill"></span></td>' +
            '<td class="qat-col-status"><span class="qat-status"></span></td>' +
            '<td class="qat-col-action">' +
              '<div class="qat-row-actions">' +
                '<button type="button" class="qat-icon-btn edit" data-act="edit" title="Edit question"><svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg></button>' +
                '<button type="button" class="qat-icon-btn danger" data-act="delete" title="Delete question"><svg viewBox="0 0 24 24"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/></svg></button>' +
              '</div>' +
            '</td>';

          tr.querySelector('.qat-q-label').textContent = q.label || 'Untitled';
          if (q.help_text) {
            tr.querySelector('.qat-q-help').textContent = q.help_text;
          }
          var condEl = tr.querySelector('.qat-q-cond span');
          if (condEl) {
            condEl.textContent = formatConditionSummary(q.show_when);
          }
          tr.querySelector('.qat-field-id').textContent = q.key || q.id || '—';

          var badge = tr.querySelector('.qat-type-badge');
          badge.textContent = String(q.type || 'text');
          badge.setAttribute('data-type', String(q.type || 'text'));

          var reqPill = tr.querySelector('.qat-pill');
          if (q.required) {
            reqPill.className = 'qat-pill is-required';
            reqPill.textContent = 'Yes';
          } else {
            reqPill.className = 'qat-pill is-optional';
            reqPill.textContent = 'No';
          }

          var status = tr.querySelector('.qat-status');
          if (q.enabled === false) {
            status.className = 'qat-status is-off';
            status.textContent = 'Disabled';
          } else {
            status.className = 'qat-status is-on';
            status.textContent = 'Enabled';
          }

          tr.querySelector('[data-act="edit"]').addEventListener('click', function () { openModal(index); });
          tr.querySelector('[data-act="delete"]').addEventListener('click', function () { deleteQuestion(index); });

          questionRows.appendChild(tr);
        });

        tableFootText.textContent = searchQuery
          ? ('Showing ' + filtered.length + ' of ' + allCount + ' · ' + requiredCount + ' required')
          : ('Showing ' + filtered.length + ' questions · ' + requiredCount + ' required');
      }

      function escapeHtml(str) {
        return String(str)
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;');
      }

      function render() {
        renderCategories();
        renderTable();
      }

      function openModal(index) {
        editIndex = (typeof index === 'number') ? index : null;
        var section = activeSection();
        var q = (editIndex !== null && section) ? section.questions[editIndex] : null;
        var currentKey = q ? (q.key || q.id || '') : '';

        document.getElementById('modal-title').textContent = q ? 'Edit Question' : 'Add Question';
        document.getElementById('modal-kicker').textContent = section ? section.title : 'Question editor';
        document.getElementById('modal-sub').textContent = q
          ? 'Update field settings and conditional visibility for this question.'
          : 'Add a new field to the selected category.';

        document.getElementById('q-label').value = q ? (q.label || '') : '';
        document.getElementById('q-key').value = q ? (q.key || q.id || '') : '';
        document.getElementById('q-type').value = q ? (q.type || 'text') : 'text';
        document.getElementById('q-options').value = q && q.options ? q.options.join('\n') : '';
        document.getElementById('q-help').value = q ? (q.help_text || '') : '';
        document.getElementById('q-required').checked = !!(q && q.required);
        document.getElementById('q-enabled').checked = q ? (q.enabled !== false) : true;
        document.getElementById('q-key').readOnly = !!q;
        document.getElementById('q-key').dataset.touched = q ? '1' : '';

        populateCondFieldOptions(currentKey);
        fillShowWhenForm(q ? q.show_when : null);
        toggleOptions();
        modal.hidden = false;
        document.getElementById('q-label').focus();
      }

      function closeModal() {
        modal.hidden = true;
        editIndex = null;
      }

      function toggleOptions() {
        optionsField.hidden = document.getElementById('q-type').value !== 'select';
      }

      function applyQuestion() {
        var section = activeSection();
        if (!section) return;

        var label = document.getElementById('q-label').value.trim();
        var key = document.getElementById('q-key').value.trim() || slugify(label);
        key = slugify(key);
        var type = document.getElementById('q-type').value;
        var options = document.getElementById('q-options').value
          .split(/\r?\n/)
          .map(function (s) { return s.trim(); })
          .filter(Boolean);

        if (!label) {
          showToast('Question label is required.', 'err');
          return;
        }
        if (type === 'select' && !options.length) {
          showToast('Select questions need at least one option.', 'err');
          return;
        }

        var duplicate = section.questions.some(function (q, i) {
          return (q.key === key || q.id === key) && i !== editIndex;
        });
        if (duplicate) {
          showToast('Field ID already exists in this category.', 'err');
          return;
        }

        var showWhen = readShowWhenFromForm();
        if (document.getElementById('q-cond-enabled').checked && !showWhen) {
          showToast('Complete the conditional rule or disable it.', 'err');
          return;
        }

        var question = {
          id: key,
          key: key,
          label: label,
          type: type,
          required: document.getElementById('q-required').checked,
          enabled: document.getElementById('q-enabled').checked,
          options: type === 'select' ? options : [],
          help_text: document.getElementById('q-help').value.trim()
        };

        if (showWhen) question.show_when = showWhen;

        if (editIndex === null) section.questions.push(question);
        else section.questions[editIndex] = question;

        closeModal();
        render();
        persistState('Question applied.');
      }

      function deleteQuestion(index) {
        var section = activeSection();
        if (!section) return;
        var q = section.questions[index];
        if (!confirm('Delete “' + (q.label || 'this question') + '”?')) return;
        section.questions.splice(index, 1);
        render();
        persistState('Question removed.');
      }

      function persistState(successMsg) {
        fetch(window.location.href, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          credentials: 'same-origin',
          body: JSON.stringify({
            action: 'save',
            city_municipality_id: muniId,
            questions: state
          })
        })
          .then(function (res) { return res.json(); })
          .then(function (data) {
            if (data && data.ok) {
              showToast(successMsg || 'Questions saved.', 'ok');
              return;
            }
            showToast((data && data.error) || 'Unable to save questions.', 'err');
          })
          .catch(function () {
            showToast('Unable to save questions.', 'err');
          });
      }

      document.getElementById('btn-add-question').addEventListener('click', function () { openModal(null); });
      document.getElementById('modal-close').addEventListener('click', closeModal);
      document.getElementById('modal-cancel').addEventListener('click', closeModal);
      document.getElementById('modal-save').addEventListener('click', applyQuestion);
      document.getElementById('q-type').addEventListener('change', toggleOptions);

      document.getElementById('q-cond-enabled').addEventListener('change', toggleCondPanel);
      document.getElementById('q-cond-field').addEventListener('change', updateCondValueInput);
      document.getElementById('q-cond-operator').addEventListener('change', updateCondValueInput);
      condValueSelect.addEventListener('change', updateCondPreview);
      condValueText.addEventListener('input', updateCondPreview);

      searchInput.addEventListener('input', function () {
        searchQuery = this.value.trim().toLowerCase();
        renderTable();
      });

      document.getElementById('q-label').addEventListener('input', function () {
        if (editIndex !== null) return;
        var keyInput = document.getElementById('q-key');
        if (!keyInput.dataset.touched) keyInput.value = slugify(this.value);
      });
      document.getElementById('q-key').addEventListener('input', function () {
        this.dataset.touched = '1';
      });

      modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
      });
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.hidden) closeModal();
      });

      render();
    })();
  </script>
  <?php endif; ?>
</body>
</html>
