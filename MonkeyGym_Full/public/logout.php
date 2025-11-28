<?php
require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/helpers.php';

startSession();

// Log activity trước khi logout
if (isLoggedIn()) {
    $db = new Database();
    logActivity($_SESSION['user_id'], 'Đăng xuất', 'Đăng xuất khỏi hệ thống');
}

// Xóa session
session_destroy();

// Redirect về trang login
redirect('public/login.php');
?>
