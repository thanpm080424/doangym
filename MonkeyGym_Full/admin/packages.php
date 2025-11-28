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
$packages = $db->select("
    SELECT 
        gt.*,
        COUNT(DISTINCT dk.ma_dang_ky) as so_hoi_vien
    FROM goi_tap gt
    LEFT JOIN dang_ky_goi dk ON gt.ma_goi = dk.ma_goi AND dk.trang_thai != 'da_huy'
    GROUP BY gt.ma_goi
    ORDER BY gt.gia ASC
");

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Gói tập - Monkey Gym</title>
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
        .package-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
            height: 100%;
        }
        .package-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .package-popular {
            border: 3px solid #ffc107;
            position: relative;
        }
        .popular-badge {
            position: absolute;
            top: -15px;
            right: 20px;
            background: #ffc107;
            color: #2d3748;
            padding: 5px 20px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 12px;
        }
        .price {
            font-size: 2.5rem;
            font-weight: bold;
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
                <a class="nav-link active" href="packages.php">
                    <i class="fas fa-box me-2"></i> Gói tập
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
    <div class="main-content">
        <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
            <?php echo $flash['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-box me-2"></i> Gói Tập</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPackageModal">
                <i class="fas fa-plus me-2"></i> Thêm gói mới
            </button>
        </div>
        
        <!-- Packages Grid -->
        <div class="row">
            <?php if (empty($packages)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <p>Chưa có gói tập nào. Hãy thêm gói đầu tiên!</p>
                </div>
            </div>
            <?php else: ?>
            <?php foreach ($packages as $index => $pkg): ?>
            <div class="col-md-4 mb-4">
                <div class="package-card <?php echo $index == 1 ? 'package-popular' : ''; ?>">
                    <?php if ($index == 1): ?>
                    <div class="popular-badge">
                        <i class="fas fa-star me-1"></i> PHỔ BIẾN
                    </div>
                    <?php endif; ?>
                    
                    <div class="text-center mb-4">
                        <h3><?php echo htmlspecialchars($pkg['ten_goi']); ?></h3>
                        <div class="price">
                            <?php echo formatCurrency($pkg['gia']); ?>
                        </div>
                        <p class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            <?php echo $pkg['thoi_han']; ?> tháng
                        </p>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($pkg['mo_ta'])); ?></p>
                    </div>
                    
                    <div class="mb-4">
                        <p class="mb-2"><strong>Quyền lợi:</strong></p>
                        <?php 
                        $benefits = explode(',', $pkg['quyen_loi']);
                        foreach ($benefits as $benefit): ?>
                        <p class="mb-1">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <?php echo trim($benefit); ?>
                        </p>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="border-top pt-3 mt-auto">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <small class="text-muted">Đã đăng ký:</small>
                            <strong class="text-primary"><?php echo $pkg['so_hoi_vien']; ?> người</strong>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button class="btn btn-warning btn-sm">
                                <i class="fas fa-edit me-2"></i> Chỉnh sửa
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
