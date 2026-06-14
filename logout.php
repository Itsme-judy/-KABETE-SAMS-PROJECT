<?php
/**
 * logout.php - Secure logout handler
 * Properly destroys session and clears all authentication data
 */

session_start();

// Store username for logging (before destroying session)
$username = $_SESSION['username'] ?? 'Unknown';

// 1. Unset all session variables
$_SESSION = array();

// 2. Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// 3. Destroy the session
session_destroy();

// 4. Log the logout event
error_log("[LOGOUT] User: " . $username . " Time: " . date('Y-m-d H:i:s'));

// 5. Set security headers to prevent caching of sensitive pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// 6. Redirect to login page
header("Location: login.php");
exit();
?>
