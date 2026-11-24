<?php
declare(strict_types=1);

require_once __DIR__ . '/../../login.php';

$user = require_role('super_admin');
$page_title = 'User Management';
$role_label = role_label('super_admin');
$user_name = user_display_name($user);
$user_initials = user_initials($user);
$logo = app_url('assets/img/system-logo.jpg');
$css = app_url('assets/css/dashboard.css');
$formCss = app_url('assets/css/admin-forms.css');
$logout = app_url('logout.php');
$activeNav = 'user_management';
$pageHeading = 'User Management';
$pageSubheading = '';

$filterMuniId = max(0, (int) ($_GET['muni'] ?? 0));
$searchQuery = trim((string) ($_GET['q'] ?? ''));
$saved = isset($_GET['saved']) ? trim((string) $_GET['saved']) : '';
$formErrors = [];
$openModal = '';
$municipalities = [];
$groupedBarangays = [];
$barangayOptions = [];

$form = [
    'admin_id' => '0',
    'username' => '',
    'password' => '',
    'password_confirm' => '',
    'role' => 'admin',
    'city_municipality_id' => '0',
    'barangay_id' => '0',
    'account_status' => 'active',
];

function um_load_municipalities(PDO $pdo): array
{
    return $pdo->query(
        'SELECT city_municipality_id, name
         FROM city_municipality
         ORDER BY name ASC'
    )->fetchAll();
}

function um_load_barangay_rows(PDO $pdo, int $muniId, string $search): array
{
    $sql = "SELECT
                b.barangay_id,
                b.barangay,
                cm.city_municipality_id,
                cm.name AS municipality,
                u.admin_id,
                u.username,
                u.role,
                u.account_status
            FROM barangay b
            INNER JOIN city_municipality cm ON cm.city_municipality_id = b.city_municipality_id
            LEFT JOIN users u ON u.barangay_id = b.barangay_id AND u.role = 'admin'
            WHERE 1 = 1";
    $params = [];

    if ($muniId > 0) {
        $sql .= ' AND cm.city_municipality_id = :muni_id';
        $params[':muni_id'] = $muniId;
    }

    if ($search !== '') {
        $sql .= ' AND (
            b.barangay LIKE :search
            OR cm.name LIKE :search
            OR u.username LIKE :search
        )';
        $params[':search'] = '%' . $search . '%';
    }

    $sql .= ' ORDER BY cm.name ASC, b.barangay ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function um_load_muni_admins(PDO $pdo): array
{
    $stmt = $pdo->query(
        "SELECT u.admin_id, u.username, u.role, u.account_status, u.city_municipality_id, cm.name AS municipality
         FROM users u
         LEFT JOIN city_municipality cm ON cm.city_municipality_id = u.city_municipality_id
         WHERE u.role = 'muni_admin'
         ORDER BY cm.name ASC, u.username ASC"
    );

    $map = [];
    foreach ($stmt->fetchAll() as $row) {
        $map[(int) $row['city_municipality_id']] = $row;
    }

    return $map;
}

function um_muni_has_admin(PDO $pdo, int $muniId, int $excludeId = 0): bool
{
    $sql = "SELECT admin_id FROM users WHERE city_municipality_id = :muni_id AND role = 'muni_admin'";
    $params = [':muni_id' => $muniId];
    if ($excludeId > 0) {
        $sql .= ' AND admin_id <> :exclude_id';
        $params[':exclude_id'] = $excludeId;
    }
    $sql .= ' LIMIT 1';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (bool) $stmt->fetch();
}

function um_build_municipality_cards(array $municipalities, array $allRows, array $muniAdmins, string $search): array
{
    $byMuni = [];
    foreach ($allRows as $row) {
        $id = (int) $row['city_municipality_id'];
        $byMuni[$id][] = $row;
    }

    $cards = [];
    $searchLower = strtolower($search);

    foreach ($municipalities as $mun) {
        $id = (int) $mun['city_municipality_id'];
        $name = (string) $mun['name'];
        $rows = $byMuni[$id] ?? [];

        $total = count($rows);
        $withAccount = 0;
        $active = 0;
        $inactive = 0;
        $suspended = 0;

        foreach ($rows as $row) {
            if (!empty($row['admin_id'])) {
                $withAccount++;
                $status = (string) ($row['account_status'] ?? '');
                if ($status === 'active') {
                    $active++;
                } elseif ($status === 'inactive') {
                    $inactive++;
                } elseif ($status === 'suspended') {
                    $suspended++;
                }
            }
        }

        if ($search !== '') {
            $matches = str_contains(strtolower($name), $searchLower);
            $muniAdmin = $muniAdmins[$id] ?? null;
            if (!$matches && $muniAdmin) {
                $matches = str_contains(strtolower((string) ($muniAdmin['username'] ?? '')), $searchLower);
            }
            if (!$matches) {
                foreach ($rows as $row) {
                    $hay = strtolower((string) $row['barangay'] . ' ' . (string) ($row['username'] ?? ''));
                    if (str_contains($hay, $searchLower)) {
                        $matches = true;
                        break;
                    }
                }
            }
            if (!$matches) {
                continue;
            }
        }

        $muniAdmin = $muniAdmins[$id] ?? null;

        $cards[] = [
            'municipality_id' => $id,
            'municipality' => $name,
            'muni_admin' => $muniAdmin,
            'total_barangays' => $total,
            'with_account' => $withAccount,
            'unassigned' => max(0, $total - $withAccount),
            'active' => $active,
            'inactive' => $inactive,
            'suspended' => $suspended,
            'accounts' => $rows,
        ];
    }

    return $cards;
}

function um_muni_initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    if (count($parts) >= 2) {
        return strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1));
    }

    return strtoupper(mb_substr($name, 0, 2));
}

function um_card_theme(int $index): string
{
    $themes = ['blue', 'red', 'gold', 'navy'];
    return $themes[$index % count($themes)];
}

function um_load_barangay_options(PDO $pdo, int $muniId = 0): array
{
    $sql = 'SELECT b.barangay_id, b.barangay, b.city_municipality_id, cm.name AS municipality
            FROM barangay b
            INNER JOIN city_municipality cm ON cm.city_municipality_id = b.city_municipality_id';
    $params = [];

    if ($muniId > 0) {
        $sql .= ' WHERE cm.city_municipality_id = :muni_id';
        $params[':muni_id'] = $muniId;
    }

    $sql .= ' ORDER BY cm.name ASC, b.barangay ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function um_username_exists(PDO $pdo, string $username, int $excludeId = 0): bool
{
    $sql = 'SELECT admin_id FROM users WHERE username = :username';
    $params = [':username' => $username];
    if ($excludeId > 0) {
        $sql .= ' AND admin_id <> :exclude_id';
        $params[':exclude_id'] = $excludeId;
    }
    $sql .= ' LIMIT 1';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (bool) $stmt->fetch();
}

function um_barangay_has_admin(PDO $pdo, int $barangayId, int $excludeId = 0): bool
{
    $sql = "SELECT admin_id FROM users WHERE barangay_id = :barangay_id AND role = 'admin'";
    $params = [':barangay_id' => $barangayId];
    if ($excludeId > 0) {
        $sql .= ' AND admin_id <> :exclude_id';
        $params[':exclude_id'] = $excludeId;
    }
    $sql .= ' LIMIT 1';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (bool) $stmt->fetch();
}

function um_redirect_with_saved(string $message, int $muniId, string $search): void
{
    $params = ['saved' => $message];
    if ($muniId > 0) {
        $params['muni'] = (string) $muniId;
    }
    if ($search !== '') {
        $params['q'] = $search;
    }
    redirect(app_url('pages/users/index.php?' . http_build_query($params)));
}

try {
    $pdo = db();
    rbim_ensure_user_schema($pdo);
    $seededMuniAdmins = rbim_seed_municipality_admins($pdo);
    $municipalities = um_load_municipalities($pdo);
    $barangayOptions = um_load_barangay_options($pdo);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = (string) ($_POST['action'] ?? '');
        $openModal = $action === 'update_user' ? 'edit' : 'create';

        foreach (array_keys($form) as $key) {
            if (isset($_POST[$key])) {
                $form[$key] = trim((string) $_POST[$key]);
            }
        }

        $adminId = max(0, (int) $form['admin_id']);
        $role = (string) $form['role'];
        $muniId = max(0, (int) $form['city_municipality_id']);
        $barangayId = max(0, (int) $form['barangay_id']);
        $username = trim($form['username']);
        $password = $form['password'];
        $passwordConfirm = $form['password_confirm'];
        $accountStatus = $form['account_status'];

        if ($username === '') {
            $formErrors[] = 'Username is required.';
        } elseif (mb_strlen($username) > 255) {
            $formErrors[] = 'Username is too long.';
        }

        if (!in_array($role, ['muni_admin', 'admin'], true)) {
            $formErrors[] = 'Invalid account role.';
        }

        if (!in_array($accountStatus, ['active', 'inactive', 'suspended'], true)) {
            $formErrors[] = 'Invalid account status.';
        }

        if ($role === 'muni_admin') {
            if ($muniId <= 0) {
                $formErrors[] = 'Municipality is required for municipality admin accounts.';
            }
            $barangayId = 0;
        } else {
            if ($barangayId <= 0) {
                $formErrors[] = 'Barangay is required for barangay admin accounts.';
            }
        }

        if ($action === 'create_user') {
            if ($password === '') {
                $formErrors[] = 'Password is required.';
            } elseif (strlen($password) < 6) {
                $formErrors[] = 'Password must be at least 6 characters.';
            } elseif ($password !== $passwordConfirm) {
                $formErrors[] = 'Passwords do not match.';
            }

            if ($username !== '' && um_username_exists($pdo, $username)) {
                $formErrors[] = 'Username is already taken.';
            }

            if ($role === 'muni_admin' && $muniId > 0 && um_muni_has_admin($pdo, $muniId)) {
                $formErrors[] = 'This municipality already has an administrator account.';
            }

            if ($role === 'admin' && $barangayId > 0 && um_barangay_has_admin($pdo, $barangayId)) {
                $formErrors[] = 'This barangay already has an administrator account.';
            }

            if ($formErrors === []) {
                if ($role === 'muni_admin') {
                    $locStmt = $pdo->prepare(
                        'SELECT name FROM city_municipality WHERE city_municipality_id = :id LIMIT 1'
                    );
                    $locStmt->execute([':id' => $muniId]);
                    $loc = $locStmt->fetch();
                    if (!$loc) {
                        $formErrors[] = 'Selected municipality was not found.';
                    } else {
                        $insert = $pdo->prepare(
                            "INSERT INTO users (username, password, role, account_status, barangay, barangay_id, city_municipality_id)
                             VALUES (:username, :password, 'muni_admin', :account_status, NULL, NULL, :city_municipality_id)"
                        );
                        $insert->execute([
                            ':username' => $username,
                            ':password' => password_hash($password, PASSWORD_DEFAULT),
                            ':account_status' => $accountStatus,
                            ':city_municipality_id' => $muniId,
                        ]);

                        log_activity(
                            (int) $user['admin_id'],
                            (string) $user['username'],
                            'create_user',
                            'Created municipality admin: ' . $username
                        );

                        um_redirect_with_saved('created', $filterMuniId, $searchQuery);
                    }
                } else {
                    $locStmt = $pdo->prepare(
                        'SELECT b.barangay, b.city_municipality_id
                         FROM barangay b
                         WHERE b.barangay_id = :id
                         LIMIT 1'
                    );
                    $locStmt->execute([':id' => $barangayId]);
                    $loc = $locStmt->fetch();
                    if (!$loc) {
                        $formErrors[] = 'Selected barangay was not found.';
                    } else {
                        $insert = $pdo->prepare(
                            "INSERT INTO users (username, password, role, account_status, barangay, barangay_id, city_municipality_id)
                             VALUES (:username, :password, 'admin', :account_status, :barangay, :barangay_id, :city_municipality_id)"
                        );
                        $insert->execute([
                            ':username' => $username,
                            ':password' => password_hash($password, PASSWORD_DEFAULT),
                            ':account_status' => $accountStatus,
                            ':barangay' => (string) $loc['barangay'],
                            ':barangay_id' => $barangayId,
                            ':city_municipality_id' => (int) $loc['city_municipality_id'],
                        ]);

                        log_activity(
                            (int) $user['admin_id'],
                            (string) $user['username'],
                            'create_user',
                            'Created barangay admin: ' . $username
                        );

                        um_redirect_with_saved('created', $filterMuniId, $searchQuery);
                    }
                }
            }
        } elseif ($action === 'update_user') {
            if ($adminId <= 0) {
                $formErrors[] = 'Invalid user selected.';
            }

            $targetStmt = $pdo->prepare(
                "SELECT admin_id, role FROM users WHERE admin_id = :admin_id LIMIT 1"
            );
            $targetStmt->execute([':admin_id' => $adminId]);
            $target = $targetStmt->fetch();

            if (!$target) {
                $formErrors[] = 'User not found.';
            } elseif (!in_array(($target['role'] ?? ''), ['muni_admin', 'admin'], true)) {
                $formErrors[] = 'Only municipality and barangay administrator accounts can be edited here.';
            } elseif (($target['role'] ?? '') !== $role) {
                $formErrors[] = 'Account role cannot be changed after creation.';
            }

            if ($username !== '' && um_username_exists($pdo, $username, $adminId)) {
                $formErrors[] = 'Username is already taken.';
            }

            if ($role === 'muni_admin' && $muniId > 0 && um_muni_has_admin($pdo, $muniId, $adminId)) {
                $formErrors[] = 'This municipality already has an administrator account.';
            }

            if ($role === 'admin' && $barangayId > 0 && um_barangay_has_admin($pdo, $barangayId, $adminId)) {
                $formErrors[] = 'This barangay already has an administrator account.';
            }

            if ($password !== '' && strlen($password) < 6) {
                $formErrors[] = 'Password must be at least 6 characters.';
            }

            if ($password !== '' && $password !== $passwordConfirm) {
                $formErrors[] = 'Passwords do not match.';
            }

            if ($formErrors === [] && $target) {
                if ($role === 'muni_admin') {
                    $locStmt = $pdo->prepare(
                        'SELECT name FROM city_municipality WHERE city_municipality_id = :id LIMIT 1'
                    );
                    $locStmt->execute([':id' => $muniId]);
                    $loc = $locStmt->fetch();
                    if (!$loc) {
                        $formErrors[] = 'Selected municipality was not found.';
                    } else {
                        $sql = "UPDATE users
                                SET username = :username,
                                    account_status = :account_status,
                                    barangay = NULL,
                                    barangay_id = NULL,
                                    city_municipality_id = :city_municipality_id";
                        $params = [
                            ':username' => $username,
                            ':account_status' => $accountStatus,
                            ':city_municipality_id' => $muniId,
                            ':admin_id' => $adminId,
                        ];

                        if ($password !== '') {
                            $sql .= ', password = :password';
                            $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
                        }

                        $sql .= " WHERE admin_id = :admin_id AND role = 'muni_admin'";

                        $update = $pdo->prepare($sql);
                        $update->execute($params);

                        log_activity(
                            (int) $user['admin_id'],
                            (string) $user['username'],
                            'update_user',
                            'Updated municipality admin: ' . $username
                        );

                        um_redirect_with_saved('updated', $filterMuniId, $searchQuery);
                    }
                } else {
                    $locStmt = $pdo->prepare(
                        'SELECT b.barangay, b.city_municipality_id
                         FROM barangay b
                         WHERE b.barangay_id = :id
                         LIMIT 1'
                    );
                    $locStmt->execute([':id' => $barangayId]);
                    $loc = $locStmt->fetch();
                    if (!$loc) {
                        $formErrors[] = 'Selected barangay was not found.';
                    } else {
                        $sql = "UPDATE users
                                SET username = :username,
                                    account_status = :account_status,
                                    barangay = :barangay,
                                    barangay_id = :barangay_id,
                                    city_municipality_id = :city_municipality_id";
                        $params = [
                            ':username' => $username,
                            ':account_status' => $accountStatus,
                            ':barangay' => (string) $loc['barangay'],
                            ':barangay_id' => $barangayId,
                            ':city_municipality_id' => (int) $loc['city_municipality_id'],
                            ':admin_id' => $adminId,
                        ];

                        if ($password !== '') {
                            $sql .= ', password = :password';
                            $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
                        }

                        $sql .= " WHERE admin_id = :admin_id AND role = 'admin'";

                        $update = $pdo->prepare($sql);
                        $update->execute($params);

                        log_activity(
                            (int) $user['admin_id'],
                            (string) $user['username'],
                            'update_user',
                            'Updated barangay admin: ' . $username
                        );

                        um_redirect_with_saved('updated', $filterMuniId, $searchQuery);
                    }
                }
            }
        }
    }

    $muniAdmins = um_load_muni_admins($pdo);
    $barangayRows = um_load_barangay_rows($pdo, 0, '');
    $municipalityCards = um_build_municipality_cards($municipalities, $barangayRows, $muniAdmins, $searchQuery);
} catch (Throwable $e) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $formErrors[] = 'Unable to process the request. Please try again.';
    }
    $municipalityCards = [];
    $seededMuniAdmins = 0;
}

$withAccount = 0;
$totalActive = 0;
foreach ($municipalityCards as $card) {
    $withAccount += (int) $card['with_account'];
    $totalActive += (int) $card['active'];
}
$muniCardsJson = json_encode($municipalityCards, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
if ($muniCardsJson === false) {
    $muniCardsJson = '[]';
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
      <?php require __DIR__ . '/../../includes/topbar.php'; ?>

      <main class="content">
        <section class="mgmt-page um-page">
          <?php if ($saved === 'created'): ?>
            <div class="admin-alert ok" role="status">User account created successfully.</div>
          <?php elseif ($saved === 'updated'): ?>
            <div class="admin-alert ok" role="status">User account updated successfully.</div>
          <?php elseif (!empty($seededMuniAdmins)): ?>
            <div class="admin-alert ok" role="status">
              Created <?php echo (int) $seededMuniAdmins; ?> municipality admin account<?php echo (int) $seededMuniAdmins === 1 ? '' : 's'; ?>
              (username = municipality name, default password <strong>admin123</strong>).
            </div>
          <?php endif; ?>

          <section class="um-kpi-grid" aria-label="User management summary">
            <article class="um-kpi-card um-kpi-card--blue">
              <p class="um-kpi-label">Municipalities</p>
              <p class="um-kpi-value"><?php echo number_format(count($municipalityCards)); ?></p>
            </article>
            <article class="um-kpi-card um-kpi-card--navy">
              <p class="um-kpi-label">Barangay accounts</p>
              <p class="um-kpi-value"><?php echo number_format($withAccount); ?></p>
            </article>
            <article class="um-kpi-card um-kpi-card--gold">
              <p class="um-kpi-label">Active accounts</p>
              <p class="um-kpi-value"><?php echo number_format($totalActive); ?></p>
            </article>
          </section>

          <div class="um-toolbar-panel">
            <div class="mgmt-toolbar um-toolbar">
            <form method="get" class="um-toolbar-form" action="index.php">
              <div class="mgmt-search um-search">
                <span class="mgmt-search-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                </span>
                <input
                  type="search"
                  name="q"
                  id="user-search"
                  class="mgmt-search-input"
                  placeholder="Search municipality, barangay, or username..."
                  value="<?php echo htmlspecialchars($searchQuery); ?>"
                  autocomplete="off"
                >
              </div>
              <?php if ($searchQuery !== ''): ?>
                <a class="um-clear-link" href="index.php">Clear search</a>
              <?php endif; ?>
            </form>
            <button type="button" class="btn btn-primary mgmt-add-btn" id="btn-open-create">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
              Add User
            </button>
          </div>
          </div>

          <section class="um-section" aria-label="Municipality accounts">
            <h2 class="um-section-title">Municipalities</h2>
            <div class="um-card-grid" id="um-muni-grid">
            <?php if (empty($municipalityCards)): ?>
              <p class="mgmt-empty um-grid-empty">No municipalities match your search.</p>
            <?php else: ?>
              <?php foreach ($municipalityCards as $i => $card): ?>
                <?php
                  $theme = um_card_theme($i);
                  $initials = um_muni_initials($card['municipality']);
                  $muniAdmin = $card['muni_admin'] ?? null;
                ?>
                <button
                  type="button"
                  class="um-muni-card um-muni-card--<?php echo htmlspecialchars($theme); ?>"
                  data-muni-id="<?php echo (int) $card['municipality_id']; ?>"
                  aria-haspopup="dialog"
                >
                  <div class="um-muni-card-inner">
                    <div class="um-muni-card-top">
                      <div class="um-muni-avatar um-muni-avatar--<?php echo htmlspecialchars($theme); ?>"><?php echo htmlspecialchars($initials); ?></div>
                      <div class="um-muni-card-head">
                        <p class="um-muni-kicker">Municipality</p>
                        <h3 class="um-muni-name"><?php echo htmlspecialchars($card['municipality']); ?></h3>
                        <p class="um-muni-meta"><?php echo (int) $card['total_barangays']; ?> barangay<?php echo (int) $card['total_barangays'] === 1 ? '' : 's'; ?></p>
                      </div>
                    </div>
                    <div class="um-muni-metrics">
                      <div class="um-muni-metric">
                        <span>Muni admin</span>
                        <strong><?php echo $muniAdmin ? htmlspecialchars((string) $muniAdmin['username']) : 'None'; ?></strong>
                      </div>
                      <div class="um-muni-metric">
                        <span>Barangay</span>
                        <strong><?php echo (int) $card['with_account']; ?> / <?php echo (int) $card['total_barangays']; ?></strong>
                      </div>
                      <div class="um-muni-metric">
                        <span>Active</span>
                        <strong><?php echo (int) $card['active']; ?></strong>
                      </div>
                    </div>
                    <div class="um-muni-card-foot">
                      <span class="um-muni-open">
                        View accounts
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                      </span>
                    </div>
                  </div>
                </button>
              <?php endforeach; ?>
            <?php endif; ?>
            </div>
          </section>
        </section>
      </main>
    </div>
  </div>

  <div class="admin-modal-backdrop um-muni-backdrop" id="muni-accounts-modal" hidden>
    <div class="admin-modal um-muni-modal" role="dialog" aria-modal="true" aria-labelledby="muni-accounts-title">
      <div class="um-muni-modal-head">
        <div class="um-muni-modal-head-main">
          <div class="um-muni-modal-icon" id="muni-modal-icon" aria-hidden="true">—</div>
          <div>
            <p class="um-muni-modal-kicker">Municipality accounts</p>
            <h3 id="muni-accounts-title">—</h3>
            <p class="um-muni-modal-sub" id="muni-accounts-sub">—</p>
          </div>
        </div>
        <button type="button" class="um-muni-modal-close" id="muni-accounts-close" aria-label="Close">
          <svg viewBox="0 0 24 24"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>
        </button>
      </div>
      <div class="um-muni-modal-summary" id="muni-modal-summary"></div>
      <div class="um-muni-modal-toolbar">
        <div class="mgmt-search um-muni-modal-search">
          <span class="mgmt-search-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
          </span>
          <input type="search" id="muni-accounts-search" class="mgmt-search-input" placeholder="Search barangay or username..." autocomplete="off">
        </div>
      </div>
      <div class="um-muni-modal-body">
        <div class="um-accounts-list" id="muni-accounts-list"></div>
        <p class="um-accounts-empty" id="muni-accounts-empty" hidden>No accounts match your search.</p>
      </div>
    </div>
  </div>

  <div class="admin-modal-backdrop" id="user-modal" hidden>
    <div class="admin-modal um-modal" role="dialog" aria-modal="true" aria-labelledby="user-modal-title">
      <div class="admin-modal-head">
        <div>
          <p class="admin-modal-kicker">User Management</p>
          <h3 id="user-modal-title">Add User</h3>
          <p class="admin-modal-sub" id="user-modal-sub">Create a municipality or barangay administrator login.</p>
        </div>
        <button type="button" class="admin-modal-close" id="user-modal-close" aria-label="Close">
          <svg viewBox="0 0 24 24"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>
        </button>
      </div>
      <div class="admin-modal-body">
        <?php if ($formErrors !== []): ?>
          <div class="admin-alert err" role="alert">
            <ul>
              <?php foreach ($formErrors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post" class="admin-modal-form" id="user-form">
          <input type="hidden" name="action" id="user-action" value="create_user">
          <input type="hidden" name="admin_id" id="user-admin-id" value="<?php echo htmlspecialchars($form['admin_id']); ?>">

          <div class="admin-form-grid hh-form-grid">
            <div class="admin-field span-2">
              <label for="user-role">Account role</label>
              <select name="role" id="user-role" required>
                <option value="muni_admin" <?php echo $form['role'] === 'muni_admin' ? 'selected' : ''; ?>>Admin (Municipality)</option>
                <option value="admin" <?php echo $form['role'] === 'admin' ? 'selected' : ''; ?>>Admin (Barangay)</option>
              </select>
            </div>
            <div class="admin-field span-2">
              <label for="user-username">Username</label>
              <input type="text" name="username" id="user-username" required maxlength="255" value="<?php echo htmlspecialchars($form['username']); ?>">
            </div>
            <div class="admin-field span-2" id="user-muni-wrap">
              <label for="user-muni-id">Municipality</label>
              <select name="city_municipality_id" id="user-muni-id">
                <option value="0">Select municipality</option>
                <?php foreach ($municipalities as $mun): ?>
                  <option
                    value="<?php echo (int) $mun['city_municipality_id']; ?>"
                    <?php echo (int) $form['city_municipality_id'] === (int) $mun['city_municipality_id'] ? 'selected' : ''; ?>
                  >
                    <?php echo htmlspecialchars((string) $mun['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="admin-field span-2" id="user-barangay-wrap">
              <label for="user-barangay-id">Barangay</label>
              <select name="barangay_id" id="user-barangay-id">
                <option value="0">Select barangay</option>
                <?php foreach ($barangayOptions as $opt): ?>
                  <option
                    value="<?php echo (int) $opt['barangay_id']; ?>"
                    data-muni-id="<?php echo (int) $opt['city_municipality_id']; ?>"
                    <?php echo (int) $form['barangay_id'] === (int) $opt['barangay_id'] ? 'selected' : ''; ?>
                  >
                    <?php echo htmlspecialchars((string) $opt['municipality'] . ' · ' . $opt['barangay']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="admin-field">
              <label for="user-status">Account status</label>
              <select name="account_status" id="user-status">
                <option value="active" <?php echo $form['account_status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $form['account_status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                <option value="suspended" <?php echo $form['account_status'] === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
              </select>
            </div>
            <div class="admin-field">
              <label for="user-password" id="user-password-label">Password</label>
              <input type="password" name="password" id="user-password" autocomplete="new-password">
            </div>
            <div class="admin-field span-2" id="user-password-confirm-wrap">
              <label for="user-password-confirm">Confirm password</label>
              <input type="password" name="password_confirm" id="user-password-confirm" autocomplete="new-password" value="<?php echo htmlspecialchars($form['password_confirm']); ?>">
              <p class="admin-field-hint" id="user-password-hint">Required when creating a new account.</p>
            </div>
          </div>

          <div class="admin-form-actions admin-form-actions-modal">
            <button type="button" class="btn btn-secondary" id="user-modal-cancel">Cancel</button>
            <button type="submit" class="btn btn-primary" id="user-modal-submit">Save account</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php require __DIR__ . '/../../includes/scripts.php'; ?>
  <script>
    window.UM_MUNI_CARDS = <?php echo $muniCardsJson; ?>;
  </script>
  <script>
    (function () {
      var muniCards = window.UM_MUNI_CARDS || [];
      var muniModal = document.getElementById('muni-accounts-modal');
      var muniCloseBtn = document.getElementById('muni-accounts-close');
      var muniTitle = document.getElementById('muni-accounts-title');
      var muniSub = document.getElementById('muni-accounts-sub');
      var muniIcon = document.getElementById('muni-modal-icon');
      var muniSummary = document.getElementById('muni-modal-summary');
      var muniList = document.getElementById('muni-accounts-list');
      var muniEmpty = document.getElementById('muni-accounts-empty');
      var muniSearch = document.getElementById('muni-accounts-search');
      var activeMuniCard = null;

      function escapeHtml(value) {
        return String(value || '')
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;');
      }

      function statusClass(status) {
        if (status === 'active' || status === 'inactive' || status === 'suspended') {
          return status;
        }
        return 'none';
      }

      function renderMuniAdminRow(muniAdmin, municipalityId) {
        if (!muniAdmin) {
          return ''
            + '<article class="um-account-row um-account-row--muni" data-search="municipality admin muni">'
            + '  <div class="um-account-main">'
            + '    <div class="um-account-avatar um-account-avatar--muni">M</div>'
            + '    <div>'
            + '      <p class="um-account-barangay">Municipality Administrator</p>'
            + '      <p class="um-account-user"><span class="um-no-account">No municipality admin assigned</span></p>'
            + '      <p class="um-account-role">Admin (Municipality)</p>'
            + '    </div>'
            + '  </div>'
            + '  <div class="um-account-meta">'
            + '    <span class="um-status is-none">Unassigned</span>'
            + '    <div class="hh-row-actions">'
            + '      <button type="button" class="hh-btn hh-btn-primary um-create-muni-btn" data-muni-id="' + municipalityId + '">Add account</button>'
            + '    </div>'
            + '  </div>'
            + '</article>';
        }

        var status = muniAdmin.account_status || '';
        return ''
          + '<article class="um-account-row um-account-row--muni" data-search="municipality admin ' + escapeHtml((muniAdmin.username || '').toLowerCase()) + '">'
          + '  <div class="um-account-main">'
          + '    <div class="um-account-avatar um-account-avatar--muni">M</div>'
          + '    <div>'
          + '      <p class="um-account-barangay">Municipality Administrator</p>'
          + '      <p class="um-account-user">' + escapeHtml(muniAdmin.username) + '</p>'
          + '      <p class="um-account-role">Admin (Municipality)</p>'
          + '    </div>'
          + '  </div>'
          + '  <div class="um-account-meta">'
          + '    <span class="um-status is-' + statusClass(status) + '">' + escapeHtml(status.charAt(0).toUpperCase() + status.slice(1)) + '</span>'
          + '    <div class="hh-row-actions">'
          + '      <button type="button" class="hh-btn hh-btn-secondary um-edit-muni-btn"'
          + '        data-admin-id="' + muniAdmin.admin_id + '"'
          + '        data-username="' + escapeHtml(muniAdmin.username) + '"'
          + '        data-muni-id="' + municipalityId + '"'
          + '        data-status="' + escapeHtml(status) + '">Edit</button>'
          + '    </div>'
          + '  </div>'
          + '</article>';
      }

      function renderAccountRow(row) {
        var hasUser = !!row.admin_id;
        var status = row.account_status || '';
        var statusLabel = hasUser ? status.charAt(0).toUpperCase() + status.slice(1) : 'Unassigned';
        var actions = hasUser
          ? '<button type="button" class="hh-btn hh-btn-secondary um-edit-btn"'
            + ' data-admin-id="' + row.admin_id + '"'
            + ' data-username="' + escapeHtml(row.username) + '"'
            + ' data-role="admin"'
            + ' data-barangay-id="' + row.barangay_id + '"'
            + ' data-muni-id="' + row.city_municipality_id + '"'
            + ' data-status="' + escapeHtml(status) + '">Edit</button>'
          : '<button type="button" class="hh-btn hh-btn-primary um-create-btn"'
            + ' data-role="admin"'
            + ' data-barangay-id="' + row.barangay_id + '"'
            + ' data-muni-id="' + row.city_municipality_id + '"'
            + ' data-barangay="' + escapeHtml(row.barangay) + '">Add account</button>';

        return ''
          + '<article class="um-account-row" data-search="' + escapeHtml((row.barangay + ' ' + (row.username || '')).toLowerCase()) + '">'
          + '  <div class="um-account-main">'
          + '    <div class="um-account-avatar">' + escapeHtml((row.barangay || '?').charAt(0).toUpperCase()) + '</div>'
          + '    <div>'
          + '      <p class="um-account-barangay">' + escapeHtml(row.barangay) + '</p>'
          + '      <p class="um-account-user">' + (hasUser ? escapeHtml(row.username) : '<span class="um-no-account">No account assigned</span>') + '</p>'
          + '      <p class="um-account-role">Admin (Barangay)</p>'
          + '    </div>'
          + '  </div>'
          + '  <div class="um-account-meta">'
          + '    <span class="um-status is-' + statusClass(status) + '">' + escapeHtml(statusLabel) + '</span>'
          + '    <div class="hh-row-actions">' + actions + '</div>'
          + '  </div>'
          + '</article>';
      }

      function filterMuniAccounts() {
        if (!muniList || !muniSearch) return;
        var q = muniSearch.value.trim().toLowerCase();
        var rows = muniList.querySelectorAll('.um-account-row');
        var visible = 0;
        rows.forEach(function (row) {
          var hay = row.getAttribute('data-search') || '';
          var show = !q || hay.indexOf(q) !== -1;
          row.hidden = !show;
          if (show) visible += 1;
        });
        if (muniEmpty) muniEmpty.hidden = visible > 0;
      }

      function openMuniModal(card) {
        if (!muniModal || !card) return;
        activeMuniCard = card;
        var initials = (card.municipality || '?').split(/\s+/).map(function (p) { return p.charAt(0); }).slice(0, 2).join('').toUpperCase();

        muniTitle.textContent = card.municipality;
        muniSub.textContent = card.total_barangays + ' barangays · ' + card.with_account + ' barangay accounts';
        muniIcon.textContent = initials;
        muniSummary.innerHTML = ''
          + '<div><span>Muni Admin</span><strong>' + (card.muni_admin ? escapeHtml(card.muni_admin.username) : 'None') + '</strong></div>'
          + '<div><span>Barangay</span><strong>' + card.with_account + '</strong></div>'
          + '<div><span>Active</span><strong>' + card.active + '</strong></div>'
          + '<div><span>Unassigned</span><strong>' + card.unassigned + '</strong></div>';

        muniList.innerHTML = renderMuniAdminRow(card.muni_admin || null, card.municipality_id)
          + (card.accounts || []).map(renderAccountRow).join('');
        if (muniSearch) muniSearch.value = '';
        if (muniEmpty) muniEmpty.hidden = true;

        muniModal.hidden = false;
        document.body.style.overflow = 'hidden';
        bindAccountActions(muniList);
        if (muniSearch) muniSearch.focus();
      }

      function closeMuniModal() {
        if (!muniModal) return;
        muniModal.hidden = true;
        activeMuniCard = null;
        document.body.style.overflow = '';
      }

      document.querySelectorAll('.um-muni-card[data-muni-id]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var id = parseInt(btn.getAttribute('data-muni-id'), 10);
          var card = muniCards.find(function (item) { return item.municipality_id === id; });
          if (card) openMuniModal(card);
        });
      });

      if (muniCloseBtn) muniCloseBtn.addEventListener('click', closeMuniModal);
      if (muniModal) {
        muniModal.addEventListener('click', function (e) {
          if (e.target === muniModal) closeMuniModal();
        });
      }
      if (muniSearch) muniSearch.addEventListener('input', filterMuniAccounts);

      var modal = document.getElementById('user-modal');
      var openCreateBtn = document.getElementById('btn-open-create');
      var closeBtn = document.getElementById('user-modal-close');
      var cancelBtn = document.getElementById('user-modal-cancel');
      var actionInput = document.getElementById('user-action');
      var adminIdInput = document.getElementById('user-admin-id');
      var titleEl = document.getElementById('user-modal-title');
      var subEl = document.getElementById('user-modal-sub');
      var usernameInput = document.getElementById('user-username');
      var barangaySelect = document.getElementById('user-barangay-id');
      var statusSelect = document.getElementById('user-status');
      var passwordInput = document.getElementById('user-password');
      var passwordHint = document.getElementById('user-password-hint');
      var passwordLabel = document.getElementById('user-password-label');
      var submitBtn = document.getElementById('user-modal-submit');
      var roleSelect = document.getElementById('user-role');
      var muniSelect = document.getElementById('user-muni-id');
      var muniWrap = document.getElementById('user-muni-wrap');
      var barangayWrap = document.getElementById('user-barangay-wrap');
      var shouldOpen = <?php echo json_encode($openModal); ?>;

      function filterBarangayOptions() {
        if (!barangaySelect || !muniSelect || !roleSelect) return;
        var muniId = roleSelect.value === 'admin' ? muniSelect.value : '';
        Array.prototype.forEach.call(barangaySelect.options, function (opt) {
          if (!opt.value || opt.value === '0') {
            opt.hidden = false;
            return;
          }
          opt.hidden = muniId !== '' && opt.getAttribute('data-muni-id') !== muniId;
        });
        if (muniId !== '') {
          var selected = barangaySelect.options[barangaySelect.selectedIndex];
          if (selected && selected.hidden) barangaySelect.value = '0';
        }
      }

      function syncRoleFields() {
        if (!roleSelect) return;
        var role = roleSelect.value;
        var isMuni = role === 'muni_admin';
        var isEdit = actionInput.value === 'update_user';

        if (muniWrap) muniWrap.hidden = false;
        if (barangayWrap) barangayWrap.hidden = isMuni;
        if (roleSelect) roleSelect.disabled = isEdit;
        if (muniSelect) muniSelect.required = true;
        if (barangaySelect) barangaySelect.required = !isMuni;

        filterBarangayOptions();
      }

      function setMode(mode, data) {
        data = data || {};
        var isEdit = mode === 'edit';
        var role = data.role || 'admin';
        var roleLabel = role === 'muni_admin' ? 'Admin (Municipality)' : 'Admin (Barangay)';

        actionInput.value = isEdit ? 'update_user' : 'create_user';
        adminIdInput.value = isEdit ? String(data.adminId || '0') : '0';
        titleEl.textContent = isEdit ? ('Edit ' + roleLabel) : 'Add User';
        subEl.textContent = isEdit
          ? ('Update login details for this ' + roleLabel.toLowerCase() + ' account.')
          : 'Create a municipality or barangay administrator login.';
        submitBtn.textContent = isEdit ? 'Save changes' : 'Create account';

        if (roleSelect) roleSelect.value = role;
        usernameInput.value = data.username || '';
        if (muniSelect && data.muniId) muniSelect.value = String(data.muniId);
        if (barangaySelect && data.barangayId) barangaySelect.value = String(data.barangayId);
        statusSelect.value = data.status || 'active';

        passwordInput.value = '';
        passwordInput.required = !isEdit;
        passwordLabel.textContent = isEdit ? 'New password (optional)' : 'Password';
        passwordHint.textContent = isEdit
          ? 'Leave blank to keep the current password.'
          : 'Required when creating a new account.';

        syncRoleFields();
      }

      function openUserModal(mode, data) {
        if (!modal) return;
        setMode(mode || 'create', data || {});
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        usernameInput.focus();
      }

      function closeUserModal() {
        if (!modal) return;
        modal.hidden = true;
        if (!muniModal || muniModal.hidden) {
          document.body.style.overflow = '';
        }
      }

      function bindAccountActions(root) {
        if (!root) return;

        root.querySelectorAll('.um-create-btn').forEach(function (btn) {
          btn.addEventListener('click', function () {
            openUserModal('create', {
              role: 'admin',
              muniId: btn.getAttribute('data-muni-id'),
              barangayId: btn.getAttribute('data-barangay-id'),
              username: btn.getAttribute('data-barangay') || ''
            });
          });
        });

        root.querySelectorAll('.um-create-muni-btn').forEach(function (btn) {
          btn.addEventListener('click', function () {
            openUserModal('create', {
              role: 'muni_admin',
              muniId: btn.getAttribute('data-muni-id')
            });
          });
        });

        root.querySelectorAll('.um-edit-btn').forEach(function (btn) {
          btn.addEventListener('click', function () {
            openUserModal('edit', {
              role: 'admin',
              adminId: btn.getAttribute('data-admin-id'),
              username: btn.getAttribute('data-username') || '',
              muniId: btn.getAttribute('data-muni-id'),
              barangayId: btn.getAttribute('data-barangay-id'),
              status: btn.getAttribute('data-status') || 'active'
            });
          });
        });

        root.querySelectorAll('.um-edit-muni-btn').forEach(function (btn) {
          btn.addEventListener('click', function () {
            openUserModal('edit', {
              role: 'muni_admin',
              adminId: btn.getAttribute('data-admin-id'),
              username: btn.getAttribute('data-username') || '',
              muniId: btn.getAttribute('data-muni-id'),
              status: btn.getAttribute('data-status') || 'active'
            });
          });
        });
      }

      if (roleSelect) roleSelect.addEventListener('change', syncRoleFields);
      if (muniSelect) muniSelect.addEventListener('change', filterBarangayOptions);

      if (openCreateBtn) {
        openCreateBtn.addEventListener('click', function () {
          openUserModal('create');
        });
      }

      if (closeBtn) closeBtn.addEventListener('click', closeUserModal);
      if (cancelBtn) cancelBtn.addEventListener('click', closeUserModal);

      if (modal) {
        modal.addEventListener('click', function (e) {
          if (e.target === modal) closeUserModal();
        });
      }

      document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        if (modal && !modal.hidden) {
          closeUserModal();
          return;
        }
        if (muniModal && !muniModal.hidden) closeMuniModal();
      });

      if (shouldOpen) {
        openUserModal(shouldOpen === 'edit' ? 'edit' : 'create');
      }
    })();
  </script>
</body>
</html>
