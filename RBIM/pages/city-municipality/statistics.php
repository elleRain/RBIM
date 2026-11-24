<?php
require_once __DIR__ . '/../../login.php';

$user = require_role('super_admin');
$page_title = 'City/Municipality Statistics';
$role_label = 'Super Administrator';
$user_name = user_display_name($user);
$user_initials = user_initials($user);
$logo = app_url('assets/img/system-logo.jpg');
$css = app_url('assets/css/dashboard.css');
$logout = app_url('logout.php');
$activeNav = 'city_statistics';
$pageHeading = 'City/Municipality Statistics';
$pageSubheading = 'Province of Siquijor · CPD – Negros Island Region';

$municipalityLogos = [
    'enrique villanueva' => 'Enrique Villanueva.jpg',
    'larena' => 'Larena.jpg',
    'lazi' => 'lazi.jpg',
    'maria' => 'maria.jpg',
    'san juan' => 'sanjuan.jpg',
    'siquijor' => 'Siquijor.png',
];

$cityStats = [];

try {
    $pdo = db();
    $stmt = $pdo->query(
        "SELECT
            cm.city_municipality_id,
            cm.name AS municipality,
            cm.type,
            COUNT(DISTINCT b.barangay_id) AS barangay_count,
            COUNT(DISTINCT h.id) AS household_count,
            COALESCE(SUM(h.total_members), 0) AS resident_count
         FROM city_municipality cm
         LEFT JOIN barangay b ON b.city_municipality_id = cm.city_municipality_id
         LEFT JOIN households h ON h.barangay_id = b.barangay_id
         GROUP BY cm.city_municipality_id, cm.name, cm.type
         ORDER BY cm.name ASC"
    );
    $cityStats = $stmt->fetchAll();
} catch (Throwable $e) {
    $cityStats = [];
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
            <div class="mgmt-search">
              <span class="mgmt-search-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
              </span>
              <input
                type="search"
                id="city-search"
                class="mgmt-search-input"
                placeholder="Search cities / municipalities..."
                autocomplete="off"
              >
            </div>
            <button type="button" class="btn btn-primary mgmt-add-btn" id="add-city-btn">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
              Add New Municipality
            </button>
          </div>

          <div class="mgmt-grid" id="city-grid" aria-label="City and municipality list">
            <?php if (empty($cityStats)): ?>
              <p class="mgmt-empty" id="city-empty">No city or municipality records found.</p>
            <?php else: ?>
              <?php foreach ($cityStats as $city): ?>
                <?php
                  $muniKey = strtolower(trim((string) $city['municipality']));
                  $logoFile = $municipalityLogos[$muniKey] ?? null;
                  $cityLogo = $logoFile
                      ? app_url('assets/img/Munacipality/' . rawurlencode($logoFile))
                      : $logo;
                ?>
                <article
                  class="mgmt-card"
                  data-name="<?php echo htmlspecialchars(strtolower($city['municipality'])); ?>"
                >
                  <div class="mgmt-card-logo">
                    <img
                      src="<?php echo htmlspecialchars($cityLogo); ?>"
                      alt="<?php echo htmlspecialchars($city['municipality']); ?> seal"
                      width="96"
                      height="96"
                    >
                  </div>
                  <h3 class="mgmt-card-name"><?php echo htmlspecialchars($city['municipality']); ?></h3>
                  <p class="mgmt-card-meta">
                    <?php echo number_format((int) $city['barangay_count']); ?> barangays
                  </p>
                  <a
                    class="mgmt-view-btn"
                    href="view.php?id=<?php echo (int) $city['city_municipality_id']; ?>"
                  >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                    View
                  </a>
                </article>
              <?php endforeach; ?>
              <p class="mgmt-empty" id="city-empty" hidden>No municipalities match your search.</p>
            <?php endif; ?>
          </div>
        </section>
      </main>
    </div>
  </div>

  <?php require __DIR__ . '/../../includes/scripts.php'; ?>
  <script>
    (function () {
      var input = document.getElementById('city-search');
      var cards = document.querySelectorAll('.mgmt-card');
      var empty = document.getElementById('city-empty');
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

        if (empty) {
          empty.hidden = visible > 0;
        }
      });
    })();
  </script>
</body>
</html>
