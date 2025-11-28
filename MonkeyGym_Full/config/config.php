<?php
/**
 * Database Configuration
 * Monkey Gym Management System
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gym_db');
define('DB_CHARSET', 'utf8mb4');

// Site settings
define('SITE_URL', 'http://localhost/MonkeyGym_Full');
define('SITE_NAME', 'Monkey Gym');

// Session settings
ini_set('session.gc_maxlifetime', 3600); // 1 hour
session_set_cookie_params(3600);

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Error reporting (development mode)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Upload settings
define('UPLOAD_PATH', __DIR__ . '/../public/uploads/');
define('QR_PATH', UPLOAD_PATH . 'qr/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Create upload directories if not exist
if (!file_exists(QR_PATH)) {
    mkdir(QR_PATH, 0777, true);
}
?>
