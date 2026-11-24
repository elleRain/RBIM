<?php
require_once __DIR__ . '/../../login.php';

$user = require_role('super_admin', 'muni_admin');
$role = (string) ($user['role'] ?? '');
$isSuperAdmin = $role === 'super_admin';
$isMuniAdmin = $role === 'muni_admin';
$muniId = (int) ($user['city_municipality_id'] ?? 0);
$municipality = (string) ($user['municipality'] ?? '');

$page_title = 'Barangay Statistics';
$role_label = role_label($role);
$user_name = user_display_name($user);
$user_initials = user_initials($user);
$logo = app_url('assets/img/system-logo.jpg');
$css = app_url('assets/css/dashboard.css');
$logout = app_url('logout.php');
$activeNav = 'barangay_statistics';
$pageHeading = 'Barangay Statistics';
$pageSubheading = $isMuniAdmin
    ? ($municipality !== '' ? $municipality . ' · CPD – Negros Island Region' : 'CPD – Negros Island Region')
    : 'Province of Siquijor · CPD – Negros Island Region';

$barangays = [];
$municipalities = [];
$filterCityId = $isMuniAdmin ? $muniId : (isset($_GET['city_id']) ? (int) $_GET['city_id'] : 0);

try {
    $pdo = db();

    if ($isMuniAdmin && $muniId > 0) {
        $muniNameStmt = $pdo->prepare('SELECT name FROM city_municipality WHERE city_municipality_id = :id LIMIT 1');
        $muniNameStmt->execute([':id' => $muniId]);
        $dbMunicipality = $muniNameStmt->fetchColumn();
        if (is_string($dbMunicipality) && $dbMunicipality !== '') {
            $municipality = $dbMunicipality;
        }
        $filterCityId = $muniId;
    } else {
        $municipalities = $pdo->query(
            'SELECT city_municipality_id, name
             FROM city_municipality
             ORDER BY name ASC'
        )->fetchAll();
    }

    if ($filterCityId > 0) {
        $stmt = $pdo->prepare(
            "SELECT
                b.barangay_id,
                b.barangay,
                cm.city_municipality_id,
                cm.name AS municipality,
                COUNT(DISTINCT h.id) AS household_count,
                COALESCE(SUM(h.total_members), 0) AS resident_count
             FROM barangay b
             INNER JOIN city_municipality cm ON cm.city_municipality_id = b.city_municipality_id
             LEFT JOIN households h ON h.barangay_id = b.barangay_id
             WHERE b.city_municipality_id = :city_id
             GROUP BY b.barangay_id, b.barangay, cm.city_municipality_id, cm.name
             ORDER BY b.barangay ASC"
        );
        $stmt->execute([':city_id' => $filterCityId]);
        $barangays = $stmt->fetchAll();
    } elseif ($isSuperAdmin) {
        $barangays = $pdo->query(
            "SELECT
                b.barangay_id,
                b.barangay,
                cm.city_municipality_id,
                cm.name AS municipality,
                COUNT(DISTINCT h.id) AS household_count,
                COALESCE(SUM(h.total_members), 0) AS resident_count
             FROM barangay b
             INNER JOIN city_municipality cm ON cm.city_municipality_id = b.city_municipality_id
             LEFT JOIN households h ON h.barangay_id = b.barangay_id
             GROUP BY b.barangay_id, b.barangay, cm.city_municipality_id, cm.name
             ORDER BY cm.name ASC, b.barangay ASC"
        )->fetchAll();
    }
} catch (Throwable $e) {
    $barangays = [];
    $municipalities = [];
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
</head>
<body>
  <div class="overlay" id="sidebar-overlay" hidden></div>

  <div class="app">
    <?php require __DIR__ . '/../../includes/sidebar.php'; ?>

    <div class="main">
      <?php require __DIR__ . '/../../includes/topbar.php'; ?>

      <main class="content">
        <section class="mgmt-page">
          <div class="mgmt-toolbar">
            <div class="mgmt-search" style="margin-bottom: 0; flex: 1;">
              <span class="mgmt-search-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
              </span>
              <input
                type="search"
                id="barangay-search"
                class="mgmt-search-input"
                placeholder="Search barangays..."
                autocomplete="off"
              >
            </div>
            <?php if ($isSuperAdmin): ?>
            <form method="get" class="mgmt-filter" action="statistics.php">
              <label for="city_id" class="visually-hidden">Filter by municipality</label>
              <select name="city_id" id="city_id" class="mgmt-select" onchange="this.form.submit()">
                <option value="0">All municipalities</option>
                <?php foreach ($municipalities as $mun): ?>
                  <option value="<?php echo (int) $mun['city_municipality_id']; ?>" <?php echo $filterCityId === (int) $mun['city_municipality_id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($mun['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </form>
            <button type="button" class="btn btn-primary mgmt-add-btn" id="add-barangay-btn">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
              Add New Barangay
            </button>
            <?php endif; ?>
          </div>

          <div class="mgmt-grid" id="barangay-grid" aria-label="Barangay list">
            <?php if (empty($barangays)): ?>
              <p class="mgmt-empty" id="barangay-empty">No barangay records found.</p>
            <?php else: ?>
              <?php foreach ($barangays as $brgy): ?>
                <article
                  class="mgmt-card"
                  data-name="<?php echo htmlspecialchars(strtolower($brgy['barangay'] . ' ' . $brgy['municipality'])); ?>"
                >
                  <div class="mgmt-card-logo">
                    <img src="<?php echo htmlspecialchars($logo); ?>" alt="" width="72" height="72">
                  </div>
                  <h3 class="mgmt-card-name"><?php echo htmlspecialchars($brgy['barangay']); ?></h3>
                  <span class="mgmt-badge">Active</span>
                  <?php if ($isSuperAdmin): ?>
                  <p class="mgmt-card-meta"><?php echo htmlspecialchars($brgy['municipality']); ?></p>
                  <?php endif; ?>
                  <a
                    class="mgmt-view-btn"
                    href="view.php?id=<?php echo (int) $brgy['barangay_id']; ?>"
                  >
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
        </section>
      </main>
    </div>
  </div>

  <?php require __DIR__ . '/../../includes/scripts.php'; ?>
  <script>
    (function () {
      var input = document.getElementById('barangay-search');
      var cards = document.querySelectorAll('.mgmt-card');
      var empty = document.getElementById('barangay-empty');
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
</body>
</html>
