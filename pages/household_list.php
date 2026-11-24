<?php
declare(strict_types=1);

require_once __DIR__ . '/../login.php';

$user = require_role('admin');
$page_title = 'Household List';
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
$barangayName = (string) ($user['barangay'] ?? '');
$province = 'Siquijor';
$municipality = '';
$households = [];
$formErrors = [];
$openModal = false;
$savedHousehold = isset($_GET['saved']) ? trim((string) $_GET['saved']) : '';

$form = [
    'address' => '',
    'respondent_name' => '',
    'household_head' => '',
    'total_members' => '0',
    'visit_number' => '1',
    'visit_date' => date('Y-m-d'),
    'start_time' => '',
    'end_time' => '',
    'result' => '',
    'next_visit_date' => '',
    'interviewer_name' => $user_name,
    'supervisor_name' => '',
    'encoding_supervisor' => '',
];

function loadHouseholdList(PDO $pdo, int $barangayId): array
{
    // Keep stored totals aligned with actual registered members.
    $pdo->exec(
        'UPDATE survey_session ss
         SET total_members = (
             SELECT COUNT(*) FROM register_members rm WHERE rm.session_id = ss.session_id
         )'
    );

    $listStmt = $pdo->prepare(
        'SELECT
            ss.session_id,
            ss.household_number,
            ss.barangay,
            ss.address,
            ss.respondent_name,
            ss.household_head,
            COALESCE(rm.member_count, 0) AS total_members,
            ss.visit_date,
            ss.result,
            ss.encoder_name
         FROM survey_session ss
         LEFT JOIN (
             SELECT session_id, COUNT(*) AS member_count
             FROM register_members
             GROUP BY session_id
         ) rm ON rm.session_id = ss.session_id
         WHERE ss.barangay_id = :barangay_id
         ORDER BY ss.session_id DESC'
    );
    $listStmt->execute([':barangay_id' => $barangayId]);
    return $listStmt->fetchAll();
}

try {
    $pdo = db();

    if ($barangayId > 0) {
        $locStmt = $pdo->prepare(
            'SELECT b.barangay, cm.name AS municipality, cm.province
             FROM barangay b
             INNER JOIN city_municipality cm ON cm.city_municipality_id = b.city_municipality_id
             WHERE b.barangay_id = :id
             LIMIT 1'
        );
        $locStmt->execute([':id' => $barangayId]);
        $loc = $locStmt->fetch();
        if ($loc) {
            $barangayName = (string) $loc['barangay'];
            $municipality = (string) $loc['municipality'];
            $province = (string) $loc['province'];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_household'])) {
            $openModal = true;
            foreach (array_keys($form) as $key) {
                if (isset($_POST[$key])) {
                    $form[$key] = trim((string) $_POST[$key]);
                }
            }

            $required = [
                'address' => 'Address',
                'respondent_name' => 'Respondent name',
                'household_head' => 'Household head',
                'visit_number' => 'Visit number',
                'visit_date' => 'Visit date',
                'result' => 'Result',
                'interviewer_name' => 'Interviewer name',
            ];

            foreach ($required as $field => $label) {
                if ($form[$field] === '') {
                    $formErrors[] = $label . ' is required.';
                }
            }

            if ($form['visit_number'] !== '' && !ctype_digit($form['visit_number'])) {
                $formErrors[] = 'Visit number must be a whole number.';
            }

            if ($form['result'] === 'Incomplete' && $form['next_visit_date'] === '') {
                $formErrors[] = 'Next visit date is required when the result is Incomplete.';
            }

            if (empty($formErrors)) {
                $yearMonth = date('ym');
                $maxStmt = $pdo->prepare(
                    'SELECT MAX(household_number) FROM survey_session WHERE household_number LIKE :prefix'
                );
                $maxStmt->execute([':prefix' => $yearMonth . '%']);
                $maxNumber = (int) $maxStmt->fetchColumn();
                $counter = $maxNumber > 0 ? ((int) substr((string) $maxNumber, -2)) + 1 : 1;
                $householdNumber = (int) ($yearMonth . str_pad((string) $counter, 2, '0', STR_PAD_LEFT));

                $totalMembers = 0;
                $status = $form['result'] === 'Complete' ? 'Completed' : 'Pending';
                $encoderName = (string) $user['username'];
                $dateEncoded = date('Y-m-d');

                $pdo->beginTransaction();

                $sessionStmt = $pdo->prepare(
                    'INSERT INTO survey_session (
                        household_number, province, city_municipality, barangay, barangay_id,
                        address, respondent_name, household_head, total_members, visit_number,
                        visit_date, start_time, end_time, result, next_visit_date,
                        interviewer_name, supervisor_name, date_encoded, encoder_name, encoding_supervisor
                    ) VALUES (
                        :household_number, :province, :city_municipality, :barangay, :barangay_id,
                        :address, :respondent_name, :household_head, :total_members, :visit_number,
                        :visit_date, :start_time, :end_time, :result, :next_visit_date,
                        :interviewer_name, :supervisor_name, :date_encoded, :encoder_name, :encoding_supervisor
                    )'
                );

                $sessionStmt->execute([
                    ':household_number' => $householdNumber,
                    ':province' => $province,
                    ':city_municipality' => $municipality,
                    ':barangay' => $barangayName,
                    ':barangay_id' => $barangayId,
                    ':address' => $form['address'],
                    ':respondent_name' => $form['respondent_name'],
                    ':household_head' => $form['household_head'],
                    ':total_members' => $totalMembers,
                    ':visit_number' => (int) $form['visit_number'],
                    ':visit_date' => $form['visit_date'],
                    ':start_time' => $form['start_time'] !== '' ? $form['start_time'] : null,
                    ':end_time' => $form['end_time'] !== '' ? $form['end_time'] : null,
                    ':result' => $form['result'],
                    ':next_visit_date' => $form['next_visit_date'] !== '' ? $form['next_visit_date'] : null,
                    ':interviewer_name' => $form['interviewer_name'],
                    ':supervisor_name' => $form['supervisor_name'] !== '' ? $form['supervisor_name'] : null,
                    ':date_encoded' => $dateEncoded,
                    ':encoder_name' => $encoderName,
                    ':encoding_supervisor' => $form['encoding_supervisor'] !== '' ? $form['encoding_supervisor'] : null,
                ]);

                $householdStmt = $pdo->prepare(
                    'INSERT INTO households (barangay_id, total_members, status) VALUES (:barangay_id, :total_members, :status)'
                );
                $householdStmt->execute([
                    ':barangay_id' => $barangayId,
                    ':total_members' => $totalMembers,
                    ':status' => $status,
                ]);

                $pdo->commit();

                log_activity(
                    (int) $user['admin_id'],
                    (string) $user['username'],
                    'add_household',
                    'Household #' . str_pad((string) $householdNumber, 6, '0', STR_PAD_LEFT) . ' in ' . $barangayName
                );

                header('Location: ' . app_url('pages/household_list.php?saved=' . $householdNumber));
                exit;
            }
        }

        $households = loadHouseholdList($pdo, $barangayId);
    }
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $formErrors[] = 'Unable to save household. Please try again.';
        $openModal = true;
    }
    $households = [];
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
    <?php require __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="main">
      <?php require __DIR__ . '/../includes/topbar.php'; ?>

      <main class="content">
        <div class="hh-page">
          <?php if ($savedHousehold !== ''): ?>
            <div class="admin-alert ok" role="status">
              Household registered successfully — <strong>H<?php echo htmlspecialchars($savedHousehold); ?></strong>
            </div>
          <?php endif; ?>

          <div class="hh-context-card">
            <div class="hh-context-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <div>
              <h1 class="hh-context-barangay">Barangay <?php echo htmlspecialchars($barangayName ?: '—'); ?></h1>
            </div>
          </div>

          <section class="hh-panel">
            <div class="hh-panel-head">
              <div class="hh-panel-title">
                <span class="hh-panel-title-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24"><path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/></svg>
                </span>
                <h2>Household List</h2>
              </div>
              <button type="button" class="hh-add-btn" id="btn-open-register" <?php echo $barangayId <= 0 ? 'disabled' : ''; ?>>
                <svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                Add Household
              </button>
            </div>

            <div class="hh-table-wrap">
              <table class="hh-table" id="household-table">
                <thead>
                  <tr>
                    <th><button type="button" class="hh-sort" data-sort="number">Number <span class="hh-sort-icon" aria-hidden="true">↕</span></button></th>
                    <th><button type="button" class="hh-sort" data-sort="barangay">Barangay <span class="hh-sort-icon" aria-hidden="true">↕</span></button></th>
                    <th><button type="button" class="hh-sort" data-sort="address">Address <span class="hh-sort-icon" aria-hidden="true">↕</span></button></th>
                    <th><button type="button" class="hh-sort" data-sort="head">Household Head <span class="hh-sort-icon" aria-hidden="true">↕</span></button></th>
                    <th><button type="button" class="hh-sort" data-sort="respondent">Respondent <span class="hh-sort-icon" aria-hidden="true">↕</span></button></th>
                    <th><button type="button" class="hh-sort" data-sort="members">Total Members <span class="hh-sort-icon" aria-hidden="true">↕</span></button></th>
                    <th class="hh-col-actions">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($barangayId <= 0): ?>
                    <tr>
                      <td colspan="7" class="hh-empty">Your account is not linked to a barangay.</td>
                    </tr>
                  <?php elseif (empty($households)): ?>
                    <tr>
                      <td colspan="7" class="hh-empty">No households registered yet. Click <strong>Add Household</strong> to create one.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($households as $hh): ?>
                      <?php
                        $hhNum = (int) ($hh['household_number'] ?? 0);
                        $hhLabel = 'H' . $hhNum;
                        $sessionId = (int) ($hh['session_id'] ?? 0);
                      ?>
                      <tr class="household-row"
                          data-number="<?php echo htmlspecialchars($hhLabel); ?>"
                          data-barangay="<?php echo htmlspecialchars(strtolower((string) ($hh['barangay'] ?? $barangayName))); ?>"
                          data-address="<?php echo htmlspecialchars(strtolower((string) ($hh['address'] ?? ''))); ?>"
                          data-head="<?php echo htmlspecialchars(strtolower((string) ($hh['household_head'] ?? ''))); ?>"
                          data-respondent="<?php echo htmlspecialchars(strtolower((string) ($hh['respondent_name'] ?? ''))); ?>"
                          data-members="<?php echo (int) ($hh['total_members'] ?? 0); ?>">
                        <td class="hh-col-number"><?php echo htmlspecialchars($hhLabel); ?></td>
                        <td><?php echo htmlspecialchars((string) ($hh['barangay'] ?? $barangayName)); ?></td>
                        <td><?php echo htmlspecialchars((string) ($hh['address'] ?? '—')); ?></td>
                        <td><?php echo htmlspecialchars((string) ($hh['household_head'] ?? '—')); ?></td>
                        <td><?php echo htmlspecialchars((string) ($hh['respondent_name'] ?? '—')); ?></td>
                        <td><?php echo number_format((int) ($hh['total_members'] ?? 0)); ?></td>
                        <td class="hh-col-actions">
                          <div class="hh-row-actions">
                            <a class="hh-btn hh-btn-primary" href="<?php echo htmlspecialchars(app_url('pages/registration/view_members.php?session_id=' . $sessionId)); ?>">View Details</a>
                            <a class="hh-btn hh-btn-secondary" href="<?php echo htmlspecialchars(app_url('pages/registration/register_member.php?session_id=' . $sessionId)); ?>">Register Members</a>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </section>
        </div>
      </main>
    </div>
  </div>

  <div class="admin-modal-backdrop" id="register-modal" hidden>
    <div class="admin-modal hh-register-modal" role="dialog" aria-modal="true" aria-labelledby="register-modal-title">
      <div class="admin-modal-head hh-modal-head">
        <div class="hh-modal-head-main">
          <span class="hh-modal-head-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
          </span>
          <div>
            <p class="admin-modal-kicker">Household registration</p>
            <h3 id="register-modal-title">Add Household</h3>
            <p class="admin-modal-sub">Encode household and visit details for <strong><?php echo htmlspecialchars($barangayName); ?></strong>.</p>
          </div>
        </div>
        <button type="button" class="admin-modal-close" id="modal-close" aria-label="Close">
          <svg viewBox="0 0 24 24"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>
        </button>
      </div>
      <div class="admin-modal-body">
        <?php require __DIR__ . '/registration/household_register_form.php'; ?>
      </div>
    </div>
  </div>

  <?php require __DIR__ . '/../includes/scripts.php'; ?>
  <script>
    (function () {
      var modal = document.getElementById('register-modal');
      var openBtn = document.getElementById('btn-open-register');
      var closeBtn = document.getElementById('modal-close');
      var cancelBtn = document.getElementById('modal-cancel');
      var shouldOpen = <?php echo $openModal ? 'true' : 'false'; ?>;

      function openModal() {
        if (!modal) return;
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        var first = document.getElementById('address');
        if (first) first.focus();
      }

      function closeModal() {
        if (!modal) return;
        modal.hidden = true;
        document.body.style.overflow = '';
      }

      if (openBtn) openBtn.addEventListener('click', openModal);
      if (closeBtn) closeBtn.addEventListener('click', closeModal);
      if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

      if (modal) {
        modal.addEventListener('click', function (e) {
          if (e.target === modal) closeModal();
        });
      }

      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal && !modal.hidden) closeModal();
      });

      if (shouldOpen) openModal();

      var result = document.getElementById('result');
      var nextVisit = document.getElementById('next_visit_date');
      var nextWrap = document.getElementById('next-visit-wrap');

      function syncNextVisit() {
        if (!result || !nextVisit) return;
        var incomplete = result.value === 'Incomplete';
        nextVisit.required = incomplete;
        if (nextWrap) nextWrap.style.opacity = incomplete ? '1' : '0.85';
      }

      if (result) {
        result.addEventListener('change', syncNextVisit);
        syncNextVisit();
      }

      var autofillBtn = document.getElementById('btn-autofill');
      var autofillToast = document.getElementById('autofill-toast');

      if (autofillBtn) {
        function randInt(min, max) { return Math.floor(Math.random() * (max - min + 1)) + min; }
        function pick(arr) { return arr[randInt(0, arr.length - 1)]; }
        function randomName() {
          var first = ['Juan', 'Maria', 'Pedro', 'Ana', 'Jose', 'Rosa', 'Carlos', 'Sofia'];
          var last = ['Santos', 'Reyes', 'Cruz', 'Garcia', 'Mendoza', 'Torres'];
          return pick(first) + ' ' + pick(last);
        }
        function formatDate(d) {
          return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }
        function randomDate(daysAgo) {
          var d = new Date(); d.setDate(d.getDate() - daysAgo); return formatDate(d);
        }
        function futureDate(daysAhead) {
          var d = new Date(); d.setDate(d.getDate() + daysAhead); return formatDate(d);
        }
        function randomTime(baseHour) {
          return String(randInt(baseHour, baseHour + 2)).padStart(2, '0') + ':' + String(randInt(0, 59)).padStart(2, '0');
        }
        function setField(id, value) {
          var el = document.getElementById(id);
          if (!el) return;
          el.value = value;
          if (el.tagName === 'SELECT') el.dispatchEvent(new Event('change', { bubbles: true }));
        }

        autofillBtn.addEventListener('click', function () {
          var barangayEl = document.getElementById('barangay');
          var barangay = barangayEl ? (barangayEl.textContent || barangayEl.value || '').trim() : 'Poblacion';
          var visitResult = pick(['Complete', 'Complete', 'Incomplete', 'Refused']);
          setField('address', randInt(1, 999) + ' Rizal St., ' + barangay);
          setField('respondent_name', randomName());
          setField('household_head', randomName());
          setField('visit_number', String(randInt(1, 3)));
          setField('visit_date', randomDate(randInt(0, 14)));
          setField('start_time', randomTime(8));
          setField('end_time', randomTime(10));
          setField('result', visitResult);
          setField('next_visit_date', visitResult === 'Incomplete' ? futureDate(randInt(1, 7)) : '');
          setField('interviewer_name', randomName());
          setField('supervisor_name', randomName());
          setField('encoding_supervisor', randomName());
          syncNextVisit();
          if (autofillToast) {
            autofillToast.hidden = false;
            clearTimeout(autofillToast._t);
            autofillToast._t = setTimeout(function () { autofillToast.hidden = true; }, 3200);
          }
        });
      }


      // Column sorting
      var sortBtns = document.querySelectorAll('.hh-sort');
      var tbody = document.querySelector('#household-table tbody');
      var currentSort = { key: null, asc: true };

      sortBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
          var key = btn.getAttribute('data-sort');
          if (!key || !tbody) return;
          var dataKey = 'data-' + (key === 'head' ? 'head' : key === 'number' ? 'number' : key);
          if (currentSort.key === key) currentSort.asc = !currentSort.asc;
          else { currentSort.key = key; currentSort.asc = true; }

          var trs = Array.prototype.slice.call(tbody.querySelectorAll('.household-row'));
          trs.sort(function (a, b) {
            var av = a.getAttribute(dataKey) || '';
            var bv = b.getAttribute(dataKey) || '';
            if (key === 'members') {
              av = parseInt(av, 10) || 0;
              bv = parseInt(bv, 10) || 0;
              return currentSort.asc ? av - bv : bv - av;
            }
            av = av.toLowerCase();
            bv = bv.toLowerCase();
            if (av < bv) return currentSort.asc ? -1 : 1;
            if (av > bv) return currentSort.asc ? 1 : -1;
            return 0;
          });

          trs.forEach(function (tr) { tbody.appendChild(tr); });

          sortBtns.forEach(function (b) { b.classList.remove('is-active'); });
          btn.classList.add('is-active');
        });
      });

    })();
  </script>
</body>
</html>
