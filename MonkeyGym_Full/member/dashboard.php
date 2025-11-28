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
    SELECT hv.*, nd.ho_ten, nd.email, nd.so_dien_thoai
    FROM hoi_vien hv
    INNER JOIN nguoi_dung nd ON hv.ma_nguoi_dung = nd.ma_nguoi_dung
    WHERE nd.ma_nguoi_dung = :id
", [':id' => $user['id']]);

// Lấy gói tập hiện tại
$currentPackage = $db->selectOne("
    SELECT 
        dk.*,
        gt.ten_goi,
        gt.gia,
        DATEDIFF(dk.ngay_ket_thuc, CURDATE()) as days_left
    FROM dang_ky_goi dk
    INNER JOIN goi_tap gt ON dk.ma_goi = gt.ma_goi
    WHERE dk.ma_hoi_vien = :id 
    AND dk.trang_thai = 'dang_hoat_dong'
    ORDER BY dk.ngay_ket_thuc DESC
    LIMIT 1
", [':id' => $member['ma_hoi_vien'] ?? 0]);

// Lịch PT sắp tới
$ptSchedule = $db->select("
    SELECT 
        ld.*,
        nd.ho_ten as ten_hlv
    FROM lich_day_pt ld
    INNER JOIN huan_luyen_vien hlv ON ld.ma_hlv = hlv.ma_hlv
    INNER JOIN nguoi_dung nd ON hlv.ma_nguoi_dung = nd.ma_nguoi_dung
    WHERE ld.ma_hoi_vien = :id 
    AND ld.ngay_day >= CURDATE()
    AND ld.trang_thai != 'huy'
    ORDER BY ld.ngay_day ASC, ld.gio_bat_dau ASC
    LIMIT 5
", [':id' => $member['ma_hoi_vien'] ?? 0]);

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Hội viên - Monkey Gym</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); min-height: 100vh; }
        .dashboard-card { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); margin-bottom: 20px; }
        .package-card { background: linear-gradient(135deg, #ffa751 0%, #ffe259 100%); color: #2d3748; padding: 25px; border-radius: 15px; }
        .package-card h2 { font-size: 2.5rem; margin: 0; }
        .qr-card { background: #f8f9fa; padding: 20px; border-radius: 10px; text-align: center; }
        .menu-item { background: white; border: 2px solid #e9ecef; border-radius: 10px; padding: 20px; text-align: center; transition: all 0.3s; cursor: pointer; }
        .menu-item:hover { border-color: #ffc107; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .menu-item i { font-size: 2.5rem; color: #ffc107; margin-bottom: 10px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="fas fa-dumbbell me-2"></i> MONKEY GYM</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-2"></i> <?php echo htmlspecialchars($user['ho_ten']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i> Thông tin cá nhân</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="confirmLogout(event)">
                                <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
    
    <div class="container py-5">
        <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
            <?php echo $flash['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <h2 class="text-white mb-4">
            Xin chào, <?php echo htmlspecialchars($user['ho_ten']); ?>! 👋
        </h2>
        
        <div class="row">
            <!-- Current Package -->
            <div class="col-md-8">
                <div class="dashboard-card">
                    <?php if ($currentPackage): ?>
                    <div class="package-card">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="mb-2">Gói tập hiện tại</h4>
                                <h2><?php echo htmlspecialchars($currentPackage['ten_goi']); ?></h2>
                                <p class="mb-2">
                                    <i class="fas fa-calendar me-2"></i>
                                    Từ <?php echo formatDate($currentPackage['ngay_bat_dau']); ?> 
                                    đến <?php echo formatDate($currentPackage['ngay_ket_thuc']); ?>
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-clock me-2"></i>
                                    <strong><?php echo $currentPackage['days_left']; ?> ngày</strong> còn lại
                                </p>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="display-4">
                                    <?php 
                                    if ($currentPackage['days_left'] > 30) {
                                        echo '✅';
                                    } elseif ($currentPackage['days_left'] > 7) {
                                        echo '⚠️';
                                    } else {
                                        echo '❗';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($currentPackage['days_left'] <= 7): ?>
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Gói tập sắp hết hạn!</strong> Hãy liên hệ lễ tân để gia hạn.
                    </div>
                    <?php endif; ?>
                    
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                        <h4>Bạn chưa có gói tập nào</h4>
                        <p class="text-muted">Hãy liên hệ lễ tân để đăng ký gói tập phù hợp!</p>
                        <a href="#" class="btn btn-primary btn-lg">Xem các gói tập</a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- PT Schedule -->
                <div class="dashboard-card">
                    <h4 class="mb-3"><i class="fas fa-calendar-alt me-2"></i> Lịch tập PT sắp tới</h4>
                    
                    <?php if (empty($ptSchedule)): ?>
                    <p class="text-muted">Bạn chưa có lịch tập PT nào.</p>
                    <a href="#" class="btn btn-success">Đặt lịch với HLV</a>
                    <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($ptSchedule as $pt): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">
                                        <i class="fas fa-user-tie me-2"></i>
                                        HLV <?php echo htmlspecialchars($pt['ten_hlv']); ?>
                                    </h6>
                                    <p class="mb-0 small text-muted">
                                        <i class="fas fa-calendar me-2"></i>
                                        <?php echo formatDate($pt['ngay_day']); ?>
                                        <i class="fas fa-clock ms-3 me-2"></i>
                                        <?php echo date('H:i', strtotime($pt['gio_bat_dau'])); ?> - 
                                        <?php echo date('H:i', strtotime($pt['gio_ket_thuc'])); ?>
                                    </p>
                                </div>
                                <span class="badge bg-primary">Đã đặt</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- QR Code & Menu -->
            <div class="col-md-4">
                <!-- QR Code -->
                <div class="dashboard-card">
                    <h5 class="text-center mb-3">Mã QR điểm danh</h5>
                    <div class="qr-card">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode($member['ma_qr'] ?? 'NO_QR'); ?>" 
                             alt="QR Code" class="img-fluid mb-2">
                        <small class="text-muted d-block">Quét mã này khi vào phòng gym</small>
                    </div>
                </div>
                
                <!-- Menu -->
                <div class="dashboard-card">
                    <h5 class="mb-3">Chức năng</h5>
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="#" class="text-decoration-none">
                                <div class="menu-item">
                                    <i class="fas fa-dumbbell"></i>
                                    <p class="mb-0 small">Lịch tập</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="#" class="text-decoration-none">
                                <div class="menu-item">
                                    <i class="fas fa-user-tie"></i>
                                    <p class="mb-0 small">Đặt HLV</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="profile.php" class="text-decoration-none">
                                <div class="menu-item">
                                    <i class="fas fa-user"></i>
                                    <p class="mb-0 small">Hồ sơ</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="#" class="text-decoration-none">
                                <div class="menu-item">
                                    <i class="fas fa-history"></i>
                                    <p class="mb-0 small">Lịch sử</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmLogout(event) {
            event.preventDefault();
            if (confirm('Bạn có chắc muốn đăng xuất không?')) {
                window.location.href = '../public/logout.php';
            }
        }
    </script>
</body>
</html>
