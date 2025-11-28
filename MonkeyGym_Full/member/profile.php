<?php
require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/helpers.php';

startSession();

if (!isLoggedIn() || !hasRole('hoi_vien')) {
    redirect('public/login.php');
}

$db = new Database();
$user = getUserInfo();

// Lấy thông tin hội viên
$member = $db->selectOne("
    SELECT hv.*, nd.ho_ten, nd.email, nd.so_dien_thoai, nd.ngay_tao
    FROM hoi_vien hv
    INNER JOIN nguoi_dung nd ON hv.ma_nguoi_dung = nd.ma_nguoi_dung
    WHERE nd.ma_nguoi_dung = :id
", [':id' => $user['id']]);

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    try {
        $db->beginTransaction();
        
        // Update nguoi_dung
        $sqlUser = "UPDATE nguoi_dung SET 
            ho_ten = :ho_ten,
            so_dien_thoai = :phone
            WHERE ma_nguoi_dung = :id";
        $db->update($sqlUser, [
            ':ho_ten' => sanitize($_POST['ho_ten']),
            ':phone' => sanitize($_POST['so_dien_thoai']),
            ':id' => $user['id']
        ]);
        
        // Update hoi_vien
        $sqlMember = "UPDATE hoi_vien SET 
            ngay_sinh = :dob,
            gioi_tinh = :gender,
            dia_chi = :address
            WHERE ma_nguoi_dung = :id";
        $db->update($sqlMember, [
            ':dob' => $_POST['ngay_sinh'] ?: null,
            ':gender' => $_POST['gioi_tinh'],
            ':address' => sanitize($_POST['dia_chi'] ?? ''),
            ':id' => $user['id']
        ]);
        
        $db->commit();
        
        setFlash('success', 'Cập nhật thông tin thành công!');
        redirect('member/profile.php');
        
    } catch (Exception $e) {
        $db->rollback();
        $error = 'Có lỗi xảy ra: ' . $e->getMessage();
    }
}

// Xử lý đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $oldPass = $_POST['old_password'];
    $newPass = $_POST['new_password'];
    $confirmPass = $_POST['confirm_password'];
    
    // Lấy mật khẩu hiện tại
    $currentUser = $db->selectOne("SELECT mat_khau FROM nguoi_dung WHERE ma_nguoi_dung = :id", 
        [':id' => $user['id']]);
    
    if (!verifyPassword($oldPass, $currentUser['mat_khau'])) {
        $error = 'Mật khẩu cũ không đúng!';
    } elseif ($newPass !== $confirmPass) {
        $error = 'Mật khẩu mới không khớp!';
    } elseif (strlen($newPass) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự!';
    } else {
        $sqlPass = "UPDATE nguoi_dung SET mat_khau = :password WHERE ma_nguoi_dung = :id";
        $db->update($sqlPass, [
            ':password' => hashPassword($newPass),
            ':id' => $user['id']
        ]);
        
        setFlash('success', 'Đổi mật khẩu thành công!');
        redirect('member/profile.php');
    }
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin cá nhân - Monkey Gym</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); min-height: 100vh; padding: 30px 0; }
        .profile-container { background: white; border-radius: 20px; padding: 40px; max-width: 900px; margin: 0 auto; box-shadow: 0 10px 40px rgba(0,0,0,0.2); position: relative; }
        .profile-header { background: linear-gradient(135deg, #ffa751 0%, #ffe259 100%); padding: 30px; border-radius: 15px; margin: -40px -40px 30px -40px; text-align: center; }
        .avatar-large { width: 120px; height: 120px; border: 5px solid white; border-radius: 50%; }
        .nav-tabs .nav-link { color: #666; }
        .nav-tabs .nav-link.active { color: #ffc107; border-color: #dee2e6 #dee2e6 #fff; }
        .btn-back { position: absolute; top: 20px; left: 20px; width: 50px; height: 50px; border-radius: 50%; background: white; border: 2px solid #ffc107; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #ffc107; transition: all 0.3s; z-index: 10; cursor: pointer; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .btn-back:hover { background: #ffc107; color: white; transform: scale(1.1); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php"><i class="fas fa-dumbbell me-2"></i> MONKEY GYM</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"><i class="fas fa-home me-2"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="confirmLogout(event)">
                        <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    
    <div class="container">
        <div class="profile-container">
            <!-- Nút Back với confirm -->
            <button class="btn-back" onclick="confirmBack()">
                <i class="fas fa-arrow-left"></i>
            </button>
            
            <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
                <?php echo $flash['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <!-- Profile Header -->
            <div class="profile-header">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($member['ho_ten']); ?>&size=120&background=f5576c&color=fff" 
                     class="avatar-large mb-3" alt="">
                <h3 class="mb-1"><?php echo htmlspecialchars($member['ho_ten']); ?></h3>
                <p class="mb-0">
                    <i class="fas fa-envelope me-2"></i> <?php echo htmlspecialchars($member['email']); ?>
                </p>
                <p class="mb-0">
                    <i class="fas fa-calendar me-2"></i> Tham gia: <?php echo formatDate($member['ngay_tao']); ?>
                </p>
            </div>
            
            <!-- Tabs -->
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#info-tab">
                        <i class="fas fa-user me-2"></i> Thông tin cá nhân
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#password-tab">
                        <i class="fas fa-key me-2"></i> Đổi mật khẩu
                    </button>
                </li>
            </ul>
            
            <div class="tab-content">
                <!-- Tab Thông tin -->
                <div class="tab-pane fade show active" id="info-tab">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="ho_ten" 
                                       value="<?php echo htmlspecialchars($member['ho_ten']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-muted">(Không thể đổi)</span></label>
                                <input type="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($member['email']); ?>" disabled>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số điện thoại</label>
                                <input type="tel" class="form-control" name="so_dien_thoai" 
                                       value="<?php echo htmlspecialchars($member['so_dien_thoai']); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ngày sinh</label>
                                <input type="date" class="form-control" name="ngay_sinh" 
                                       value="<?php echo $member['ngay_sinh']; ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Giới tính</label>
                                <select class="form-select" name="gioi_tinh">
                                    <option value="nam" <?php echo $member['gioi_tinh'] == 'nam' ? 'selected' : ''; ?>>Nam</option>
                                    <option value="nu" <?php echo $member['gioi_tinh'] == 'nu' ? 'selected' : ''; ?>>Nữ</option>
                                </select>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Địa chỉ</label>
                                <textarea class="form-control" name="dia_chi" rows="2"><?php echo htmlspecialchars($member['dia_chi'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" name="update_profile" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i> Lưu thay đổi
                            </button>
                            <a href="dashboard.php" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times me-2"></i> Hủy
                            </a>
                        </div>
                    </form>
                </div>
                
                <!-- Tab Đổi mật khẩu -->
                <div class="tab-pane fade" id="password-tab">
                    <form method="POST" action="">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Lưu ý:</strong> Mật khẩu phải có ít nhất 6 ký tự.
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu cũ <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="old_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu mới <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="new_password" 
                                   minlength="6" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="confirm_password" 
                                   minlength="6" required>
                        </div>
                        
                        <button type="submit" name="change_password" class="btn btn-warning btn-lg">
                            <i class="fas fa-key me-2"></i> Đổi mật khẩu
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmBack() {
            if (confirm('Bạn có chắc muốn quay lại Dashboard không?')) {
                window.location.href = 'dashboard.php';
            }
        }
        
        function confirmLogout(event) {
            event.preventDefault();
            if (confirm('Bạn có chắc muốn đăng xuất không?')) {
                window.location.href = '../public/logout.php';
            }
        }
    </script>
</body>
</html>
