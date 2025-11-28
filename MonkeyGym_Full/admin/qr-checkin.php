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

// Xử lý check-in
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['qr_code'])) {
    $qrCode = sanitize($_POST['qr_code']);
    
    try {
        // Tìm hội viên theo QR Code
        $member = $db->selectOne("
            SELECT 
                hv.ma_hoi_vien,
                hv.ma_nguoi_dung,
                nd.ho_ten,
                nd.email,
                nd.so_dien_thoai,
                hv.ma_qr
            FROM hoi_vien hv
            INNER JOIN nguoi_dung nd ON hv.ma_nguoi_dung = nd.ma_nguoi_dung
            WHERE hv.ma_qr = :qr
        ", [':qr' => $qrCode]);
        
        if (!$member) {
            jsonResponse(false, 'Không tìm thấy hội viên với mã QR này!');
        }
        
        // Kiểm tra gói tập còn hiệu lực
        $package = $db->selectOne("
            SELECT 
                dk.*,
                gt.ten_goi
            FROM dang_ky_goi dk
            INNER JOIN goi_tap gt ON dk.ma_goi = gt.ma_goi
            WHERE dk.ma_hoi_vien = :id 
            AND dk.trang_thai = 'dang_hoat_dong'
            AND dk.ngay_ket_thuc >= CURDATE()
            ORDER BY dk.ngay_ket_thuc DESC
            LIMIT 1
        ", [':id' => $member['ma_hoi_vien']]);
        
        if (!$package) {
            jsonResponse(false, 'Hội viên chưa có gói tập hoặc đã hết hạn!', [
                'member' => $member
            ]);
        }
        
        // Kiểm tra đã check-in hôm nay chưa
        $today = date('Y-m-d');
        $checkedToday = $db->selectOne("
            SELECT * FROM diem_danh 
            WHERE ma_hoi_vien = :id 
            AND DATE(thoi_gian_vao) = :today
        ", [':id' => $member['ma_hoi_vien'], ':today' => $today]);
        
        if ($checkedToday && !$checkedToday['thoi_gian_ra']) {
            jsonResponse(false, 'Hội viên đã check-in hôm nay lúc ' . date('H:i', strtotime($checkedToday['thoi_gian_vao'])), [
                'member' => $member,
                'package' => $package,
                'checkin' => $checkedToday
            ]);
        }
        
        // Tạo check-in mới
        $sqlCheckin = "INSERT INTO diem_danh (ma_hoi_vien, thoi_gian_vao, ghi_chu) 
                       VALUES (:member_id, NOW(), :note)";
        $checkinId = $db->insert($sqlCheckin, [
            ':member_id' => $member['ma_hoi_vien'],
            ':note' => 'Check-in bởi ' . $user['ho_ten']
        ]);
        
        if ($checkinId) {
            logActivity($user['id'], 'Check-in hội viên', 'Hội viên: ' . $member['ho_ten']);
            
            jsonResponse(true, 'Check-in thành công!', [
                'member' => $member,
                'package' => $package,
                'checkin_id' => $checkinId,
                'time' => date('H:i:s')
            ]);
        } else {
            jsonResponse(false, 'Lỗi khi lưu check-in!');
        }
        
    } catch (Exception $e) {
        jsonResponse(false, 'Lỗi: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quét QR Check-in - Monkey Gym</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .scanner-container { background: white; border-radius: 20px; padding: 30px; max-width: 800px; margin: 50px auto; box-shadow: 0 10px 40px rgba(0,0,0,0.3); }
        .scanner-header { background: linear-gradient(135deg, #ffa751 0%, #ffe259 100%); padding: 25px; border-radius: 15px; margin: -30px -30px 30px -30px; text-align: center; }
        #reader { border-radius: 10px; overflow: hidden; margin: 20px 0; }
        .result-card { background: #f8f9fa; border-radius: 10px; padding: 20px; margin-top: 20px; display: none; }
        .result-card.success { background: #d4edda; border: 2px solid #28a745; }
        .result-card.error { background: #f8d7da; border: 2px solid #dc3545; }
        .member-info { background: white; border-radius: 10px; padding: 15px; margin-top: 15px; }
        .btn-scan { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 12px 30px; font-size: 18px; }
        .stats-box { background: white; border-radius: 10px; padding: 15px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../admin/dashboard.php">
                <i class="fas fa-dumbbell me-2"></i> MONKEY GYM
            </a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../admin/dashboard.php">
                        <i class="fas fa-home me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../public/logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    
    <div class="container">
        <div class="scanner-container">
            <div class="scanner-header">
                <h2 class="mb-0">
                    <i class="fas fa-qrcode me-2"></i> QUÉT QR CODE CHECK-IN
                </h2>
                <p class="mb-0 mt-2">Đưa mã QR của hội viên vào khung quét</p>
            </div>
            
            <!-- Stats hôm nay -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stats-box">
                        <h3 class="mb-0" id="today-checkins">0</h3>
                        <small class="text-muted">Check-in hôm nay</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-box">
                        <h3 class="mb-0" id="current-time"><?php echo date('H:i:s'); ?></h3>
                        <small class="text-muted">Giờ hiện tại</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-box">
                        <h3 class="mb-0"><?php echo date('d/m/Y'); ?></h3>
                        <small class="text-muted">Ngày hôm nay</small>
                    </div>
                </div>
            </div>
            
            <!-- Scanner -->
            <div id="reader"></div>
            
            <div class="text-center mb-3">
                <button id="btn-start-scan" class="btn btn-primary btn-lg btn-scan">
                    <i class="fas fa-camera me-2"></i> Bắt đầu quét
                </button>
                <button id="btn-stop-scan" class="btn btn-danger btn-lg" style="display: none;">
                    <i class="fas fa-stop me-2"></i> Dừng quét
                </button>
            </div>
            
            <!-- Manual Input -->
            <div class="text-center">
                <button class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#manualInput">
                    <i class="fas fa-keyboard me-2"></i> Nhập mã thủ công
                </button>
            </div>
            
            <div class="collapse mt-3" id="manualInput">
                <div class="input-group">
                    <input type="text" id="manual-qr" class="form-control" placeholder="Nhập mã QR...">
                    <button class="btn btn-primary" onclick="checkInManual()">
                        <i class="fas fa-check me-2"></i> Check-in
                    </button>
                </div>
            </div>
            
            <!-- Result -->
            <div id="result" class="result-card"></div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let html5QrCode;
        let isScanning = false;
        
        // Update time
        setInterval(() => {
            const now = new Date();
            document.getElementById('current-time').textContent = 
                now.toTimeString().split(' ')[0];
        }, 1000);
        
        // Load today stats
        loadTodayStats();
        
        function loadTodayStats() {
            // Giả lập - bạn có thể tạo API riêng để lấy stats
            const today = new Date().toISOString().split('T')[0];
            // TODO: Call API to get today's check-ins count
            document.getElementById('today-checkins').textContent = '0';
        }
        
        // Start scanning
        document.getElementById('btn-start-scan').addEventListener('click', function() {
            startScanning();
        });
        
        document.getElementById('btn-stop-scan').addEventListener('click', function() {
            stopScanning();
        });
        
        function startScanning() {
            if (isScanning) return;
            
            html5QrCode = new Html5Qrcode("reader");
            
            html5QrCode.start(
                { facingMode: "environment" },
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                },
                (decodedText, decodedResult) => {
                    // QR Code detected
                    processQRCode(decodedText);
                    stopScanning();
                },
                (errorMessage) => {
                    // Scanning error - ignore
                }
            ).then(() => {
                isScanning = true;
                document.getElementById('btn-start-scan').style.display = 'none';
                document.getElementById('btn-stop-scan').style.display = 'inline-block';
            }).catch(err => {
                alert('Không thể mở camera: ' + err);
            });
        }
        
        function stopScanning() {
            if (!isScanning) return;
            
            html5QrCode.stop().then(() => {
                isScanning = false;
                document.getElementById('btn-start-scan').style.display = 'inline-block';
                document.getElementById('btn-stop-scan').style.display = 'none';
            });
        }
        
        function checkInManual() {
            const qrCode = document.getElementById('manual-qr').value.trim();
            if (!qrCode) {
                alert('Vui lòng nhập mã QR!');
                return;
            }
            processQRCode(qrCode);
        }
        
        function processQRCode(qrCode) {
            // Show loading
            const resultDiv = document.getElementById('result');
            resultDiv.className = 'result-card';
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Đang xử lý...</p></div>';
            
            // Send to server
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'qr_code=' + encodeURIComponent(qrCode)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess(data);
                    // Update stats
                    const current = parseInt(document.getElementById('today-checkins').textContent);
                    document.getElementById('today-checkins').textContent = current + 1;
                } else {
                    showError(data);
                }
            })
            .catch(error => {
                showError({ message: 'Lỗi kết nối: ' + error });
            });
        }
        
        function showSuccess(data) {
            const resultDiv = document.getElementById('result');
            resultDiv.className = 'result-card success';
            resultDiv.innerHTML = `
                <div class="text-center">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h4 class="text-success">✅ CHECK-IN THÀNH CÔNG!</h4>
                    <p class="mb-0">${data.message}</p>
                </div>
                <div class="member-info">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Họ tên:</strong> ${data.data.member.ho_ten}</p>
                            <p class="mb-1"><strong>Email:</strong> ${data.data.member.email}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Gói tập:</strong> ${data.data.package.ten_goi}</p>
                            <p class="mb-1"><strong>Thời gian:</strong> ${data.data.time}</p>
                        </div>
                    </div>
                </div>
            `;
            
            // Play success sound (optional)
            playSound('success');
            
            // Clear after 3 seconds
            setTimeout(() => {
                resultDiv.style.display = 'none';
                document.getElementById('manual-qr').value = '';
            }, 3000);
        }
        
        function showError(data) {
            const resultDiv = document.getElementById('result');
            resultDiv.className = 'result-card error';
            resultDiv.innerHTML = `
                <div class="text-center">
                    <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                    <h4 class="text-danger">❌ KHÔNG THỂ CHECK-IN!</h4>
                    <p class="mb-0">${data.message}</p>
                </div>
                ${data.data && data.data.member ? `
                    <div class="member-info">
                        <p class="mb-1"><strong>Họ tên:</strong> ${data.data.member.ho_ten}</p>
                        <p class="mb-0"><strong>Email:</strong> ${data.data.member.email}</p>
                    </div>
                ` : ''}
            `;
            
            // Play error sound (optional)
            playSound('error');
            
            // Clear after 5 seconds
            setTimeout(() => {
                resultDiv.style.display = 'none';
                document.getElementById('manual-qr').value = '';
            }, 5000);
        }
        
        function playSound(type) {
            // Optional: Add sound effects
            // const audio = new Audio(type === 'success' ? 'success.mp3' : 'error.mp3');
            // audio.play();
        }
        
        // Auto-focus manual input when shown
        document.getElementById('manualInput').addEventListener('shown.bs.collapse', function() {
            document.getElementById('manual-qr').focus();
        });
    </script>
</body>
</html>
