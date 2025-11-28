<?php
require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/helpers.php';

startSession();

if (!isLoggedIn() || !hasRole('huan_luyen_vien')) {
    redirect('public/login.php');
}

$db = new Database();
$user = getUserInfo();

// Lấy thông tin HLV
$trainer = $db->selectOne("
    SELECT hlv.*, AVG(dg.diem_so) as rating
    FROM huan_luyen_vien hlv
    LEFT JOIN danh_gia_hlv dg ON hlv.ma_hlv = dg.ma_hlv
    INNER JOIN nguoi_dung nd ON hlv.ma_nguoi_dung = nd.ma_nguoi_dung
    WHERE nd.ma_nguoi_dung = :id
    GROUP BY hlv.ma_hlv
", [':id' => $user['id']]);

// Thống kê
$stats = [
    'sessions_today' => 0,
    'sessions_week' => 0,
    'total_students' => 0,
    'rating' => $trainer['rating'] ?? 0
];

try {
    $result = $db->selectOne("
        SELECT COUNT(*) as total FROM lich_day_pt 
        WHERE ma_hlv = :id AND DATE(ngay_day) = CURDATE()
    ", [':id' => $trainer['ma_hlv'] ?? 0]);
    $stats['sessions_today'] = $result['total'] ?? 0;
    
    $result = $db->selectOne("
        SELECT COUNT(*) as total FROM lich_day_pt 
        WHERE ma_hlv = :id AND WEEK(ngay_day) = WEEK(CURDATE())
    ", [':id' => $trainer['ma_hlv'] ?? 0]);
    $stats['sessions_week'] = $result['total'] ?? 0;
} catch (Exception $e) {
}

// Lịch dạy hôm nay
$schedule = $db->select("
    SELECT 
        ld.*,
        nd.ho_ten as ten_hoi_vien
    FROM lich_day_pt ld
    INNER JOIN hoi_vien hv ON ld.ma_hoi_vien = hv.ma_hoi_vien
    INNER JOIN nguoi_dung nd ON hv.ma_nguoi_dung = nd.ma_nguoi_dung
    WHERE ld.ma_hlv = :id AND DATE(ld.ngay_day) = CURDATE()
    ORDER BY ld.gio_bat_dau ASC
", [':id' => $trainer['ma_hlv'] ?? 0]);

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard HLV - Monkey Gym</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); min-height: 100vh; }
        .dashboard-card { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); margin-bottom: 20px; }
        .stat-box { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 25px; border-radius: 10px; text-align: center; }
        .stat-box h2 { font-size: 3rem; font-weight: bold; margin: 0; }
        .schedule-item { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 10px; border-left: 4px solid #11998e; }
        .rating-stars { color: #ffc107; font-size: 1.5rem; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="fas fa-dumbbell me-2"></i> MONKEY GYM - HLV</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-2"></i> <?php echo htmlspecialchars($user['ho_ten']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> Thông tin cá nhân</a></li>
                        <li><hr class="dropdown-divider"></li>
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
        
        <!-- Profile Card -->
        <div class="dashboard-card">
            <div class="row align-items-center">
                <div class="col-auto">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['ho_ten']); ?>&size=100&background=11998e&color=fff" 
                         class="rounded-circle" alt="">
                </div>
                <div class="col">
                    <h2><?php echo htmlspecialchars($user['ho_ten']); ?> 💪</h2>
                    <p class="text-muted mb-1">
                        <i class="fas fa-graduation-cap me-2"></i> 
                        <?php echo htmlspecialchars($trainer['chuyen_mon'] ?? 'Personal Trainer'); ?>
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-briefcase me-2"></i> 
                        <?php echo $trainer['kinh_nghiem'] ?? 0; ?> năm kinh nghiệm
                    </p>
                    <div class="rating-stars mt-2">
                        <?php 
                        $rating = round($stats['rating']);
                        for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star<?php echo $i <= $rating ? '' : '-o'; ?>"></i>
                        <?php endfor; ?>
                        <small class="text-muted">(<?php echo number_format($stats['rating'], 1); ?>)</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-box">
                    <h2><?php echo $stats['sessions_today']; ?></h2>
                    <p class="mb-0">Buổi dạy hôm nay</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <h2><?php echo $stats['sessions_week']; ?></h2>
                    <p class="mb-0">Buổi dạy tuần này</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <h2><?php echo number_format($stats['rating'], 1); ?></h2>
                    <p class="mb-0">Đánh giá trung bình</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <h2><i class="fas fa-calendar-check"></i></h2>
                    <p class="mb-0">Lịch hôm nay</p>
                </div>
            </div>
        </div>
        
        <!-- Schedule Today -->
        <div class="dashboard-card">
            <h4 class="mb-3"><i class="fas fa-calendar-alt me-2"></i> Lịch dạy hôm nay</h4>
            
            <?php if (empty($schedule)): ?>
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <p class="text-muted">Không có lịch dạy hôm nay. Hãy nghỉ ngơi! 😊</p>
            </div>
            <?php else: ?>
            <?php foreach ($schedule as $item): ?>
            <div class="schedule-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">
                            <i class="fas fa-user me-2"></i>
                            <?php echo htmlspecialchars($item['ten_hoi_vien']); ?>
                        </h5>
                        <p class="mb-0 text-muted">
                            <i class="fas fa-clock me-2"></i>
                            <?php echo date('H:i', strtotime($item['gio_bat_dau'])); ?> - 
                            <?php echo date('H:i', strtotime($item['gio_ket_thuc'])); ?>
                        </p>
                        <?php if ($item['ghi_chu']): ?>
                        <p class="mb-0 small text-muted">
                            <i class="fas fa-sticky-note me-2"></i>
                            <?php echo htmlspecialchars($item['ghi_chu']); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <span class="badge bg-<?php echo $item['trang_thai'] == 'hoan_thanh' ? 'success' : 'primary'; ?> p-2">
                            <?php 
                            $statusText = [
                                'da_dat' => 'Đã đặt',
                                'hoan_thanh' => 'Hoàn thành',
                                'huy' => 'Đã hủy'
                            ];
                            echo $statusText[$item['trang_thai']] ?? 'Đã đặt';
                            ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="dashboard-card h-100">
                    <h5><i class="fas fa-calendar-plus me-2"></i> Lịch dạy</h5>
                    <p class="text-muted">Xem lịch dạy đầy đủ và quản lý thời gian</p>
                    <a href="#" class="btn btn-success w-100">Xem lịch đầy đủ</a>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="dashboard-card h-100">
                    <h5><i class="fas fa-users me-2"></i> Học viên</h5>
                    <p class="text-muted">Danh sách học viên đang theo học</p>
                    <a href="#" class="btn btn-primary w-100">Xem học viên</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
