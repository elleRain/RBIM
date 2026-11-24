<?php
require_once __DIR__ . '/../../login.php';

$user = require_role('super_admin', 'muni_admin');
$role = (string) ($user['role'] ?? '');
$isMuniAdmin = $role === 'muni_admin';
$muniId = (int) ($user['city_municipality_id'] ?? 0);
$page_title = 'Barangay Details';
$role_label = role_label($role);
$user_name = user_display_name($user);
$user_initials = user_initials($user);
$logo = app_url('assets/img/system-logo.jpg');
$css = app_url('assets/css/dashboard.css');
$logout = app_url('logout.php');
$activeNav = 'barangay_statistics';

$barangayId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$barangay = null;

try {
    $pdo = db();
    $stmt = $pdo->prepare(
        "SELECT
            b.barangay_id,
            b.barangay,
            cm.city_municipality_id,
            cm.name AS municipality,
            cm.province,
            COUNT(DISTINCT h.id) AS household_count,
            COALESCE(SUM(h.total_members), 0) AS resident_count
         FROM barangay b
         INNER JOIN city_municipality cm ON cm.city_municipality_id = b.city_municipality_id
         LEFT JOIN households h ON h.barangay_id = b.barangay_id
         WHERE b.barangay_id = :id
         GROUP BY b.barangay_id, b.barangay, cm.city_municipality_id, cm.name, cm.province
         LIMIT 1"
    );
    $stmt->execute([':id' => $barangayId]);
    $barangay = $stmt->fetch();
} catch (Throwable $e) {
    $barangay = null;
}

if (!$barangay) {
    redirect(app_url('pages/barangay/statistics.php'));
}

if ($isMuniAdmin && $muniId > 0 && (int) ($barangay['city_municipality_id'] ?? 0) !== $muniId) {
    redirect(app_url('pages/barangay/statistics.php'));
}

$pageHeading = 'Barangay Details';
$pageSubheading = '';
$backUrl = 'statistics.php';
$backLabel = 'Back to Barangay Statistics';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($barangay['barangay']); ?> — RBIM</title>
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
        <section class="stats-grid kpi-3" aria-label="Barangay totals">
          <article class="stat-card kpi-blue">
            <div class="stat-top">
              <div>
                <p class="stat-label">Municipality</p>
                <p class="stat-value" style="font-size:1.35rem;"><?php echo htmlspecialchars($barangay['municipality']); ?></p>
              </div>
              <div class="stat-icon blue" aria-hidden="true">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
              </div>
            </div>
            <p class="stat-foot">Parent LGU</p>
          </article>
          <article class="stat-card kpi-red">
            <div class="stat-top">
              <div>
                <p class="stat-label">Households</p>
                <p class="stat-value"><?php echo number_format((int) $barangay['household_count']); ?></p>
              </div>
              <div class="stat-icon red" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
              </div>
            </div>
            <p class="stat-foot">Household profiles</p>
          </article>
          <article class="stat-card kpi-gold">
            <div class="stat-top">
              <div>
                <p class="stat-label">Residents</p>
                <p class="stat-value"><?php echo number_format((int) $barangay['resident_count']); ?></p>
              </div>
              <div class="stat-icon gold" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              </div>
            </div>
            <p class="stat-foot">Members on record</p>
          </article>
        </section>
      </main>
    </div>
  </div>

  <?php require __DIR__ . '/../../includes/scripts.php'; ?>
</body>
</html>
