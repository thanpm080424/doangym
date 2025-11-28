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

$memberId = $_GET['id'] ?? 0;

// Lấy thông tin hội viên
$member = $db->selectOne("
    SELECT 
        hv.*,
        nd.ho_ten,
        nd.email,
        nd.so_dien_thoai,
        nd.ngay_tao
    FROM hoi_vien hv
    INNER JOIN nguoi_dung nd ON hv.ma_nguoi_dung = nd.ma_nguoi_dung
    WHERE hv.ma_hoi_vien = :id
", [':id' => $memberId]);

if (!$member) {
    setFlash('danger', 'Không tìm thấy hội viên!');
    redirect('admin/members.php');
}

// Lấy lịch sử đăng ký gói
$packages = $db->select("
    SELECT 
        dk.*,
        gt.ten_goi,
        gt.gia,
        nd.ho_ten as nguoi_dang_ky
    FROM dang_ky_goi dk
    INNER JOIN goi_tap gt ON dk.ma_goi = gt.ma_goi
    LEFT JOIN nguoi_dung nd ON dk.nguoi_dang_ky = nd.ma_nguoi_dung
    WHERE dk.ma_hoi_vien = :id
    ORDER BY dk.ngay_bat_dau DESC
", [':id' => $memberId]);

// Lấy lịch sử thanh toán
$payments = $db->select("
    SELECT * FROM thanh_toan
    WHERE ma_dang_ky IN (SELECT ma_dang_ky FROM dang_ky_goi WHERE ma_hoi_vien = :id)
    ORDER BY ngay_thanh_toan DESC
    LIMIT 10
", [':id' => $memberId]);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết Hội viên - Monkey Gym</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f5f5f5; }
        .profile-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px; border-radius: 15px; margin-bottom: 30px; }
        .profile-avatar { width: 120px; height: 120px; border: 5px solid white; }
        .info-card { background: white; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="mb-3">
            <a href="members.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Quay lại
            </a>
        </div>
        
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="row align-items-center">
                <div class="col-auto">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($member['ho_ten']); ?>&size=120&background=ffc107&color=2d3748" 
                         class="rounded-circle profile-avatar" alt="">
                </div>
                <div class="col">
                    <h2 class="mb-1"><?php echo htmlspecialchars($member['ho_ten']); ?></h2>
                    <p class="mb-0">
                        <i class="fas fa-id-card me-2"></i> Mã HV: #<?php echo $member['ma_hoi_vien']; ?>
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-calendar me-2"></i> Ngày tham gia: <?php echo formatDate($member['ngay_tao']); ?>
                    </p>
                </div>
                <div class="col-auto">
                    <a href="member-edit.php?id=<?php echo $member['ma_hoi_vien']; ?>" class="btn btn-warning btn-lg">
                        <i class="fas fa-edit me-2"></i> Chỉnh sửa
                    </a>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Thông tin cá nhân -->
            <div class="col-md-6">
                <div class="info-card">
                    <h5 class="mb-3"><i class="fas fa-user me-2"></i> Thông tin cá nhân</h5>
                    <table class="table table-borderless">
                        <tr>
                            <th width="150">Email:</th>
                            <td><?php echo htmlspecialchars($member['email']); ?></td>
                        </tr>
                        <tr>
                            <th>Số điện thoại:</th>
                            <td><?php echo htmlspecialchars($member['so_dien_thoai']); ?></td>
                        </tr>
                        <tr>
                            <th>Giới tính:</th>
                            <td>
                                <?php if ($member['gioi_tinh'] == 'nam'): ?>
                                    <i class="fas fa-mars text-primary"></i> Nam
                                <?php else: ?>
                                    <i class="fas fa-venus text-danger"></i> Nữ
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Ngày sinh:</th>
                            <td><?php echo $member['ngay_sinh'] ? formatDate($member['ngay_sinh']) : 'Chưa cập nhật'; ?></td>
                        </tr>
                        <tr>
                            <th>Địa chỉ:</th>
                            <td><?php echo $member['dia_chi'] ?: 'Chưa cập nhật'; ?></td>
                        </tr>
                    </table>
                </div>
                
                <!-- QR Code -->
                <div class="info-card text-center">
                    <h5 class="mb-3"><i class="fas fa-qrcode me-2"></i> Mã QR điểm danh</h5>
                    <div class="qr-code bg-light p-4 rounded">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode($member['ma_qr']); ?>" 
                             alt="QR Code" class="img-fluid">
                    </div>
                    <small class="text-muted mt-2 d-block"><?php echo $member['ma_qr']; ?></small>
                    <button class="btn btn-sm btn-primary mt-2" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> In QR Code
                    </button>
                </div>
            </div>
            
            <!-- Gói tập & Thanh toán -->
            <div class="col-md-6">
                <!-- Gói tập hiện tại -->
                <div class="info-card">
                    <h5 class="mb-3"><i class="fas fa-box me-2"></i> Lịch sử gói tập</h5>
                    <?php if (empty($packages)): ?>
                    <p class="text-muted">Chưa đăng ký gói tập nào</p>
                    <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($packages as $pkg): ?>
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong><?php echo $pkg['ten_goi']; ?></strong>
                                <?php
                                $statusClass = [
                                    'dang_hoat_dong' => 'success',
                                    'cho_thanh_toan' => 'warning',
                                    'da_huy' => 'danger',
                                    'het_han' => 'secondary'
                                ];
                                $statusText = [
                                    'dang_hoat_dong' => 'Đang hoạt động',
                                    'cho_thanh_toan' => 'Chờ thanh toán',
                                    'da_huy' => 'Đã hủy',
                                    'het_han' => 'Hết hạn'
                                ];
                                ?>
                                <span class="badge bg-<?php echo $statusClass[$pkg['trang_thai']]; ?>">
                                    <?php echo $statusText[$pkg['trang_thai']]; ?>
                                </span>
                            </div>
                            <div class="small text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                <?php echo formatDate($pkg['ngay_bat_dau']); ?> - <?php echo formatDate($pkg['ngay_ket_thuc']); ?>
                            </div>
                            <div class="small text-muted">
                                <i class="fas fa-dollar-sign me-1"></i>
                                <?php echo formatCurrency($pkg['gia_thanh_toan']); ?>
                            </div>
                            <?php if ($pkg['nguoi_dang_ky']): ?>
                            <div class="small text-muted">
                                <i class="fas fa-user me-1"></i>
                                Đăng ký bởi: <?php echo $pkg['nguoi_dang_ky']; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Lịch sử thanh toán -->
                <div class="info-card">
                    <h5 class="mb-3"><i class="fas fa-money-bill me-2"></i> Lịch sử thanh toán</h5>
                    <?php if (empty($payments)): ?>
                    <p class="text-muted">Chưa có giao dịch nào</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Ngày</th>
                                    <th>Số tiền</th>
                                    <th>Phương thức</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td><?php echo formatDate($payment['ngay_thanh_toan']); ?></td>
                                    <td><strong><?php echo formatCurrency($payment['so_tien']); ?></strong></td>
                                    <td><?php echo ucfirst($payment['phuong_thuc']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $payment['trang_thai'] == 'thanh_cong' ? 'success' : 'warning'; ?>">
                                            <?php echo $payment['trang_thai'] == 'thanh_cong' ? 'Thành công' : 'Chờ xử lý'; ?>
                                        </span>
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
        
        <!-- Actions -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="mb-3">Thao tác</h5>
                <div class="d-flex gap-2">
                    <a href="member-edit.php?id=<?php echo $member['ma_hoi_vien']; ?>" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i> Chỉnh sửa thông tin
                    </a>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#renewModal">
                        <i class="fas fa-sync me-2"></i> Gia hạn gói tập
                    </button>
                    <button class="btn btn-primary">
                        <i class="fas fa-qrcode me-2"></i> Điểm danh
                    </button>
                    <button class="btn btn-danger" onclick="if(confirm('Bạn có chắc muốn xóa?')) location.href='member-delete.php?id=<?php echo $member['ma_hoi_vien']; ?>'">
                        <i class="fas fa-trash me-2"></i> Xóa hội viên
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
