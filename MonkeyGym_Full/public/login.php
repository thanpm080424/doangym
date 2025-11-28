<?php
require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/helpers.php';

startSession();

// Nếu đã đăng nhập, redirect
if (isLoggedIn()) {
    $role = $_SESSION['vai_tro'];
    switch($role) {
        case 'quan_tri':
            redirect('admin/dashboard.php');
            break;
        case 'nhan_vien':
            redirect('staff/dashboard.php');
            break;
        case 'huan_luyen_vien':
            redirect('trainer/dashboard.php');
            break;
        case 'hoi_vien':
            redirect('member/dashboard.php');
            break;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    } else {
        $db = new Database();
        
        $sql = "SELECT * FROM nguoi_dung WHERE ten_dang_nhap = :username AND trang_thai = 1";
        $user = $db->selectOne($sql, [':username' => $username]);
        
        if ($user && verifyPassword($password, $user['mat_khau'])) {
            // Đăng nhập thành công
            $_SESSION['user_id'] = $user['ma_nguoi_dung'];
            $_SESSION['username'] = $user['ten_dang_nhap'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['ho_ten'] = $user['ho_ten'];
            $_SESSION['vai_tro'] = $user['vai_tro'];
            $_SESSION['avatar'] = $user['avatar'];
            
            // Cập nhật lần đăng nhập cuối
            $updateSql = "UPDATE nguoi_dung SET lan_dang_nhap_cuoi = NOW() WHERE ma_nguoi_dung = :id";
            $db->update($updateSql, [':id' => $user['ma_nguoi_dung']]);
            
            // Log activity
            logActivity($user['ma_nguoi_dung'], 'Đăng nhập', 'Đăng nhập thành công');
            
            // Redirect theo vai trò
            switch($user['vai_tro']) {
                case 'quan_tri':
                    redirect('admin/dashboard.php');
                    break;
                case 'nhan_vien':
                    redirect('staff/dashboard.php');
                    break;
                case 'huan_luyen_vien':
                    redirect('trainer/dashboard.php');
                    break;
                case 'hoi_vien':
                    redirect('member/dashboard.php');
                    break;
            }
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không đúng!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Monkey Gym</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }
        .login-left {
            background: linear-gradient(135deg, #ffa751 0%, #ffe259 100%);
            padding: 60px;
            color: #333;
        }
        .login-left h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 20px;
        }
        .login-right {
            padding: 60px;
        }
        .form-control:focus {
            border-color: #ffc107;
            box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #ffa751 0%, #ffe259 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            color: #333;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="login-container">
                    <div class="row g-0">
                        <div class="col-md-6 login-left d-none d-md-block">
                            <i class="fas fa-dumbbell fa-5x mb-4"></i>
                            <h1>🐒 MONKEY GYM</h1>
                            <p class="fs-5">Hệ thống quản lý phòng gym hiện đại</p>
                            <ul class="list-unstyled mt-4">
                                <li class="mb-2"><i class="fas fa-check-circle me-2"></i> Quản lý hội viên thông minh</li>
                                <li class="mb-2"><i class="fas fa-check-circle me-2"></i> Điểm danh bằng QR Code</li>
                                <li class="mb-2"><i class="fas fa-check-circle me-2"></i> Báo cáo chi tiết</li>
                                <li class="mb-2"><i class="fas fa-check-circle me-2"></i> Giao diện thân thiện</li>
                            </ul>
                        </div>
                        <div class="col-md-6 login-right">
                            <h2 class="mb-4">Đăng Nhập</h2>
                            
                            <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label class="form-label">Tên đăng nhập</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" name="username" required autofocus>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Mật khẩu</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" name="password" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="remember">
                                    <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                                </div>
                                
                                <button type="submit" class="btn btn-login w-100 mb-3">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Đăng Nhập
                                </button>
                                
                                <div class="text-center">
                                    <small class="text-muted">Quên mật khẩu? <a href="#">Khôi phục</a></small>
                                </div>
                            </form>
                            
                            <hr class="my-4">
                            
                            <div class="alert alert-info">
                                <strong>Tài khoản demo:</strong><br>
                                Admin: <code>admin</code> / <code>password</code><br>
                                Nhân viên: <code>nhanvien01</code> / <code>password</code><br>
                                HLV: <code>hlv01</code> / <code>password</code><br>
                                Hội viên: <code>hoivien01</code> / <code>password</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
