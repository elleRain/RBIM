<?php
declare(strict_types=1);

require_once __DIR__ . '/../../login.php';

$user = require_role('admin');
$page_title = 'Household Details';
$role_label = 'Barangay Administrator';
$user_name = user_display_name($user);
$user_initials = user_initials($user);
$logo = app_url('assets/img/system-logo.jpg');
$css = app_url('assets/css/dashboard.css');
$formCss = app_url('assets/css/admin-forms.css');
$logout = app_url('logout.php');
$activeNav = 'household_list';
$pageHeading = '';
$pageSubheading = '';

$barangayId = (int) ($user['barangay_id'] ?? 0);
$sessionId = isset($_GET['session_id']) ? (int) $_GET['session_id'] : 0;
$openMemberId = isset($_GET['member_id']) ? (int) $_GET['member_id'] : 0;

$household = null;
$members = [];
$membersPayload = [];

function vm_display(?string $value): string
{
    $value = trim((string) $value);
    return $value === '' ? '—' : htmlspecialchars($value);
}

function vm_plain(?string $value): string
{
    $value = trim((string) $value);
    return $value === '' ? '—' : $value;
}

function vm_sex_label($sex): string
{
    $s = (string) $sex;
    if ($s === '1') {
        return 'Male';
    }
    if ($s === '2') {
        return 'Female';
    }
    return $s !== '' ? $s : '—';
}

function vm_initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $letters = '';
    foreach ($parts as $part) {
        if ($part === '') {
            continue;
        }
        if (function_exists('mb_substr') && function_exists('mb_strtoupper')) {
            $letters .= mb_strtoupper(mb_substr($part, 0, 1));
        } else {
            $letters .= strtoupper(substr($part, 0, 1));
        }
        if (strlen($letters) >= 2) {
            break;
        }
    }
    return $letters !== '' ? $letters : '?';
}

function vm_format_date(?string $date): string
{
    $date = trim((string) $date);
    if ($date === '' || $date === '0000-00-00') {
        return '—';
    }
    $ts = strtotime($date);
    return $ts ? date('M j, Y', $ts) : $date;
}

function vm_format_time(?string $time): string
{
    $time = trim((string) $time);
    if ($time === '') {
        return '—';
    }
    $ts = strtotime($time);
    return $ts ? date('g:i A', $ts) : $time;
}

function vm_value_for_key(array $row, string $key): string
{
    $raw = $row[$key] ?? null;
    if ($key === 'sex') {
        return vm_sex_label($raw);
    }
    if (str_contains($key, 'date')) {
        return vm_format_date($raw !== null ? (string) $raw : null);
    }
    if (str_contains($key, 'time') && !str_contains($key, 'date')) {
        return vm_format_time($raw !== null ? (string) $raw : null);
    }
    if ($raw === null) {
        return '—';
    }
    return vm_plain((string) $raw);
}

/**
 * @param array<string, mixed> $row
 * @param list<array{0:string,1:string}> $fields
 * @return list<array{label:string,value:string}>
 */
function vm_map_fields(array $row, array $fields): array
{
    $out = [];
    foreach ($fields as [$key, $label]) {
        $out[] = [
            'label' => $label,
            'value' => vm_value_for_key($row, $key),
        ];
    }
    return $out;
}

/**
 * @param array<string, mixed> $row
 * @param list<array{0:string,1:string}> $fields
 */
function vm_render_fields(array $row, array $fields): void
{
    echo '<div class="vm-facts">';
    foreach ($fields as [$key, $label]) {
        echo '<div class="vm-fact">';
        echo '<span class="vm-fact-label">' . htmlspecialchars($label) . '</span>';
        echo '<span class="vm-fact-value">' . htmlspecialchars(vm_value_for_key($row, $key)) . '</span>';
        echo '</div>';
    }
    echo '</div>';
}

$memberTabDefs = [
    ['id' => 'basic', 'label' => 'Basic', 'fields' => [
        ['name', 'Full name'],
        ['relationship', 'Relationship to head'],
        ['sex', 'Sex'],
        ['age', 'Age'],
        ['date_of_birth', 'Date of birth'],
        ['place_of_birth', 'Place of birth'],
        ['nationality', 'Nationality'],
        ['marital_status', 'Marital status'],
        ['religion', 'Religion'],
        ['ethnicity', 'Ethnicity'],
    ]],
    ['id' => 'education', 'label' => 'Education', 'fields' => [
        ['highest_education', 'Highest education'],
        ['currently_enrolled', 'Currently enrolled'],
        ['school_level', 'School level'],
        ['place_of_school', 'Place of school'],
    ]],
    ['id' => 'work', 'label' => 'Work', 'fields' => [
        ['q15_monthly_income', 'Monthly income'],
        ['q15a_source_of_income', 'Source of income'],
        ['q15b_occupation', 'Occupation'],
        ['q17_work_status', 'Work status'],
        ['q18_place_of_work', 'Place of work'],
    ]],
    ['id' => 'health', 'label' => 'Health', 'fields' => [
        ['q26_health_insurance', 'Health insurance'],
        ['q26a_nutritional_status', 'Nutritional status'],
        ['q27_facility_visited', 'Facility visited'],
        ['q28_reason_for_visit', 'Reason for visit'],
        ['q29_disability', 'Disability'],
        ['q30_solo_parent_status', 'Solo parent'],
        ['q31_registered_senior', 'Registered senior'],
        ['q32_registered_voter', 'Registered voter'],
    ]],
    ['id' => 'maternal', 'label' => 'Maternal', 'fields' => [
        ['q19_place_of_delivery', 'Place of delivery'],
        ['q20_birth_attendant', 'Birth attendant'],
        ['q21_immunization', 'Immunization'],
        ['q22_living_children', 'Living children'],
        ['q23_fp_method', 'FP method'],
        ['q24_source_of_fp', 'Source of FP'],
        ['q25_intention_to_use_fp', 'Intention to use FP'],
    ]],
    ['id' => 'migration', 'label' => 'Migration', 'fields' => [
        ['q33_previous_residence_5yrs', 'Residence 5 years ago'],
        ['q34_previous_residence_6mos', 'Previous residence (6 mos)'],
        ['q35_length_of_stay', 'Length of stay'],
        ['q36_type_of_resident', 'Type of resident'],
        ['q37_date_of_transfer', 'Date of transfer'],
        ['q38_reasons_for_leaving', 'Reasons for leaving'],
        ['q39_plan_to_return', 'Plan to return'],
        ['q40_reasons_for_transferring', 'Reasons for transferring'],
        ['q41_duration_of_stay', 'Duration of stay'],
        ['q42a_ctc_possession', 'CTC possession'],
        ['q42b_ctc_issued_in_barangay', 'CTC issued in barangay'],
        ['q43_skills_training_interest', 'Skills training interest'],
        ['q44_skills', 'Skills'],
    ]],
    ['id' => 'housing', 'label' => 'Housing', 'fields' => [
        ['q45_housing_tenure', 'Housing tenure'],
        ['q46_lot_tenure', 'Lot tenure'],
        ['q47_fuel_for_lighting', 'Fuel for lighting'],
        ['q48_fuel_for_cooking', 'Fuel for cooking'],
        ['q49_drinking_water_source', 'Drinking water source'],
        ['q50a_garbage_disposal_method', 'Garbage disposal'],
        ['q50b_garbage_segregation', 'Garbage segregation'],
        ['q51_toilet_facility', 'Toilet facility'],
        ['q52_building_type', 'Building type'],
        ['q53_outer_wall_material', 'Outer wall material'],
    ]],
    ['id' => 'needs', 'label' => 'Needs', 'fields' => [
        ['q54_female_death_details', 'Female death details'],
        ['q55_child_death_details', 'Child death details'],
        ['q56_common_diseases', 'Common diseases'],
        ['q57_primary_needs', 'Primary needs'],
        ['q58_future_residence', 'Future residence'],
        ['q59_hunger_experience', 'Hunger experience'],
        ['q60_cannot_read', 'Cannot read'],
        ['q61_evacuation_center', 'Evacuation center'],
        ['q62_emergency_kit', 'Emergency kit'],
    ]],
];

try {
    $pdo = db();

    if ($barangayId <= 0 || $sessionId <= 0) {
        redirect(app_url('pages/household_list.php'));
    }

    $hhStmt = $pdo->prepare(
        'SELECT *
         FROM survey_session
         WHERE session_id = :sid AND barangay_id = :bid
         LIMIT 1'
    );
    $hhStmt->execute([':sid' => $sessionId, ':bid' => $barangayId]);
    $household = $hhStmt->fetch(PDO::FETCH_ASSOC);

    if (!$household) {
        redirect(app_url('pages/household_list.php'));
    }

    $hhLabel = 'H' . (int) $household['household_number'];
    $household['household_number_label'] = $hhLabel;
    $page_title = 'Household Details';

    $memStmt = $pdo->prepare(
        'SELECT *
         FROM register_members
         WHERE session_id = :sid
         ORDER BY
           CASE WHEN LOWER(relationship) = \'head\' THEN 0 ELSE 1 END,
           name ASC'
    );
    $memStmt->execute([':sid' => $sessionId]);
    $members = $memStmt->fetchAll(PDO::FETCH_ASSOC);

    $pdo->prepare(
        'UPDATE survey_session SET total_members = :n WHERE session_id = :sid'
    )->execute([':n' => count($members), ':sid' => $sessionId]);
    $household['total_members'] = count($members);

    foreach ($members as $m) {
        $mid = (int) ($m['member_id'] ?? 0);
        $name = (string) ($m['name'] ?? '');
        $isHead = strcasecmp((string) ($m['relationship'] ?? ''), 'Head') === 0;
        $tabs = [];
        foreach ($memberTabDefs as $tab) {
            $tabs[] = [
                'id' => $tab['id'],
                'label' => $tab['label'],
                'fields' => vm_map_fields($m, $tab['fields']),
            ];
        }
        $membersPayload[(string) $mid] = [
            'id' => $mid,
            'name' => $name !== '' ? $name : '—',
            'relationship' => vm_plain((string) ($m['relationship'] ?? '')),
            'sex' => vm_sex_label($m['sex'] ?? ''),
            'age' => isset($m['age']) && $m['age'] !== '' && $m['age'] !== null ? (string) (int) $m['age'] : '—',
            'marital_status' => vm_plain((string) ($m['marital_status'] ?? '')),
            'initials' => vm_initials($name),
            'isHead' => $isHead,
            'tabs' => $tabs,
        ];
    }

    if ($openMemberId > 0 && !isset($membersPayload[(string) $openMemberId])) {
        $openMemberId = 0;
    }
} catch (Throwable $e) {
    redirect(app_url('pages/household_list.php'));
}

$registerUrl = app_url('pages/registration/register_member.php?session_id=' . $sessionId);
$listUrl = app_url('pages/household_list.php');
$resultRaw = trim((string) ($household['result'] ?? ''));
$resultClass = strcasecmp($resultRaw, 'Complete') === 0 ? 'is-ok' : (strcasecmp($resultRaw, 'Incomplete') === 0 ? 'is-warn' : '');
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
        $backUrl = $listUrl;
        $backLabel = 'Household List';
        require __DIR__ . '/../../includes/topbar.php';
      ?>

      <main class="content">
        <div class="hh-page vm-page">

          <div class="hh-context-card vm-context">
            <div class="hh-context-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <div class="vm-context-text">
              <p class="vm-kicker"><?php echo htmlspecialchars($hhLabel); ?> · Barangay <?php echo vm_display((string) ($household['barangay'] ?? '')); ?></p>
              <h1 class="hh-context-barangay"><?php echo vm_display((string) ($household['household_head'] ?? '')); ?></h1>
              <p class="vm-context-sub"><?php echo vm_display((string) ($household['address'] ?? '')); ?></p>
            </div>
            <div class="vm-context-actions">
              <a class="hh-btn hh-btn-primary" href="<?php echo htmlspecialchars($registerUrl); ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                Register Member
              </a>
            </div>
          </div>

          <div class="vm-summary">
            <div class="vm-summary-item">
              <span class="vm-summary-label">Members</span>
              <strong><?php echo number_format((int) ($household['total_members'] ?? 0)); ?></strong>
            </div>
            <div class="vm-summary-item">
              <span class="vm-summary-label">Visit result</span>
              <strong>
                <?php if ($resultRaw !== ''): ?>
                  <span class="vm-status <?php echo $resultClass; ?>"><?php echo htmlspecialchars($resultRaw); ?></span>
                <?php else: ?>
                  —
                <?php endif; ?>
              </strong>
            </div>
            <div class="vm-summary-item">
              <span class="vm-summary-label">Visit date</span>
              <strong><?php echo htmlspecialchars(vm_format_date(isset($household['visit_date']) ? (string) $household['visit_date'] : null)); ?></strong>
            </div>
            <div class="vm-summary-item">
              <span class="vm-summary-label">Respondent</span>
              <strong><?php echo vm_display((string) ($household['respondent_name'] ?? '')); ?></strong>
            </div>
          </div>

          <section class="hh-panel">
            <div class="hh-panel-head">
              <div class="hh-panel-title">
                <span class="hh-panel-title-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </span>
                <h2>Household record</h2>
              </div>
            </div>
            <div class="vm-panel-body">
              <div class="vm-block">
                <h3 class="vm-block-title">Location</h3>
                <?php
                  vm_render_fields($household, [
                      ['province', 'Province'],
                      ['city_municipality', 'City / Municipality'],
                      ['barangay', 'Barangay'],
                      ['address', 'Address'],
                  ]);
                ?>
              </div>
              <div class="vm-block">
                <h3 class="vm-block-title">Household</h3>
                <?php
                  vm_render_fields($household, [
                      ['household_number_label', 'Household number'],
                      ['household_head', 'Household head'],
                      ['respondent_name', 'Respondent'],
                      ['total_members', 'Total members'],
                  ]);
                ?>
              </div>
              <div class="vm-block">
                <h3 class="vm-block-title">Visit</h3>
                <?php
                  vm_render_fields($household, [
                      ['visit_number', 'Visit number'],
                      ['visit_date', 'Visit date'],
                      ['start_time', 'Start time'],
                      ['end_time', 'End time'],
                      ['result', 'Result'],
                      ['next_visit_date', 'Next visit date'],
                  ]);
                ?>
              </div>
              <div class="vm-block">
                <h3 class="vm-block-title">Personnel</h3>
                <?php
                  vm_render_fields($household, [
                      ['interviewer_name', 'Interviewer'],
                      ['supervisor_name', 'Supervisor'],
                      ['encoder_name', 'Encoder'],
                      ['encoding_supervisor', 'Encoding supervisor'],
                      ['date_encoded', 'Date encoded'],
                  ]);
                ?>
              </div>
            </div>
          </section>

          <section class="hh-panel">
            <div class="hh-panel-head">
              <div class="hh-panel-title">
                <span class="hh-panel-title-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </span>
                <h2>Registered members</h2>
              </div>
              <a class="hh-add-btn" href="<?php echo htmlspecialchars($registerUrl); ?>">
                <svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                Add Member
              </a>
            </div>

            <?php if (empty($members)): ?>
              <div class="vm-empty">
                <p>No members registered yet for this household.</p>
                <a class="hh-btn hh-btn-primary" href="<?php echo htmlspecialchars($registerUrl); ?>">Register first member</a>
              </div>
            <?php else: ?>
              <div class="hh-table-wrap">
                <table class="hh-table">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Name</th>
                      <th>Relationship</th>
                      <th>Sex</th>
                      <th>Age</th>
                      <th>Civil status</th>
                      <th class="hh-col-actions">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($members as $i => $m): ?>
                      <?php
                        $mid = (int) ($m['member_id'] ?? 0);
                        $payload = $membersPayload[(string) $mid] ?? null;
                        $mName = $payload['name'] ?? '—';
                        $isHead = !empty($payload['isHead']);
                      ?>
                      <tr>
                        <td class="hh-col-number"><?php echo $i + 1; ?></td>
                        <td>
                          <div class="vm-name-cell">
                            <strong><?php echo htmlspecialchars((string) $mName); ?></strong>
                            <?php if ($isHead): ?><span class="vm-tag">Head</span><?php endif; ?>
                          </div>
                        </td>
                        <td><?php echo htmlspecialchars((string) ($payload['relationship'] ?? '—')); ?></td>
                        <td><?php echo htmlspecialchars((string) ($payload['sex'] ?? '—')); ?></td>
                        <td><?php echo htmlspecialchars((string) ($payload['age'] ?? '—')); ?></td>
                        <td><?php echo htmlspecialchars((string) ($payload['marital_status'] ?? '—')); ?></td>
                        <td class="hh-col-actions">
                          <button type="button" class="hh-btn hh-btn-primary" data-view-member="<?php echo $mid; ?>">View Details</button>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </section>

        </div>
      </main>
    </div>
  </div>

  <div class="admin-modal-backdrop" id="member-detail-modal" hidden>
    <div class="admin-modal vm-member-modal" role="dialog" aria-modal="true" aria-labelledby="member-modal-title">
      <div class="admin-modal-head hh-modal-head vm-member-modal-head">
        <div class="hh-modal-head-main">
          <span class="hh-modal-head-icon vm-member-modal-avatar" id="member-modal-avatar" aria-hidden="true">?</span>
          <div>
            <p class="admin-modal-kicker" id="member-modal-kicker">Member details</p>
            <h3 id="member-modal-title">Member</h3>
            <p class="admin-modal-sub" id="member-modal-sub"></p>
          </div>
        </div>
        <button type="button" class="admin-modal-close" id="member-modal-close" aria-label="Close">
          <svg viewBox="0 0 24 24"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>
        </button>
      </div>

      <div class="vm-modal-tabs" id="member-modal-tabs" role="tablist" aria-label="Member detail sections"></div>

      <div class="admin-modal-body vm-member-modal-body">
        <div id="member-modal-panels"></div>
      </div>
    </div>
  </div>

  <?php require __DIR__ . '/../../includes/scripts.php'; ?>
  <script>
    (function () {
      var members = <?php echo json_encode($membersPayload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
      var openId = <?php echo (int) $openMemberId; ?>;
      var hhLabel = <?php echo json_encode($hhLabel, JSON_UNESCAPED_UNICODE); ?>;

      var modal = document.getElementById('member-detail-modal');
      var closeBtn = document.getElementById('member-modal-close');
      var tabsEl = document.getElementById('member-modal-tabs');
      var panelsEl = document.getElementById('member-modal-panels');
      var titleEl = document.getElementById('member-modal-title');
      var subEl = document.getElementById('member-modal-sub');
      var kickerEl = document.getElementById('member-modal-kicker');
      var avatarEl = document.getElementById('member-modal-avatar');
      var activeMemberId = null;

      function esc(text) {
        var d = document.createElement('div');
        d.textContent = text == null ? '' : String(text);
        return d.innerHTML;
      }

      function setTab(tabId) {
        if (!tabsEl || !panelsEl) return;
        tabsEl.querySelectorAll('[role="tab"]').forEach(function (btn) {
          var on = btn.getAttribute('data-tab') === tabId;
          btn.classList.toggle('is-active', on);
          btn.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        panelsEl.querySelectorAll('[data-panel]').forEach(function (panel) {
          panel.hidden = panel.getAttribute('data-panel') !== tabId;
        });
      }

      function renderMember(member) {
        if (!member) return;

        titleEl.textContent = member.name || 'Member';
        kickerEl.textContent = hhLabel + (member.isHead ? ' · Household head' : ' · Member details');
        subEl.textContent = [member.relationship, member.sex, member.age !== '—' ? 'Age ' + member.age : null]
          .filter(Boolean)
          .join(' · ');
        avatarEl.textContent = member.initials || '?';
        avatarEl.classList.toggle('is-head', !!member.isHead);

        var tabsHtml = '';
        var panelsHtml = '';
        (member.tabs || []).forEach(function (tab, idx) {
          tabsHtml +=
            '<button type="button" class="vm-modal-tab' + (idx === 0 ? ' is-active' : '') + '" role="tab" data-tab="' +
            esc(tab.id) + '" aria-selected="' + (idx === 0 ? 'true' : 'false') + '">' +
            esc(tab.label) + '</button>';

          var fieldsHtml = '<div class="vm-facts">';
          (tab.fields || []).forEach(function (field) {
            fieldsHtml +=
              '<div class="vm-fact">' +
              '<span class="vm-fact-label">' + esc(field.label) + '</span>' +
              '<span class="vm-fact-value">' + esc(field.value) + '</span>' +
              '</div>';
          });
          fieldsHtml += '</div>';

          panelsHtml +=
            '<div class="vm-modal-panel" data-panel="' + esc(tab.id) + '"' + (idx === 0 ? '' : ' hidden') + '>' +
            '<h4 class="vm-modal-panel-title">' + esc(tab.label) + '</h4>' +
            fieldsHtml +
            '</div>';
        });

        tabsEl.innerHTML = tabsHtml;
        panelsEl.innerHTML = panelsHtml;
      }

      function openModal(memberId) {
        var member = members[String(memberId)];
        if (!member || !modal) return;
        activeMemberId = String(memberId);
        renderMember(member);
        modal.hidden = false;
        document.body.style.overflow = 'hidden';

        try {
          var url = new URL(window.location.href);
          url.searchParams.set('member_id', String(memberId));
          history.replaceState(null, '', url.toString());
        } catch (e) {}
      }

      function closeModal() {
        if (!modal) return;
        modal.hidden = true;
        document.body.style.overflow = '';
        activeMemberId = null;
        try {
          var url = new URL(window.location.href);
          url.searchParams.delete('member_id');
          history.replaceState(null, '', url.toString());
        } catch (e) {}
      }

      document.querySelectorAll('[data-view-member]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          openModal(btn.getAttribute('data-view-member'));
        });
      });

      if (tabsEl) {
        tabsEl.addEventListener('click', function (e) {
          var btn = e.target.closest('[data-tab]');
          if (!btn) return;
          setTab(btn.getAttribute('data-tab'));
        });
      }

      if (closeBtn) closeBtn.addEventListener('click', closeModal);
      if (modal) {
        modal.addEventListener('click', function (e) {
          if (e.target === modal) closeModal();
        });
      }
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal && !modal.hidden) closeModal();
      });

      if (openId > 0) openModal(openId);
    })();
  </script>
</body>
</html>
