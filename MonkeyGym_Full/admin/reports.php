<?php
require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/helpers.php';

startSession();

if (!isLoggedIn() || !hasRole(['quan_tri'])) {
    redirect('public/login.php');
}

$db = new Database();
$user = getUserInfo();

// Thống kê tổng quan
$stats = [
    'total_revenue' => 0,
    'total_members' => 0,
    'new_members_month' => 0,
    'active_packages' => 0
];

try {
    $result = $db->selectOne("SELECT COALESCE(SUM(so_tien), 0) as total FROM thanh_toan WHERE trang_thai = 'thanh_cong'");
    $stats['total_revenue'] = $result['total'];
    
    $result = $db->selectOne("SELECT COUNT(*) as total FROM hoi_vien");
    $stats['total_members'] = $result['total'];
    
    $result = $db->selectOne("SELECT COUNT(*) as total FROM hoi_vien hv INNER JOIN nguoi_dung nd ON hv.ma_nguoi_dung = nd.ma_nguoi_dung WHERE MONTH(nd.ngay_tao) = MONTH(CURDATE()) AND YEAR(nd.ngay_tao) = YEAR(CURDATE())");
    $stats['new_members_month'] = $result['total'];
    
    $result = $db->selectOne("SELECT COUNT(*) as total FROM dang_ky_goi WHERE trang_thai = 'dang_hoat_dong'");
    $stats['active_packages'] = $result['total'];
} catch (Exception $e) {
    // Keep default values
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo - Monkey Gym</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f5f5f5; }
        .sidebar {
            background: #2d3748;
            min-height: 100vh;
            color: white;
            position: fixed;
            width: 250px;
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
            margin-left: 250px;
            padding: 30px;
        }
        .report-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
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
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-home me-2"></i> Dashboard
                </a>
                <a class="nav-link" href="members.php">
                    <i class="fas fa-users me-2"></i> Hội viên
                </a>
                <a class="nav-link" href="packages.php">
                    <i class="fas fa-box me-2"></i> Gói tập
                </a>
                <a class="nav-link" href="trainers.php">
                    <i class="fas fa-dumbbell me-2"></i> HLV
                </a>
                <a class="nav-link" href="staff.php">
                    <i class="fas fa-user-tie me-2"></i> Nhân viên
                </a>
                <a class="nav-link active" href="reports.php">
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
    <div class="main-content">
        <h2 class="mb-4"><i class="fas fa-chart-bar me-2"></i> Báo cáo Thống kê</h2>
        
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="report-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1">Tổng doanh thu</p>
                            <h3 class="text-success"><?php echo formatCurrency($stats['total_revenue']); ?></h3>
                        </div>
                        <i class="fas fa-dollar-sign fa-3x text-success opacity-25"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="report-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1">Tổng hội viên</p>
                            <h3 class="text-primary"><?php echo number_format($stats['total_members']); ?></h3>
                        </div>
                        <i class="fas fa-users fa-3x text-primary opacity-25"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="report-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1">HV mới tháng này</p>
                            <h3 class="text-info"><?php echo number_format($stats['new_members_month']); ?></h3>
                        </div>
                        <i class="fas fa-user-plus fa-3x text-info opacity-25"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="report-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1">Gói đang hoạt động</p>
                            <h3 class="text-warning"><?php echo number_format($stats['active_packages']); ?></h3>
                        </div>
                        <i class="fas fa-box fa-3x text-warning opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts -->
        <div class="row">
            <div class="col-md-12">
                <div class="report-card mb-4">
                    <h5 class="mb-3">Doanh thu theo tháng</h5>
                    <canvas id="revenueChart" height="80"></canvas>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="report-card">
                    <h5 class="mb-3">Gói tập phổ biến</h5>
                    <canvas id="packageChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="report-card">
                    <h5 class="mb-3">Tăng trưởng hội viên</h5>
                    <canvas id="memberChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Revenue Chart
        new Chart(document.getElementById('revenueChart'), {
            type: 'bar',
            data: {
                labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
                datasets: [{
                    label: 'Doanh thu (triệu đồng)',
                    data: [12, 19, 15, 25, 22, 30, 28, 35, 32, 40, 38, 45],
                    backgroundColor: '#ffc107'
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } }
            }
        });
        
        // Package Chart
        new Chart(document.getElementById('packageChart'), {
            type: 'doughnut',
            data: {
                labels: ['Gói 1 tháng', 'Gói 3 tháng', 'Gói 6 tháng', 'Gói 12 tháng'],
                datasets: [{
                    data: [30, 40, 20, 10],
                    backgroundColor: ['#667eea', '#ffc107', '#28a745', '#dc3545']
                }]
            }
        });
        
        // Member Chart
        new Chart(document.getElementById('memberChart'), {
            type: 'line',
            data: {
                labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6'],
                datasets: [{
                    label: 'Hội viên mới',
                    data: [10, 15, 20, 18, 25, 30],
                    borderColor: '#667eea',
                    tension: 0.4
                }]
            }
        });
    </script>
</body>
</html>
