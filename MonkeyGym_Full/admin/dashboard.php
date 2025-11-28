<?php
require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/helpers.php';

startSession();

// Kiểm tra đăng nhập và quyền admin
if (!isLoggedIn() || !hasRole('quan_tri')) {
    redirect('public/login.php');
}

$db = new Database();
$user = getUserInfo();

// Lấy thống kê
$stats = [
    'total_members' => 0,
    'active_members' => 0,
    'expiring_soon' => 0,
    'revenue_month' => 0
];

try {
    // Tổng số hội viên
    $result = $db->selectOne("SELECT COUNT(*) as total FROM hoi_vien");
    $stats['total_members'] = $result['total'] ?? 0;
    
    // Hội viên đang hoạt động
    $result = $db->selectOne("
        SELECT COUNT(DISTINCT hv.ma_hoi_vien) as total 
        FROM hoi_vien hv
        INNER JOIN dang_ky_goi dk ON hv.ma_hoi_vien = dk.ma_hoi_vien
        WHERE dk.trang_thai = 'dang_hoat_dong'
        AND dk.ngay_ket_thuc >= CURDATE()
    ");
    $stats['active_members'] = $result['total'] ?? 0;
    
    // Hội viên sắp hết hạn (trong 7 ngày)
    $result = $db->selectOne("
        SELECT COUNT(DISTINCT hv.ma_hoi_vien) as total 
        FROM hoi_vien hv
        INNER JOIN dang_ky_goi dk ON hv.ma_hoi_vien = dk.ma_hoi_vien
        WHERE dk.trang_thai = 'dang_hoat_dong'
        AND dk.ngay_ket_thuc BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ");
    $stats['expiring_soon'] = $result['total'] ?? 0;
    
    // Doanh thu tháng này
    $result = $db->selectOne("
        SELECT COALESCE(SUM(so_tien), 0) as total 
        FROM thanh_toan 
        WHERE MONTH(ngay_thanh_toan) = MONTH(CURDATE())
        AND YEAR(ngay_thanh_toan) = YEAR(CURDATE())
        AND trang_thai = 'thanh_cong'
    ");
    $stats['revenue_month'] = $result['total'] ?? 0;
    
} catch (Exception $e) {
    // Nếu có lỗi, giữ giá trị mặc định
}

// Lấy hoạt động gần đây
$recent_activities = [];
try {
    $recent_activities = $db->select("
        SELECT 
            nd.ho_ten,
            'Đăng ký gói tập' as action,
            dk.ngay_bat_dau as time
        FROM dang_ky_goi dk
        INNER JOIN hoi_vien hv ON dk.ma_hoi_vien = hv.ma_hoi_vien
        INNER JOIN nguoi_dung nd ON hv.ma_nguoi_dung = nd.ma_nguoi_dung
        ORDER BY dk.ngay_bat_dau DESC
        LIMIT 5
    ");
} catch (Exception $e) {
    // Nếu có lỗi, để mảng rỗng
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Monkey Gym Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../public/css/styles.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .sidebar {
            background: #2d3748;
            min-height: 100vh;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 5px 0;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: #ffc107;
            color: #2d3748;
        }
        .main-content {
            background: white;
            min-height: 100vh;
            padding: 30px;
        }
        .stat-card {
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-left: 5px solid;
        }
        .stat-card.primary { border-left-color: #667eea; }
        .stat-card.success { border-left-color: #28a745; }
        .stat-card.warning { border-left-color: #ffc107; }
        .stat-card.danger { border-left-color: #dc3545; }
        .navbar-custom {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-4 text-center border-bottom">
                    <h4>🐒 MONKEY GYM</h4>
                    <small>Admin Panel</small>
                </div>
                
                <div class="p-3">
                    <div class="mb-3 text-center">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['ho_ten']); ?>&background=ffc107&color=2d3748&size=80" 
                             class="rounded-circle mb-2" alt="Avatar">
                        <div><strong><?php echo htmlspecialchars($user['ho_ten']); ?></strong></div>
                        <small class="text-muted">Admin</small>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-home me-2"></i> Dashboard
                        </a>
                        <a class="nav-link" href="members.php">
                            <i class="fas fa-users me-2"></i> Hội viên
                        </a>
                        <a class="nav-link" href="packages.php">
                            <i class="fas fa-box me-2"></i> Gói tập
                        </a>
                        <a class="nav-link" href="qr-checkin.php">
                            <i class="fas fa-qrcode me-2"></i> Quét QR Check-in
                        </a>
                        <a class="nav-link" href="trainers.php">
                            <i class="fas fa-dumbbell me-2"></i> HLV
                        </a>
                        <a class="nav-link" href="staff.php">
                            <i class="fas fa-user-tie me-2"></i> Nhân viên
                        </a>
                        <a class="nav-link" href="reports.php">
                            <i class="fas fa-chart-bar me-2"></i> Báo cáo
                        </a>
                        <hr>
                        <a class="nav-link" href="../public/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-0">
                <!-- Top Navbar -->
                <nav class="navbar navbar-expand-lg navbar-custom">
                    <div class="container-fluid">
                        <h5 class="mb-0">Dashboard</h5>
                        <div class="d-flex align-items-center">
                            <span class="me-3">
                                <i class="fas fa-calendar me-2"></i>
                                <?php echo date('d/m/Y'); ?>
                            </span>
                            <span class="badge bg-success">Online</span>
                        </div>
                    </div>
                </nav>
                
                <!-- Content -->
                <div class="main-content">
                    <?php if ($flash): ?>
                    <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
                        <?php echo $flash['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <h2 class="mb-4">Xin chào, <?php echo htmlspecialchars($user['ho_ten']); ?>! 👋</h2>
                    
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="stat-card primary">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Tổng hội viên</p>
                                        <h3 class="mb-0"><?php echo number_format($stats['total_members']); ?></h3>
                                    </div>
                                    <div class="fs-1 text-primary">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="stat-card success">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Đang hoạt động</p>
                                        <h3 class="mb-0"><?php echo number_format($stats['active_members']); ?></h3>
                                    </div>
                                    <div class="fs-1 text-success">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="stat-card warning">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Sắp hết hạn</p>
                                        <h3 class="mb-0"><?php echo number_format($stats['expiring_soon']); ?></h3>
                                    </div>
                                    <div class="fs-1 text-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="stat-card danger">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Doanh thu tháng</p>
                                        <h3 class="mb-0"><?php echo formatCurrency($stats['revenue_month']); ?></h3>
                                    </div>
                                    <div class="fs-1 text-danger">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Charts -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Doanh thu 12 tháng</h5>
                                    <canvas id="revenueChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Hoạt động gần đây</h5>
                                    <div class="list-group list-group-flush">
                                        <?php if (empty($recent_activities)): ?>
                                        <div class="text-muted text-center py-3">
                                            Chưa có hoạt động
                                        </div>
                                        <?php else: ?>
                                        <?php foreach ($recent_activities as $activity): ?>
                                        <div class="list-group-item px-0">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-circle text-success me-2" style="font-size: 8px;"></i>
                                                <div class="flex-grow-1">
                                                    <strong><?php echo htmlspecialchars($activity['ho_ten']); ?></strong>
                                                    <div class="small text-muted"><?php echo $activity['action']; ?></div>
                                                </div>
                                                <small class="text-muted">
                                                    <?php echo formatDateTime($activity['time']); ?>
                                                </small>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Thao tác nhanh</h5>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <a href="members.php?action=add" class="btn btn-primary w-100 mb-2">
                                                <i class="fas fa-user-plus me-2"></i>
                                                Thêm hội viên mới
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="packages.php" class="btn btn-success w-100 mb-2">
                                                <i class="fas fa-box me-2"></i>
                                                Quản lý gói tập
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="reports.php" class="btn btn-info w-100 mb-2">
                                                <i class="fas fa-chart-line me-2"></i>
                                                Xem báo cáo
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="settings.php" class="btn btn-warning w-100 mb-2">
                                                <i class="fas fa-cog me-2"></i>
                                                Cài đặt
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Revenue Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
                datasets: [{
                    label: 'Doanh thu (triệu đồng)',
                    data: [12, 19, 15, 25, 22, 30, 28, 35, 32, 40, 38, 45],
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
