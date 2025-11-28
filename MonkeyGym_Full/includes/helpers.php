<?php
/**
 * Helper Functions
 */

// Bắt đầu session nếu chưa có
function startSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Kiểm tra đăng nhập
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']) && isset($_SESSION['vai_tro']);
}

// Kiểm tra quyền
function hasRole($roles) {
    startSession();
    if (!isLoggedIn()) return false;
    
    if (is_array($roles)) {
        return in_array($_SESSION['vai_tro'], $roles);
    }
    return $_SESSION['vai_tro'] === $roles;
}

// Redirect
function redirect($url) {
    header("Location: " . SITE_URL . "/" . $url);
    exit();
}

// JSON response
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

// Sanitize input
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Generate QR Code
function generateQRCode($data, $filename) {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    try {
        $qrCode = new \Endroid\QrCode\QrCode($data);
        $qrCode->setSize(300);
        $qrCode->setMargin(10);
        
        $path = QR_PATH . $filename . '.png';
        $qrCode->writeFile($path);
        
        return 'uploads/qr/' . $filename . '.png';
    } catch (Exception $e) {
        return false;
    }
}

// Format currency VND
function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.') . 'đ';
}

// Format date
function formatDate($date, $format = 'd/m/Y') {
    if (!$date) return '';
    return date($format, strtotime($date));
}

// Format datetime
function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    if (!$datetime) return '';
    return date($format, strtotime($datetime));
}

// Calculate days difference
function daysDifference($date1, $date2 = null) {
    if (!$date2) $date2 = date('Y-m-d');
    
    $datetime1 = new DateTime($date1);
    $datetime2 = new DateTime($date2);
    $interval = $datetime2->diff($datetime1);
    
    return $interval->days * ($interval->invert ? -1 : 1);
}

// Get package status
function getPackageStatus($endDate) {
    $days = daysDifference($endDate);
    
    if ($days < 0) {
        return ['status' => 'het_han', 'class' => 'danger', 'text' => 'Hết hạn'];
    } elseif ($days <= 7) {
        return ['status' => 'sap_het_han', 'class' => 'warning', 'text' => 'Sắp hết hạn'];
    } else {
        return ['status' => 'dang_hoat_dong', 'class' => 'success', 'text' => 'Hoạt động'];
    }
}

// Upload file
function uploadFile($file, $folder = 'uploads') {
    $target_dir = __DIR__ . "/../public/" . $folder . "/";
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $filename = uniqid() . '_' . basename($file["name"]);
    $target_file = $target_dir . $filename;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check file size
    if ($file["size"] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File quá lớn (max 5MB)'];
    }
    
    // Allow certain file formats
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    if (!in_array($fileType, $allowed)) {
        return ['success' => false, 'message' => 'Chỉ cho phép JPG, JPEG, PNG, GIF, PDF'];
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => true, 'path' => $folder . '/' . $filename];
    }
    
    return ['success' => false, 'message' => 'Lỗi upload file'];
}

// Get user info
function getUserInfo() {
    startSession();
    if (!isLoggedIn()) return null;
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'ho_ten' => $_SESSION['ho_ten'] ?? '',
        'vai_tro' => $_SESSION['vai_tro'] ?? '',
        'avatar' => $_SESSION['avatar'] ?? null
    ];
}

// Set flash message
function setFlash($type, $message) {
    startSession();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// Get flash message
function getFlash() {
    startSession();
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Generate random string
function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

// Send email (basic)
function sendEmail($to, $subject, $message) {
    $headers = "From: " . SITE_NAME . " <noreply@monkeygym.vn>\r\n";
    $headers .= "Reply-To: noreply@monkeygym.vn\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Log activity
function logActivity($userId, $action, $details = '') {
    global $db;
    
    $sql = "INSERT INTO log_hoat_dong (ma_nguoi_dung, hanh_dong, chi_tiet, ip_address, user_agent) 
            VALUES (:user_id, :action, :details, :ip, :user_agent)";
    
    $params = [
        ':user_id' => $userId,
        ':action' => $action,
        ':details' => $details,
        ':ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ];
    
    $db->query($sql, $params);
}
?>
