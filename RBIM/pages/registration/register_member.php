<?php
declare(strict_types=1);

require_once __DIR__ . '/../../login.php';

$user = require_role('admin');
$page_title = 'Register Member';
$role_label = 'Barangay Administrator';
$user_name = user_display_name($user);
$user_initials = user_initials($user);
$logo = app_url('assets/img/system-logo.jpg');
$css = app_url('assets/css/dashboard.css');
$formCss = app_url('assets/css/admin-forms.css');
$logout = app_url('logout.php');
$activeNav = 'household_list';
$pageHeading = 'Register Member';
$pageSubheading = '';

$barangayId = (int) ($user['barangay_id'] ?? 0);
$sessionId = isset($_GET['session_id']) ? (int) $_GET['session_id'] : (isset($_POST['session_id']) ? (int) $_POST['session_id'] : 0);
$household = null;
$formErrors = [];
$savedOk = isset($_GET['saved']) && $_GET['saved'] === '1';
$savedMemberName = isset($_GET['name']) ? trim((string) $_GET['name']) : '';
if (strlen($savedMemberName) > 120) {
    $savedMemberName = substr($savedMemberName, 0, 120);
}
$hasHouseholdHead = false;
$existingHeadName = '';

$fieldKeys = [
    'name', 'relationship', 'sex', 'age', 'date_of_birth', 'place_of_birth', 'nationality',
    'marital_status', 'religion', 'ethnicity',
    'highest_education', 'currently_enrolled', 'school_level', 'place_of_school',
    'monthly_income', 'source_of_income', 'occupation', 'work_status', 'place_of_work',
    'place_of_delivery', 'birth_attendant', 'immunization', 'living_children',
    'fp_method', 'source_of_fp', 'intention_to_use_fp',
    'health_insurance', 'nutritional_status', 'facility_visited', 'reason_for_visit', 'disability',
    'solo_parent', 'registered_senior', 'registered_voter',
    'previous_residence_5yrs', 'previous_residence', 'length_of_stay', 'type_of_resident',
    'date_of_transfer', 'reason_leaving_1', 'reason_leaving_2', 'reason_leaving_3', 'return_previous',
    'reason_transferring_1', 'reason_transferring_2', 'reason_transferring_3', 'duration_of_stay',
    'ctc_possession', 'ctc_issued_in_barangay', 'skills_training', 'skills',
    'housing_tenure', 'lot_tenure', 'fuel_for_lighting', 'fuel_for_cooking', 'drinking_water_source',
    'garbage_disposal', 'garbage_segregation', 'toilet_facility', 'building_type', 'outer_wall_material',
    'female_death_details', 'child_death_details', 'common_diseases', 'primary_needs', 'future_residence',
    'hunger_experience', 'cannot_read', 'evacuation_center', 'emergency_kit',
];

$form = array_fill_keys($fieldKeys, '');
$form['nationality'] = 'Filipino';

function ensure_member_extra_columns(PDO $pdo): void
{
    $needed = [
        'q26a_nutritional_status' => 'VARCHAR(150) NULL',
        'q59_hunger_experience' => 'VARCHAR(50) NULL',
        'q60_cannot_read' => 'VARCHAR(50) NULL',
        'q61_evacuation_center' => 'VARCHAR(50) NULL',
        'q62_emergency_kit' => 'VARCHAR(50) NULL',
    ];
    foreach ($needed as $col => $def) {
        $chk = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = :c'
        );
        $chk->execute([':t' => 'register_members', ':c' => $col]);
        if ((int) $chk->fetchColumn() === 0) {
            $pdo->exec('ALTER TABLE register_members ADD COLUMN `' . $col . '` ' . $def);
        }
    }
}

function mf_null_if_empty(string $v): ?string
{
    $v = trim($v);
    return $v === '' ? null : $v;
}

try {
    $pdo = db();

    if ($barangayId <= 0 || $sessionId <= 0) {
        redirect(app_url('pages/household_list.php'));
    }

    $hhStmt = $pdo->prepare(
        'SELECT session_id, household_number, barangay, barangay_id, address, household_head, respondent_name, total_members
         FROM survey_session
         WHERE session_id = :sid AND barangay_id = :bid
         LIMIT 1'
    );
    $hhStmt->execute([':sid' => $sessionId, ':bid' => $barangayId]);
    $household = $hhStmt->fetch();

    if (!$household) {
        redirect(app_url('pages/household_list.php'));
    }

    $pageSubheading = 'H' . (int) $household['household_number'] . ' · ' . (string) $household['address'];
    $pageHeading = 'Register Member';

    $headStmt = $pdo->prepare(
        'SELECT member_id, name
         FROM register_members
         WHERE session_id = :sid AND LOWER(TRIM(relationship)) = \'head\'
         LIMIT 1'
    );
    $headStmt->execute([':sid' => $sessionId]);
    $existingHead = $headStmt->fetch(PDO::FETCH_ASSOC) ?: null;
    $hasHouseholdHead = $existingHead !== null;
    $existingHeadName = $hasHouseholdHead ? (string) ($existingHead['name'] ?? '') : '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_member'])) {
        foreach ($fieldKeys as $key) {
            if (isset($_POST[$key])) {
                $form[$key] = trim((string) $_POST[$key]);
            }
        }

        if ($form['name'] === '') {
            $formErrors[] = 'Name is required.';
        }
        if ($form['relationship'] === '') {
            $formErrors[] = 'Relationship is required.';
        }
        if ($form['sex'] === '' || !in_array($form['sex'], ['1', '2'], true)) {
            $formErrors[] = 'Sex is required.';
        }
        if ($form['age'] === '' || !ctype_digit($form['age'])) {
            $formErrors[] = 'Age is required.';
        }

        $isHeadRelationship = strcasecmp($form['relationship'], 'Head') === 0;
        if ($isHeadRelationship) {
            // Re-check at submit time so concurrent saves cannot create a second head.
            $headCheck = $pdo->prepare(
                'SELECT name FROM register_members
                 WHERE session_id = :sid AND LOWER(TRIM(relationship)) = \'head\'
                 LIMIT 1'
            );
            $headCheck->execute([':sid' => $sessionId]);
            $headRow = $headCheck->fetch(PDO::FETCH_ASSOC);
            if ($headRow) {
                $hasHouseholdHead = true;
                $existingHeadName = (string) ($headRow['name'] ?? '');
                $who = $existingHeadName !== '' ? ' (' . $existingHeadName . ')' : '';
                $formErrors[] = 'This household already has a head' . $who . '. Only one head is allowed per household.';
                if (strcasecmp($form['relationship'], 'Head') === 0) {
                    $form['relationship'] = '';
                }
            }
        }

        if (empty($formErrors)) {
            ensure_member_extra_columns($pdo);

            $leaving = trim(implode('; ', array_filter([
                $form['reason_leaving_1'],
                $form['reason_leaving_2'],
                $form['reason_leaving_3'],
            ])));
            $transferring = trim(implode('; ', array_filter([
                $form['reason_transferring_1'],
                $form['reason_transferring_2'],
                $form['reason_transferring_3'],
            ])));

            $pdo->beginTransaction();

            if ($isHeadRelationship) {
                $lockHead = $pdo->prepare(
                    'SELECT name FROM register_members
                     WHERE session_id = :sid AND LOWER(TRIM(relationship)) = \'head\'
                     LIMIT 1 FOR UPDATE'
                );
                $lockHead->execute([':sid' => $sessionId]);
                $lockedHead = $lockHead->fetch(PDO::FETCH_ASSOC);
                if ($lockedHead) {
                    $pdo->rollBack();
                    $who = trim((string) ($lockedHead['name'] ?? ''));
                    $formErrors[] = 'This household already has a head'
                        . ($who !== '' ? ' (' . $who . ')' : '')
                        . '. Only one head is allowed per household.';
                    $hasHouseholdHead = true;
                    $existingHeadName = $who;
                    $form['relationship'] = '';
                }
            }

            if (empty($formErrors)) {
            $ins = $pdo->prepare(
                'INSERT INTO register_members (
                    household_number, session_id, name, relationship, sex, age, date_of_birth,
                    place_of_birth, nationality, marital_status, religion, ethnicity,
                    highest_education, currently_enrolled, school_level, place_of_school,
                    q15_monthly_income, q15a_source_of_income, q15b_occupation, q17_work_status, q18_place_of_work,
                    q19_place_of_delivery, q20_birth_attendant, q21_immunization, q22_living_children,
                    q23_fp_method, q24_source_of_fp, q25_intention_to_use_fp,
                    q26_health_insurance, q26a_nutritional_status, q27_facility_visited, q28_reason_for_visit, q29_disability,
                    q30_solo_parent_status, q31_registered_senior, q32_registered_voter,
                    q33_previous_residence_5yrs, q34_previous_residence_6mos, q35_length_of_stay, q36_type_of_resident,
                    q37_date_of_transfer, q38_reasons_for_leaving, q39_plan_to_return, q40_reasons_for_transferring,
                    q41_duration_of_stay, q42a_ctc_possession, q42b_ctc_issued_in_barangay,
                    q43_skills_training_interest, q44_skills,
                    q45_housing_tenure, q46_lot_tenure, q47_fuel_for_lighting, q48_fuel_for_cooking,
                    q49_drinking_water_source, q50a_garbage_disposal_method, q50b_garbage_segregation,
                    q51_toilet_facility, q52_building_type, q53_outer_wall_material,
                    q54_female_death_details, q55_child_death_details, q56_common_diseases,
                    q57_primary_needs, q58_future_residence,
                    q59_hunger_experience, q60_cannot_read, q61_evacuation_center, q62_emergency_kit
                ) VALUES (
                    :household_number, :session_id, :name, :relationship, :sex, :age, :date_of_birth,
                    :place_of_birth, :nationality, :marital_status, :religion, :ethnicity,
                    :highest_education, :currently_enrolled, :school_level, :place_of_school,
                    :monthly_income, :source_of_income, :occupation, :work_status, :place_of_work,
                    :place_of_delivery, :birth_attendant, :immunization, :living_children,
                    :fp_method, :source_of_fp, :intention_to_use_fp,
                    :health_insurance, :nutritional_status, :facility_visited, :reason_for_visit, :disability,
                    :solo_parent, :registered_senior, :registered_voter,
                    :previous_residence_5yrs, :previous_residence, :length_of_stay, :type_of_resident,
                    :date_of_transfer, :reasons_leaving, :return_previous, :reasons_transferring,
                    :duration_of_stay, :ctc_possession, :ctc_issued_in_barangay,
                    :skills_training, :skills,
                    :housing_tenure, :lot_tenure, :fuel_for_lighting, :fuel_for_cooking,
                    :drinking_water_source, :garbage_disposal, :garbage_segregation,
                    :toilet_facility, :building_type, :outer_wall_material,
                    :female_death_details, :child_death_details, :common_diseases,
                    :primary_needs, :future_residence,
                    :hunger_experience, :cannot_read, :evacuation_center, :emergency_kit
                )'
            );

            $ins->execute([
                ':household_number' => (int) $household['household_number'],
                ':session_id' => $sessionId,
                ':name' => $form['name'],
                ':relationship' => $form['relationship'],
                ':sex' => (int) $form['sex'],
                ':age' => (int) $form['age'],
                ':date_of_birth' => mf_null_if_empty($form['date_of_birth']),
                ':place_of_birth' => mf_null_if_empty($form['place_of_birth']),
                ':nationality' => mf_null_if_empty($form['nationality']),
                ':marital_status' => mf_null_if_empty($form['marital_status']),
                ':religion' => mf_null_if_empty($form['religion']),
                ':ethnicity' => mf_null_if_empty($form['ethnicity']),
                ':highest_education' => mf_null_if_empty($form['highest_education']),
                ':currently_enrolled' => mf_null_if_empty($form['currently_enrolled']),
                ':school_level' => mf_null_if_empty($form['school_level']),
                ':place_of_school' => mf_null_if_empty($form['place_of_school']),
                ':monthly_income' => mf_null_if_empty($form['monthly_income']),
                ':source_of_income' => mf_null_if_empty($form['source_of_income']),
                ':occupation' => mf_null_if_empty($form['occupation']),
                ':work_status' => mf_null_if_empty($form['work_status']),
                ':place_of_work' => mf_null_if_empty($form['place_of_work']),
                ':place_of_delivery' => mf_null_if_empty($form['place_of_delivery']),
                ':birth_attendant' => mf_null_if_empty($form['birth_attendant']),
                ':immunization' => mf_null_if_empty($form['immunization']),
                ':living_children' => mf_null_if_empty($form['living_children']),
                ':fp_method' => mf_null_if_empty($form['fp_method']),
                ':source_of_fp' => mf_null_if_empty($form['source_of_fp']),
                ':intention_to_use_fp' => mf_null_if_empty($form['intention_to_use_fp']),
                ':health_insurance' => mf_null_if_empty($form['health_insurance']),
                ':nutritional_status' => mf_null_if_empty($form['nutritional_status']),
                ':facility_visited' => mf_null_if_empty($form['facility_visited']),
                ':reason_for_visit' => mf_null_if_empty($form['reason_for_visit']),
                ':disability' => mf_null_if_empty($form['disability']),
                ':solo_parent' => mf_null_if_empty($form['solo_parent']),
                ':registered_senior' => mf_null_if_empty($form['registered_senior']),
                ':registered_voter' => mf_null_if_empty($form['registered_voter']),
                ':previous_residence_5yrs' => mf_null_if_empty($form['previous_residence_5yrs']),
                ':previous_residence' => mf_null_if_empty($form['previous_residence']),
                ':length_of_stay' => mf_null_if_empty($form['length_of_stay']),
                ':type_of_resident' => mf_null_if_empty($form['type_of_resident']),
                ':date_of_transfer' => mf_null_if_empty($form['date_of_transfer']),
                ':reasons_leaving' => $leaving !== '' ? $leaving : null,
                ':return_previous' => mf_null_if_empty($form['return_previous']),
                ':reasons_transferring' => $transferring !== '' ? $transferring : null,
                ':duration_of_stay' => mf_null_if_empty($form['duration_of_stay']),
                ':ctc_possession' => mf_null_if_empty($form['ctc_possession']),
                ':ctc_issued_in_barangay' => mf_null_if_empty($form['ctc_issued_in_barangay']),
                ':skills_training' => mf_null_if_empty($form['skills_training']),
                ':skills' => mf_null_if_empty($form['skills']),
                ':housing_tenure' => mf_null_if_empty($form['housing_tenure']),
                ':lot_tenure' => mf_null_if_empty($form['lot_tenure']),
                ':fuel_for_lighting' => mf_null_if_empty($form['fuel_for_lighting']),
                ':fuel_for_cooking' => mf_null_if_empty($form['fuel_for_cooking']),
                ':drinking_water_source' => mf_null_if_empty($form['drinking_water_source']),
                ':garbage_disposal' => mf_null_if_empty($form['garbage_disposal']),
                ':garbage_segregation' => mf_null_if_empty($form['garbage_segregation']),
                ':toilet_facility' => mf_null_if_empty($form['toilet_facility']),
                ':building_type' => mf_null_if_empty($form['building_type']),
                ':outer_wall_material' => mf_null_if_empty($form['outer_wall_material']),
                ':female_death_details' => mf_null_if_empty($form['female_death_details']),
                ':child_death_details' => mf_null_if_empty($form['child_death_details']),
                ':common_diseases' => mf_null_if_empty($form['common_diseases']),
                ':primary_needs' => mf_null_if_empty($form['primary_needs']),
                ':future_residence' => mf_null_if_empty($form['future_residence']),
                ':hunger_experience' => mf_null_if_empty($form['hunger_experience']),
                ':cannot_read' => mf_null_if_empty($form['cannot_read']),
                ':evacuation_center' => mf_null_if_empty($form['evacuation_center']),
                ':emergency_kit' => mf_null_if_empty($form['emergency_kit']),
            ]);

            $pdo->prepare(
                'UPDATE survey_session SET total_members = (
                     SELECT COUNT(*) FROM register_members WHERE session_id = :sid
                 ) WHERE session_id = :sid2'
            )->execute([':sid' => $sessionId, ':sid2' => $sessionId]);

            $pdo->commit();

            log_activity(
                (int) $user['admin_id'],
                (string) $user['username'],
                'register_member',
                'Member “' . $form['name'] . '” for household H' . (int) $household['household_number']
            );

            header('Location: ' . app_url(
                'pages/registration/register_member.php?session_id=' . $sessionId
                . '&saved=1&name=' . rawurlencode($form['name'])
            ));
            exit;
            }
        }
    }
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $formErrors[] = 'Unable to save member. Please try again.';
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
  <link rel="stylesheet" href="<?php echo htmlspecialchars($formCss); ?>">
</head>
<body>
  <div class="overlay" id="sidebar-overlay" hidden></div>

  <div class="app">
    <?php require __DIR__ . '/../../includes/sidebar.php'; ?>

    <div class="main">
      <?php
        $backUrl = app_url('pages/household_list.php');
        $backLabel = 'Household List';
        require __DIR__ . '/../../includes/topbar.php';
      ?>

      <main class="content">
        <div class="hh-page" style="max-width:920px;">
          <?php require __DIR__ . '/member_registration_form.php'; ?>
        </div>
      </main>
    </div>
  </div>

  <?php if ($savedOk): ?>
    <div class="admin-modal-backdrop mf-success-backdrop" id="member-success-modal" role="presentation">
      <div class="admin-modal mf-success-modal" role="alertdialog" aria-modal="true" aria-labelledby="member-success-title" aria-describedby="member-success-desc">
        <button type="button" class="admin-modal-close mf-success-close" id="member-success-close" aria-label="Close">
          <svg viewBox="0 0 24 24"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>
        </button>
        <div class="mf-success-body">
          <div class="mf-success-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          </div>
          <p class="mf-success-kicker">Registration complete</p>
          <h3 id="member-success-title">Member registered successfully</h3>
          <p id="member-success-desc" class="mf-success-desc">
            <?php if ($savedMemberName !== ''): ?>
              <strong><?php echo htmlspecialchars($savedMemberName); ?></strong> has been added to this household.
            <?php else: ?>
              The member has been added to this household.
            <?php endif; ?>
          </p>
          <div class="mf-success-actions">
            <a class="hh-btn hh-btn-primary" href="<?php echo htmlspecialchars(app_url('pages/registration/register_member.php?session_id=' . $sessionId)); ?>">Add another</a>
            <a class="hh-btn hh-btn-secondary" href="<?php echo htmlspecialchars(app_url('pages/registration/view_members.php?session_id=' . $sessionId)); ?>">View household</a>
          </div>
          <p class="mf-success-timer" id="member-success-timer">Closing in <span>5</span>s</p>
        </div>
        <div class="mf-success-progress" aria-hidden="true">
          <span id="member-success-progress"></span>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php require __DIR__ . '/../../includes/scripts.php'; ?>
  <?php if ($savedOk): ?>
  <script>
    (function () {
      var modal = document.getElementById('member-success-modal');
      var closeBtn = document.getElementById('member-success-close');
      var timerEl = document.querySelector('#member-success-timer span');
      var progressEl = document.getElementById('member-success-progress');
      if (!modal) return;

      var totalMs = 5000;
      var started = Date.now();
      var closed = false;
      var rafId = 0;
      var tickId = 0;

      function cleanUrl() {
        try {
          var url = new URL(window.location.href);
          url.searchParams.delete('saved');
          url.searchParams.delete('name');
          history.replaceState(null, '', url.toString());
        } catch (e) {}
      }

      function closeModal() {
        if (closed) return;
        closed = true;
        if (rafId) cancelAnimationFrame(rafId);
        if (tickId) clearInterval(tickId);
        modal.hidden = true;
        document.body.style.overflow = '';
        cleanUrl();
      }

      function frame() {
        if (closed) return;
        var elapsed = Date.now() - started;
        var pct = Math.min(100, (elapsed / totalMs) * 100);
        if (progressEl) progressEl.style.width = pct + '%';
        if (elapsed >= totalMs) {
          closeModal();
          return;
        }
        rafId = requestAnimationFrame(frame);
      }

      document.body.style.overflow = 'hidden';
      if (closeBtn) closeBtn.addEventListener('click', closeModal);
      modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
      });
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeModal();
      });

      tickId = setInterval(function () {
        if (closed || !timerEl) return;
        var left = Math.max(0, Math.ceil((totalMs - (Date.now() - started)) / 1000));
        timerEl.textContent = String(left);
      }, 250);

      rafId = requestAnimationFrame(frame);
    })();
  </script>
  <?php endif; ?>
</body>
</html>
