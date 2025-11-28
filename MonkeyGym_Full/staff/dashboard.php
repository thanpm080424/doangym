<?php
require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/helpers.php';

startSession();

if (!isLoggedIn() || !hasRole('nhan_vien')) {
    redirect('public/login.php');
}

$db = new Database();
$user = getUserInfo();

// Thống kê cho nhân viên
$stats = [
    'members_today' => 0,
    'registrations_today' => 0,
    'expiring_soon' => 0,
    'total_members' => 0
];

try {
    $result = $db->selectOne("SELECT COUNT(*) as total FROM hoi_vien");
    $stats['total_members'] = $result['total'] ?? 0;
    
    $result = $db->selectOne("
        SELECT COUNT(DISTINCT hv.ma_hoi_vien) as total 
        FROM hoi_vien hv
        INNER JOIN dang_ky_goi dk ON hv.ma_hoi_vien = dk.ma_hoi_vien
        WHERE dk.ngay_ket_thuc BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ");
    $stats['expiring_soon'] = $result['total'] ?? 0;
    
    $result = $db->selectOne("
        SELECT COUNT(*) as total FROM dang_ky_goi 
        WHERE DATE(ngay_bat_dau) = CURDATE()
    ");
    $stats['registrations_today'] = $result['total'] ?? 0;
} catch (Exception $e) {
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Nhân viên - Monkey Gym</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .dashboard-card { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); margin-bottom: 20px; }
        .stat-box { background: linear-gradient(135deg, #ffa751 0%, #ffe259 100%); color: #2d3748; padding: 25px; border-radius: 10px; text-align: center; }
        .stat-box h2 { font-size: 3rem; font-weight: bold; margin: 0; }
        .menu-card { background: white; border-radius: 10px; padding: 25px; text-align: center; transition: all 0.3s; cursor: pointer; }
        .menu-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
        .menu-card i { font-size: 3rem; color: #ffc107; margin-bottom: 15px; }
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
                        <li><a class="dropdown-item" href="../public/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Đăng xuất</a></li>
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
        
        <div class="dashboard-card">
            <h2 class="mb-4">
                <i class="fas fa-tachometer-alt me-2"></i> 
                Dashboard Nhân viên
                <small class="text-muted d-block mt-2" style="font-size: 1rem;">
                    Xin chào, <?php echo htmlspecialchars($user['ho_ten']); ?>! 👋
                </small>
            </h2>
            
            <!-- Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-box">
                        <h2><?php echo number_format($stats['total_members']); ?></h2>
                        <p class="mb-0">Tổng hội viên</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <h2><?php echo number_format($stats['registrations_today']); ?></h2>
                        <p class="mb-0">Đăng ký hôm nay</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <h2><?php echo number_format($stats['expiring_soon']); ?></h2>
                        <p class="mb-0">Sắp hết hạn</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <h2><i class="fas fa-clock"></i></h2>
                        <p class="mb-0"><?php echo date('H:i'); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Menu -->
            <h4 class="mb-3">Chức năng</h4>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <a href="../admin/members.php" class="text-decoration-none">
                        <div class="menu-card">
                            <i class="fas fa-users"></i>
                            <h5>Quản lý Hội viên</h5>
                            <p class="text-muted mb-0">Xem, thêm, sửa thông tin</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="../admin/member-add.php" class="text-decoration-none">
                        <div class="menu-card">
                            <i class="fas fa-user-plus"></i>
                            <h5>Đăng ký Hội viên</h5>
                            <p class="text-muted mb-0">Thêm hội viên mới</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="../admin/packages.php" class="text-decoration-none">
                        <div class="menu-card">
                            <i class="fas fa-box"></i>
                            <h5>Gói tập</h5>
                            <p class="text-muted mb-0">Xem các gói tập</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="#" class="text-decoration-none">
                        <div class="menu-card">
                            <i class="fas fa-qrcode"></i>
                            <h5>Điểm danh</h5>
                            <p class="text-muted mb-0">Quét QR Code</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="#" class="text-decoration-none">
                        <div class="menu-card">
                            <i class="fas fa-dollar-sign"></i>
                            <h5>Thu tiền</h5>
                            <p class="text-muted mb-0">Thanh toán, gia hạn</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="../admin/reports.php" class="text-decoration-none">
                        <div class="menu-card">
                            <i class="fas fa-chart-bar"></i>
                            <h5>Báo cáo</h5>
                            <p class="text-muted mb-0">Xem thống kê</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
