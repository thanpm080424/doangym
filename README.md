# POWER GYM - Hệ thống Quản lý Phòng Gym

## 📁 Cấu trúc thư mục

```
gym-management/
├── login.html              # Trang đăng nhập
├── member-portal.html      # Trang hội viên
├── admin-dashboard.html    # Trang admin
├── styles.css             # CSS chung cho toàn bộ hệ thống
├── auth.js                # JavaScript xử lý đăng nhập
├── member-portal.js       # JavaScript cho trang hội viên
├── admin-dashboard.js     # JavaScript cho trang admin
└── README.md             # File hướng dẫn này
```

## 🚀 Cách sử dụng

### 1. Chạy trực tiếp (HTML/CSS/JS thuần)

```bash
# Mở file login.html bằng trình duyệt
# Hoặc dùng Live Server trong VS Code
```

### 2. Tích hợp vào Laravel 12

#### Bước 1: Copy files vào Laravel

```bash
# Copy CSS vào public/css/
cp styles.css path/to/laravel/public/css/

# Copy JS vào public/js/
cp auth.js member-portal.js admin-dashboard.js path/to/laravel/public/js/

# Copy HTML vào resources/views/
cp login.html path/to/laravel/resources/views/login.blade.php
cp member-portal.html path/to/laravel/resources/views/member-portal.blade.php
cp admin-dashboard.html path/to/laravel/resources/views/admin-dashboard.blade.php
```

#### Bước 2: Chỉnh sửa Blade templates

Thay đổi các đường dẫn CSS/JS trong file .blade.php:

```html
<!-- Thay vì -->
<link rel="stylesheet" href="styles.css">

<!-- Dùng -->
<link rel="stylesheet" href="{{ asset('css/styles.css') }}">

<!-- Thay vì -->
<script src="auth.js"></script>

<!-- Dùng -->
<script src="{{ asset('js/auth.js') }}"></script>
```

#### Bước 3: Tạo routes

```php
// routes/web.php
Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/member-portal', function () {
    return view('member-portal');
})->middleware('auth')->name('member.portal');

Route::get('/admin-dashboard', function () {
    return view('admin-dashboard');
})->middleware(['auth', 'admin'])->name('admin.dashboard');
```

#### Bước 4: Tạo API endpoints (tùy chọn)

Thay thế localStorage bằng API Laravel:

```php
// routes/api.php
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
Route::get('/members', [MemberController::class, 'index'])->middleware('auth');
// ... thêm các endpoints khác
```

## 🔐 Tài khoản demo

### Admin
- **Username:** admin
- **Password:** admin123

### Hội viên
- **Username:** member1
- **Password:** 123456

## 📱 Tính năng

### Trang Hội viên
- ✅ Trang chủ với thông tin cá nhân
- ✅ Điểm danh QR Code với timer
- ✅ Xem gói tập hiện tại
- ✅ Lịch sử điểm danh
- ✅ Lịch tập với PT
- ✅ Giao diện responsive mobile-first

### Trang Admin
- ✅ Dashboard tổng quan
- ✅ Quản lý hội viên (thêm/sửa/xóa)
- ✅ Quản lý gói tập
- ✅ Quản lý huấn luyện viên
- ✅ Quản lý điểm danh
- ✅ Báo cáo thống kê
- ✅ Sidebar có thể thu gọn

## 🎨 Công nghệ sử dụng

- **HTML5** - Cấu trúc
- **CSS3** - Styling với custom properties
- **Vanilla JavaScript** - Logic xử lý
- **LocalStorage** - Lưu trữ phiên đăng nhập (tạm thời)
- **Google Fonts (Inter)** - Typography

## 🔧 Tùy chỉnh

### Thay đổi màu sắc

Chỉnh sửa CSS variables trong `styles.css`:

```css
:root {
    --bg-primary: #09090b;
    --orange: #f97316;
    --red: #ef4444;
    /* ... */
}
```

### Thêm dữ liệu mới

Chỉnh sửa mock data trong các file JS:

```javascript
// member-portal.js
const MEMBERS = [
    // Thêm member mới
];

// admin-dashboard.js
const MEMBERS_DATA = [
    // Thêm member mới
];
```

## 📝 Lưu ý khi tích hợp Laravel

1. **Authentication**: Thay LocalStorage bằng Laravel Session/Sanctum
2. **API**: Tạo controllers và routes cho CRUD operations
3. **Database**: Tạo migrations cho các bảng: users, members, packages, trainers, attendance, schedules
4. **Validation**: Thêm validation rules trong Laravel
5. **Authorization**: Sử dụng Laravel Gates/Policies cho phân quyền
6. **File Upload**: Xử lý upload ảnh đại diện, QR code
7. **Email**: Cấu hình email thông báo hết hạn gói tập

## 🐛 Troubleshooting

### Lỗi CORS khi tích hợp API
Thêm middleware CORS trong Laravel:
```php
// app/Http/Kernel.php
protected $middleware = [
    \Fruitcake\Cors\HandleCors::class,
];
```

### CSS không load
Kiểm tra đường dẫn asset() và chạy:
```bash
php artisan storage:link
```

### JavaScript không hoạt động
- Kiểm tra Console log trong browser (F12)
- Đảm bảo script được load sau DOM ready

## 📧 Hỗ trợ

Nếu cần hỗ trợ, vui lòng:
1. Kiểm tra console log trong browser
2. Xem lại hướng dẫn README
3. Đảm bảo tất cả files đã được copy đúng vị trí

---

**Version:** 1.0.0  
**Date:** 2025-03-20  
**Author:** Power Gym Team
