<?php
/**
 * General Configuration File
 * Contains application settings and constants
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Application settings
define('SITE_NAME', 'Wellness Center Booking & Reservation System');
define('SITE_URL', 'http://localhost/CIT17-Final-Project');
define('ADMIN_EMAIL', 'admin@wellness.com');

// Directory paths
define('ROOT_PATH', dirname(dirname(__FILE__)));
define('UPLOAD_PATH', ROOT_PATH . '/uploads/');
define('IMAGE_PATH', ROOT_PATH . '/assets/images/');

// URL paths
define('BASE_URL', SITE_URL . '/');
define('ASSETS_URL', BASE_URL . 'assets/');
define('CSS_URL', ASSETS_URL . 'css/');
define('JS_URL', ASSETS_URL . 'js/');
define('IMG_URL', ASSETS_URL . 'images/');

// Time zone
date_default_timezone_set('Asia/Manila');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session timeout (30 minutes)
define('SESSION_TIMEOUT', 1800);

// Pagination
define('ITEMS_PER_PAGE', 10);

// Include database configuration
require_once ROOT_PATH . '/config/database.php';

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if user is therapist
 */
function isTherapist() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'therapist';
}

/**
 * Check if user is customer
 */
function isCustomer() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'customer';
}

/**
 * Redirect to a specific page
 */
function redirect($page) {
    header('Location: ' . BASE_URL . $page);
    exit();
}

/**
 * Sanitize input data
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate($date) {
    return date('F d, Y', strtotime($date));
}

/**
 * Format time
 */
function formatTime($time) {
    return date('h:i A', strtotime($time));
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Set flash message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}
?>

