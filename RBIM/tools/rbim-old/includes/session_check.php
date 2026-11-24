<?php
// Start session if not already started
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
} catch (Exception $e) {
    error_log("Session start error: " . $e->getMessage());
    // Create a fallback session array if session fails
    if (!isset($_SESSION)) {
        $_SESSION = array();
    }
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
    try {
        return isset($_SESSION) && isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
    } catch (Exception $e) {
        error_log("Session check error in isLoggedIn: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if user has specific role
 * 
 * @param string $role The role to check for
 * @return bool True if user has the specified role, false otherwise
 */
function hasRole($role) {
    try {
        return isset($_SESSION) && isset($_SESSION['role']) && $_SESSION['role'] === $role;
    } catch (Exception $e) {
        error_log("Session check error in hasRole: " . $e->getMessage());
        return false;
    }
}

/**
 * Get current user ID
 * 
 * @return int|null User ID if logged in, null otherwise
 */
function getCurrentUserId() {
    try {
        return isset($_SESSION) && isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    } catch (Exception $e) {
        error_log("Session check error in getCurrentUserId: " . $e->getMessage());
        return null;
    }
}

/**
 * Get current username
 * 
 * @return string|null Username if logged in, null otherwise
 */
function getCurrentUsername() {
    try {
        return isset($_SESSION) && isset($_SESSION['username']) ? $_SESSION['username'] : null;
    } catch (Exception $e) {
        error_log("Session check error in getCurrentUsername: " . $e->getMessage());
        return null;
    }
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