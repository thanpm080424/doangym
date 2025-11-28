<?php
require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/helpers.php';

startSession();

if (!isLoggedIn() || !hasRole(['quan_tri', 'nhan_vien'])) {
    redirect('public/login.php');
}

$db = new Database();
$user = getUserInfo();

// Lấy danh sách gói tập
$packages = $db->select("SELECT * FROM goi_tap WHERE trang_thai = 1 ORDER BY gia ASC");

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->beginTransaction();
        
        // Validate
        $errors = [];
        if (empty($_POST['ho_ten'])) $errors[] = "Họ tên không được trống";
        if (empty($_POST['email'])) $errors[] = "Email không được trống";
        if (empty($_POST['so_dien_thoai'])) $errors[] = "SĐT không được trống";
        
        // Kiểm tra email trùng
        $checkEmail = $db->selectOne("SELECT ma_nguoi_dung FROM nguoi_dung WHERE email = :email", 
            [':email' => $_POST['email']]);
        if ($checkEmail) $errors[] = "Email đã tồn tại!";
        
        if (!empty($errors)) {
            throw new Exception(implode(", ", $errors));
        }
        
        // Tạo username và password
        $username = strtolower(str_replace(' ', '', $_POST['ho_ten'])) . rand(100, 999);
        $password = 'gym' . rand(1000, 9999);
        
        // Insert nguoi_dung
        $sqlUser = "INSERT INTO nguoi_dung (ten_dang_nhap, mat_khau, email, so_dien_thoai, ho_ten, vai_tro) 
                    VALUES (:username, :password, :email, :phone, :ho_ten, 'hoi_vien')";
        $userId = $db->insert($sqlUser, [
            ':username' => $username,
            ':password' => hashPassword($password),
            ':email' => sanitize($_POST['email']),
            ':phone' => sanitize($_POST['so_dien_thoai']),
            ':ho_ten' => sanitize($_POST['ho_ten'])
        ]);
        
        if (!$userId) throw new Exception("Lỗi tạo tài khoản");
        
        // Tạo QR Code (đơn giản hóa - chỉ lưu text)
        $qrCode = "MEMBER_" . $userId . "_" . time();
        
        // Insert hoi_vien
        $sqlMember = "INSERT INTO hoi_vien (ma_nguoi_dung, gioi_tinh, ngay_sinh, dia_chi, ma_qr) 
                      VALUES (:user_id, :gender, :dob, :address, :qr)";
        $memberId = $db->insert($sqlMember, [
            ':user_id' => $userId,
            ':gender' => $_POST['gioi_tinh'] ?? 'nam',
            ':dob' => $_POST['ngay_sinh'] ?? null,
            ':address' => sanitize($_POST['dia_chi'] ?? ''),
            ':qr' => $qrCode
        ]);
        
        if (!$memberId) throw new Exception("Lỗi tạo hội viên");
        
        // Đăng ký gói nếu có
        if (!empty($_POST['ma_goi'])) {
            $package = $db->selectOne("SELECT * FROM goi_tap WHERE ma_goi = :id", [':id' => $_POST['ma_goi']]);
            
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d', strtotime("+{$package['thoi_han']} months"));
            
            $sqlPackage = "INSERT INTO dang_ky_goi (ma_hoi_vien, ma_goi, ngay_bat_dau, ngay_ket_thuc, 
                           trang_thai, gia_thanh_toan, nguoi_dang_ky) 
                           VALUES (:member_id, :package_id, :start, :end, 'cho_thanh_toan', :price, :staff)";
            $db->insert($sqlPackage, [
                ':member_id' => $memberId,
                ':package_id' => $_POST['ma_goi'],
                ':start' => $startDate,
                ':end' => $endDate,
                ':price' => $package['gia'],
                ':staff' => $user['id']
            ]);
        }
        
        $db->commit();
        
        logActivity($user['id'], 'Thêm hội viên mới', 'ID: ' . $memberId);
        
        setFlash('success', "Thêm hội viên thành công! Username: <strong>$username</strong> - Password: <strong>$password</strong>");
        redirect('admin/members.php');
        
    } catch (Exception $e) {
        $db->rollback();
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Hội viên - Monkey Gym</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 30px; }
        .form-container { background: white; border-radius: 20px; padding: 40px; max-width: 800px; margin: 0 auto; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        .form-header { background: linear-gradient(135deg, #ffa751 0%, #ffe259 100%); padding: 30px; border-radius: 15px 15px 0 0; margin: -40px -40px 30px -40px; }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h2 class="text-center mb-0"><i class="fas fa-user-plus me-2"></i> Thêm Hội viên Mới</h2>
        </div>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="ho_ten" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Giới tính</label>
                    <select class="form-select" name="gioi_tinh">
                        <option value="nam">Nam</option>
                        <option value="nu">Nữ</option>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" name="so_dien_thoai" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ngày sinh</label>
                    <input type="date" class="form-control" name="ngay_sinh">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Gói tập</label>
                    <select class="form-select" name="ma_goi">
                        <option value="">-- Chọn gói tập --</option>
                        <?php foreach ($packages as $pkg): ?>
                        <option value="<?php echo $pkg['ma_goi']; ?>">
                            <?php echo $pkg['ten_goi']; ?> - <?php echo formatCurrency($pkg['gia']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-12 mb-3">
                    <label class="form-label">Địa chỉ</label>
                    <textarea class="form-control" name="dia_chi" rows="2"></textarea>
                </div>
            </div>
            
            <hr>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Lưu ý:</strong> Hệ thống sẽ tự động tạo tài khoản đăng nhập và gửi thông tin qua email.
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                    <i class="fas fa-save me-2"></i> Lưu hội viên
                </button>
                <a href="members.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times me-2"></i> Hủy
                </a>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
