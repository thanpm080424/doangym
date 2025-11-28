<?php
require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/helpers.php';

startSession();
if (!isLoggedIn()) redirect('public/login.php');

$db = new Database();
$user = getUserInfo();

// Lấy danh sách nhân viên
$staff = $db->select("
    SELECT 
        nv.*,
        nd.ho_ten,
        nd.email,
        nd.so_dien_thoai,
        pb.ten_phong_ban
    FROM nhan_vien nv
    INNER JOIN nguoi_dung nd ON nv.ma_nguoi_dung = nd.ma_nguoi_dung
    LEFT JOIN phong_ban pb ON nv.ma_phong_ban = pb.ma_phong_ban
    ORDER BY nd.ho_ten ASC
");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Nhân viên - Monkey Gym</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f5f5f5; }
        .sidebar { background: #2d3748; min-height: 100vh; color: white; position: fixed; width: 250px; }
        .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 20px; border-radius: 8px; margin: 5px 0; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #ffc107; color: #2d3748; }
        .main-content { margin-left: 250px; padding: 30px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="p-4 text-center border-bottom">
            <h4>🐒 MONKEY GYM</h4>
            <small>Admin Panel</small>
        </div>
        <div class="p-3">
            <nav class="nav flex-column">
                <a class="nav-link" href="dashboard.php"><i class="fas fa-home me-2"></i> Dashboard</a>
                <a class="nav-link" href="members.php"><i class="fas fa-users me-2"></i> Hội viên</a>
                <a class="nav-link" href="packages.php"><i class="fas fa-box me-2"></i> Gói tập</a>
                <a class="nav-link" href="trainers.php"><i class="fas fa-dumbbell me-2"></i> HLV</a>
                <a class="nav-link active" href="staff.php"><i class="fas fa-user-tie me-2"></i> Nhân viên</a>
                <a class="nav-link" href="reports.php"><i class="fas fa-chart-bar me-2"></i> Báo cáo</a>
                <hr>
                <a class="nav-link" href="../public/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Đăng xuất</a>
            </nav>
        </div>
    </div>
    
    <div class="main-content">
        <h2 class="mb-4"><i class="fas fa-user-tie me-2"></i> Quản lý Nhân viên</h2>
        
        <div class="card">
            <div class="card-body">
                <?php if (empty($staff)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Chưa có nhân viên nào</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Họ tên</th>
                                <th>Email</th>
                                <th>SĐT</th>
                                <th>Phòng ban</th>
                                <th>Chức vụ</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($staff as $s): ?>
                            <tr>
                                <td>
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($s['ho_ten']); ?>&size=40" 
                                         class="rounded-circle me-2" alt="">
                                    <?php echo htmlspecialchars($s['ho_ten']); ?>
                                </td>
                                <td><?php echo $s['email']; ?></td>
                                <td><?php echo $s['so_dien_thoai']; ?></td>
                                <td><?php echo $s['ten_phong_ban'] ?? 'Chưa phân'; ?></td>
                                <td><?php echo htmlspecialchars($s['chuc_vu'] ?? 'Nhân viên'); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
