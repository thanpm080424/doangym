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

// Lấy danh sách HLV
$trainers = $db->select("
    SELECT 
        hlv.*,
        nd.ho_ten,
        nd.email,
        nd.so_dien_thoai,
        COUNT(DISTINCT ld.ma_lich) as so_buoi_day,
        AVG(dg.diem_so) as diem_trung_binh
    FROM huan_luyen_vien hlv
    INNER JOIN nguoi_dung nd ON hlv.ma_nguoi_dung = nd.ma_nguoi_dung
    LEFT JOIN lich_day_pt ld ON hlv.ma_hlv = ld.ma_hlv
    LEFT JOIN danh_gia_hlv dg ON hlv.ma_hlv = dg.ma_hlv
    GROUP BY hlv.ma_hlv, nd.ho_ten, nd.email, nd.so_dien_thoai, hlv.chuyen_mon, hlv.kinh_nghiem, hlv.trang_thai
    ORDER BY nd.ho_ten ASC
");

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý HLV - Monkey Gym</title>
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
        .trainer-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s;
            height: 100%;
        }
        .trainer-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        .rating-stars {
            color: #ffc107;
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
                <a class="nav-link active" href="trainers.php">
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
    <div class="main-content">
        <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
            <?php echo $flash['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-dumbbell me-2"></i> Quản lý Huấn luyện viên</h2>
            <button class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Thêm HLV mới
            </button>
        </div>
        
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h3><?php echo count($trainers); ?></h3>
                        <p class="mb-0">Tổng số HLV</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h3><?php echo count(array_filter($trainers, fn($t) => ($t['trang_thai'] ?? 0) == 1)); ?></h3>
                        <p class="mb-0">Đang hoạt động</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h3><?php echo array_sum(array_column($trainers, 'so_buoi_day')); ?></h3>
                        <p class="mb-0">Tổng buổi dạy</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Trainers Grid -->
        <?php if (empty($trainers)): ?>
        <div class="alert alert-info text-center py-5">
            <i class="fas fa-inbox fa-3x mb-3"></i>
            <h4>Chưa có HLV nào</h4>
            <p class="text-muted">Hãy thêm huấn luyện viên đầu tiên cho phòng gym của bạn!</p>
            <button class="btn btn-primary btn-lg">
                <i class="fas fa-plus me-2"></i> Thêm HLV đầu tiên
            </button>
        </div>
        <?php else: ?>
        <div class="row">
            <?php foreach ($trainers as $trainer): ?>
            <div class="col-md-4 mb-4">
                <div class="trainer-card">
                    <div class="text-center mb-3">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($trainer['ho_ten']); ?>&size=100&background=ffc107&color=2d3748" 
                             class="rounded-circle mb-2" alt="">
                        <h5 class="mb-1"><?php echo htmlspecialchars($trainer['ho_ten']); ?></h5>
                        <p class="text-muted mb-0"><?php echo htmlspecialchars($trainer['chuyen_mon'] ?? 'Đa năng'); ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Kinh nghiệm:</small>
                            <strong><?php echo $trainer['kinh_nghiem'] ?? 0; ?> năm</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Buổi dạy:</small>
                            <strong><?php echo $trainer['so_buoi_day']; ?> buổi</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Đánh giá:</small>
                            <div class="rating-stars">
                                <?php 
                                $rating = round($trainer['diem_trung_binh'] ?? 0);
                                for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star<?php echo $i <= $rating ? '' : '-half-alt'; ?>"></i>
                                <?php endfor; ?>
                                <small class="text-muted">(<?php echo number_format($trainer['diem_trung_binh'] ?? 0, 1); ?>)</small>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="small mb-3">
                        <p class="mb-1"><i class="fas fa-envelope me-2"></i> <?php echo $trainer['email']; ?></p>
                        <p class="mb-0"><i class="fas fa-phone me-2"></i> <?php echo $trainer['so_dien_thoai']; ?></p>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-calendar me-2"></i> Xem lịch dạy
                        </button>
                        <button class="btn btn-sm btn-outline-warning">
                            <i class="fas fa-edit me-2"></i> Chỉnh sửa
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
