<?php
declare(strict_types=1);

require_once __DIR__ . '/../../login.php';

$user = require_role('super_admin', 'muni_admin', 'admin');
$role = (string) ($user['role'] ?? '');
$isSuperAdmin = $role === 'super_admin';
$isMuniAdmin = $role === 'muni_admin';
$barangayId = (int) ($user['barangay_id'] ?? 0);
$muniId = (int) ($user['city_municipality_id'] ?? 0);

$page_title = 'Statistics Reports';
$role_label = role_label($role);
$user_name = user_display_name($user);
$user_initials = user_initials($user);
$logo = app_url('assets/img/system-logo.jpg');
$css = app_url('assets/css/dashboard.css');
$rptCss = app_url('pages/reports/reports.css');
$logout = app_url('logout.php');
$activeNav = 'statistics_reports';
$pageHeading = 'Statistics Reports';
$showFullscreenBtn = true;
$showCrossGraphBtn = false;
$pageSubheading = '';

$filterMuniId = $isSuperAdmin ? max(0, (int) ($_GET['muni'] ?? 0)) : ($isMuniAdmin ? $muniId : 0);
$filterBrgyId = ($isSuperAdmin || $isMuniAdmin) ? max(0, (int) ($_GET['brgy'] ?? 0)) : 0;
$filterSex = (string) ($_GET['sex'] ?? '');
if (!in_array($filterSex, ['1', '2'], true)) {
    $filterSex = '';
}
$filterAge = trim((string) ($_GET['age'] ?? ''));
$municipalities = [];
$barangays = [];
$ageBandOptions = [
    '0-4', '5-9', '10-14', '15-19', '20-24', '25-29', '30-34', '35-39',
    '40-44', '45-49', '50-54', '55-59', '60-64', '65-69', '70-74', '75-79', '80+',
];
if (!in_array($filterAge, $ageBandOptions, true)) {
    $filterAge = '';
}

$totalMunicipalities = 0;
$totalBarangays = 0;
$totalHouseholds = 0;
$totalResidents = 0;
$muniKpiBarangays = 0;
$muniKpiHouseholds = 0;
$muniKpiResidents = 0;
$maleResidents = 0;
$femaleResidents = 0;
$seniorResidents = 0;
$pwdResidents = 0;
$voters = 0;
$soloParents = 0;

$ageBands = [
    '0-4', '5-9', '10-14', '15-19', '20-24', '25-29', '30-34', '35-39',
    '40-44', '45-49', '50-54', '55-59', '60-64', '65-69', '70-74', '75-79', '80+',
];
$pyramid = [];
foreach ($ageBands as $band) {
    $pyramid[$band] = ['male' => 0, 'female' => 0];
}
$pyramidMax = 1;

$education = [];
$marital = [];
$workStatus = [];
$waterSource = [];
$toilet = [];
$muniResidents = [];
$enrolled = [];
$schoolLevel = [];
$monthlyIncome = [];
$incomeSource = [];
$healthInsurance = [];
$facilityVisited = [];
$visitReason = [];
$disability = [];
$placeOfDelivery = [];
$birthAttendant = [];
$fpMethod = [];
$fpIntention = [];
$residentType = [];
$ctcPossession = [];
$housingTenure = [];
$lotTenure = [];
$garbageSegregation = [];
$buildingType = [];
$wallMaterial = [];
$hunger = [];
$cannotRead = [];
$evacuation = [];
$emergencyKit = [];
$crossRecords = [];
$crossDimensions = rpt_cross_dimensions($isSuperAdmin);

function rpt_label(?string $value, string $fallback = 'Not stated'): string
{
    $value = trim((string) $value);
    return $value === '' ? $fallback : $value;
}

function rpt_bump(array &$bag, string $key, int $n = 1): void
{
    if (!isset($bag[$key])) {
        $bag[$key] = 0;
    }
    $bag[$key] += $n;
}

function rpt_top(array $bag, int $limit = 8): array
{
    arsort($bag);
    if (count($bag) <= $limit) {
        return $bag;
    }
    $top = array_slice($bag, 0, $limit, true);
    $rest = array_slice($bag, $limit, null, true);
    $other = array_sum($rest);
    if ($other > 0) {
        $top['Others'] = ($top['Others'] ?? 0) + $other;
    }
    return $top;
}

function rpt_short(?string $value, string $fallback = 'Not stated'): string
{
    $value = rpt_label($value, $fallback);
    if (stripos($value, 'Non-Migrant') === 0) {
        return 'Non-Migrant';
    }
    if (stripos($value, 'Migrant') === 0) {
        return 'Migrant';
    }
    return $value;
}

function rpt_set(array $bag): array
{
    return ['labels' => array_keys($bag), 'values' => array_values($bag)];
}

function rpt_age_band(int $age): string
{
    if ($age < 0) {
        return '';
    }
    return $age >= 80 ? '80+' : (intdiv($age, 5) * 5) . '-' . (intdiv($age, 5) * 5 + 4);
}

function rpt_is_senior(int $age, ?string $seniorFlag): bool
{
    $flag = strtolower(trim((string) $seniorFlag));
    return $age >= 60 || in_array($flag, ['yes', '1', 'registered', 'oo'], true);
}

function rpt_panel(string $id, string $title): void
{
    echo '<article class="rpt-chart-panel">'
        . '<div class="panel-header"><div><h2>' . htmlspecialchars($title) . '</h2></div></div>'
        . '<div class="rpt-chart-wrap"><canvas id="' . htmlspecialchars($id) . '"></canvas></div>'
        . '</article>';
}

/** @return array<string, string> */
function rpt_cross_dimensions(bool $isSuperAdmin): array
{
    $dims = [
        'sex' => 'Sex',
        'age_group' => 'Age group',
        'education' => 'Education',
        'marital' => 'Marital status',
        'work' => 'Work status',
        'enrolled' => 'Currently enrolled',
        'school_level' => 'School level',
        'monthly_income' => 'Monthly income',
        'income_source' => 'Source of income',
        'health_insurance' => 'Health insurance',
        'facility_visited' => 'Health facility visited',
        'visit_reason' => 'Reason for visit',
        'disability' => 'Disability',
        'resident_type' => 'Type of resident',
        'housing_tenure' => 'Housing tenure',
        'water' => 'Drinking water source',
        'toilet' => 'Toilet facility',
    ];
    if ($isSuperAdmin) {
        $dims = ['municipality' => 'Municipality'] + $dims;
    }
    return $dims;
}

/** @return array<string, string> */
function rpt_cross_record(array $row, int $age, int $sex, bool $isSuperAdmin): array
{
    $record = [
        'sex' => $sex === 1 ? 'Male' : ($sex === 2 ? 'Female' : 'Not stated'),
        'age_group' => rpt_age_band($age) ?: 'Not stated',
        'education' => rpt_label($row['highest_education'] ?? null),
        'marital' => rpt_label($row['marital_status'] ?? null),
        'work' => rpt_label($row['q17_work_status'] ?? null),
        'enrolled' => rpt_label($row['currently_enrolled'] ?? null),
        'school_level' => rpt_label($row['school_level'] ?? null),
        'monthly_income' => rpt_label($row['q15_monthly_income'] ?? null),
        'income_source' => rpt_label($row['q15a_source_of_income'] ?? null),
        'health_insurance' => rpt_label($row['q26_health_insurance'] ?? null),
        'facility_visited' => rpt_label($row['q27_facility_visited'] ?? null),
        'visit_reason' => rpt_label($row['q28_reason_for_visit'] ?? null),
        'disability' => rpt_label($row['q29_disability'] ?? null),
        'resident_type' => rpt_short($row['q36_type_of_resident'] ?? null),
        'housing_tenure' => rpt_label($row['q45_housing_tenure'] ?? null),
        'water' => rpt_label($row['q49_drinking_water_source'] ?? null),
        'toilet' => rpt_label($row['q51_toilet_facility'] ?? null),
    ];
    if ($isSuperAdmin) {
        $record['municipality'] = rpt_label($row['municipality'] ?? null, 'Unassigned');
    }
    return $record;
}

try {
    $pdo = db();
    $scopeJoin = '';
    $scopeWhere = '';
    $scopeParams = [];
    $whereParts = [];

    if ($isSuperAdmin) {
        $municipalities = $pdo->query(
            'SELECT city_municipality_id, name FROM city_municipality ORDER BY name'
        )->fetchAll(PDO::FETCH_ASSOC);

        $brgySql = 'SELECT barangay_id, barangay, city_municipality_id FROM barangay';
        $brgyParams = [];
        if ($filterMuniId > 0) {
            $brgySql .= ' WHERE city_municipality_id = :mid';
            $brgyParams[':mid'] = $filterMuniId;
        }
        $brgySql .= ' ORDER BY barangay';
        $brgyStmt = $pdo->prepare($brgySql);
        $brgyStmt->execute($brgyParams);
        $barangays = $brgyStmt->fetchAll(PDO::FETCH_ASSOC);

        if ($filterBrgyId > 0) {
            $validBrgy = false;
            foreach ($barangays as $b) {
                if ((int) $b['barangay_id'] === $filterBrgyId) {
                    $validBrgy = true;
                    if ($filterMuniId === 0) {
                        $filterMuniId = (int) $b['city_municipality_id'];
                    }
                    break;
                }
            }
            if (!$validBrgy) {
                $filterBrgyId = 0;
            }
        }
    }

    if ($isMuniAdmin && $muniId > 0) {
        $brgyStmt = $pdo->prepare(
            'SELECT barangay_id, barangay, city_municipality_id
             FROM barangay
             WHERE city_municipality_id = :mid
             ORDER BY barangay'
        );
        $brgyStmt->execute([':mid' => $muniId]);
        $barangays = $brgyStmt->fetchAll(PDO::FETCH_ASSOC);
        $filterMuniId = $muniId;

        if ($filterBrgyId > 0) {
            $validBrgy = false;
            foreach ($barangays as $b) {
                if ((int) $b['barangay_id'] === $filterBrgyId) {
                    $validBrgy = true;
                    break;
                }
            }
            if (!$validBrgy) {
                $filterBrgyId = 0;
            }
        }

        $bc = $pdo->prepare('SELECT COUNT(*) FROM barangay WHERE city_municipality_id = :id');
        $bc->execute([':id' => $muniId]);
        $muniKpiBarangays = (int) $bc->fetchColumn();

        $hhMuni = $pdo->prepare(
            'SELECT COUNT(*) FROM households h
             INNER JOIN barangay b ON b.barangay_id = h.barangay_id
             WHERE b.city_municipality_id = :id'
        );
        $hhMuni->execute([':id' => $muniId]);
        $muniKpiHouseholds = (int) $hhMuni->fetchColumn();

        $resMuni = $pdo->prepare(
            'SELECT COUNT(*) FROM register_members rm
             INNER JOIN survey_session ss ON ss.session_id = rm.session_id
             INNER JOIN barangay b ON b.barangay_id = ss.barangay_id
             WHERE b.city_municipality_id = :id'
        );
        $resMuni->execute([':id' => $muniId]);
        $muniKpiResidents = (int) $resMuni->fetchColumn();
    }

    if (!$isSuperAdmin && !$isMuniAdmin && $barangayId > 0) {
        $scopeJoin = ' INNER JOIN survey_session ss ON ss.session_id = rm.session_id ';
        $whereParts[] = 'ss.barangay_id = :bid';
        $scopeParams[':bid'] = $barangayId;

        $totalMunicipalities = 1;
        $totalBarangays = 1;
        $hh = $pdo->prepare('SELECT COUNT(*) FROM households WHERE barangay_id = :id');
        $hh->execute([':id' => $barangayId]);
        $totalHouseholds = (int) $hh->fetchColumn();
    } else {
        $scopeJoin = ' INNER JOIN survey_session ss ON ss.session_id = rm.session_id
                       INNER JOIN barangay b ON b.barangay_id = ss.barangay_id
                       INNER JOIN city_municipality cm ON cm.city_municipality_id = b.city_municipality_id ';

        if ($filterBrgyId > 0) {
            $whereParts[] = 'ss.barangay_id = :fbrgy';
            $scopeParams[':fbrgy'] = $filterBrgyId;
        } elseif ($filterMuniId > 0) {
            $whereParts[] = 'cm.city_municipality_id = :fmuni';
            $scopeParams[':fmuni'] = $filterMuniId;
        }

        if ($filterMuniId > 0) {
            $totalMunicipalities = 1;
            $bc = $pdo->prepare('SELECT COUNT(*) FROM barangay WHERE city_municipality_id = :id');
            $bc->execute([':id' => $filterMuniId]);
            $totalBarangays = (int) $bc->fetchColumn();
            if ($filterBrgyId > 0) {
                $totalBarangays = 1;
                $hh = $pdo->prepare('SELECT COUNT(*) FROM households WHERE barangay_id = :id');
                $hh->execute([':id' => $filterBrgyId]);
                $totalHouseholds = (int) $hh->fetchColumn();
            } else {
                $hh = $pdo->prepare(
                    'SELECT COUNT(*) FROM households h
                     INNER JOIN barangay b ON b.barangay_id = h.barangay_id
                     WHERE b.city_municipality_id = :id'
                );
                $hh->execute([':id' => $filterMuniId]);
                $totalHouseholds = (int) $hh->fetchColumn();
            }
        } else {
            $totalMunicipalities = (int) $pdo->query('SELECT COUNT(*) FROM city_municipality')->fetchColumn();
            $totalBarangays = (int) $pdo->query('SELECT COUNT(*) FROM barangay')->fetchColumn();
            $totalHouseholds = (int) $pdo->query('SELECT COUNT(*) FROM households')->fetchColumn();
        }
    }

    if ($filterSex !== '') {
        $whereParts[] = 'rm.sex = :fsex';
        $scopeParams[':fsex'] = (int) $filterSex;
    }

    $scopeWhere = $whereParts ? (' WHERE ' . implode(' AND ', $whereParts)) : '';

    $sql = "SELECT
                rm.sex,
                rm.age,
                rm.marital_status,
                rm.highest_education,
                rm.currently_enrolled,
                rm.school_level,
                rm.q15_monthly_income,
                rm.q15a_source_of_income,
                rm.q17_work_status,
                rm.q19_place_of_delivery,
                rm.q20_birth_attendant,
                rm.q23_fp_method,
                rm.q25_intention_to_use_fp,
                rm.q26_health_insurance,
                rm.q27_facility_visited,
                rm.q28_reason_for_visit,
                rm.q29_disability,
                rm.q30_solo_parent_status,
                rm.q31_registered_senior,
                rm.q32_registered_voter,
                rm.q36_type_of_resident,
                rm.q42a_ctc_possession,
                rm.q45_housing_tenure,
                rm.q46_lot_tenure,
                rm.q49_drinking_water_source,
                rm.q50b_garbage_segregation,
                rm.q51_toilet_facility,
                rm.q52_building_type,
                rm.q53_outer_wall_material,
                rm.q59_hunger_experience,
                rm.q60_cannot_read,
                rm.q61_evacuation_center,
                rm.q62_emergency_kit"
        . ($isSuperAdmin ? ", cm.name AS municipality" : "")
        . " FROM register_members rm {$scopeJoin} {$scopeWhere}";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($scopeParams);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        $sex = (int) ($row['sex'] ?? 0);
        $age = isset($row['age']) ? (int) $row['age'] : -1;
        $band = rpt_age_band($age);
        $isSenior = rpt_is_senior($age, $row['q31_registered_senior'] ?? null);

        if ($filterAge !== '' && $band !== $filterAge) {
            continue;
        }

        $totalResidents++;

        if ($sex === 1) {
            $maleResidents++;
        } elseif ($sex === 2) {
            $femaleResidents++;
        }

        if ($isSenior) {
            $seniorResidents++;
        }

        $dis = strtolower(trim((string) ($row['q29_disability'] ?? '')));
        if ($dis !== '' && !in_array($dis, ['none', 'no', '0'], true)) {
            $pwdResidents++;
        }

        $voter = strtolower(trim((string) ($row['q32_registered_voter'] ?? '')));
        if (in_array($voter, ['yes', '1', 'registered', 'oo'], true)) {
            $voters++;
        }

        $solo = strtolower(trim((string) ($row['q30_solo_parent_status'] ?? '')));
        if (in_array($solo, ['yes', '1', 'oo'], true)) {
            $soloParents++;
        }

        if ($age >= 0 && ($sex === 1 || $sex === 2) && isset($pyramid[$band])) {
            $pyramid[$band][$sex === 1 ? 'male' : 'female']++;
        }

        rpt_bump($education, rpt_label($row['highest_education'] ?? null));
        rpt_bump($marital, rpt_label($row['marital_status'] ?? null));
        rpt_bump($workStatus, rpt_label($row['q17_work_status'] ?? null));
        rpt_bump($waterSource, rpt_label($row['q49_drinking_water_source'] ?? null));
        rpt_bump($toilet, rpt_label($row['q51_toilet_facility'] ?? null));
        rpt_bump($enrolled, rpt_label($row['currently_enrolled'] ?? null));
        rpt_bump($schoolLevel, rpt_label($row['school_level'] ?? null));
        rpt_bump($monthlyIncome, rpt_label($row['q15_monthly_income'] ?? null));
        rpt_bump($incomeSource, rpt_label($row['q15a_source_of_income'] ?? null));
        rpt_bump($healthInsurance, rpt_label($row['q26_health_insurance'] ?? null));
        rpt_bump($facilityVisited, rpt_label($row['q27_facility_visited'] ?? null));
        rpt_bump($visitReason, rpt_label($row['q28_reason_for_visit'] ?? null));
        rpt_bump($disability, rpt_label($row['q29_disability'] ?? null));
        rpt_bump($placeOfDelivery, rpt_label($row['q19_place_of_delivery'] ?? null));
        rpt_bump($birthAttendant, rpt_label($row['q20_birth_attendant'] ?? null));
        rpt_bump($fpMethod, rpt_label($row['q23_fp_method'] ?? null));
        rpt_bump($fpIntention, rpt_label($row['q25_intention_to_use_fp'] ?? null));
        rpt_bump($residentType, rpt_short($row['q36_type_of_resident'] ?? null));
        rpt_bump($ctcPossession, rpt_label($row['q42a_ctc_possession'] ?? null));
        rpt_bump($housingTenure, rpt_label($row['q45_housing_tenure'] ?? null));
        rpt_bump($lotTenure, rpt_label($row['q46_lot_tenure'] ?? null));
        rpt_bump($garbageSegregation, rpt_label($row['q50b_garbage_segregation'] ?? null));
        rpt_bump($buildingType, rpt_label($row['q52_building_type'] ?? null));
        rpt_bump($wallMaterial, rpt_label($row['q53_outer_wall_material'] ?? null));
        rpt_bump($hunger, rpt_label($row['q59_hunger_experience'] ?? null));
        rpt_bump($cannotRead, rpt_label($row['q60_cannot_read'] ?? null));
        rpt_bump($evacuation, rpt_label($row['q61_evacuation_center'] ?? null));
        rpt_bump($emergencyKit, rpt_label($row['q62_emergency_kit'] ?? null));

        if ($isSuperAdmin) {
            rpt_bump($muniResidents, rpt_label($row['municipality'] ?? null, 'Unassigned'));
        }

        $crossRecords[] = rpt_cross_record($row, $age, $sex, $isSuperAdmin);
    }

    foreach ($pyramid as $counts) {
        $pyramidMax = max($pyramidMax, (int) $counts['male'], (int) $counts['female']);
    }

    $education = rpt_top($education);
    $marital = rpt_top($marital, 7);
    $workStatus = rpt_top($workStatus, 7);
    $waterSource = rpt_top($waterSource, 7);
    $toilet = rpt_top($toilet, 7);
    $enrolled = rpt_top($enrolled, 5);
    $schoolLevel = rpt_top($schoolLevel, 7);
    $monthlyIncome = rpt_top($monthlyIncome, 8);
    $incomeSource = rpt_top($incomeSource, 6);
    $healthInsurance = rpt_top($healthInsurance, 6);
    $facilityVisited = rpt_top($facilityVisited, 7);
    $visitReason = rpt_top($visitReason, 7);
    $disability = rpt_top($disability, 6);
    $placeOfDelivery = rpt_top($placeOfDelivery, 6);
    $birthAttendant = rpt_top($birthAttendant, 6);
    $fpMethod = rpt_top($fpMethod, 8);
    $fpIntention = rpt_top($fpIntention, 5);
    $residentType = rpt_top($residentType, 4);
    $ctcPossession = rpt_top($ctcPossession, 4);
    $housingTenure = rpt_top($housingTenure, 5);
    $lotTenure = rpt_top($lotTenure, 5);
    $garbageSegregation = rpt_top($garbageSegregation, 4);
    $buildingType = rpt_top($buildingType, 6);
    $wallMaterial = rpt_top($wallMaterial, 7);
    $hunger = rpt_top($hunger, 4);
    $cannotRead = rpt_top($cannotRead, 4);
    $evacuation = rpt_top($evacuation, 4);
    $emergencyKit = rpt_top($emergencyKit, 4);
    if ($isSuperAdmin) {
        ksort($muniResidents);
    }
} catch (Throwable $e) {
    $rows = [];
}

$filtersActive = $filterMuniId > 0 || $filterBrgyId > 0 || $filterSex !== '' || $filterAge !== '';

$pyramidTotal = $maleResidents + $femaleResidents;
$maleShare = $pyramidTotal > 0 ? round(($maleResidents / $pyramidTotal) * 100, 1) : 0;
$femaleShare = $pyramidTotal > 0 ? round(($femaleResidents / $pyramidTotal) * 100, 1) : 0;
$pyramidRows = array_reverse($pyramid, true);

$chartPayload = [
    'education' => rpt_set($education),
    'marital' => rpt_set($marital),
    'work' => rpt_set($workStatus),
    'water' => rpt_set($waterSource),
    'toilet' => rpt_set($toilet),
    'sex' => [
        'labels' => ['Male', 'Female'],
        'values' => [$maleResidents, $femaleResidents],
    ],
    'muni' => rpt_set($muniResidents),
    'sectors' => [
        'labels' => ['Solo parents', 'Seniors', 'Registered voters', 'PWD'],
        'values' => [$soloParents, $seniorResidents, $voters, $pwdResidents],
    ],
    'enrolled' => rpt_set($enrolled),
    'schoolLevel' => rpt_set($schoolLevel),
    'monthlyIncome' => rpt_set($monthlyIncome),
    'incomeSource' => rpt_set($incomeSource),
    'healthInsurance' => rpt_set($healthInsurance),
    'facilityVisited' => rpt_set($facilityVisited),
    'visitReason' => rpt_set($visitReason),
    'disability' => rpt_set($disability),
    'placeOfDelivery' => rpt_set($placeOfDelivery),
    'birthAttendant' => rpt_set($birthAttendant),
    'fpMethod' => rpt_set($fpMethod),
    'fpIntention' => rpt_set($fpIntention),
    'residentType' => rpt_set($residentType),
    'ctcPossession' => rpt_set($ctcPossession),
    'housingTenure' => rpt_set($housingTenure),
    'lotTenure' => rpt_set($lotTenure),
    'garbageSegregation' => rpt_set($garbageSegregation),
    'buildingType' => rpt_set($buildingType),
    'wallMaterial' => rpt_set($wallMaterial),
    'hunger' => rpt_set($hunger),
    'cannotRead' => rpt_set($cannotRead),
    'evacuation' => rpt_set($evacuation),
    'emergencyKit' => rpt_set($emergencyKit),
];
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
  <link rel="stylesheet" href="<?php echo htmlspecialchars($rptCss); ?>">
</head>
<body>
  <div class="overlay" id="sidebar-overlay" hidden></div>

  <div class="app">
    <?php require __DIR__ . '/../../includes/sidebar.php'; ?>

    <div class="main" id="rpt-fullscreen-root">
      <?php require __DIR__ . '/../../includes/topbar.php'; ?>

      <main class="content">
        <section class="rpt-dash">
          <div class="rpt-page-header">
            <?php require __DIR__ . '/partials/statistics_filters.php'; ?>
          </div>

          <div class="rpt-kpis">
            <section class="stats-grid kpi-compact kpi-compact-<?php echo $isSuperAdmin ? '3' : '2'; ?>" aria-label="Coverage totals">
              <?php if ($isSuperAdmin): ?>
                <article class="stat-card kpi-blue">
                  <div class="stat-top">
                    <div>
                      <p class="stat-label">Municipalities</p>
                      <p class="stat-value"><?php echo number_format($totalMunicipalities); ?></p>
                    </div>
                    <div class="stat-icon blue" aria-hidden="true">
                      <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    </div>
                  </div>
                </article>
                <article class="stat-card kpi-red">
                  <div class="stat-top">
                    <div>
                      <p class="stat-label">Barangays</p>
                      <p class="stat-value"><?php echo number_format($totalBarangays); ?></p>
                    </div>
                    <div class="stat-icon red" aria-hidden="true">
                      <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
                    </div>
                  </div>
                </article>
              <?php elseif ($isMuniAdmin): ?>
                <article class="stat-card kpi-red">
                  <div class="stat-top">
                    <div>
                      <p class="stat-label">Barangays</p>
                      <p class="stat-value"><?php echo number_format($muniKpiBarangays); ?></p>
                    </div>
                    <div class="stat-icon red" aria-hidden="true">
                      <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
                    </div>
                  </div>
                </article>
                <article class="stat-card kpi-gold">
                  <div class="stat-top">
                    <div>
                      <p class="stat-label">Households</p>
                      <p class="stat-value"><?php echo number_format($muniKpiHouseholds); ?></p>
                    </div>
                    <div class="stat-icon gold" aria-hidden="true">
                      <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
                    </div>
                  </div>
                </article>
              <?php else: ?>
                <article class="stat-card kpi-blue">
                  <div class="stat-top">
                    <div>
                      <p class="stat-label">Barangay</p>
                      <p class="stat-value" style="font-size:1.15rem;"><?php echo htmlspecialchars((string) ($user['barangay'] ?? '—')); ?></p>
                    </div>
                    <div class="stat-icon blue" aria-hidden="true">
                      <svg viewBox="0 0 24 24"><path d="M3 21h18"/><path d="M5 21V8l7-5 7 5v13"/><path d="M9 21v-6h6v6"/></svg>
                    </div>
                  </div>
                </article>
              <?php endif; ?>
              <?php if (!$isMuniAdmin): ?>
              <article class="stat-card kpi-gold">
                <div class="stat-top">
                  <div>
                    <p class="stat-label">Households</p>
                    <p class="stat-value"><?php echo number_format($totalHouseholds); ?></p>
                  </div>
                  <div class="stat-icon gold" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
                  </div>
                </div>
              </article>
              <?php endif; ?>
            </section>

            <section class="stats-grid kpi-compact kpi-compact-4" aria-label="Population totals">
              <article class="stat-card kpi-navy">
                <div class="stat-top">
                  <div>
                    <p class="stat-label">Residents</p>
                    <p class="stat-value"><?php echo number_format($totalResidents); ?></p>
                  </div>
                  <div class="stat-icon navy" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                  </div>
                </div>
              </article>
              <article class="stat-card kpi-blue">
                <div class="stat-top">
                  <div>
                    <p class="stat-label">Male</p>
                    <p class="stat-value"><?php echo number_format($maleResidents); ?></p>
                  </div>
                  <div class="stat-icon blue" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M16 3h5v5"/><path d="M11 13l10-10"/><circle cx="9" cy="15" r="6"/></svg>
                  </div>
                </div>
              </article>
              <article class="stat-card kpi-red">
                <div class="stat-top">
                  <div>
                    <p class="stat-label">Female</p>
                    <p class="stat-value"><?php echo number_format($femaleResidents); ?></p>
                  </div>
                  <div class="stat-icon red" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="5"/><path d="M12 13v8"/><path d="M9 18h6"/></svg>
                  </div>
                </div>
              </article>
              <article class="stat-card kpi-gold">
                <div class="stat-top">
                  <div>
                    <p class="stat-label">Senior</p>
                    <p class="stat-value"><?php echo number_format($seniorResidents); ?></p>
                  </div>
                  <div class="stat-icon gold" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                  </div>
                </div>
              </article>
            </section>
          </div>

          <div class="rpt-cross-backdrop" id="cross-graph-modal" hidden>
            <div class="rpt-cross-modal" role="dialog" aria-modal="true" aria-labelledby="cross-graph-title">
              <div class="rpt-cross-modal-head">
                <div class="rpt-cross-modal-head-main">
                  <div class="rpt-cross-modal-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-6"/></svg>
                  </div>
                  <div>
                    <p class="rpt-cross-modal-kicker">Statistics Reports</p>
                    <h3 id="cross-graph-title">Cross graph</h3>
                    <p class="rpt-cross-modal-sub">Merge two statistics into one combined chart.</p>
                  </div>
                </div>
                <button type="button" class="rpt-cross-modal-close" id="cross-graph-close" aria-label="Close">
                  <svg viewBox="0 0 24 24"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>
                </button>
              </div>
              <div class="rpt-cross-modal-body">
                <section class="rpt-cross-config">
                  <div class="rpt-cross-config-head">
                    <h4>Chart configuration</h4>
                  </div>
                  <div class="rpt-cross-controls">
                    <label class="rpt-filter-field">
                      <span>Group by</span>
                      <select id="cross-dim-x">
                        <?php foreach ($crossDimensions as $key => $label): ?>
                          <option value="<?php echo htmlspecialchars($key); ?>" <?php echo $key === 'education' ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </label>
                    <div class="rpt-cross-merge" aria-hidden="true">×</div>
                    <label class="rpt-filter-field">
                      <span>Break down by</span>
                      <select id="cross-dim-y">
                        <?php foreach ($crossDimensions as $key => $label): ?>
                          <option value="<?php echo htmlspecialchars($key); ?>" <?php echo $key === 'sex' ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </label>
                    <div class="rpt-filter-field rpt-cross-generate-field">
                      <span>Generate</span>
                      <button type="button" class="btn btn-primary rpt-cross-generate-btn" id="cross-graph-generate">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18"/><path d="M7 14l4-4 3 3 5-6"/></svg>
                        Generate graph
                      </button>
                    </div>
                  </div>
                  <p class="rpt-cross-caption" id="cross-graph-caption"></p>
                </section>
                <section class="rpt-cross-result">
                  <div class="rpt-cross-result-head">
                    <h4>Preview</h4>
                    <p class="rpt-cross-result-hint" id="cross-result-hint" hidden></p>
                  </div>
                  <div class="rpt-cross-chart-wrap" id="cross-chart-wrap">
                    <div class="rpt-cross-empty" id="cross-chart-placeholder" aria-hidden="true">
                      <svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 14l4-4 3 3 5-6"/></svg>
                    </div>
                    <div class="rpt-cross-chart-host" id="cross-chart-host" hidden>
                      <div class="rpt-cross-chart-canvas">
                        <canvas id="chart-cross"></canvas>
                      </div>
                    </div>
                  </div>
                </section>
              </div>
            </div>
          </div>

          <section class="panel pyramid-panel">
            <div class="panel-header">
              <div>
                <h2>Population pyramid</h2>
              </div>
              <div class="pyramid-summary">
                <span class="pyramid-summary-item male">
                  <strong><?php echo number_format($maleResidents); ?></strong> Male (<?php echo $maleShare; ?>%)
                </span>
                <span class="pyramid-summary-item female">
                  <strong><?php echo number_format($femaleResidents); ?></strong> Female (<?php echo $femaleShare; ?>%)
                </span>
              </div>
            </div>
            <div class="rpt-pyramid-wrap">
              <?php if ($pyramidTotal === 0): ?>
                <div class="rpt-empty-chart">No resident records yet.</div>
              <?php else: ?>
                <div class="pyramid-body">
                  <div class="pyramid-legend" aria-hidden="true">
                    <span class="pyramid-legend-item male"><i></i> Male</span>
                    <span class="pyramid-legend-item female"><i></i> Female</span>
                  </div>
                  <div class="pyramid-head">
                    <div class="pyramid-side-label">Male</div>
                    <div class="pyramid-age-label">Age</div>
                    <div class="pyramid-side-label">Female</div>
                  </div>
                  <div class="pyramid-chart" role="img" aria-label="Population pyramid">
                    <?php foreach ($pyramidRows as $band => $counts): ?>
                      <?php
                        $maleCount = (int) $counts['male'];
                        $femaleCount = (int) $counts['female'];
                        $malePct = $pyramidMax > 0 ? round(($maleCount / $pyramidMax) * 100, 2) : 0;
                        $femalePct = $pyramidMax > 0 ? round(($femaleCount / $pyramidMax) * 100, 2) : 0;
                      ?>
                      <div class="pyramid-row">
                        <div class="pyramid-bar-cell male">
                          <span class="pyramid-count"><?php echo number_format($maleCount); ?></span>
                          <div class="pyramid-track">
                            <div class="pyramid-bar" style="width: <?php echo $malePct; ?>%;"></div>
                          </div>
                        </div>
                        <div class="pyramid-age"><?php echo htmlspecialchars($band); ?></div>
                        <div class="pyramid-bar-cell female">
                          <div class="pyramid-track">
                            <div class="pyramid-bar" style="width: <?php echo $femalePct; ?>%;"></div>
                          </div>
                          <span class="pyramid-count"><?php echo number_format($femaleCount); ?></span>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </section>

          <section class="rpt-charts">
            <article class="rpt-chart-panel">
              <div class="panel-header">
                <div>
                  <h2><?php echo $isSuperAdmin ? 'Residents by municipality' : 'Highest education completed'; ?></h2>
                </div>
              </div>
              <div class="rpt-chart-wrap tall">
                <canvas id="chart-primary"></canvas>
              </div>
            </article>
            <article class="rpt-chart-panel">
              <div class="panel-header">
                <div>
                  <h2>Sex distribution</h2>
                </div>
              </div>
              <div class="rpt-chart-wrap">
                <canvas id="chart-sex"></canvas>
              </div>
            </article>
          </section>

          <section class="rpt-charts equal">
            <article class="rpt-chart-panel">
              <div class="panel-header">
                <div>
                  <h2><?php echo $isSuperAdmin ? 'Highest education completed' : 'Marital status'; ?></h2>
                </div>
              </div>
              <div class="rpt-chart-wrap">
                <canvas id="chart-education"></canvas>
              </div>
            </article>
            <article class="rpt-chart-panel">
              <div class="panel-header">
                <div>
                  <h2>Work status</h2>
                </div>
              </div>
              <div class="rpt-chart-wrap">
                <canvas id="chart-work"></canvas>
              </div>
            </article>
          </section>

          <section class="rpt-charts equal">
            <article class="rpt-chart-panel">
              <div class="panel-header">
                <div>
                  <h2>Drinking water source</h2>
                </div>
              </div>
              <div class="rpt-chart-wrap">
                <canvas id="chart-water"></canvas>
              </div>
            </article>
            <article class="rpt-chart-panel">
              <div class="panel-header">
                <div>
                  <h2>Toilet facility</h2>
                </div>
              </div>
              <div class="rpt-chart-wrap">
                <canvas id="chart-toilet"></canvas>
              </div>
            </article>
          </section>

          <section class="rpt-charts equal">
            <article class="rpt-chart-panel">
              <div class="panel-header">
                <div>
                  <h2>Special sectors</h2>
                </div>
              </div>
              <div class="rpt-chart-wrap">
                <canvas id="chart-sectors"></canvas>
              </div>
            </article>
            <article class="rpt-chart-panel">
              <div class="panel-header">
                <div>
                  <h2>Marital status</h2>
                </div>
              </div>
              <div class="rpt-chart-wrap">
                <canvas id="chart-marital"></canvas>
              </div>
            </article>
          </section>

          <p class="rpt-section-label">Demographic characteristics</p>
          <section class="rpt-charts equal">
            <?php rpt_panel('chart-enrolled', 'Currently enrolled'); ?>
            <?php rpt_panel('chart-school', 'School level'); ?>
            <?php rpt_panel('chart-resident-type', 'Type of resident'); ?>
          </section>

          <p class="rpt-section-label">Economic activity</p>
          <section class="rpt-charts equal">
            <?php rpt_panel('chart-income', 'Monthly income'); ?>
            <?php rpt_panel('chart-income-source', 'Source of income'); ?>
          </section>

          <p class="rpt-section-label">Health information</p>
          <section class="rpt-charts equal">
            <?php rpt_panel('chart-insurance', 'Health insurance'); ?>
            <?php rpt_panel('chart-facility', 'Health facility visited'); ?>
          </section>
          <section class="rpt-charts equal">
            <?php rpt_panel('chart-visit-reason', 'Reason for visit'); ?>
            <?php rpt_panel('chart-disability', 'Disability'); ?>
          </section>

          <p class="rpt-section-label">Maternal &amp; child health</p>
          <section class="rpt-charts equal">
            <?php rpt_panel('chart-delivery', 'Place of delivery'); ?>
            <?php rpt_panel('chart-attendant', 'Birth attendant'); ?>
          </section>
          <section class="rpt-charts equal">
            <?php rpt_panel('chart-fp', 'Family planning method'); ?>
            <?php rpt_panel('chart-fp-intention', 'Intention to use FP'); ?>
          </section>

          <p class="rpt-section-label">Socio-civic participation</p>
          <section class="rpt-charts equal">
            <?php rpt_panel('chart-ctc', 'CTC possession'); ?>
            <?php rpt_panel('chart-housing', 'Housing tenure'); ?>
          </section>

          <p class="rpt-section-label">Housing &amp; environment</p>
          <section class="rpt-charts equal">
            <?php rpt_panel('chart-lot', 'Lot tenure'); ?>
            <?php rpt_panel('chart-building', 'Type of building'); ?>
          </section>
          <section class="rpt-charts equal">
            <?php rpt_panel('chart-wall', 'Outer wall material'); ?>
            <?php rpt_panel('chart-garbage', 'Garbage segregation'); ?>
          </section>

          <p class="rpt-section-label">Community needs</p>
          <section class="rpt-charts equal">
            <?php rpt_panel('chart-hunger', 'Hunger experience'); ?>
            <?php rpt_panel('chart-literacy', 'Cannot read (age 8+)'); ?>
          </section>
          <section class="rpt-charts equal">
            <?php rpt_panel('chart-evac', 'Knows evacuation center'); ?>
            <?php rpt_panel('chart-kit', 'Has emergency kit'); ?>
          </section>
        </section>
      </main>
    </div>
  </div>

  <?php require __DIR__ . '/../../includes/scripts.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script>
    (function () {
      var data = <?php echo json_encode($chartPayload, JSON_UNESCAPED_UNICODE); ?>;
      var isSuper = <?php echo $isSuperAdmin ? 'true' : 'false'; ?>;
      var font = "'Plus Jakarta Sans', sans-serif";
      var colors = ['#0038a8', '#ce1126', '#fcd116', '#0f766e', '#6d28d9', '#b45309', '#0369a1', '#64748b'];

      if (typeof Chart === 'undefined') return;
      Chart.defaults.font.family = font;
      Chart.defaults.color = '#64748b';
      Chart.defaults.plugins.legend.labels.usePointStyle = true;
      Chart.defaults.plugins.legend.labels.boxWidth = 8;

      function hasValues(set) {
        return set && set.values && set.values.some(function (n) { return Number(n) > 0; });
      }

      function emptyChart(el) {
        if (!el || !el.parentNode) return;
        el.parentNode.innerHTML = '<div class="rpt-empty-chart">No data yet.</div>';
      }

      function bar(id, set, horizontal) {
        var el = document.getElementById(id);
        if (!el) return;
        if (!hasValues(set)) { emptyChart(el); return; }
        new Chart(el, {
          type: 'bar',
          data: {
            labels: set.labels,
            datasets: [{
              data: set.values,
              backgroundColor: colors[0],
              borderRadius: 6,
              maxBarThickness: 28
            }]
          },
          options: {
            indexAxis: horizontal ? 'y' : 'x',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
              x: { grid: { display: !horizontal, color: '#eef2f7' }, ticks: { maxRotation: 40 } },
              y: { grid: { display: horizontal, color: '#eef2f7' }, beginAtZero: true, ticks: { precision: 0 } }
            }
          }
        });
      }

      function doughnut(id, set, palette) {
        var el = document.getElementById(id);
        if (!el) return;
        if (!hasValues(set)) { emptyChart(el); return; }
        new Chart(el, {
          type: 'doughnut',
          data: {
            labels: set.labels,
            datasets: [{
              data: set.values,
              backgroundColor: palette || [colors[0], colors[1]],
              borderWidth: 0
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: { legend: { position: 'bottom' } }
          }
        });
      }

      if (isSuper) bar('chart-primary', data.muni, true);
      else bar('chart-primary', data.education, true);
      doughnut('chart-sex', data.sex);
      bar('chart-education', isSuper ? data.education : data.marital, true);
      bar('chart-work', data.work, true);
      bar('chart-water', data.water, true);
      bar('chart-toilet', data.toilet, true);
      bar('chart-sectors', data.sectors, true);
      doughnut('chart-marital', data.marital, colors);

      doughnut('chart-enrolled', data.enrolled, colors);
      bar('chart-school', data.schoolLevel, true);
      bar('chart-income', data.monthlyIncome, true);
      doughnut('chart-income-source', data.incomeSource, colors);
      bar('chart-insurance', data.healthInsurance, true);
      bar('chart-facility', data.facilityVisited, true);
      doughnut('chart-visit-reason', data.visitReason, colors);
      doughnut('chart-disability', data.disability, colors);
      doughnut('chart-delivery', data.placeOfDelivery, colors);
      doughnut('chart-attendant', data.birthAttendant, colors);
      bar('chart-fp', data.fpMethod, true);
      doughnut('chart-fp-intention', data.fpIntention, colors);
      doughnut('chart-resident-type', data.residentType, colors);
      doughnut('chart-ctc', data.ctcPossession, colors);
      doughnut('chart-housing', data.housingTenure, colors);
      doughnut('chart-lot', data.lotTenure, colors);
      bar('chart-building', data.buildingType, true);
      bar('chart-wall', data.wallMaterial, true);
      doughnut('chart-garbage', data.garbageSegregation, colors);
      doughnut('chart-hunger', data.hunger, colors);
      doughnut('chart-literacy', data.cannotRead, colors);
      doughnut('chart-evac', data.evacuation, colors);
      doughnut('chart-kit', data.emergencyKit, colors);

      var crossRecords = <?php echo json_encode($crossRecords, JSON_UNESCAPED_UNICODE); ?>;
      var crossLabels = <?php echo json_encode($crossDimensions, JSON_UNESCAPED_UNICODE); ?>;
      var crossChart = null;
      var crossCaption = document.getElementById('cross-graph-caption');
      var crossResultHint = document.getElementById('cross-result-hint');
      var crossDimX = document.getElementById('cross-dim-x');
      var crossDimY = document.getElementById('cross-dim-y');
      var crossModal = document.getElementById('cross-graph-modal');
      var crossOpenBtn = document.getElementById('cross-graph-open');
      var crossCloseBtn = document.getElementById('cross-graph-close');
      var crossGenerateBtn = document.getElementById('cross-graph-generate');
      var crossPlaceholder = document.getElementById('cross-chart-placeholder');
      var crossChartHost = document.getElementById('cross-chart-host');

      function topKeys(counts, limit) {
        var entries = Object.keys(counts).map(function (k) {
          return { key: k, total: counts[k] };
        });
        entries.sort(function (a, b) { return b.total - a.total; });
        if (entries.length <= limit) {
          return entries.map(function (e) { return e.key; });
        }
        var top = entries.slice(0, limit).map(function (e) { return e.key; });
        top.push('Others');
        return top;
      }

      function buildCrossTab(dimX, dimY) {
        var xTotals = {};
        var yTotals = {};
        var matrix = {};
        crossRecords.forEach(function (row) {
          var xVal = row[dimX] || 'Not stated';
          var yVal = row[dimY] || 'Not stated';
          xTotals[xVal] = (xTotals[xVal] || 0) + 1;
          yTotals[yVal] = (yTotals[yVal] || 0) + 1;
          if (!matrix[xVal]) matrix[xVal] = {};
          matrix[xVal][yVal] = (matrix[xVal][yVal] || 0) + 1;
        });

        var xLabels = topKeys(xTotals, 8);
        var yLabels = topKeys(yTotals, 6);
        var xHasOthers = xLabels.indexOf('Others') !== -1;
        var yHasOthers = yLabels.indexOf('Others') !== -1;
        var topX = xHasOthers ? xLabels.slice(0, -1) : xLabels.slice();
        var topY = yHasOthers ? yLabels.slice(0, -1) : yLabels.slice();
        var merged = {};

        Object.keys(matrix).forEach(function (xKey) {
          var targetX = topX.indexOf(xKey) !== -1 ? xKey : (xHasOthers ? 'Others' : null);
          if (!targetX) return;
          if (!merged[targetX]) merged[targetX] = {};
          Object.keys(matrix[xKey]).forEach(function (yKey) {
            var targetY = topY.indexOf(yKey) !== -1 ? yKey : (yHasOthers ? 'Others' : null);
            if (!targetY) return;
            merged[targetX][targetY] = (merged[targetX][targetY] || 0) + matrix[xKey][yKey];
          });
        });

        var datasets = yLabels.map(function (yKey, idx) {
          return {
            label: yKey,
            data: xLabels.map(function (xKey) {
              return (merged[xKey] && merged[xKey][yKey]) ? merged[xKey][yKey] : 0;
            }),
            backgroundColor: colors[idx % colors.length],
            borderRadius: 4,
            maxBarThickness: 22,
            categoryPercentage: 0.72,
            barPercentage: 0.82
          };
        });

        var maxLabelLen = xLabels.reduce(function (max, label) {
          return Math.max(max, String(label).length);
        }, 0);

        return {
          labels: xLabels,
          datasets: datasets,
          caption: (crossLabels[dimX] || dimX) + ' × ' + (crossLabels[dimY] || dimY),
          horizontal: xLabels.length >= 5 || maxLabelLen > 14,
          rowCount: xLabels.length,
          seriesCount: yLabels.length
        };
      }

      function ensureCrossCanvas() {
        if (!crossChartHost) return null;
        return document.getElementById('chart-cross');
      }

      function truncateLabel(label, max) {
        label = String(label || '');
        return label.length > max ? label.slice(0, max - 1) + '…' : label;
      }

      function resizeCrossChart() {
        if (!crossChart) return;
        crossChart.resize();
      }

      function setCrossHint(message, isInfo) {
        if (!crossResultHint) return;
        if (!message) {
          crossResultHint.hidden = true;
          crossResultHint.textContent = '';
          crossResultHint.classList.remove('is-info');
          return;
        }
        crossResultHint.hidden = false;
        crossResultHint.textContent = message;
        crossResultHint.classList.toggle('is-info', !!isInfo);
      }

      function showCrossPlaceholder() {
        if (crossChartHost) {
          crossChartHost.hidden = true;
          crossChartHost.style.minHeight = '';
          crossChartHost.style.height = '';
        }
        if (crossPlaceholder) crossPlaceholder.hidden = false;
      }

      function showCrossCanvas() {
        if (crossPlaceholder) crossPlaceholder.hidden = true;
        if (crossChartHost) {
          crossChartHost.hidden = false;
          crossChartHost.style.minHeight = '';
          crossChartHost.style.height = '';
        }
        return ensureCrossCanvas();
      }

      function openCrossModal() {
        if (!crossModal) return;
        crossModal.hidden = false;
        document.body.style.overflow = 'hidden';
        requestAnimationFrame(function () {
          resizeCrossChart();
          setTimeout(resizeCrossChart, 120);
        });
      }

      function closeCrossModal() {
        if (!crossModal) return;
        crossModal.hidden = true;
        document.body.style.overflow = '';
      }

      function renderCrossGraph() {
        var dimX = crossDimX ? crossDimX.value : 'education';
        var dimY = crossDimY ? crossDimY.value : 'sex';

        if (dimX === dimY) {
          if (crossChart) {
            crossChart.destroy();
            crossChart = null;
          }
          if (crossCaption) crossCaption.textContent = '';
          setCrossHint('Choose two different statistics.');
          showCrossPlaceholder();
          return;
        }

        if (!crossRecords.length) {
          if (crossCaption) crossCaption.textContent = '';
          setCrossHint('No records match the current filters.');
          showCrossPlaceholder();
          return;
        }

        var tab = buildCrossTab(dimX, dimY);
        if (crossCaption) crossCaption.textContent = tab.caption;
        setCrossHint('', false);
        showCrossCanvas();
        requestAnimationFrame(function () {
          var canvas = ensureCrossCanvas();
          if (!canvas) return;

          if (crossChart) crossChart.destroy();
          crossChart = new Chart(canvas, {
            type: 'bar',
            data: {
              labels: tab.labels,
              datasets: tab.datasets
            },
            options: {
              indexAxis: tab.horizontal ? 'y' : 'x',
              responsive: true,
              maintainAspectRatio: false,
              layout: {
                padding: { top: 8, right: 16, bottom: 8, left: 8 }
              },
              plugins: {
                legend: {
                  position: 'bottom',
                  align: 'start',
                  labels: {
                    boxWidth: 10,
                    boxHeight: 10,
                    padding: 10,
                    font: { size: 10 },
                    usePointStyle: true
                  }
                },
                tooltip: {
                  callbacks: {
                    footer: function (items) {
                      var sum = items.reduce(function (acc, item) {
                        return acc + Number(item.raw || 0);
                      }, 0);
                      return items.length > 1 ? 'Segment total: ' + sum.toLocaleString() : '';
                    }
                  }
                }
              },
              scales: {
                x: {
                  stacked: false,
                  grid: {
                    display: tab.horizontal,
                    color: '#eef2f7'
                  },
                  ticks: {
                    precision: 0,
                    autoSkip: true,
                    maxTicksLimit: tab.horizontal ? 8 : 7,
                    maxRotation: tab.horizontal ? 0 : 35,
                    minRotation: 0,
                    callback: tab.horizontal
                      ? undefined
                      : function (value) {
                          return truncateLabel(this.getLabelForValue(value), 16);
                        }
                  }
                },
                y: {
                  stacked: false,
                  beginAtZero: true,
                  grid: {
                    display: !tab.horizontal,
                    color: '#eef2f7'
                  },
                  ticks: {
                    precision: 0,
                    autoSkip: true,
                    maxTicksLimit: tab.horizontal ? 12 : 8,
                    callback: tab.horizontal
                      ? function (value) {
                          return truncateLabel(this.getLabelForValue(value), 22);
                        }
                      : undefined
                  }
                }
              }
            }
          });
          requestAnimationFrame(function () {
            resizeCrossChart();
            setTimeout(resizeCrossChart, 120);
          });
        });
      }

      if (crossOpenBtn) crossOpenBtn.addEventListener('click', openCrossModal);
      if (crossCloseBtn) crossCloseBtn.addEventListener('click', closeCrossModal);
      if (crossModal) {
        crossModal.addEventListener('click', function (e) {
          if (e.target === crossModal) closeCrossModal();
        });
      }
      if (crossGenerateBtn) crossGenerateBtn.addEventListener('click', renderCrossGraph);
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && crossModal && !crossModal.hidden) closeCrossModal();
      });
    })();
  </script>
  <script>
    (function () {
      var form = document.getElementById('rpt-cross-filter');
      if (!form) return;
      var muni = document.getElementById('filter-muni');
      var brgy = document.getElementById('filter-brgy');

      form.querySelectorAll('select').forEach(function (el) {
        el.addEventListener('change', function () {
          if (el === muni && brgy) brgy.value = '0';
          form.submit();
        });
      });
    })();
  </script>
  <script>
    (function () {
      var root = document.getElementById('rpt-fullscreen-root');
      var btn = document.getElementById('rpt-fullscreen-toggle');
      if (!root || !btn) return;

      var iconEnter = btn.querySelector('.topbar-fs-icon-enter');
      var iconExit = btn.querySelector('.topbar-fs-icon-exit');
      var labelEl = btn.querySelector('.topbar-fs-label');
      var labelEnter = 'Fullscreen';
      var labelExit = 'Exit fullscreen';

      function getFullscreenElement() {
        return document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement;
      }

      function requestFs(el) {
        if (el.requestFullscreen) return el.requestFullscreen();
        if (el.webkitRequestFullscreen) return el.webkitRequestFullscreen();
        if (el.msRequestFullscreen) return el.msRequestFullscreen();
        return Promise.reject(new Error('Fullscreen not supported'));
      }

      function exitFs() {
        if (document.exitFullscreen) return document.exitFullscreen();
        if (document.webkitExitFullscreen) return document.webkitExitFullscreen();
        if (document.msExitFullscreen) return document.msExitFullscreen();
        return Promise.reject(new Error('Fullscreen not supported'));
      }

      function resizeCharts() {
        if (typeof Chart === 'undefined') return;
        document.querySelectorAll('canvas').forEach(function (canvas) {
          var chart = Chart.getChart(canvas);
          if (chart) chart.resize();
        });
      }

      function syncBtn() {
        var active = getFullscreenElement() === root;
        btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        btn.setAttribute('aria-label', active ? labelExit : labelEnter);
        btn.title = active ? labelExit : labelEnter;
        if (labelEl) labelEl.textContent = active ? labelExit : labelEnter;
        if (iconEnter) iconEnter.hidden = active;
        if (iconExit) iconExit.hidden = !active;
      }

      function onFullscreenChange() {
        syncBtn();
        window.setTimeout(resizeCharts, 120);
      }

      btn.addEventListener('click', function () {
        if (getFullscreenElement() === root) {
          exitFs().catch(function () {});
        } else {
          requestFs(root).catch(function () {});
        }
      });

      document.addEventListener('fullscreenchange', onFullscreenChange);
      document.addEventListener('webkitfullscreenchange', onFullscreenChange);
      syncBtn();
    })();
  </script>
</body>
</html>
