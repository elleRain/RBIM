<?php
require_once __DIR__ . '/../login.php';

$user = require_login();
$role = (string) ($user['role'] ?? '');

if ($role !== 'super_admin' && $role !== 'muni_admin' && $role !== 'admin') {
    redirect(app_url('index.php'));
}

$isSuperAdmin = $role === 'super_admin';
$isMuniAdmin = $role === 'muni_admin';
$user_name = user_display_name($user);
$user_initials = user_initials($user);
$logo = app_url('assets/img/system-logo.jpg');
$css = app_url('assets/css/dashboard.css');
$logout = app_url('logout.php');
$activeNav = 'dashboard';

if ($isSuperAdmin) {
    $page_title = 'Super Admin Dashboard';
    $role_label = 'Super Administrator';
    $pageHeading = 'Dashboard';
    $pageSubheading = 'Province of Siquijor · CPD – Negros Island Region';
    $mapCss = app_url('maps/siquijor-map.css');
    $mapJs = app_url('maps/siquijor-map.js');
    $mapLeafletCss = app_url('maps/vendor/leaflet/leaflet.css');
    $mapLeafletJs = app_url('maps/vendor/leaflet/leaflet.js');
    $mapGeo = app_url('maps/siquijor-municipalities.geojson');
    $mapBarangayGeo = app_url('maps/siquijor-barangays.geojson');
    $mapGeoJsonInline = '{}';
    $mapBarangayGeoJsonInline = '{}';
    $geoFile = __DIR__ . '/../maps/siquijor-municipalities.geojson';
    $brgyGeoFile = __DIR__ . '/../maps/siquijor-barangays.geojson';
    if (is_file($geoFile)) {
        $raw = file_get_contents($geoFile);
        if ($raw !== false && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && !empty($decoded['features'])) {
                $mapGeoJsonInline = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }
    }
    if (is_file($brgyGeoFile)) {
        $raw = file_get_contents($brgyGeoFile);
        if ($raw !== false && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && !empty($decoded['features'])) {
                $mapBarangayGeoJsonInline = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }
    }
    if ($mapGeoJsonInline === false || $mapGeoJsonInline === null || $mapGeoJsonInline === '') {
        $mapGeoJsonInline = '{}';
    }
    if ($mapBarangayGeoJsonInline === false || $mapBarangayGeoJsonInline === null || $mapBarangayGeoJsonInline === '') {
        $mapBarangayGeoJsonInline = '{}';
    }
    $municipalityMapStats = [];
    $municipalityBarangayLists = [];

    try {
        $pdo = db();
        $totalCities = (int) $pdo->query('SELECT COUNT(*) FROM city_municipality')->fetchColumn();
        $totalBarangays = (int) $pdo->query('SELECT COUNT(*) FROM barangay')->fetchColumn();
        $totalHouseholds = (int) $pdo->query('SELECT COUNT(*) FROM households')->fetchColumn();
        $totalResidents = (int) $pdo->query('SELECT COUNT(*) FROM register_members')->fetchColumn();

        $munStatsStmt = $pdo->query(
            "SELECT
                cm.name AS municipality,
                COUNT(DISTINCT b.barangay_id) AS barangays,
                COUNT(DISTINCT h.id) AS households,
                (
                    SELECT COUNT(*)
                    FROM register_members rm
                    INNER JOIN survey_session ss ON ss.session_id = rm.session_id
                    INNER JOIN barangay b2 ON b2.barangay_id = ss.barangay_id
                    WHERE b2.city_municipality_id = cm.city_municipality_id
                ) AS residents
             FROM city_municipality cm
             LEFT JOIN barangay b ON b.city_municipality_id = cm.city_municipality_id
             LEFT JOIN households h ON h.barangay_id = b.barangay_id
             GROUP BY cm.city_municipality_id, cm.name
             ORDER BY cm.name ASC"
        );
        foreach ($munStatsStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $municipalityMapStats[(string) $row['municipality']] = [
                'barangays' => (int) $row['barangays'],
                'households' => (int) $row['households'],
                'residents' => (int) $row['residents'],
            ];
        }

        $brgyListStmt = $pdo->query(
            "SELECT
                cm.name AS municipality,
                b.barangay AS barangay,
                COUNT(DISTINCT h.id) AS households,
                (
                    SELECT COUNT(*)
                    FROM register_members rm
                    INNER JOIN survey_session ss ON ss.session_id = rm.session_id
                    WHERE ss.barangay_id = b.barangay_id
                ) AS residents
             FROM barangay b
             INNER JOIN city_municipality cm ON cm.city_municipality_id = b.city_municipality_id
             LEFT JOIN households h ON h.barangay_id = b.barangay_id
             GROUP BY b.barangay_id, b.barangay, cm.name
             ORDER BY cm.name ASC, b.barangay ASC"
        );
        foreach ($brgyListStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $munName = (string) $row['municipality'];
            if (!isset($municipalityBarangayLists[$munName])) {
                $municipalityBarangayLists[$munName] = [];
            }
            $municipalityBarangayLists[$munName][] = [
                'name' => (string) $row['barangay'],
                'households' => (int) $row['households'],
                'residents' => (int) $row['residents'],
            ];
        }
    } catch (Throwable $e) {
        $totalCities = 0;
        $totalBarangays = 0;
        $totalHouseholds = 0;
        $totalResidents = 0;
        $municipalityMapStats = [];
        $municipalityBarangayLists = [];
    }
} elseif ($isMuniAdmin) {
    $page_title = 'Municipality Admin Dashboard';
    $role_label = role_label('muni_admin');
    $municipality = (string) ($user['municipality'] ?? 'Unassigned municipality');
    $pageHeading = 'Dashboard';
    $pageSubheading = '';
    $muniId = (int) ($user['city_municipality_id'] ?? 0);
    $totalBarangays = 0;
    $totalHouseholds = 0;
    $totalResidents = 0;
    $mapCss = app_url('maps/siquijor-map.css');
    $mapJs = app_url('maps/siquijor-map.js');
    $mapLeafletCss = app_url('maps/vendor/leaflet/leaflet.css');
    $mapLeafletJs = app_url('maps/vendor/leaflet/leaflet.js');
    $mapGeo = app_url('maps/siquijor-municipalities.geojson');
    $mapBarangayGeo = app_url('maps/siquijor-barangays.geojson');
    $mapGeoJsonInline = '{}';
    $mapBarangayGeoJsonInline = '{}';
    $municipalityMapStats = [];
    $municipalityBarangayLists = [];
    $geoFile = __DIR__ . '/../maps/siquijor-municipalities.geojson';
    $brgyGeoFile = __DIR__ . '/../maps/siquijor-barangays.geojson';
    if (is_file($geoFile)) {
        $raw = file_get_contents($geoFile);
        if ($raw !== false && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && !empty($decoded['features'])) {
                $mapGeoJsonInline = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }
    }
    if (is_file($brgyGeoFile)) {
        $raw = file_get_contents($brgyGeoFile);
        if ($raw !== false && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && !empty($decoded['features'])) {
                $mapBarangayGeoJsonInline = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }
    }
    if ($mapGeoJsonInline === false || $mapGeoJsonInline === null || $mapGeoJsonInline === '') {
        $mapGeoJsonInline = '{}';
    }
    if ($mapBarangayGeoJsonInline === false || $mapBarangayGeoJsonInline === null || $mapBarangayGeoJsonInline === '') {
        $mapBarangayGeoJsonInline = '{}';
    }

    $filterGeoForMunicipality = static function (string $json, string $municipalityName, string $property = 'name'): string {
        $decoded = json_decode($json, true);
        if (!is_array($decoded) || empty($decoded['features'])) {
            return '{}';
        }

        $decoded['features'] = array_values(array_filter(
            $decoded['features'],
            static function (array $feature) use ($municipalityName, $property): bool {
                $value = (string) ($feature['properties'][$property] ?? '');
                return strcasecmp($value, $municipalityName) === 0;
            }
        ));

        $encoded = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $encoded === false ? '{}' : $encoded;
    };

    try {
        $pdo = db();
        if ($muniId > 0) {
            $muniNameStmt = $pdo->prepare('SELECT name FROM city_municipality WHERE city_municipality_id = :id LIMIT 1');
            $muniNameStmt->execute([':id' => $muniId]);
            $dbMunicipality = $muniNameStmt->fetchColumn();
            if (is_string($dbMunicipality) && $dbMunicipality !== '') {
                $municipality = $dbMunicipality;
            }

            $mapBarangayGeoJsonInline = $filterGeoForMunicipality($mapBarangayGeoJsonInline, $municipality, 'municipality');
            $bc = $pdo->prepare('SELECT COUNT(*) FROM barangay WHERE city_municipality_id = :id');
            $bc->execute([':id' => $muniId]);
            $totalBarangays = (int) $bc->fetchColumn();

            $hh = $pdo->prepare(
                'SELECT COUNT(*) FROM households h
                 INNER JOIN barangay b ON b.barangay_id = h.barangay_id
                 WHERE b.city_municipality_id = :id'
            );
            $hh->execute([':id' => $muniId]);
            $totalHouseholds = (int) $hh->fetchColumn();

            $res = $pdo->prepare(
                'SELECT COUNT(*) FROM register_members rm
                 INNER JOIN survey_session ss ON ss.session_id = rm.session_id
                 INNER JOIN barangay b ON b.barangay_id = ss.barangay_id
                 WHERE b.city_municipality_id = :id'
            );
            $res->execute([':id' => $muniId]);
            $totalResidents = (int) $res->fetchColumn();

            $municipalityMapStats[$municipality] = [
                'barangays' => $totalBarangays,
                'households' => $totalHouseholds,
                'residents' => $totalResidents,
            ];

            $brgyListStmt = $pdo->prepare(
                "SELECT
                    b.barangay AS barangay,
                    COUNT(DISTINCT h.id) AS households,
                    (
                        SELECT COUNT(*)
                        FROM register_members rm
                        INNER JOIN survey_session ss ON ss.session_id = rm.session_id
                        WHERE ss.barangay_id = b.barangay_id
                    ) AS residents
                 FROM barangay b
                 INNER JOIN city_municipality cm ON cm.city_municipality_id = b.city_municipality_id
                 LEFT JOIN households h ON h.barangay_id = b.barangay_id
                 WHERE cm.city_municipality_id = :muni_id
                 GROUP BY b.barangay_id, b.barangay
                 ORDER BY b.barangay ASC"
            );
            $brgyListStmt->execute([':muni_id' => $muniId]);
            $municipalityBarangayLists[$municipality] = [];
            foreach ($brgyListStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $municipalityBarangayLists[$municipality][] = [
                    'name' => (string) $row['barangay'],
                    'households' => (int) $row['households'],
                    'residents' => (int) $row['residents'],
                ];
            }
        }
    } catch (Throwable $e) {
        $totalBarangays = 0;
        $totalHouseholds = 0;
        $totalResidents = 0;
        $municipalityMapStats = [];
        $municipalityBarangayLists = [];
    }
} else {
    $page_title = 'Admin Dashboard';
    $role_label = role_label('admin');
    $barangay = $user['barangay'] ?: 'Unassigned barangay';
    $pageHeading = 'Dashboard';
    $pageSubheading = $barangay;
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
  <?php if ($isSuperAdmin || $isMuniAdmin): ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($mapLeafletCss); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($mapCss); ?>">
  <?php endif; ?>
</head>
<body>
  <div class="overlay" id="sidebar-overlay" hidden></div>

  <div class="app">
    <?php require __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="main">
      <?php require __DIR__ . '/../includes/topbar.php'; ?>

      <main class="content">
        <?php if ($isSuperAdmin): ?>
          <section class="stats-grid kpi-4" aria-label="Key performance indicators">
            <article class="stat-card kpi-blue">
              <div class="stat-top">
                <div>
                  <p class="stat-label">Total City / Municipality</p>
                  <p class="stat-value"><?php echo number_format($totalCities); ?></p>
                </div>
                <div class="stat-icon blue" aria-hidden="true">
                  <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                </div>
              </div>
              <p class="stat-foot">Municipalities · Province of Siquijor</p>
            </article>

            <article class="stat-card kpi-red">
              <div class="stat-top">
                <div>
                  <p class="stat-label">Total Barangays</p>
                  <p class="stat-value"><?php echo number_format($totalBarangays); ?></p>
                </div>
                <div class="stat-icon red" aria-hidden="true">
                  <svg viewBox="0 0 24 24"><path d="M3 21h18"/><path d="M5 21V8l7-5 7 5v13"/><path d="M9 21v-6h6v6"/></svg>
                </div>
              </div>
              <p class="stat-foot">Barangays · Province of Siquijor</p>
            </article>

            <article class="stat-card kpi-gold">
              <div class="stat-top">
                <div>
                  <p class="stat-label">Total Households</p>
                  <p class="stat-value"><?php echo number_format($totalHouseholds); ?></p>
                </div>
                <div class="stat-icon gold" aria-hidden="true">
                  <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
                </div>
              </div>
              <p class="stat-foot">Registered household profiles</p>
            </article>

            <article class="stat-card kpi-navy">
              <div class="stat-top">
                <div>
                  <p class="stat-label">Total Residents</p>
                  <p class="stat-value"><?php echo number_format($totalResidents); ?></p>
                </div>
                <div class="stat-icon navy" aria-hidden="true">
                  <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
              </div>
              <p class="stat-foot">Inhabitants and migrants on record</p>
            </article>
          </section>

          <section class="rbim-map-panel" aria-label="Siquijor municipality map">
            <div class="rbim-map-head">
              <div class="rbim-map-head-main">
                <span class="rbim-map-head-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </span>
                <div>
                  <p class="rbim-map-kicker">Geographic overview</p>
                  <h2>Province of Siquijor</h2>
                  <p>CyclOSM map of Siquijor — click a municipality for coverage stats.</p>
                </div>
              </div>
              <div class="rbim-map-head-meta">
                <div class="rbim-map-chip">
                  <span>Municipalities</span>
                  <strong><?php echo number_format($totalCities); ?></strong>
                </div>
                <div class="rbim-map-chip">
                  <span>Barangays</span>
                  <strong><?php echo number_format($totalBarangays); ?></strong>
                </div>
                <button type="button" class="rbim-map-reset" id="rbim-map-reset" title="Reset map view">
                  <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                  Reset view
                </button>
              </div>
            </div>
            <div class="rbim-map-layout">
              <div class="rbim-map-stage">
                <div
                  id="rbim-siquijor-map"
                  class="rbim-map-canvas is-loading"
                  data-geojson="<?php echo htmlspecialchars($mapGeo); ?>"
                  data-barangay-geojson="<?php echo htmlspecialchars($mapBarangayGeo); ?>"
                  data-stats="<?php echo htmlspecialchars(json_encode($municipalityMapStats, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>"
                  data-barangays="<?php echo htmlspecialchars(json_encode($municipalityBarangayLists, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>"
                ></div>
                <div class="rbim-map-loading" id="rbim-map-loading" aria-live="polite">Loading map…</div>
                <div class="rbim-map-hint" id="rbim-map-hint">CyclOSM · click a municipality</div>
                <div class="rbim-map-float" id="rbim-map-float" hidden>
                  <div class="rbim-map-float-head" id="rbim-map-float-drag">
                    <div>
                      <p class="rbim-map-float-kicker">Municipality · drag / resize</p>
                      <h3 id="rbim-map-float-title">—</h3>
                    </div>
                    <button type="button" class="rbim-map-float-close" id="rbim-map-float-close" aria-label="Close municipality details">
                      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                  </div>
                  <div class="rbim-map-float-metrics" id="rbim-map-float-metrics"></div>
                  <div class="rbim-map-float-list-wrap">
                    <p class="rbim-map-float-list-title">Barangay list</p>
                    <ul class="rbim-map-float-list" id="rbim-map-float-list"></ul>
                  </div>
                  <div class="rbim-map-float-resize" id="rbim-map-float-resize" title="Drag to resize" aria-hidden="true"></div>
                </div>
              </div>
              <aside class="rbim-map-side">
                <div class="rbim-map-selected" id="rbim-map-selected">
                  <p class="rbim-map-selected-label">Selected area</p>
                  <h3 id="rbim-map-selected-name">Province overview</h3>
                  <p id="rbim-map-selected-sub">CyclOSM view · click a municipality</p>
                  <div class="rbim-map-selected-grid" id="rbim-map-selected-grid">
                    <div><span>Barangays</span><strong><?php echo number_format($totalBarangays); ?></strong></div>
                    <div><span>Households</span><strong><?php echo number_format($totalHouseholds); ?></strong></div>
                    <div><span>Residents</span><strong><?php echo number_format($totalResidents); ?></strong></div>
                  </div>
                </div>
                <h3 class="rbim-map-side-title">Municipalities</h3>
                <div class="rbim-map-legend" id="rbim-map-legend"></div>
              </aside>
            </div>
          </section>
        <?php elseif ($isMuniAdmin): ?>
          <section class="stats-grid kpi-3" aria-label="Municipality key performance indicators">
            <article class="stat-card kpi-red">
              <div class="stat-top">
                <div>
                  <p class="stat-label">Total Barangays</p>
                  <p class="stat-value"><?php echo number_format($totalBarangays); ?></p>
                </div>
                <div class="stat-icon red" aria-hidden="true">
                  <svg viewBox="0 0 24 24"><path d="M3 21h18"/><path d="M5 21V8l7-5 7 5v13"/><path d="M9 21v-6h6v6"/></svg>
                </div>
              </div>
              <p class="stat-foot">Barangays · <?php echo htmlspecialchars($municipality); ?></p>
            </article>

            <article class="stat-card kpi-gold">
              <div class="stat-top">
                <div>
                  <p class="stat-label">Total Households</p>
                  <p class="stat-value"><?php echo number_format($totalHouseholds); ?></p>
                </div>
                <div class="stat-icon gold" aria-hidden="true">
                  <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
                </div>
              </div>
              <p class="stat-foot">Registered household profiles</p>
            </article>

            <article class="stat-card kpi-navy">
              <div class="stat-top">
                <div>
                  <p class="stat-label">Total Residents</p>
                  <p class="stat-value"><?php echo number_format($totalResidents); ?></p>
                </div>
                <div class="stat-icon navy" aria-hidden="true">
                  <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
              </div>
              <p class="stat-foot">Inhabitants and migrants on record</p>
            </article>
          </section>

          <section class="rbim-map-panel rbim-map-panel--muni" aria-label="Municipality geographic overview">
            <div class="rbim-map-head">
              <div class="rbim-map-head-main">
                <span class="rbim-map-head-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </span>
                <div>
                  <p class="rbim-map-kicker">Geographic overview</p>
                  <h2><?php echo htmlspecialchars($municipality); ?></h2>
                  <p>CyclOSM map of your municipality — click a barangay for coverage stats.</p>
                </div>
              </div>
              <div class="rbim-map-head-meta">
                <div class="rbim-map-chip">
                  <span>Barangays</span>
                  <strong><?php echo number_format($totalBarangays); ?></strong>
                </div>
                <div class="rbim-map-chip">
                  <span>Households</span>
                  <strong><?php echo number_format($totalHouseholds); ?></strong>
                </div>
                <button type="button" class="rbim-map-reset" id="rbim-map-reset" title="Reset map view">
                  <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                  Reset view
                </button>
              </div>
            </div>
            <div class="rbim-map-layout rbim-map-layout--muni">
              <div class="rbim-map-stage">
                <div
                  id="rbim-siquijor-map"
                  class="rbim-map-canvas is-loading"
                  data-geojson="<?php echo htmlspecialchars($mapGeo); ?>"
                  data-barangay-geojson="<?php echo htmlspecialchars($mapBarangayGeo); ?>"
                  data-focus-municipality="<?php echo htmlspecialchars($municipality); ?>"
                  data-municipality="<?php echo htmlspecialchars($municipality); ?>"
                  data-stats="<?php echo htmlspecialchars(json_encode($municipalityMapStats, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>"
                  data-barangays="<?php echo htmlspecialchars(json_encode($municipalityBarangayLists, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>"
                ></div>
                <div class="rbim-map-loading" id="rbim-map-loading" aria-live="polite">Loading map…</div>
                <div class="rbim-map-hint" id="rbim-map-hint">CyclOSM · click a barangay</div>
                <div class="rbim-map-float rbim-map-float--muni" id="rbim-map-float" hidden>
                  <div class="rbim-map-float-head" id="rbim-map-float-drag">
                    <div>
                      <p class="rbim-map-float-kicker">Municipality · drag / resize</p>
                      <h3 id="rbim-map-float-title">—</h3>
                    </div>
                    <button type="button" class="rbim-map-float-close" id="rbim-map-float-close" aria-label="Close municipality details" hidden>
                      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                  </div>
                  <div class="rbim-map-float-metrics" id="rbim-map-float-metrics"></div>
                  <div class="rbim-map-float-list-wrap">
                    <p class="rbim-map-float-list-title">Barangay list</p>
                    <ul class="rbim-map-float-list" id="rbim-map-float-list"></ul>
                  </div>
                  <div class="rbim-map-float-resize" id="rbim-map-float-resize" title="Drag to resize" aria-hidden="true"></div>
                </div>
              </div>
            </div>
          </section>
        <?php endif; ?>
      </main>
    </div>
  </div>

  <?php require __DIR__ . '/../includes/scripts.php'; ?>
  <?php if ($isSuperAdmin || $isMuniAdmin): ?>
    <script>
      window.RBIM_SIQUIJOR_GEOJSON = <?php echo $mapGeoJsonInline; ?>;
      window.RBIM_SIQUIJOR_BARANGAY_GEOJSON = <?php echo $mapBarangayGeoJsonInline; ?>;
    </script>
    <script src="<?php echo htmlspecialchars($mapLeafletJs); ?>"></script>
    <script src="<?php echo htmlspecialchars($mapJs); ?>"></script>
  <?php endif; ?>
</body>
</html>
