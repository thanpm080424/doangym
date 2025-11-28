-- ========================================
-- HE THONG QUAN LY PHONG GYM - PHIEN BAN CUOI CUNG
-- Version: 4.0 Final - 26 BANG
-- Day du nhung khong du thua
-- Dành cho: DO AN NAM 4
-- ========================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+07:00";

DROP DATABASE IF EXISTS `gym_db`;
CREATE DATABASE IF NOT EXISTS `gym_db` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `gym_db`;

-- ========================================
-- PHAN 1: CORE - NGUOI DUNG (3 BANG)
-- ========================================

-- Bang 1: NGUOI_DUNG
CREATE TABLE `nguoi_dung` (
  `ma_nguoi_dung` INT(11) NOT NULL AUTO_INCREMENT,
  `ten_dang_nhap` VARCHAR(50) NOT NULL,
  `mat_khau` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `so_dien_thoai` VARCHAR(15) DEFAULT NULL,
  `vai_tro` ENUM('quan_tri','nhan_vien','huan_luyen_vien','hoi_vien') NOT NULL DEFAULT 'hoi_vien',
  `ho_ten` VARCHAR(100) NOT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lan_dang_nhap_cuoi` DATETIME DEFAULT NULL,
  `trang_thai` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`ma_nguoi_dung`),
  UNIQUE KEY `ten_dang_nhap` (`ten_dang_nhap`),
  UNIQUE KEY `email` (`email`),
  INDEX `idx_vai_tro` (`vai_tro`),
  INDEX `idx_trang_thai` (`trang_thai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Bang nguoi dung - Tai khoan dang nhap cho tat ca user';

-- Bang 2: PHONG_BAN
CREATE TABLE `phong_ban` (
  `ma_phong_ban` INT(11) NOT NULL AUTO_INCREMENT,
  `ten_phong_ban` VARCHAR(100) NOT NULL,
  `mo_ta` TEXT DEFAULT NULL,
  `truong_phong` INT(11) DEFAULT NULL,
  `ngay_thanh_lap` DATE DEFAULT NULL,
  `trang_thai` TINYINT(1) DEFAULT 1,
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_phong_ban`),
  FOREIGN KEY (`truong_phong`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Phong ban: Le tan, Ke toan, Marketing, Ky thuat';

-- Bang 3: NHAN_VIEN (QUAN TRONG!)
CREATE TABLE `nhan_vien` (
  `ma_nhan_vien` INT(11) NOT NULL AUTO_INCREMENT,
  `ma_nguoi_dung` INT(11) NOT NULL,
  `ma_phong_ban` INT(11) DEFAULT NULL,
  `chuc_vu` VARCHAR(100) DEFAULT NULL,
  `ngay_vao_lam` DATE NOT NULL,
  `ngay_nghi_viec` DATE DEFAULT NULL,
  `luong_co_ban` DECIMAL(10,2) DEFAULT 0,
  `trang_thai_lam_viec` ENUM('dang_lam','nghi_viec','tam_nghi') DEFAULT 'dang_lam',
  `so_cmnd` VARCHAR(20) DEFAULT NULL,
  `ngay_sinh` DATE DEFAULT NULL,
  `gioi_tinh` ENUM('nam','nu','khac') DEFAULT NULL,
  `dia_chi` TEXT DEFAULT NULL,
  `sdt_khan_cap` VARCHAR(15) DEFAULT NULL,
  `ten_lien_he_khan_cap` VARCHAR(100) DEFAULT NULL,
  `trinh_do` VARCHAR(100) DEFAULT NULL,
  `kinh_nghiem_nam` INT(11) DEFAULT 0,
  `ghi_chu` TEXT DEFAULT NULL,
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_nhan_vien`),
  UNIQUE KEY `ma_nguoi_dung` (`ma_nguoi_dung`),
  FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE,
  FOREIGN KEY (`ma_phong_ban`) REFERENCES `phong_ban` (`ma_phong_ban`) ON DELETE SET NULL,
  INDEX `idx_trang_thai` (`trang_thai_lam_viec`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Quan ly nhan vien - Thieu sot nghiem trong neu khong co bang nay!';

-- ========================================
-- PHAN 2: CORE - HOI VIEN (1 BANG)
-- ========================================

-- Bang 4: HOI_VIEN
CREATE TABLE `hoi_vien` (
  `ma_hoi_vien` INT(11) NOT NULL AUTO_INCREMENT,
  `ma_nguoi_dung` INT(11) NOT NULL,
  `gioi_tinh` ENUM('nam','nu','khac') NOT NULL,
  `ngay_sinh` DATE DEFAULT NULL,
  `dia_chi` TEXT DEFAULT NULL,
  `sdt_khan_cap` VARCHAR(15) DEFAULT NULL,
  `ten_lien_he_khan_cap` VARCHAR(100) DEFAULT NULL,
  `ma_qr` VARCHAR(255) DEFAULT NULL,
  `ghi_chu_suc_khoe` TEXT DEFAULT NULL,
  `muc_tieu` TEXT DEFAULT NULL,
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_hoi_vien`),
  UNIQUE KEY `ma_nguoi_dung` (`ma_nguoi_dung`),
  UNIQUE KEY `ma_qr` (`ma_qr`),
  FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Thong tin chi tiet hoi vien';

-- ========================================
-- PHAN 3: CORE - GOI TAP & PT (4 BANG)
-- ========================================

-- Bang 5: GOI_TAP
CREATE TABLE `goi_tap` (
  `ma_goi` INT(11) NOT NULL AUTO_INCREMENT,
  `ten_goi` VARCHAR(100) NOT NULL,
  `thoi_han` INT(11) NOT NULL COMMENT 'So thang',
  `gia` DECIMAL(10,2) NOT NULL,
  `so_buoi_pt` INT(11) DEFAULT 0,
  `mo_ta` TEXT DEFAULT NULL,
  `trang_thai` TINYINT(1) DEFAULT 1,
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_goi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Cac goi tap: 1, 3, 6, 12 thang';

-- Bang 6: GOI_PT
CREATE TABLE `goi_pt` (
  `ma_goi_pt` INT(11) NOT NULL AUTO_INCREMENT,
  `ten_goi` VARCHAR(100) NOT NULL,
  `so_buoi` INT(11) NOT NULL,
  `gia` DECIMAL(10,2) NOT NULL,
  `thoi_han` INT(11) NOT NULL COMMENT 'So thang',
  `mo_ta` TEXT DEFAULT NULL,
  `trang_thai` TINYINT(1) DEFAULT 1,
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_goi_pt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Goi tap PT 1-1 voi HLV';

-- Bang 7: DANG_KY_GOI
CREATE TABLE `dang_ky_goi` (
  `ma_dang_ky` INT(11) NOT NULL AUTO_INCREMENT,
  `ma_hoi_vien` INT(11) NOT NULL,
  `ma_goi` INT(11) NOT NULL,
  `ma_hlv` INT(11) DEFAULT NULL,
  `ma_khuyen_mai` INT(11) DEFAULT NULL,
  `ngay_bat_dau` DATE NOT NULL,
  `ngay_ket_thuc` DATE NOT NULL,
  `ngay_canh_bao` DATE DEFAULT NULL COMMENT 'Ngay canh bao truoc khi het han (7 ngay)',
  `trang_thai` ENUM('cho_thanh_toan','dang_hoat_dong','sap_het_han','het_han','huy') NOT NULL DEFAULT 'cho_thanh_toan',
  `so_buoi_pt_con_lai` INT(11) DEFAULT 0,
  `gia_goc` DECIMAL(10,2) NOT NULL,
  `giam_gia` DECIMAL(10,2) DEFAULT 0,
  `gia_thanh_toan` DECIMAL(10,2) NOT NULL,
  `ghi_chu` TEXT DEFAULT NULL,
  `nguoi_dang_ky` INT(11) DEFAULT NULL COMMENT 'Nhan vien dang ky cho hoi vien',
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ngay_cap_nhat` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_dang_ky`),
  FOREIGN KEY (`ma_hoi_vien`) REFERENCES `hoi_vien` (`ma_hoi_vien`) ON DELETE CASCADE,
  FOREIGN KEY (`ma_goi`) REFERENCES `goi_tap` (`ma_goi`) ON DELETE RESTRICT,
  FOREIGN KEY (`ma_hlv`) REFERENCES `huan_luyen_vien` (`ma_hlv`) ON DELETE SET NULL,
  FOREIGN KEY (`ma_khuyen_mai`) REFERENCES `khuyen_mai` (`ma_khuyen_mai`) ON DELETE SET NULL,
  FOREIGN KEY (`nguoi_dang_ky`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE SET NULL,
  INDEX `idx_trang_thai` (`trang_thai`),
  INDEX `idx_ngay_ket_thuc` (`ngay_ket_thuc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Dang ky goi tap cua hoi vien';

-- Bang 8: DANG_KY_GOI_PT
CREATE TABLE `dang_ky_goi_pt` (
  `ma_dang_ky_pt` INT(11) NOT NULL AUTO_INCREMENT,
  `ma_hoi_vien` INT(11) NOT NULL,
  `ma_goi_pt` INT(11) NOT NULL,
  `ma_hlv` INT(11) NOT NULL,
  `ngay_bat_dau` DATE NOT NULL,
  `ngay_ket_thuc` DATE NOT NULL,
  `so_buoi_con_lai` INT(11) NOT NULL,
  `trang_thai` ENUM('cho_thanh_toan','dang_hoat_dong','sap_het_han','het_han','huy') NOT NULL DEFAULT 'cho_thanh_toan',
  `gia_thanh_toan` DECIMAL(10,2) NOT NULL,
  `ghi_chu` TEXT DEFAULT NULL,
  `nguoi_dang_ky` INT(11) DEFAULT NULL,
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_dang_ky_pt`),
  FOREIGN KEY (`ma_hoi_vien`) REFERENCES `hoi_vien` (`ma_hoi_vien`) ON DELETE CASCADE,
  FOREIGN KEY (`ma_goi_pt`) REFERENCES `goi_pt` (`ma_goi_pt`) ON DELETE RESTRICT,
  FOREIGN KEY (`ma_hlv`) REFERENCES `huan_luyen_vien` (`ma_hlv`) ON DELETE RESTRICT,
  FOREIGN KEY (`nguoi_dang_ky`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE SET NULL,
  INDEX `idx_trang_thai` (`trang_thai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Dang ky goi PT rieng';

-- ========================================
-- PHAN 4: CORE - HUAN LUYEN VIEN (3 BANG)
-- ========================================

-- Bang 9: HUAN_LUYEN_VIEN
CREATE TABLE `huan_luyen_vien` (
  `ma_hlv` INT(11) NOT NULL AUTO_INCREMENT,
  `ma_nguoi_dung` INT(11) NOT NULL,
  `chuyen_mon` VARCHAR(200) DEFAULT NULL,
  `kinh_nghiem` INT(11) DEFAULT NULL,
  `chung_chi` TEXT DEFAULT NULL,
  `gia_thue` DECIMAL(10,2) DEFAULT NULL,
  `danh_gia` DECIMAL(3,2) DEFAULT 0.00,
  `so_luong_danh_gia` INT(11) DEFAULT 0,
  `trang_thai` TINYINT(1) DEFAULT 1,
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_hlv`),
  UNIQUE KEY `ma_nguoi_dung` (`ma_nguoi_dung`),
  FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Thong tin chi tiet HLV (Personal Trainer)';

-- Bang 10: LICH_LAM_VIEC_HLV
CREATE TABLE `lich_lam_viec_hlv` (
  `ma_lich_lam_viec` INT(11) NOT NULL AUTO_INCREMENT,
  `ma_hlv` INT(11) NOT NULL,
  `thu` ENUM('2','3','4','5','6','7','CN') NOT NULL,
  `gio_bat_dau` TIME NOT NULL,
  `gio_ket_thuc` TIME NOT NULL,
  `trang_thai` TINYINT(1) DEFAULT 1,
  `ghi_chu` TEXT DEFAULT NULL,
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_lich_lam_viec`),
  FOREIGN KEY (`ma_hlv`) REFERENCES `huan_luyen_vien` (`ma_hlv`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Lich lam viec cua HLV trong tuan';

-- Bang 11: LICH_TAP
CREATE TABLE `lich_tap` (
  `ma_lich` INT(11) NOT NULL AUTO_INCREMENT,
  `ma_hoi_vien` INT(11) NOT NULL,
  `ma_hlv` INT(11) NOT NULL,
  `ma_dang_ky_pt` INT(11) DEFAULT NULL,
  `ngay_tap` DATE NOT NULL,
  `gio_bat_dau` TIME NOT NULL,
  `gio_ket_thuc` TIME NOT NULL,
  `trang_thai` ENUM('da_dat','xac_nhan','hoan_thanh','huy','vang') NOT NULL DEFAULT 'da_dat',
  `danh_gia` INT(11) DEFAULT NULL CHECK (`danh_gia` BETWEEN 1 AND 5),
  `nhan_xet` TEXT DEFAULT NULL,
  `ghi_chu` TEXT DEFAULT NULL,
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_lich`),
  FOREIGN KEY (`ma_hoi_vien`) REFERENCES `hoi_vien` (`ma_hoi_vien`) ON DELETE CASCADE,
  FOREIGN KEY (`ma_hlv`) REFERENCES `huan_luyen_vien` (`ma_hlv`) ON DELETE CASCADE,
  FOREIGN KEY (`ma_dang_ky_pt`) REFERENCES `dang_ky_goi_pt` (`ma_dang_ky_pt`) ON DELETE SET NULL,
  INDEX `idx_ngay_tap` (`ngay_tap`),
  INDEX `idx_trang_thai` (`trang_thai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Lich tap PT 1-1';

-- ========================================
-- PHAN 5: CORE - LOP HOC NHOM (2 BANG)
-- ========================================

-- Bang 12: LOP_HOC_NHOM
CREATE TABLE `lop_hoc_nhom` (
  `ma_lop` INT(11) NOT NULL AUTO_INCREMENT,
  `ten_lop` VARCHAR(100) NOT NULL,
  `loai_lop` VARCHAR(50) DEFAULT NULL COMMENT 'Yoga, Zumba, Boxing, Aerobic',
  `ma_hlv` INT(11) NOT NULL,
  `thu` ENUM('2','3','4','5','6','7','CN') NOT NULL,
  `gio_bat_dau` TIME NOT NULL,
  `gio_ket_thuc` TIME NOT NULL,
  `so_luong_toi_da` INT(11) DEFAULT 20,
  `mo_ta` TEXT DEFAULT NULL,
  `trang_thai` TINYINT(1) DEFAULT 1,
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_lop`),
  FOREIGN KEY (`ma_hlv`) REFERENCES `huan_luyen_vien` (`ma_hlv`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Lop hoc nhom: Yoga, Zumba, Boxing...';

-- Bang 13: DANG_KY_LOP
CREATE TABLE `dang_ky_lop` (
  `ma_dang_ky_lop` INT(11) NOT NULL AUTO_INCREMENT,
  `ma_hoi_vien` INT(11) NOT NULL,
  `ma_lop` INT(11) NOT NULL,
  `ngay_dang_ky` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `trang_thai` ENUM('dang_hoc','nghi','hoan_thanh') DEFAULT 'dang_hoc',
  `ghi_chu` TEXT DEFAULT NULL,
  PRIMARY KEY (`ma_dang_ky_lop`),
  FOREIGN KEY (`ma_hoi_vien`) REFERENCES `hoi_vien` (`ma_hoi_vien`) ON DELETE CASCADE,
  FOREIGN KEY (`ma_lop`) REFERENCES `lop_hoc_nhom` (`ma_lop`) ON DELETE CASCADE,
  UNIQUE KEY `unique_dang_ky` (`ma_hoi_vien`, `ma_lop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Dang ky tham gia lop hoc nhom';

-- ========================================
-- PHAN 6: CORE - DIEM DANH (2 BANG)
-- ========================================

-- Bang 14: DIEM_DANH (DA SUA - BO gio_ra)
CREATE TABLE `diem_danh` (
  `ma_diem_danh` INT(11) NOT NULL AUTO_INCREMENT,
  `ma_hoi_vien` INT(11) NOT NULL,
  `ngay` DATE NOT NULL,
  `gio_diem_danh` TIME NOT NULL COMMENT 'CHI QUET 1 LAN KHI VAO - Khong can gio ra!',
  `phuong_thuc` ENUM('qr_code','the_tu','nhan_vien') DEFAULT 'qr_code',
  `ghi_chu` TEXT DEFAULT NULL,
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_diem_danh`),
  FOREIGN KEY (`ma_hoi_vien`) REFERENCES `hoi_vien` (`ma_hoi_vien`) ON DELETE CASCADE,
  UNIQUE KEY `unique_diem_danh` (`ma_hoi_vien`, `ngay`),
  INDEX `idx_ngay` (`ngay`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Diem danh hoi vien - Chi can gio vao, KHONG CAN gio ra (gym khac voi cong ty!)';

-- Bang 15: CHAM_CONG_NHAN_VIEN
CREATE TABLE `cham_cong_nhan_vien` (
  `ma_cham_cong` INT(11) NOT NULL AUTO_INCREMENT,
  `ma_nhan_vien` INT(11) NOT NULL,
  `ngay` DATE NOT NULL,
  `gio_vao` TIME DEFAULT NULL,
  `gio_ra` TIME DEFAULT NULL,
  `tong_gio_lam` DECIMAL(4,2) DEFAULT NULL,
  `ghi_chu` TEXT DEFAULT NULL,
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_cham_cong`),
  FOREIGN KEY (`ma_nhan_vien`) REFERENCES `nhan_vien` (`ma_nhan_vien`) ON DELETE CASCADE,
  UNIQUE KEY `unique_cham_cong` (`ma_nhan_vien`, `ngay`),
  INDEX `idx_ngay` (`ngay`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Cham cong nhan vien - CAN co gio vao/ra de tinh luong';

-- ========================================
-- PHAN 7: NHAN SU - CA LAM VIEC (2 BANG)
-- ========================================

-- Bang 16: CA_LAM_VIEC
CREATE TABLE `ca_lam_viec` (
  `ma_ca` INT(11) NOT NULL AUTO_INCREMENT,
  `ten_ca` VARCHAR(50) NOT NULL,
  `gio_bat_dau` TIME NOT NULL,
  `gio_ket_thuc` TIME NOT NULL,
  `mo_ta` TEXT DEFAULT NULL,
  `trang_thai` TINYINT(1) DEFAULT 1,
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_ca`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Cac ca lam viec: Ca sang, Ca chieu, Ca toi';

-- Bang 17: PHAN_CA_NHAN_VIEN
CREATE TABLE `phan_ca_nhan_vien` (
  `ma_phan_ca` INT(11) NOT NULL AUTO_INCREMENT,
  `ma_nhan_vien` INT(11) NOT NULL,
  `ma_ca` INT(11) NOT NULL,
  `ngay` DATE NOT NULL,
  `trang_thai` ENUM('da_xep','vang','hoan_thanh') DEFAULT 'da_xep',
  `ghi_chu` TEXT DEFAULT NULL,
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_phan_ca`),
  FOREIGN KEY (`ma_nhan_vien`) REFERENCES `nhan_vien` (`ma_nhan_vien`) ON DELETE CASCADE,
  FOREIGN KEY (`ma_ca`) REFERENCES `ca_lam_viec` (`ma_ca`) ON DELETE CASCADE,
  UNIQUE KEY `unique_phan_ca` (`ma_nhan_vien`, `ngay`, `ma_ca`),
  INDEX `idx_ngay` (`ngay`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Phan ca lam viec cho nhan vien';

-- ========================================
-- PHAN 8: TAI CHINH (3 BANG)
-- ========================================

-- Bang 18: THANH_TOAN
CREATE TABLE `thanh_toan` (
  `ma_thanh_toan` INT(11) NOT NULL AUTO_INCREMENT,
  `ma_dang_ky` INT(11) DEFAULT NULL,
  `ma_dang_ky_pt` INT(11) DEFAULT NULL,
  `ma_lich` INT(11) DEFAULT NULL COMMENT 'Thanh toan buoi PT rieng le',
  `loai_thanh_toan` ENUM('goi_tap','goi_pt','san_pham','khac') NOT NULL,
  `so_tien` DECIMAL(10,2) NOT NULL,
  `phuong_thuc` ENUM('tien_mat','chuyen_khoan','the') NOT NULL DEFAULT 'tien_mat',
  `ma_giao_dich` VARCHAR(100) DEFAULT NULL,
  `ngay_thanh_toan` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `trang_thai` ENUM('cho_xu_ly','thanh_cong','that_bai','hoan_tien') NOT NULL DEFAULT 'thanh_cong',
  `nguoi_thu` INT(11) DEFAULT NULL,
  `ghi_chu` TEXT DEFAULT NULL,
  PRIMARY KEY (`ma_thanh_toan`),
  FOREIGN KEY (`ma_dang_ky`) REFERENCES `dang_ky_goi` (`ma_dang_ky`) ON DELETE SET NULL,
  FOREIGN KEY (`ma_dang_ky_pt`) REFERENCES `dang_ky_goi_pt` (`ma_dang_ky_pt`) ON DELETE SET NULL,
  FOREIGN KEY (`ma_lich`) REFERENCES `lich_tap` (`ma_lich`) ON DELETE SET NULL,
  FOREIGN KEY (`nguoi_thu`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE SET NULL,
  INDEX `idx_ngay_thanh_toan` (`ngay_thanh_toan`),
  INDEX `idx_trang_thai` (`trang_thai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Quan ly thanh toan - Thu tien';

-- Bang 19: CHI_PHI
CREATE TABLE `chi_phi` (
  `ma_chi_phi` INT(11) NOT NULL AUTO_INCREMENT,
  `loai_chi_phi` ENUM('tien_dien','tien_nuoc','tien_thue','luong','bao_tri','marketing','khac') NOT NULL,
  `mo_ta` TEXT DEFAULT NULL,
  `so_tien` DECIMAL(10,2) NOT NULL,
  `ngay_chi` DATE NOT NULL,
  `nguoi_duyet` INT(11) DEFAULT NULL,
  `trang_thai` ENUM('cho_duyet','da_duyet','tu_choi') DEFAULT 'cho_duyet',
  `chung_tu` VARCHAR(255) DEFAULT NULL,
  `ghi_chu` TEXT DEFAULT NULL,
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_chi_phi`),
  FOREIGN KEY (`nguoi_duyet`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE SET NULL,
  INDEX `idx_ngay_chi` (`ngay_chi`),
  INDEX `idx_loai` (`loai_chi_phi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Quan ly chi phi van hanh (dien, nuoc, thue, bao tri, marketing...)';

-- Bang 20: LUONG_NHAN_VIEN
CREATE TABLE `luong_nhan_vien` (
  `ma_bang_luong` INT(11) NOT NULL AUTO_INCREMENT,
  `ma_nhan_vien` INT(11) NOT NULL,
  `thang` INT(11) NOT NULL,
  `nam` INT(11) NOT NULL,
  `luong_co_ban` DECIMAL(10,2) NOT NULL,
  `thuong` DECIMAL(10,2) DEFAULT 0,
  `phat` DECIMAL(10,2) DEFAULT 0,
  `bao_hiem` DECIMAL(10,2) DEFAULT 0,
  `thuc_nhan` DECIMAL(10,2) NOT NULL COMMENT 'luong_co_ban + thuong - phat - bao_hiem',
  `ngay_thanh_toan` DATE DEFAULT NULL,
  `trang_thai` ENUM('chua_thanh_toan','da_thanh_toan') DEFAULT 'chua_thanh_toan',
  `ghi_chu` TEXT DEFAULT NULL,
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_bang_luong`),
  FOREIGN KEY (`ma_nhan_vien`) REFERENCES `nhan_vien` (`ma_nhan_vien`) ON DELETE CASCADE,
  UNIQUE KEY `unique_luong` (`ma_nhan_vien`, `thang`, `nam`),
  INDEX `idx_thang_nam` (`thang`, `nam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Bang luong nhan vien theo thang';

-- ========================================
-- PHAN 9: MARKETING (2 BANG)
-- ========================================

-- Bang 21: KHUYEN_MAI
CREATE TABLE `khuyen_mai` (
  `ma_khuyen_mai` INT(11) NOT NULL AUTO_INCREMENT,
  `ten_khuyen_mai` VARCHAR(200) NOT NULL,
  `ma_code` VARCHAR(50) UNIQUE DEFAULT NULL,
  `loai_khuyen_mai` ENUM('giam_tien','giam_phan_tram','tang_buoi_pt','tang_thoi_gian') NOT NULL,
  `gia_tri` DECIMAL(10,2) NOT NULL,
  `ngay_bat_dau` DATE NOT NULL,
  `ngay_ket_thuc` DATE NOT NULL,
  `so_luong_gioi_han` INT(11) DEFAULT NULL,
  `so_luong_da_dung` INT(11) DEFAULT 0,
  `goi_ap_dung` VARCHAR(255) DEFAULT NULL COMMENT 'JSON: [1,2,3]',
  `dieu_kien_ap_dung` TEXT DEFAULT NULL,
  `mo_ta` TEXT DEFAULT NULL,
  `trang_thai` TINYINT(1) DEFAULT 1,
  `nguoi_tao` INT(11) DEFAULT NULL,
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_khuyen_mai`),
  FOREIGN KEY (`nguoi_tao`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE SET NULL,
  INDEX `idx_ma_code` (`ma_code`),
  INDEX `idx_ngay` (`ngay_bat_dau`, `ngay_ket_thuc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Chuong trinh khuyen mai';

-- Bang 22: TIN_TUC
CREATE TABLE `tin_tuc` (
  `ma_tin` INT(11) NOT NULL AUTO_INCREMENT,
  `tieu_de` VARCHAR(255) NOT NULL,
  `noi_dung` TEXT NOT NULL,
  `hinh_anh` VARCHAR(500) DEFAULT NULL,
  `loai_tin` ENUM('thong_bao','tin_tuc','su_kien','khuyen_mai') NOT NULL DEFAULT 'tin_tuc',
  `doi_tuong` ENUM('tat_ca','hoi_vien','hlv','nhan_vien') NOT NULL DEFAULT 'tat_ca',
  `uu_tien` TINYINT(1) DEFAULT 0 COMMENT 'Ghim tin',
  `ngay_xuat_ban` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ngay_het_han` DATETIME DEFAULT NULL,
  `luot_xem` INT(11) DEFAULT 0,
  `trang_thai` TINYINT(1) DEFAULT 1,
  `nguoi_tao` INT(11) DEFAULT NULL,
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_tin`),
  FOREIGN KEY (`nguoi_tao`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE SET NULL,
  INDEX `idx_loai_tin` (`loai_tin`),
  INDEX `idx_doi_tuong` (`doi_tuong`),
  INDEX `idx_ngay_xuat_ban` (`ngay_xuat_ban`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tin tuc, thong bao, su kien';

-- ========================================
-- PHAN 10: KHO & THIET BI (4 BANG)
-- ========================================

-- Bang 23: THIET_BI (DA GOP - Co field lich_su_bao_tri JSON)
CREATE TABLE `thiet_bi` (
  `ma_thiet_bi` INT(11) NOT NULL AUTO_INCREMENT,
  `ten_thiet_bi` VARCHAR(200) NOT NULL,
  `loai_thiet_bi` VARCHAR(100) DEFAULT NULL COMMENT 'May chay, Xe dap, Ta don...',
  `ma_seri` VARCHAR(100) UNIQUE DEFAULT NULL,
  `ngay_mua` DATE DEFAULT NULL,
  `gia_mua` DECIMAL(10,2) DEFAULT NULL,
  `vi_tri` VARCHAR(100) DEFAULT NULL COMMENT 'Tang 1, Tang 2, Khu Cardio...',
  `trang_thai` ENUM('hoat_dong','bao_tri','hong','thanh_ly') DEFAULT 'hoat_dong',
  `chu_ky_bao_tri` INT(11) DEFAULT NULL COMMENT 'So ngay',
  `ngay_bao_tri_cuoi` DATE DEFAULT NULL,
  `lich_su_bao_tri` TEXT DEFAULT NULL COMMENT 'JSON: [{ngay:"2024-01-15", chi_phi:500000, mo_ta:"Thay dau may", nguoi_thuc_hien:"Nguyen Van A"}]',
  `ghi_chu` TEXT DEFAULT NULL,
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_thiet_bi`),
  INDEX `idx_trang_thai` (`trang_thai`),
  INDEX `idx_loai` (`loai_thiet_bi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Quan ly thiet bi gym - Lich su bao tri luu trong field JSON';

-- Bang 24: KHO
CREATE TABLE `kho` (
  `ma_hang` INT(11) NOT NULL AUTO_INCREMENT,
  `ten_hang` VARCHAR(200) NOT NULL,
  `loai_hang` ENUM('nuoc_uong','khan','do_bo_sung','thiet_bi_nho','khac') NOT NULL,
  `don_vi_tinh` VARCHAR(20) NOT NULL,
  `gia_nhap` DECIMAL(10,2) NOT NULL,
  `gia_ban` DECIMAL(10,2) NOT NULL,
  `so_luong_ton` INT(11) DEFAULT 0,
  `nguong_canh_bao` INT(11) DEFAULT 10,
  `ghi_chu` TEXT DEFAULT NULL,
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_hang`),
  INDEX `idx_loai_hang` (`loai_hang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Danh muc hang ton kho: Nuoc, khan, whey protein...';

-- Bang 25: NHAP_KHO (DA SUA - nha_cung_cap la TEXT)
CREATE TABLE `nhap_kho` (
  `ma_phieu_nhap` INT(11) NOT NULL AUTO_INCREMENT,
  `ma_hang` INT(11) NOT NULL,
  `nha_cung_cap` VARCHAR(200) DEFAULT NULL COMMENT 'Ten NCC - luu TEXT thay vi FK',
  `so_luong` INT(11) NOT NULL,
  `don_gia` DECIMAL(10,2) NOT NULL,
  `thanh_tien` DECIMAL(10,2) NOT NULL,
  `ngay_nhap` DATE NOT NULL,
  `nguoi_nhap` INT(11) DEFAULT NULL,
  `ghi_chu` TEXT DEFAULT NULL,
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_phieu_nhap`),
  FOREIGN KEY (`ma_hang`) REFERENCES `kho` (`ma_hang`) ON DELETE CASCADE,
  FOREIGN KEY (`nguoi_nhap`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE SET NULL,
  INDEX `idx_ngay_nhap` (`ngay_nhap`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Phieu nhap kho - NCC luu text de don gian';

-- Bang 26: XUAT_KHO
CREATE TABLE `xuat_kho` (
  `ma_phieu_xuat` INT(11) NOT NULL AUTO_INCREMENT,
  `ma_hang` INT(11) NOT NULL,
  `ma_hoi_vien` INT(11) DEFAULT NULL COMMENT 'Ban cho hoi vien',
  `so_luong` INT(11) NOT NULL,
  `don_gia` DECIMAL(10,2) NOT NULL,
  `thanh_tien` DECIMAL(10,2) NOT NULL,
  `ngay_xuat` DATE NOT NULL,
  `nguoi_xuat` INT(11) DEFAULT NULL,
  `ghi_chu` TEXT DEFAULT NULL,
  `ngay_tao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_phieu_xuat`),
  FOREIGN KEY (`ma_hang`) REFERENCES `kho` (`ma_hang`) ON DELETE CASCADE,
  FOREIGN KEY (`ma_hoi_vien`) REFERENCES `hoi_vien` (`ma_hoi_vien`) ON DELETE SET NULL,
  FOREIGN KEY (`nguoi_xuat`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE SET NULL,
  INDEX `idx_ngay_xuat` (`ngay_xuat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Phieu xuat kho - Ban hang cho hoi vien';

-- ========================================
-- TRIGGERS VA VIEWS
-- ========================================

DELIMITER //

-- Trigger 1: Cap nhat trang thai goi tap
CREATE TRIGGER trg_cap_nhat_trang_thai_goi
BEFORE UPDATE ON dang_ky_goi
FOR EACH ROW
BEGIN
    DECLARE ngay_hien_tai DATE;
    SET ngay_hien_tai = CURDATE();
    
    IF NEW.ngay_ket_thuc < ngay_hien_tai AND NEW.trang_thai = 'dang_hoat_dong' THEN
        SET NEW.trang_thai = 'het_han';
    END IF;
    
    IF DATEDIFF(NEW.ngay_ket_thuc, ngay_hien_tai) <= 7 
       AND DATEDIFF(NEW.ngay_ket_thuc, ngay_hien_tai) > 0 
       AND NEW.trang_thai = 'dang_hoat_dong' THEN
        SET NEW.trang_thai = 'sap_het_han';
        SET NEW.ngay_canh_bao = ngay_hien_tai;
    END IF;
END//

-- Trigger 2: Cap nhat so luong khuyen mai
CREATE TRIGGER trg_cap_nhat_khuyen_mai
AFTER INSERT ON dang_ky_goi
FOR EACH ROW
BEGIN
    IF NEW.ma_khuyen_mai IS NOT NULL THEN
        UPDATE khuyen_mai 
        SET so_luong_da_dung = so_luong_da_dung + 1
        WHERE ma_khuyen_mai = NEW.ma_khuyen_mai;
    END IF;
END//

-- Trigger 3: Cap nhat ton kho khi nhap
CREATE TRIGGER trg_nhap_kho
AFTER INSERT ON nhap_kho
FOR EACH ROW
BEGIN
    UPDATE kho 
    SET so_luong_ton = so_luong_ton + NEW.so_luong
    WHERE ma_hang = NEW.ma_hang;
END//

-- Trigger 4: Cap nhat ton kho khi xuat
CREATE TRIGGER trg_xuat_kho
AFTER INSERT ON xuat_kho
FOR EACH ROW
BEGIN
    UPDATE kho 
    SET so_luong_ton = so_luong_ton - NEW.so_luong
    WHERE ma_hang = NEW.ma_hang;
END//

DELIMITER ;

-- ========================================
-- VIEWS
-- ========================================

-- View 1: Hoi vien sap het han
CREATE OR REPLACE VIEW v_hoi_vien_sap_het_han AS
SELECT 
    hv.ma_hoi_vien,
    nd.ho_ten,
    nd.email,
    nd.so_dien_thoai,
    gt.ten_goi,
    dk.ngay_bat_dau,
    dk.ngay_ket_thuc,
    DATEDIFF(dk.ngay_ket_thuc, CURDATE()) as so_ngay_con_lai,
    dk.trang_thai
FROM dang_ky_goi dk
JOIN hoi_vien hv ON dk.ma_hoi_vien = hv.ma_hoi_vien
JOIN nguoi_dung nd ON hv.ma_nguoi_dung = nd.ma_nguoi_dung
JOIN goi_tap gt ON dk.ma_goi = gt.ma_goi
WHERE dk.trang_thai IN ('dang_hoat_dong', 'sap_het_han')
  AND DATEDIFF(dk.ngay_ket_thuc, CURDATE()) BETWEEN 0 AND 7
ORDER BY dk.ngay_ket_thuc ASC;

-- View 2: Thong ke khuyen mai
CREATE OR REPLACE VIEW v_thong_ke_khuyen_mai AS
SELECT 
    km.ma_khuyen_mai,
    km.ten_khuyen_mai,
    km.ma_code,
    km.loai_khuyen_mai,
    km.gia_tri,
    km.so_luong_gioi_han,
    km.so_luong_da_dung,
    COUNT(DISTINCT dk.ma_dang_ky) as so_don_hang,
    SUM(dk.giam_gia) as tong_giam_gia,
    COUNT(DISTINCT dk.ma_hoi_vien) as so_hoi_vien_su_dung
FROM khuyen_mai km
LEFT JOIN dang_ky_goi dk ON km.ma_khuyen_mai = dk.ma_khuyen_mai
GROUP BY km.ma_khuyen_mai;

-- View 3: Hang ton kho can canh bao
CREATE OR REPLACE VIEW v_canh_bao_ton_kho AS
SELECT 
    ma_hang,
    ten_hang,
    loai_hang,
    so_luong_ton,
    nguong_canh_bao,
    (nguong_canh_bao - so_luong_ton) as can_nhap_them
FROM kho
WHERE so_luong_ton <= nguong_canh_bao
ORDER BY can_nhap_them DESC;

-- ========================================
-- DU LIEU MAU
-- ========================================

-- Insert mau nguoi_dung
INSERT INTO nguoi_dung (ten_dang_nhap, mat_khau, email, ho_ten, vai_tro) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@gym.vn', 'Quan Tri Vien', 'quan_tri'),
('nhanvien01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'nv01@gym.vn', 'Nguyen Van Nhan Vien', 'nhan_vien'),
('hlv01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hlv01@gym.vn', 'Tran Thi HLV', 'huan_luyen_vien'),
('hoivien01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hv01@gym.vn', 'Le Van Hoi Vien', 'hoi_vien');

-- Insert mau phong_ban
INSERT INTO phong_ban (ten_phong_ban, mo_ta, ngay_thanh_lap) VALUES
('Phong Le Tan', 'Tiep tan khach hang, dang ky hoi vien', '2020-01-01'),
('Phong Ke Toan', 'Quan ly tai chinh, luong', '2020-01-01'),
('Phong Marketing', 'Khuyen mai, quang cao', '2020-06-01'),
('Phong Ky Thuat', 'Bao tri thiet bi', '2020-01-01');

-- Insert mau nhan_vien
INSERT INTO nhan_vien (ma_nguoi_dung, ma_phong_ban, chuc_vu, ngay_vao_lam, luong_co_ban, ngay_sinh, gioi_tinh) VALUES
(2, 1, 'Nhan Vien Le Tan', '2023-01-15', 8000000, '1995-03-20', 'nu');

-- Insert mau ca_lam_viec
INSERT INTO ca_lam_viec (ten_ca, gio_bat_dau, gio_ket_thuc, mo_ta) VALUES
('Ca Sang', '06:00:00', '14:00:00', 'Ca sang 6h - 14h'),
('Ca Chieu', '14:00:00', '22:00:00', 'Ca chieu 14h - 22h'),
('Ca Toi', '18:00:00', '02:00:00', 'Ca toi 18h - 2h sang (gym 24/7)');

-- Insert mau goi_tap
INSERT INTO goi_tap (ten_goi, thoi_han, gia, mo_ta) VALUES
('Goi 1 thang', 1, 500000, 'Goi tap co ban 1 thang'),
('Goi 3 thang', 3, 1350000, 'Goi tap tiet kiem 3 thang'),
('Goi 6 thang', 6, 2400000, 'Goi tap pho bien 6 thang'),
('Goi 12 thang', 12, 4200000, 'Goi tap uu dai nhat 12 thang');

-- Insert mau kho
INSERT INTO kho (ten_hang, loai_hang, don_vi_tinh, gia_nhap, gia_ban, so_luong_ton, nguong_canh_bao) VALUES
('Nuoc Aquafina 500ml', 'nuoc_uong', 'chai', 5000, 10000, 100, 20),
('Nuoc Revive 500ml', 'nuoc_uong', 'chai', 6000, 12000, 50, 15),
('Khan lau mat', 'khan', 'cai', 15000, 30000, 200, 30),
('Whey Protein', 'do_bo_sung', 'hop', 800000, 1200000, 10, 5);

-- Insert mau thiet_bi
INSERT INTO thiet_bi (ten_thiet_bi, loai_thiet_bi, ngay_mua, gia_mua, vi_tri, chu_ky_bao_tri) VALUES
('May chay bo Technogym Run 700', 'May chay bo', '2023-01-10', 50000000, 'Tang 1 - Khu Cardio', 30),
('Xe dap tap Technogym Bike', 'Xe dap tap', '2023-01-10', 30000000, 'Tang 1 - Khu Cardio', 60),
('Bo ta don 1-50kg', 'Ta don', '2023-01-15', 20000000, 'Tang 2 - Khu Free Weight', 90);

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

-- ========================================
-- THONG BAO HOAN THANH
-- ========================================

SELECT 
    'Database gym_db da duoc tao thanh cong!' as ThongBao,
    '26 bang' as TongSoBang,
    'Day du nhung khong du thua' as DacDiem,
    'Xem chi tiet trong file: DATABASE_DAY_DU_KHONG_DU_THUA.md' as TaiLieu;

-- Kiem tra so bang
SELECT COUNT(*) as tong_bang 
FROM information_schema.tables 
WHERE table_schema = 'gym_db';
