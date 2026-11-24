<?php
require_once __DIR__ . '/../../login.php';

$user = require_role('super_admin');
$page_title = 'Municipality Details';
$role_label = 'Super Administrator';
$user_name = user_display_name($user);
$user_initials = user_initials($user);
$logo = app_url('assets/img/system-logo.jpg');
$css = app_url('assets/css/dashboard.css');
$logout = app_url('logout.php');
$activeNav = 'city_statistics';

$cityId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$activeTab = isset($_GET['tab']) ? (string) $_GET['tab'] : 'barangay-list';
$allowedTabs = ['barangay-list', 'statistics-report', 'household-list'];
if (!in_array($activeTab, $allowedTabs, true)) {
    $activeTab = 'barangay-list';
}

$city = null;
$barangays = [];
$households = [];
$maleResidents = 0;
$femaleResidents = 0;
$seniorResidents = 0;
$ageBands = [
    '0-4', '5-9', '10-14', '15-19', '20-24', '25-29', '30-34', '35-39',
    '40-44', '45-49', '50-54', '55-59', '60-64', '65-69', '70-74', '75-79', '80+',
];
$pyramid = [];
foreach ($ageBands as $band) {
    $pyramid[$band] = ['male' => 0, 'female' => 0];
}
$pyramidMax = 1;

try {
    $pdo = db();

    $stmt = $pdo->prepare(
        "SELECT
            cm.city_municipality_id,
            cm.name AS municipality,
            cm.type,
            cm.province,
            COUNT(DISTINCT b.barangay_id) AS barangay_count,
            COUNT(DISTINCT h.id) AS household_count,
            COALESCE(SUM(h.total_members), 0) AS resident_count
         FROM city_municipality cm
         LEFT JOIN barangay b ON b.city_municipality_id = cm.city_municipality_id
         LEFT JOIN households h ON h.barangay_id = b.barangay_id
         WHERE cm.city_municipality_id = :id
         GROUP BY cm.city_municipality_id, cm.name, cm.type, cm.province
         LIMIT 1"
    );
    $stmt->execute([':id' => $cityId]);
    $city = $stmt->fetch();

    if ($city) {
        $bStmt = $pdo->prepare(
            "SELECT
                b.barangay_id,
                b.barangay,
                COUNT(DISTINCT h.id) AS household_count,
                COALESCE(SUM(h.total_members), 0) AS resident_count
             FROM barangay b
             LEFT JOIN households h ON h.barangay_id = b.barangay_id
             WHERE b.city_municipality_id = :id
             GROUP BY b.barangay_id, b.barangay
             ORDER BY b.barangay ASC"
        );
        $bStmt->execute([':id' => $cityId]);
        $barangays = $bStmt->fetchAll();

        $hStmt = $pdo->prepare(
            "SELECT
                h.id,
                h.total_members,
                h.status,
                h.created_at,
                b.barangay_id,
                b.barangay
             FROM households h
             INNER JOIN barangay b ON b.barangay_id = h.barangay_id
             WHERE b.city_municipality_id = :id
             ORDER BY b.barangay ASC, h.created_at DESC, h.id DESC"
        );
        $hStmt->execute([':id' => $cityId]);
        $households = $hStmt->fetchAll();

        // Demographics from register_members (sex: 1=Male, 2=Female; senior: age >= 60)
        $demoStmt = $pdo->prepare(
            "SELECT
                SUM(CASE WHEN rm.sex = 1 THEN 1 ELSE 0 END) AS male_count,
                SUM(CASE WHEN rm.sex = 2 THEN 1 ELSE 0 END) AS female_count,
                SUM(
                    CASE
                        WHEN rm.age >= 60 THEN 1
                        WHEN LOWER(TRIM(COALESCE(rm.q31_registered_senior, ''))) IN ('yes', '1', 'registered', 'oo') THEN 1
                        ELSE 0
                    END
                ) AS senior_count
             FROM register_members rm
             INNER JOIN survey_session ss ON ss.session_id = rm.session_id
             INNER JOIN barangay b ON b.barangay_id = ss.barangay_id
             WHERE b.city_municipality_id = :id"
        );
        $demoStmt->execute([':id' => $cityId]);
        $demo = $demoStmt->fetch() ?: [];
        $maleResidents = (int) ($demo['male_count'] ?? 0);
        $femaleResidents = (int) ($demo['female_count'] ?? 0);
        $seniorResidents = (int) ($demo['senior_count'] ?? 0);

        $ageStmt = $pdo->prepare(
            "SELECT rm.age, rm.sex
             FROM register_members rm
             INNER JOIN survey_session ss ON ss.session_id = rm.session_id
             INNER JOIN barangay b ON b.barangay_id = ss.barangay_id
             WHERE b.city_municipality_id = :id
               AND rm.sex IN (1, 2)
               AND rm.age IS NOT NULL
               AND rm.age >= 0"
        );
        $ageStmt->execute([':id' => $cityId]);
        foreach ($ageStmt->fetchAll() as $row) {
            $age = (int) $row['age'];
            $sex = (int) $row['sex'];
            if ($age >= 80) {
                $band = '80+';
            } else {
                $start = intdiv($age, 5) * 5;
                $band = $start . '-' . ($start + 4);
            }
            if (!isset($pyramid[$band])) {
                continue;
            }
            if ($sex === 1) {
                $pyramid[$band]['male']++;
            } else {
                $pyramid[$band]['female']++;
            }
        }
        foreach ($pyramid as $counts) {
            $pyramidMax = max($pyramidMax, (int) $counts['male'], (int) $counts['female']);
        }
    }
} catch (Throwable $e) {
    $city = null;
    $barangays = [];
    $households = [];
}

if (!$city) {
    redirect(app_url('pages/city-municipality/statistics.php'));
}

$pageHeading = 'City/Municipality';
$pageSubheading = $city['municipality'] . ' · Province of Siquijor';
$backUrl = 'statistics.php';
$backLabel = 'Back';
$tabBase = 'view.php?id=' . $cityId;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($city['municipality']); ?> â€” RBIM</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo htmlspecialchars($css); ?>">
</head>
<body>
  <div class="overlay" id="sidebar-overlay" hidden></div>

  <div class="app">
    <?php require __DIR__ . '/../../includes/sidebar.php'; ?>

    <div class="main">
      <?php require __DIR__ . '/../../includes/topbar.php'; ?>

      <main class="content">
        <div class="view-tabs" role="tablist" aria-label="Municipality sections">
          <button type="button" class="view-tab<?php echo $activeTab === 'barangay-list' ? ' active' : ''; ?>" role="tab" data-tab="barangay-list" aria-selected="<?php echo $activeTab === 'barangay-list' ? 'true' : 'false'; ?>">
            Barangay List
          </button>
          <button type="button" class="view-tab<?php echo $activeTab === 'statistics-report' ? ' active' : ''; ?>" role="tab" data-tab="statistics-report" aria-selected="<?php echo $activeTab === 'statistics-report' ? 'true' : 'false'; ?>">
            Statistics Report
          </button>
          <button type="button" class="view-tab<?php echo $activeTab === 'household-list' ? ' active' : ''; ?>" role="tab" data-tab="household-list" aria-selected="<?php echo $activeTab === 'household-list' ? 'true' : 'false'; ?>">
            Barangay Household List
          </button>
        </div>

        <section class="tab-panel<?php echo $activeTab === 'barangay-list' ? ' active' : ''; ?>" id="tab-barangay-list" role="tabpanel" <?php echo $activeTab === 'barangay-list' ? '' : 'hidden'; ?>>
          <div class="mgmt-page">
            <div class="mgmt-search">
              <span class="mgmt-search-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
              </span>
              <input type="search" id="barangay-search" class="mgmt-search-input" placeholder="Search barangays..." autocomplete="off">
            </div>

            <div class="mgmt-grid" id="barangay-grid" aria-label="Barangay list">
              <?php if (empty($barangays)): ?>
                <p class="mgmt-empty" id="barangay-empty">No barangays found.</p>
              <?php else: ?>
                <?php foreach ($barangays as $brgy): ?>
                  <article class="mgmt-card" data-name="<?php echo htmlspecialchars(strtolower($brgy['barangay'])); ?>">
                    <div class="mgmt-card-logo">
                      <img src="<?php echo htmlspecialchars($logo); ?>" alt="" width="72" height="72">
                    </div>
                    <h3 class="mgmt-card-name"><?php echo htmlspecialchars($brgy['barangay']); ?></h3>
                    <p class="mgmt-card-meta">
                      <?php echo number_format((int) $brgy['household_count']); ?> households Â·
                      <?php echo number_format((int) $brgy['resident_count']); ?> residents
                    </p>
                    <a class="mgmt-view-btn" href="<?php echo htmlspecialchars(app_url('pages/barangay/view.php?id=' . (int) $brgy['barangay_id'])); ?>">
                      <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                      </svg>
                      View
                    </a>
                  </article>
                <?php endforeach; ?>
                <p class="mgmt-empty" id="barangay-empty" hidden>No barangays match your search.</p>
              <?php endif; ?>
            </div>
          </div>
        </section>

        <section class="tab-panel<?php echo $activeTab === 'statistics-report' ? ' active' : ''; ?>" id="tab-statistics-report" role="tabpanel" <?php echo $activeTab === 'statistics-report' ? '' : 'hidden'; ?>>
          <section class="stats-grid kpi-compact kpi-compact-6" aria-label="Municipality statistics">
            <article class="stat-card kpi-blue">
              <div class="stat-top">
                <div>
                  <p class="stat-label">Barangays</p>
                  <p class="stat-value"><?php echo number_format((int) $city['barangay_count']); ?></p>
                </div>
                <div class="stat-icon blue" aria-hidden="true">
                  <svg viewBox="0 0 24 24"><path d="M3 21h18"/><path d="M5 21V8l7-5 7 5v13"/><path d="M9 21v-6h6v6"/></svg>
                </div>
              </div>
            </article>
            <article class="stat-card kpi-red">
              <div class="stat-top">
                <div>
                  <p class="stat-label">Households</p>
                  <p class="stat-value"><?php echo number_format((int) $city['household_count']); ?></p>
                </div>
                <div class="stat-icon red" aria-hidden="true">
                  <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
                </div>
              </div>
            </article>
            <article class="stat-card kpi-gold">
              <div class="stat-top">
                <div>
                  <p class="stat-label">Residents</p>
                  <p class="stat-value"><?php echo number_format((int) $city['resident_count']); ?></p>
                </div>
                <div class="stat-icon gold" aria-hidden="true">
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

          <?php
            $pyramidTotal = $maleResidents + $femaleResidents;
            $maleShare = $pyramidTotal > 0 ? round(($maleResidents / $pyramidTotal) * 100, 1) : 0;
            $femaleShare = $pyramidTotal > 0 ? round(($femaleResidents / $pyramidTotal) * 100, 1) : 0;
            $pyramidRows = array_reverse($pyramid, true);
          ?>
          <section class="panel pyramid-panel">
            <div class="panel-header">
              <div>
                <h2>Population pyramid</h2>
                <p>Total residents by age group and sex · <?php echo htmlspecialchars($city['municipality']); ?></p>
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

              <div class="pyramid-chart" role="img" aria-label="Population pyramid showing male and female residents by age group">
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

              <div class="pyramid-axis" aria-hidden="true">
                <div class="pyramid-axis-side">
                  <span><?php echo number_format($pyramidMax); ?></span>
                  <span><?php echo number_format((int) round($pyramidMax / 2)); ?></span>
                  <span>0</span>
                </div>
                <div class="pyramid-axis-gap"></div>
                <div class="pyramid-axis-side right">
                  <span>0</span>
                  <span><?php echo number_format((int) round($pyramidMax / 2)); ?></span>
                  <span><?php echo number_format($pyramidMax); ?></span>
                </div>
              </div>
            </div>
          </section>
        </section>

        <section class="tab-panel<?php echo $activeTab === 'household-list' ? ' active' : ''; ?>" id="tab-household-list" role="tabpanel" <?php echo $activeTab === 'household-list' ? '' : 'hidden'; ?>>
          <div class="mgmt-page">
            <div class="mgmt-search">
              <span class="mgmt-search-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
              </span>
              <input type="search" id="household-search" class="mgmt-search-input" placeholder="Search households by barangay or status..." autocomplete="off">
            </div>

            <section class="panel">
              <div class="panel-header">
                <div>
                  <h2>Barangay household list</h2>
                  <p><?php echo number_format(count($households)); ?> households in <?php echo htmlspecialchars($city['municipality']); ?></p>
                </div>
              </div>
              <div class="table-wrap">
                <table class="data" id="household-table">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Household ID</th>
                      <th>Barangay</th>
                      <th>Members</th>
                      <th>Status</th>
                      <th>Date Created</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($households)): ?>
                      <tr id="household-empty-row">
                        <td colspan="6" style="color:var(--muted);">No household records found for this municipality.</td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($households as $i => $hh): ?>
                        <tr class="household-row" data-search="<?php echo htmlspecialchars(strtolower($hh['barangay'] . ' ' . $hh['status'] . ' ' . $hh['id'])); ?>">
                          <td><?php echo $i + 1; ?></td>
                          <td><strong>HH-<?php echo str_pad((string) $hh['id'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                          <td><?php echo htmlspecialchars($hh['barangay']); ?></td>
                          <td><?php echo number_format((int) $hh['total_members']); ?></td>
                          <td>
                            <span class="pill <?php echo ($hh['status'] ?? '') === 'Completed' ? 'success' : 'warning'; ?>">
                              <?php echo htmlspecialchars($hh['status'] ?? 'Pending'); ?>
                            </span>
                          </td>
                          <td><?php echo htmlspecialchars(date('M d, Y', strtotime((string) $hh['created_at']))); ?></td>
                        </tr>
                      <?php endforeach; ?>
                      <tr id="household-empty-row" hidden>
                        <td colspan="6" style="color:var(--muted);">No households match your search.</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </section>
          </div>
        </section>
      </main>
    </div>
  </div>

  <?php require __DIR__ . '/../../includes/scripts.php'; ?>
  <script>
    (function () {
      var tabs = document.querySelectorAll('.view-tab');
      var panels = {
        'barangay-list': document.getElementById('tab-barangay-list'),
        'statistics-report': document.getElementById('tab-statistics-report'),
        'household-list': document.getElementById('tab-household-list')
      };
      var tabBase = <?php echo json_encode($tabBase); ?>;

      function activateTab(tabName) {
        tabs.forEach(function (tab) {
          var isActive = tab.getAttribute('data-tab') === tabName;
          tab.classList.toggle('active', isActive);
          tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        Object.keys(panels).forEach(function (key) {
          var panel = panels[key];
          if (!panel) return;
          var show = key === tabName;
          panel.classList.toggle('active', show);
          panel.hidden = !show;
        });

        if (window.history && window.history.replaceState) {
          window.history.replaceState(null, '', tabBase + '&tab=' + encodeURIComponent(tabName));
        }
      }

      tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
          activateTab(tab.getAttribute('data-tab'));
        });
      });

      var input = document.getElementById('barangay-search');
      var grid = document.getElementById('barangay-grid');
      if (input && grid) {
        var cards = Array.prototype.slice.call(grid.querySelectorAll('.mgmt-card'));
        var empty = document.getElementById('barangay-empty');

        function filterCards() {
          var q = (input.value || '').trim().toLowerCase();
          var visible = 0;
          cards.forEach(function (card) {
            var name = (card.getAttribute('data-name') || '').toLowerCase();
            var titleEl = card.querySelector('.mgmt-card-name');
            var title = titleEl ? (titleEl.textContent || '').toLowerCase() : '';
            var show = !q || name.indexOf(q) !== -1 || title.indexOf(q) !== -1;
            card.classList.toggle('is-hidden', !show);
            card.hidden = !show;
            if (show) visible += 1;
          });
          if (empty) {
            empty.hidden = visible > 0;
            empty.classList.toggle('is-hidden', visible > 0);
          }
        }

        input.addEventListener('input', filterCards);
        input.addEventListener('keyup', filterCards);
        input.addEventListener('search', filterCards);
        input.addEventListener('paste', function () { setTimeout(filterCards, 0); });
      }

      var hhInput = document.getElementById('household-search');
      var hhRows = Array.prototype.slice.call(document.querySelectorAll('.household-row'));
      var hhEmpty = document.getElementById('household-empty-row');
      if (hhInput) {
        function filterHouseholds() {
          var q = (hhInput.value || '').trim().toLowerCase();
          var visible = 0;
          hhRows.forEach(function (row) {
            var hay = (row.getAttribute('data-search') || '').toLowerCase();
            var show = !q || hay.indexOf(q) !== -1;
            row.hidden = !show;
            if (show) visible += 1;
          });
          if (hhEmpty && hhRows.length) hhEmpty.hidden = visible > 0;
        }
        hhInput.addEventListener('input', filterHouseholds);
        hhInput.addEventListener('keyup', filterHouseholds);
        hhInput.addEventListener('search', filterHouseholds);
        hhInput.addEventListener('paste', function () { setTimeout(filterHouseholds, 0); });
      }
    })();
  </script>
</body>
</html>
