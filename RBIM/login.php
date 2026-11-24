<?php
/**
 * Authentication helpers and login handler for RBIM.
 */

declare(strict_types=1);

require_once __DIR__ . '/plugins/conn.php';

function rbim_start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function current_user(): ?array
{
    rbim_start_session();

    if (empty($_SESSION['user']) || !is_array($_SESSION['user'])) {
        return null;
    }

    return $_SESSION['user'];
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_super_admin(?array $user = null): bool
{
    $user = $user ?? current_user();
    return $user !== null && ($user['role'] ?? '') === 'super_admin';
}

function is_muni_admin(?array $user = null): bool
{
    $user = $user ?? current_user();
    return $user !== null && ($user['role'] ?? '') === 'muni_admin';
}

function is_barangay_admin(?array $user = null): bool
{
    $user = $user ?? current_user();
    return $user !== null && ($user['role'] ?? '') === 'admin';
}

function is_admin(?array $user = null): bool
{
    return is_barangay_admin($user);
}

/** @return array<string, string> */
function rbim_roles(): array
{
    return [
        'super_admin' => 'Super Admin',
        'muni_admin' => 'Admin (Municipality)',
        'admin' => 'Admin (Barangay)',
    ];
}

function role_label(string $role): string
{
    return rbim_roles()[$role] ?? ucfirst(str_replace('_', ' ', $role));
}

function rbim_ensure_user_schema(PDO $pdo): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    try {
        $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'city_municipality_id'")->fetchAll();
        if ($cols === []) {
            $pdo->exec('ALTER TABLE users ADD COLUMN city_municipality_id INT(11) NULL DEFAULT NULL AFTER barangay_id');
        }
    } catch (Throwable $e) {
        // Schema upgrade is optional at runtime; migration SQL is also provided.
    }
}

/**
 * Create missing municipality admin accounts for every LGU.
 * Username = municipality name, password = default password (admin123).
 * Also repairs legacy seeded accounts (username like larena_admin) so they can log in.
 * Returns number of accounts created or repaired.
 */
function rbim_seed_municipality_admins(PDO $pdo, string $defaultPassword = 'admin123'): int
{
    rbim_ensure_user_schema($pdo);

    try {
        $municipalities = $pdo->query(
            'SELECT city_municipality_id, name FROM city_municipality ORDER BY name ASC'
        )->fetchAll();

        if ($municipalities === []) {
            return 0;
        }

        $existingStmt = $pdo->prepare(
            "SELECT admin_id, username
             FROM users
             WHERE role = 'muni_admin' AND city_municipality_id = :muni_id
             ORDER BY admin_id ASC
             LIMIT 1"
        );
        $usernameTaken = $pdo->prepare(
            'SELECT admin_id FROM users WHERE username = :username AND admin_id <> :exclude_id LIMIT 1'
        );
        $insert = $pdo->prepare(
            "INSERT INTO users (username, password, role, account_status, barangay, barangay_id, city_municipality_id)
             VALUES (:username, :password, 'muni_admin', 'active', NULL, NULL, :city_municipality_id)"
        );
        $update = $pdo->prepare(
            "UPDATE users
             SET username = :username,
                 password = :password,
                 account_status = 'active',
                 barangay = NULL,
                 barangay_id = NULL
             WHERE admin_id = :admin_id AND role = 'muni_admin'"
        );

        $passwordHash = password_hash($defaultPassword, PASSWORD_DEFAULT);
        $changed = 0;

        foreach ($municipalities as $mun) {
            $muniId = (int) $mun['city_municipality_id'];
            $username = trim((string) $mun['name']);
            if ($username === '' || $muniId <= 0) {
                continue;
            }

            $existingStmt->execute([':muni_id' => $muniId]);
            $existing = $existingStmt->fetch();

            if (!$existing) {
                $usernameTaken->execute([':username' => $username, ':exclude_id' => 0]);
                if ($usernameTaken->fetch()) {
                    continue;
                }

                $insert->execute([
                    ':username' => $username,
                    ':password' => $passwordHash,
                    ':city_municipality_id' => $muniId,
                ]);
                $changed++;
                continue;
            }

            $currentUsername = (string) ($existing['username'] ?? '');
            $isLegacyUsername = strcasecmp($currentUsername, $username) !== 0;

            if (!$isLegacyUsername) {
                continue;
            }

            $usernameTaken->execute([
                ':username' => $username,
                ':exclude_id' => (int) $existing['admin_id'],
            ]);
            if ($usernameTaken->fetch()) {
                continue;
            }

            $update->execute([
                ':username' => $username,
                ':password' => $passwordHash,
                ':admin_id' => (int) $existing['admin_id'],
            ]);
            $changed++;
        }

        return $changed;
    } catch (Throwable $e) {
        return 0;
    }
}

function app_base_path(): string
{
    static $base = null;

    if ($base !== null) {
        return $base;
    }

    $projectRoot = realpath(__DIR__);
    $docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');

    if ($projectRoot && $docRoot && str_starts_with($projectRoot, $docRoot)) {
        $relative = str_replace('\\', '/', substr($projectRoot, strlen($docRoot)));
        $base = rtrim($relative, '/') ?: '';
    } else {
        $base = '/RBIM';
    }

    return $base;
}

function app_url(string $path = ''): string
{
    $path = '/' . ltrim($path, '/');
    return app_base_path() . ($path === '/' ? '' : $path);
}

function dashboard_path_for_role(string $role): string
{
    return app_url('pages/dashboard.php');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function require_login(): array
{
    $user = current_user();

    if ($user === null) {
        redirect(app_url('index.php'));
    }

    return $user;
}

function require_role(string ...$roles): array
{
    $user = require_login();

    if (!in_array($user['role'], $roles, true)) {
        redirect(dashboard_path_for_role($user['role']));
    }

    return $user;
}

function log_activity(?int $userId, string $username, string $action, ?string $details = null): void
{
    try {
        $stmt = db()->prepare(
            'INSERT INTO activity_logs (user_id, username, action, details, ip_address, user_agent, created_at)
             VALUES (:user_id, :username, :action, :details, :ip_address, :user_agent, NOW())'
        );

        $stmt->execute([
            ':user_id' => $userId ?? 0,
            ':username' => mb_substr($username, 0, 50),
            ':action' => mb_substr($action, 0, 50),
            ':details' => $details,
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            ':user_agent' => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 255),
        ]);
    } catch (Throwable $e) {
        // Do not block login/logout if logging fails.
    }
}

/**
 * @return array{ok:bool, error?:string, user?:array}
 */
function attempt_login(string $username, string $password): array
{
    $username = trim($username);

    if ($username === '' || $password === '') {
        return ['ok' => false, 'error' => 'Please enter your username and password.'];
    }

    $pdo = db();
    rbim_ensure_user_schema($pdo);
    rbim_seed_municipality_admins($pdo);

    $stmt = $pdo->prepare(
        'SELECT u.admin_id, u.username, u.password, u.role, u.account_status, u.barangay, u.barangay_id, u.city_municipality_id,
                cm.name AS municipality
         FROM users u
         LEFT JOIN city_municipality cm ON cm.city_municipality_id = u.city_municipality_id
         WHERE LOWER(u.username) = LOWER(:username)
         LIMIT 1'
    );
    $stmt->execute([':username' => $username]);
    $row = $stmt->fetch();

    $passwordOk = false;
    if ($row) {
        $stored = (string) ($row['password'] ?? '');
        $passwordOk = password_verify($password, $stored);
        if (!$passwordOk && hash_equals($stored, $password)) {
            $passwordOk = true;
            $rehash = $pdo->prepare('UPDATE users SET password = :password WHERE admin_id = :admin_id');
            $rehash->execute([
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':admin_id' => (int) $row['admin_id'],
            ]);
        }
    }

    if (!$row || !$passwordOk) {
        log_activity(
            $row['admin_id'] ?? null,
            $username !== '' ? $username : 'unknown',
            'login_failed',
            'Invalid username or password'
        );
        return ['ok' => false, 'error' => 'Invalid username or password.'];
    }

    if (($row['account_status'] ?? '') !== 'active') {
        log_activity(
            (int) $row['admin_id'],
            $row['username'],
            'login_blocked',
            'Account status: ' . ($row['account_status'] ?? 'unknown')
        );
        return ['ok' => false, 'error' => 'Your account is not active. Contact the system administrator.'];
    }

    $allowedRoles = ['super_admin', 'muni_admin', 'admin'];
    if (!in_array($row['role'], $allowedRoles, true)) {
        return ['ok' => false, 'error' => 'Your account role is not authorized to sign in.'];
    }

    rbim_start_session();
    session_regenerate_id(true);

    $_SESSION['user'] = [
        'admin_id' => (int) $row['admin_id'],
        'username' => $row['username'],
        'role' => $row['role'],
        'account_status' => $row['account_status'],
        'barangay' => $row['barangay'],
        'barangay_id' => $row['barangay_id'] !== null ? (int) $row['barangay_id'] : null,
        'city_municipality_id' => $row['city_municipality_id'] !== null ? (int) $row['city_municipality_id'] : null,
        'municipality' => $row['municipality'] ?? null,
    ];

    log_activity((int) $row['admin_id'], $row['username'], 'login', 'Successful sign-in');

    return ['ok' => true, 'user' => $_SESSION['user']];
}

function user_display_name(array $user): string
{
    if (($user['role'] ?? '') === 'muni_admin' && !empty($user['municipality'])) {
        return (string) $user['municipality'];
    }

    if (!empty($user['barangay'])) {
        return (string) $user['barangay'];
    }

    return (string) ($user['username'] ?? 'User');
}

function user_initials(array $user): string
{
    $name = user_display_name($user);
    $parts = preg_split('/[\s\.\-_]+/', $name) ?: [];
    $parts = array_values(array_filter($parts));

    if (count($parts) >= 2) {
        return strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1));
    }

    return strtoupper(mb_substr($name, 0, 2));
}

if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'login.php') {
    rbim_start_session();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect(app_url('index.php'));
    }

    $username = isset($_POST['username']) ? (string) $_POST['username'] : '';
    $password = isset($_POST['password']) ? (string) $_POST['password'] : '';

    $result = attempt_login($username, $password);

    if (!$result['ok']) {
        $_SESSION['login_error'] = $result['error'] ?? 'Unable to sign in.';
        $_SESSION['login_username'] = trim($username);
        redirect(app_url('index.php'));
    }

    $user = $result['user'];
    redirect(dashboard_path_for_role($user['role']));
}
