<?php
/**
 * Sign the current user out.
 */

declare(strict_types=1);

require_once __DIR__ . '/login.php';

function logout_user(): void
{
    rbim_start_session();
    $user = current_user();

    if ($user !== null) {
        log_activity((int) $user['admin_id'], $user['username'], 'logout', 'Signed out');
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'] ?? '',
            (bool) $params['secure'],
            (bool) $params['httponly']
        );
    }

    session_destroy();
}

if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'logout.php') {
    logout_user();
    redirect(app_url('index.php'));
}
