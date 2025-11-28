<?php
require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/helpers.php';

startSession();

// Kiểm tra quyền (chỉ admin và nhân viên)
if (!hasRole(['quan_tri', 'nhan_vien'])) {
    jsonResponse(['success' => false, 'message' => 'Không có quyền truy cập'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$db = new Database();

try {
    // Lấy dữ liệu
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate
    $required = ['ho_ten', 'email', 'so_dien_thoai', 'gioi_tinh', 'ma_goi'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            jsonResponse(['success' => false, 'message' => 'Thiếu trường: ' . $field], 400);
        }
    }
    
    // Kiểm tra email trùng
    $checkEmail = $db->selectOne(
        "SELECT ma_nguoi_dung FROM nguoi_dung WHERE email = :email",
        [':email' => $data['email']]
    );
    
    if ($checkEmail) {
        jsonResponse(['success' => false, 'message' => 'Email đã tồn tại!'], 400);
    }
    
    // Bắt đầu transaction
    $db->beginTransaction();
    
    // Tạo username từ email
    $username = explode('@', $data['email'])[0] . rand(100, 999);
    $password = generateRandomString(8); // Mật khẩu ngẫu nhiên
    
    // Insert vào bảng nguoi_dung
    $sqlUser = "INSERT INTO nguoi_dung (ten_dang_nhap, mat_khau, email, so_dien_thoai, vai_tro, ho_ten) 
                VALUES (:username, :password, :email, :phone, 'hoi_vien', :ho_ten)";
    
    $paramsUser = [
        ':username' => $username,
        ':password' => hashPassword($password),
        ':email' => $data['email'],
        ':phone' => $data['so_dien_thoai'],
        ':ho_ten' => $data['ho_ten']
    ];
    
    $userId = $db->insert($sqlUser, $paramsUser);
    
    if (!$userId) {
        throw new Exception('Lỗi tạo tài khoản');
    }
    
    // Tạo mã QR
    $qrData = "MEMBER:" . $userId;
    $qrFilename = 'member_' . $userId . '_' . time();
    $qrPath = generateQRCode($qrData, $qrFilename);
    
    // Insert vào bảng hoi_vien
    $sqlMember = "INSERT INTO hoi_vien (ma_nguoi_dung, gioi_tinh, ngay_sinh, dia_chi, 
                  sdt_khan_cap, ten_lien_he_khan_cap, ma_qr, ghi_chu_suc_khoe, muc_tieu) 
                  VALUES (:user_id, :gioi_tinh, :ngay_sinh, :dia_chi, :sdt_kc, :ten_kc, :qr, :ghi_chu, :muc_tieu)";
    
    $paramsMember = [
        ':user_id' => $userId,
        ':gioi_tinh' => $data['gioi_tinh'],
        ':ngay_sinh' => $data['ngay_sinh'] ?? null,
        ':dia_chi' => $data['dia_chi'] ?? null,
        ':sdt_kc' => $data['sdt_khan_cap'] ?? null,
        ':ten_kc' => $data['ten_lien_he_kc'] ?? null,
        ':qr' => $qrPath,
        ':ghi_chu' => $data['ghi_chu_suc_khoe'] ?? null,
        ':muc_tieu' => $data['muc_tieu'] ?? null
    ];
    
    $memberId = $db->insert($sqlMember, $paramsMember);
    
    if (!$memberId) {
        throw new Exception('Lỗi tạo thông tin hội viên');
    }
    
    // Lấy thông tin gói tập
    $package = $db->selectOne(
        "SELECT * FROM goi_tap WHERE ma_goi = :id",
        [':id' => $data['ma_goi']]
    );
    
    if (!$package) {
        throw new Exception('Gói tập không tồn tại');
    }
    
    // Tính ngày bắt đầu và kết thúc
    $startDate = $data['ngay_bat_dau'] ?? date('Y-m-d');
    $endDate = date('Y-m-d', strtotime($startDate . ' + ' . $package['thoi_han'] . ' months'));
    $alertDate = date('Y-m-d', strtotime($endDate . ' - 7 days'));
    
    // Áp dụng khuyến mãi nếu có
    $discount = 0;
    if (!empty($data['ma_khuyen_mai'])) {
        $promo = $db->selectOne(
            "SELECT * FROM khuyen_mai WHERE ma_code = :code AND trang_thai = 1 
             AND ngay_bat_dau <= CURDATE() AND ngay_ket_thuc >= CURDATE()",
            [':code' => $data['ma_khuyen_mai']]
        );
        
        if ($promo) {
            if ($promo['loai_khuyen_mai'] == 'phan_tram') {
                $discount = $package['gia'] * $promo['gia_tri'] / 100;
            } else {
                $discount = $promo['gia_tri'];
            }
        }
    }
    
    $finalPrice = $package['gia'] - $discount;
    
    // Đăng ký gói tập
    $sqlPackage = "INSERT INTO dang_ky_goi (ma_hoi_vien, ma_goi, ngay_bat_dau, ngay_ket_thuc, 
                   ngay_canh_bao, trang_thai, gia_goc, giam_gia, gia_thanh_toan, nguoi_dang_ky) 
                   VALUES (:member_id, :package_id, :start, :end, :alert, 'cho_thanh_toan', 
                   :original, :discount, :final, :staff)";
    
    $paramsPackage = [
        ':member_id' => $memberId,
        ':package_id' => $data['ma_goi'],
        ':start' => $startDate,
        ':end' => $endDate,
        ':alert' => $alertDate,
        ':original' => $package['gia'],
        ':discount' => $discount,
        ':final' => $finalPrice,
        ':staff' => $_SESSION['user_id']
    ];
    
    $registrationId = $db->insert($sqlPackage, $paramsPackage);
    
    if (!$registrationId) {
        throw new Exception('Lỗi đăng ký gói tập');
    }
    
    // Commit transaction
    $db->commit();
    
    // Log activity
    logActivity($_SESSION['user_id'], 'Đăng ký hội viên mới', 'Hội viên: ' . $data['ho_ten']);
    
    // Gửi email thông báo (optional)
    $emailSubject = "Chào mừng đến với Monkey Gym!";
    $emailMessage = "
    <h2>Xin chào {$data['ho_ten']},</h2>
    <p>Chào mừng bạn đến với Monkey Gym!</p>
    <p><strong>Thông tin đăng nhập:</strong></p>
    <p>Username: <strong>{$username}</strong></p>
    <p>Password: <strong>{$password}</strong></p>
    <p>Vui lòng đổi mật khẩu sau khi đăng nhập lần đầu.</p>
    <p>Trân trọng,<br>Monkey Gym Team</p>
    ";
    
    sendEmail($data['email'], $emailSubject, $emailMessage);
    
    jsonResponse([
        'success' => true,
        'message' => 'Đăng ký hội viên thành công!',
        'data' => [
            'member_id' => $memberId,
            'user_id' => $userId,
            'username' => $username,
            'password' => $password,
            'qr_code' => $qrPath
        ]
    ]);
    
} catch (Exception $e) {
    $db->rollback();
    jsonResponse([
        'success' => false,
        'message' => 'Lỗi: ' . $e->getMessage()
    ], 500);
}
?>
