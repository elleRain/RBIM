<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Basic session check functionality
 * 
 * This file provides basic session validation and security functions
 * for the RBIM application.
 */

/**
 * Check if a user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

/**
 * Check if user has specific role
 * 
 * @param string $role The role to check for
 * @return bool True if user has the specified role, false otherwise
 */
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Get current user ID
 * 
 * @return int|null User ID if logged in, null otherwise
 */
function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

/**
 * Get current username
 * 
 * @return string|null Username if logged in, null otherwise
 */
function getCurrentUsername() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : null;
}

// Set default session timeout (30 minutes)
if (!isset($_SESSION['LAST_ACTIVITY'])) {
    $_SESSION['LAST_ACTIVITY'] = time();
}

// Check if session has expired (30 minutes of inactivity)
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    // Session expired, destroy it
    session_unset();
    session_destroy();
    // This is just a fallback, actual redirects should be handled by the including file
}

// Update last activity time
$_SESSION['LAST_ACTIVITY'] = time();
?>