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

// Xử lý tìm kiếm
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? 'all';

// Query danh sách hội viên
$sql = "SELECT 
    hv.ma_hoi_vien,
    nd.ho_ten,
    nd.email,
    nd.so_dien_thoai,
    hv.gioi_tinh,
    hv.ma_qr,
    MAX(dk.ngay_ket_thuc) as ngay_het_han,
    CASE 
        WHEN MAX(dk.ngay_ket_thuc) < CURDATE() THEN 'het_han'
        WHEN MAX(dk.ngay_ket_thuc) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'sap_het_han'
        ELSE 'hoat_dong'
    END as trang_thai
FROM hoi_vien hv
INNER JOIN nguoi_dung nd ON hv.ma_nguoi_dung = nd.ma_nguoi_dung
LEFT JOIN dang_ky_goi dk ON hv.ma_hoi_vien = dk.ma_hoi_vien
WHERE 1=1";

$params = [];

if (!empty($search)) {
    $sql .= " AND (nd.ho_ten LIKE :search OR nd.email LIKE :search OR nd.so_dien_thoai LIKE :search)";
    $params[':search'] = "%$search%";
}

$sql .= " GROUP BY hv.ma_hoi_vien, nd.ho_ten, nd.email, nd.so_dien_thoai, hv.gioi_tinh, hv.ma_qr";

if ($status != 'all') {
    $sql .= " HAVING trang_thai = :status";
    $params[':status'] = $status;
}

$sql .= " ORDER BY nd.ho_ten ASC";

$members = $db->select($sql, $params);

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Hội viên - Monkey Gym</title>
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
        .member-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        .member-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transform: translateY(-2px);
        }
        .badge-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
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
                <a class="nav-link active" href="members.php">
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
            <h2><i class="fas fa-users me-2"></i> Quản lý Hội viên</h2>
            <a href="member-add.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Thêm hội viên mới
            </a>
        </div>
        
        <!-- Filter & Search -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Tìm theo tên, email, SĐT..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="status" class="form-select">
                            <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>Tất cả trạng thái</option>
                            <option value="hoat_dong" <?php echo $status == 'hoat_dong' ? 'selected' : ''; ?>>Đang hoạt động</option>
                            <option value="sap_het_han" <?php echo $status == 'sap_het_han' ? 'selected' : ''; ?>>Sắp hết hạn</option>
                            <option value="het_han" <?php echo $status == 'het_han' ? 'selected' : ''; ?>>Hết hạn</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-2"></i> Lọc
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Member List -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Họ tên</th>
                                <th>Email</th>
                                <th>Số điện thoại</th>
                                <th>Giới tính</th>
                                <th>Ngày hết hạn</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($members)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Chưa có hội viên nào</p>
                                    <a href="member-add.php" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i> Thêm hội viên đầu tiên
                                    </a>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($members as $member): ?>
                            <tr>
                                <td><strong>#<?php echo $member['ma_hoi_vien']; ?></strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($member['ho_ten']); ?>&size=40" 
                                             class="rounded-circle me-2" alt="">
                                        <strong><?php echo htmlspecialchars($member['ho_ten']); ?></strong>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($member['email']); ?></td>
                                <td><?php echo htmlspecialchars($member['so_dien_thoai']); ?></td>
                                <td>
                                    <?php if ($member['gioi_tinh'] == 'nam'): ?>
                                        <i class="fas fa-mars text-primary"></i> Nam
                                    <?php else: ?>
                                        <i class="fas fa-venus text-danger"></i> Nữ
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($member['ngay_het_han']): ?>
                                        <?php echo formatDate($member['ngay_het_han']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Chưa đăng ký</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = [
                                        'hoat_dong' => 'success',
                                        'sap_het_han' => 'warning',
                                        'het_han' => 'danger'
                                    ];
                                    $statusText = [
                                        'hoat_dong' => 'Hoạt động',
                                        'sap_het_han' => 'Sắp hết hạn',
                                        'het_han' => 'Hết hạn'
                                    ];
                                    ?>
                                    <span class="badge bg-<?php echo $statusClass[$member['trang_thai']]; ?>">
                                        <?php echo $statusText[$member['trang_thai']]; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="member-view.php?id=<?php echo $member['ma_hoi_vien']; ?>" 
                                           class="btn btn-info" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="member-edit.php?id=<?php echo $member['ma_hoi_vien']; ?>" 
                                           class="btn btn-warning" title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="member-qr.php?id=<?php echo $member['ma_hoi_vien']; ?>" 
                                           class="btn btn-success" title="QR Code">
                                            <i class="fas fa-qrcode"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Tổng số: <strong><?php echo count($members); ?></strong> hội viên
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
