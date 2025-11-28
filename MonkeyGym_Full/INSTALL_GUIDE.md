# 🚀 HƯỚNG DẪN CÀI ĐẶT HỆ THỐNG MONKEY GYM
## Phiên bản Full-Stack với PHP + MySQL

---

## 📋 YÊU CẦU HỆ THỐNG

### Phần mềm cần thiết:
1. **XAMPP** hoặc **WAMP** (Windows) / **LAMP** (Linux) / **MAMP** (Mac)
   - Tải XAMPP: https://www.apachefriends.org/
   - Bao gồm: Apache, MySQL, PHP 8.0+

2. **Visual Studio Code** (khuyến nghị)
   - Tải: https://code.visualstudio.com/
   - Extensions: PHP Intelephense, MySQL

3. **Trình duyệt web hiện đại**
   - Chrome, Firefox, hoặc Edge

---

## 🔧 BƯỚC 1: CÀI ĐẶT XAMPP

### Windows:
1. Download XAMPP từ: https://www.apachefriends.org/download.html
2. Chọn phiên bản PHP 8.0 trở lên
3. Chạy file cài đặt (xampp-windows-x64-*.exe)
4. Chọn thư mục cài đặt (mặc định: C:\xampp)
5. Bỏ chọn "Learn more about Bitnami"
6. Nhấn "Next" và chờ cài đặt hoàn tất

### Khởi động XAMPP:
1. Mở XAMPP Control Panel
2. Start **Apache**
3. Start **MySQL**
4. Kiểm tra: Mở trình duyệt, vào http://localhost
   - Nếu thấy trang XAMPP → Thành công!

---

## 💾 BƯỚC 2: IMPORT DATABASE

### Cách 1: Sử dụng phpMyAdmin (Dễ nhất)

1. **Mở phpMyAdmin:**
   - Vào: http://localhost/phpmyadmin
   - Không cần password (mặc định)

2. **Tạo database:**
   - Click tab "Databases"
   - Tên database: `gym_db`
   - Collation: `utf8mb4_unicode_ci`
   - Click "Create"

3. **Import dữ liệu:**
   - Click vào database `gym_db` vừa tạo
   - Click tab "Import"
   - Click "Choose File"
   - Chọn file: `MonkeyGym.sql`
   - Scroll xuống, click "Go"
   - Chờ import xong (khoảng 10-30 giây)
   - Thành công khi thấy thông báo màu xanh!

4. **Kiểm tra:**
   - Click vào `gym_db`
   - Xem danh sách bảng bên trái
   - Phải có **26 bảng**

### Cách 2: Sử dụng MySQL Command Line

```bash
# Mở command prompt/terminal
cd C:\xampp\mysql\bin

# Import database
mysql -u root -p < đường_dẫn_đến_file\MonkeyGym.sql

# Ví dụ:
mysql -u root -p < C:\Users\YourName\Downloads\MonkeyGym.sql
```

---

## 📁 BƯỚC 3: CÀI ĐẶT MÃ NGUỒN

### Copy files vào XAMPP:

1. **Tìm thư mục htdocs:**
   - Windows: `C:\xampp\htdocs\`
   - Mac: `/Applications/XAMPP/htdocs/`
   - Linux: `/opt/lampp/htdocs/`

2. **Copy thư mục MonkeyGym_Full:**
   ```
   C:\xampp\htdocs\MonkeyGym_Full\
   ```

3. **Cấu trúc thư mục cuối cùng:**
   ```
   htdocs/
   └── MonkeyGym_Full/
       ├── public/
       ├── includes/
       ├── config/
       ├── api/
       ├── admin/
       ├── staff/
       ├── trainer/
       ├── member/
       └── database/
   ```

---

## ⚙️ BƯỚC 4: CẤU HÌNH HỆ THỐNG

### File config/config.php đã cấu hình sẵn:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Mặc định XAMPP không có password
define('DB_NAME', 'gym_db');
```

### Nếu MySQL của bạn có password:

1. Mở file: `config/config.php`
2. Sửa dòng:
   ```php
   define('DB_PASS', 'your_password_here');
   ```
3. Save file

---

## 🎯 BƯỚC 5: CHẠY HỆ THỐNG

### Truy cập hệ thống:

1. **Trang đăng nhập:**
   ```
   http://localhost/MonkeyGym_Full/public/login.php
   ```

2. **Dashboard (sau khi đăng nhập):**
   ```
   http://localhost/MonkeyGym_Full/admin/dashboard.php
   ```

---

## 👤 TÀI KHOẢN DEMO

### Đăng nhập bằng các tài khoản sau:

| Vai trò | Username | Password | Quyền hạn |
|---------|----------|----------|-----------|
| **Quản trị viên** | `admin` | `password` | Toàn bộ hệ thống |
| **Nhân viên** | `nhanvien01` | `password` | Quản lý hội viên, gói tập |
| **HLV** | `hlv01` | `password` | Xem lịch dạy, học viên |
| **Hội viên** | `hoivien01` | `password` | Xem thông tin cá nhân |

### ⚠️ LƯU Ý: Đổi mật khẩu ngay sau lần đăng nhập đầu tiên!

---

## 🔍 KIỂM TRA LỖI

### Lỗi 1: "Connection failed"

**Nguyên nhân:** Không kết nối được MySQL

**Giải pháp:**
1. Kiểm tra MySQL đã chạy chưa (XAMPP Control Panel)
2. Kiểm tra username/password trong `config/config.php`
3. Thử kết nối bằng phpMyAdmin

### Lỗi 2: "Table doesn't exist"

**Nguyên nhân:** Chưa import database

**Giải pháp:**
1. Vào phpMyAdmin
2. Import lại file MonkeyGym.sql
3. Kiểm tra database `gym_db` có 26 bảng

### Lỗi 3: "404 Not Found"

**Nguyên nhân:** Sai đường dẫn

**Giải pháp:**
1. Kiểm tra thư mục đã copy đúng vào htdocs chưa
2. Đảm bảo URL: `http://localhost/MonkeyGym_Full/public/login.php`
3. Khởi động lại Apache

### Lỗi 4: "Deprecated" hoặc Warning PHP

**Nguyên nhân:** Phiên bản PHP không tương thích

**Giải pháp:**
1. Cài XAMPP với PHP 8.0+
2. Hoặc tắt hiển thị lỗi trong `config.php`:
   ```php
   error_reporting(0);
   ini_set('display_errors', 0);
   ```

---

## 📱 CHỨC NĂNG ĐÃ HOẠT ĐỘNG

### ✅ Đã hoàn thành:

1. **Đăng nhập/Đăng xuất**
   - Xác thực người dùng
   - Phân quyền theo vai trò
   - Session management

2. **Đăng ký hội viên (API)**
   - Tạo tài khoản tự động
   - Tạo mã QR
   - Gửi email thông báo
   - Đăng ký gói tập

3. **Dashboard**
   - Thống kê tổng quan
   - Biểu đồ doanh thu (Chart.js)
   - Hoạt động gần đây
   - Thông báo cảnh báo

4. **Quản lý hội viên**
   - Xem danh sách
   - Thêm/Sửa/Xóa
   - Tìm kiếm, lọc
   - Gia hạn gói tập

---

## 🚧 CHỨC NĂNG SẼ BỔ SUNG

### Trong bản cập nhật tiếp theo:

- [ ] Điểm danh QR Code (quét camera)
- [ ] Đặt lịch PT cho hội viên
- [ ] Quản lý kho hàng
- [ ] Báo cáo xuất Excel/PDF
- [ ] Thông báo real-time
- [ ] Mobile responsive hoàn chỉnh

---

## 📖 CẤU TRÚC HỆ THỐNG

### Thư mục và chức năng:

```
MonkeyGym_Full/
│
├── config/
│   └── config.php          # Cấu hình database, site
│
├── includes/
│   ├── Database.php        # Class kết nối database
│   └── helpers.php         # Functions tiện ích
│
├── public/
│   ├── login.php           # Trang đăng nhập
│   ├── css/                # CSS tùy chỉnh
│   ├── js/                 # JavaScript
│   └── uploads/            # Upload files, QR codes
│
├── api/
│   └── register-member.php # API đăng ký hội viên
│
├── admin/
│   └── dashboard.php       # Dashboard quản trị
│
├── staff/
│   └── dashboard.php       # Dashboard nhân viên
│
├── trainer/
│   └── dashboard.php       # Dashboard HLV
│
└── member/
    └── dashboard.php       # Dashboard hội viên
```

---

## 🎨 TÍNH NĂNG NỔI BẬT

### 1. Bảo mật:
- ✅ Password hashing (bcrypt)
- ✅ PDO Prepared Statements
- ✅ XSS Protection
- ✅ CSRF Token (sắp có)
- ✅ Session management

### 2. Hiệu năng:
- ✅ Database indexing
- ✅ Query optimization
- ✅ Lazy loading
- ✅ Caching (sắp có)

### 3. Trải nghiệm người dùng:
- ✅ Responsive design
- ✅ Loading spinner
- ✅ Toast notifications
- ✅ Form validation
- ✅ Real-time search

---

## 📞 HỖ TRỢ

### Gặp vấn đề?

1. **Đọc kỹ hướng dẫn trên**
2. **Kiểm tra lỗi:**
   - Xem file log: `C:\xampp\apache\logs\error.log`
   - Xem MySQL log: `C:\xampp\mysql\data\*.err`

3. **Các lỗi thường gặp đã được giải quyết ở phần "Kiểm tra lỗi"**

4. **Video hướng dẫn:** (Sắp có)

---

## 🎯 TEST HỆ THỐNG

### Checklist sau khi cài đặt:

- [ ] XAMPP đã chạy (Apache + MySQL)
- [ ] Database `gym_db` có 26 bảng
- [ ] Truy cập được http://localhost/MonkeyGym_Full/public/login.php
- [ ] Đăng nhập thành công với tài khoản admin
- [ ] Thấy dashboard với thống kê
- [ ] Xem được danh sách hội viên

### Nếu tất cả đều OK → Hệ thống đã sẵn sàng! 🎉

---

## 📈 ROADMAP

### Version 1.0 (Hiện tại)
- ✅ Đăng nhập/Phân quyền
- ✅ CRUD hội viên cơ bản
- ✅ Dashboard thống kê
- ✅ API đăng ký

### Version 2.0 (Tiếp theo)
- [ ] Điểm danh QR Code
- [ ] Quản lý lịch PT
- [ ] Thanh toán online
- [ ] Báo cáo nâng cao

### Version 3.0 (Tương lai)
- [ ] Mobile App
- [ ] AI recommendations
- [ ] Multi-branch support
- [ ] Cloud deployment

---

## 🏆 KẾT QUẢ MONG ĐỢI

Sau khi hoàn thành các bước trên, bạn sẽ có:

✅ Hệ thống gym quản lý hoạt động đầy đủ
✅ Database 26 bảng với dữ liệu mẫu
✅ Đăng nhập với 4 vai trò khác nhau
✅ Dashboard thống kê trực quan
✅ API hoạt động để mở rộng
✅ Nền tảng để phát triển thêm

---

**🐒 MONKEY GYM - LET'S BUILD SOMETHING AMAZING! 💪**

*Last updated: November 2024*
*Version: 1.0.0*
