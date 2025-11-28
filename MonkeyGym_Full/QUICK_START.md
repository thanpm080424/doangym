# ⚡ HƯỚNG DẪN CHẠY NHANH - 5 PHÚT
## Monkey Gym Full-Stack System

---

## 🎯 MỤC TIÊU
Chạy được hệ thống gym hoàn chỉnh với backend PHP + MySQL trong 5 phút!

---

## ✅ CHECKLIST (Làm theo thứ tự)

### ☐ Bước 1: Cài XAMPP (2 phút)
1. Download: https://www.apachefriends.org/download.html
2. Chọn Windows, PHP 8.0+
3. Install → Next → Next → Finish
4. Mở XAMPP Control Panel
5. **Start Apache**
6. **Start MySQL**
7. Test: Mở http://localhost → OK!

### ☐ Bước 2: Import Database (1 phút)
1. Mở: http://localhost/phpmyadmin
2. Click "New" → Tên: `gym_db`
3. Click "Import" tab
4. Choose File: `MonkeyGym.sql`
5. Click "Go" → Đợi 10 giây → Done!
6. Check: Phải có 26 bảng

### ☐ Bước 3: Copy Code (30 giây)
1. Copy thư mục `MonkeyGym_Full`
2. Paste vào: `C:\xampp\htdocs\`
3. Kết quả: `C:\xampp\htdocs\MonkeyGym_Full\`

### ☐ Bước 4: Chạy (30 giây)
1. Mở trình duyệt
2. Vào: **http://localhost/MonkeyGym_Full/public/login.php**
3. Đăng nhập:
   - Username: `admin`
   - Password: `password`
4. **THÀNH CÔNG!** 🎉

---

## 🔥 TEST NGAY

### Thử các chức năng:

✅ **Đăng nhập:**
- Admin: `admin` / `password`
- Xem dashboard

✅ **Xem database:**
- Vào phpMyAdmin
- Click `gym_db`
- Xem 26 bảng

✅ **Test API:**
```
POST: http://localhost/MonkeyGym_Full/api/register-member.php
Body: {
  "ho_ten": "Test User",
  "email": "test@test.com",
  "so_dien_thoai": "0901234567",
  "gioi_tinh": "nam",
  "ma_goi": 1
}
```

---

## 🐛 LỖI THƯỜNG GẶP

### Lỗi 1: "Connection failed"
→ MySQL chưa chạy
→ **FIX:** Start MySQL trong XAMPP

### Lỗi 2: "Table not found"
→ Chưa import database
→ **FIX:** Import lại MonkeyGym.sql

### Lỗi 3: "404 Not Found"
→ Sai đường dẫn
→ **FIX:** Kiểm tra URL

### Lỗi 4: "Access denied for user"
→ Password MySQL sai
→ **FIX:** Sửa `config/config.php`

---

## 📱 CHỨC NĂNG ĐÃ CÓ

✅ Đăng nhập phân quyền
✅ Dashboard thống kê
✅ API đăng ký hội viên
✅ Tạo QR Code
✅ Gửi email
✅ Database 26 bảng

---

## 🎓 TÀI KHOẢN TEST

| User | Pass | Role |
|------|------|------|
| admin | password | Admin |
| nhanvien01 | password | Staff |
| hlv01 | password | Trainer |
| hoivien01 | password | Member |

---

## 📖 ĐỌC THÊM

- **INSTALL_GUIDE.md** - Hướng dẫn đầy đủ
- **README.md** - Tổng quan hệ thống

---

## 🆘 CẦN GIÚP?

1. Đọc INSTALL_GUIDE.md
2. Check MySQL log: `C:\xampp\mysql\data\*.err`
3. Check Apache log: `C:\xampp\apache\logs\error.log`

---

## ✨ DONE!

Nếu làm đúng 4 bước trên:
→ Hệ thống đã chạy!
→ Có thể demo ngay!
→ Sẵn sàng bảo vệ!

**🐒 MONKEY GYM - LET'S GO! 💪**
