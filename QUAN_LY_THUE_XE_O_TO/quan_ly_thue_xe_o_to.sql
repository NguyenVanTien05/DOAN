DROP DATABASE IF EXISTS quan_ly_thue_xe_o_to;
CREATE DATABASE quan_ly_thue_xe_o_to CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quan_ly_thue_xe_o_to;

CREATE TABLE nguoi_dung (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ho_ten VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mat_khau VARCHAR(255) NOT NULL,
    so_dien_thoai VARCHAR(20) DEFAULT NULL,
    dia_chi VARCHAR(255) DEFAULT NULL,
    cccd VARCHAR(20) DEFAULT NULL,
    vai_tro ENUM('admin', 'nguoi_cho_thue', 'khach_hang') NOT NULL DEFAULT 'khach_hang',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE chi_nhanh (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nguoi_cho_thue_id INT NOT NULL,
    ten_chi_nhanh VARCHAR(150) NOT NULL,
    dia_chi VARCHAR(255) NOT NULL,
    vi_do DECIMAL(10,7) NOT NULL,
    kinh_do DECIMAL(10,7) NOT NULL,
    mo_ta TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_chi_nhanh_nguoi_cho_thue FOREIGN KEY (nguoi_cho_thue_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE
);

CREATE TABLE xe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ten_xe VARCHAR(150) NOT NULL,
    nguoi_cho_thue_id INT NOT NULL,
    hang_xe VARCHAR(100) NOT NULL,
    bien_so VARCHAR(20) NOT NULL UNIQUE,
    anh_xe VARCHAR(255) DEFAULT NULL,
    vi_do_hien_tai DECIMAL(10,7) DEFAULT NULL,
    kinh_do_hien_tai DECIMAL(10,7) DEFAULT NULL,
    so_cho INT NOT NULL,
    nhien_lieu VARCHAR(50) NOT NULL,
    hop_so VARCHAR(100) NOT NULL,
    gia_thue_ngay DECIMAL(15,2) NOT NULL DEFAULT 0,
    chi_nhanh_id INT NOT NULL,
    trang_thai ENUM('san_sang', 'dang_thue', 'bao_duong') NOT NULL DEFAULT 'san_sang',
    mo_ta TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_xe_nguoi_cho_thue FOREIGN KEY (nguoi_cho_thue_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    CONSTRAINT fk_xe_chi_nhanh FOREIGN KEY (chi_nhanh_id) REFERENCES chi_nhanh(id) ON DELETE CASCADE
);

CREATE TABLE dat_xe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nguoi_dung_id INT NOT NULL,
    xe_id INT NOT NULL,
    chi_nhanh_nhan_id INT NOT NULL,
    chi_nhanh_tra_id INT NOT NULL,
    dia_chi_khach VARCHAR(255) DEFAULT NULL,
    vi_do_khach DECIMAL(10,7) DEFAULT NULL,
    kinh_do_khach DECIMAL(10,7) DEFAULT NULL,
    ngay_nhan DATETIME NOT NULL,
    ngay_tra DATETIME NOT NULL,
    thoi_diem_tra_thuc_te DATETIME DEFAULT NULL,
    tong_tien DECIMAL(15,2) NOT NULL DEFAULT 0,
    phi_phat_tra_muon DECIMAL(15,2) NOT NULL DEFAULT 0,
    ghi_chu TEXT,
    trang_thai ENUM('cho_xac_nhan', 'da_xac_nhan', 'dang_thue', 'hoan_thanh', 'da_huy') NOT NULL DEFAULT 'cho_xac_nhan',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_dat_xe_nguoi_dung FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    CONSTRAINT fk_dat_xe_xe FOREIGN KEY (xe_id) REFERENCES xe(id) ON DELETE CASCADE,
    CONSTRAINT fk_dat_xe_cn_nhan FOREIGN KEY (chi_nhanh_nhan_id) REFERENCES chi_nhanh(id) ON DELETE CASCADE,
    CONSTRAINT fk_dat_xe_cn_tra FOREIGN KEY (chi_nhanh_tra_id) REFERENCES chi_nhanh(id) ON DELETE CASCADE
);

INSERT INTO nguoi_dung (ho_ten, email, mat_khau, so_dien_thoai, dia_chi, cccd, vai_tro) VALUES
('Quan Tri He Thong', 'admin@carrent.vn', 'admin123', '0900000001', 'Ha Noi', '001122334455', 'admin'),
('Nguyen Van Cho Thue', 'chothue@carrent.vn', '123456', '0900000002', 'Cau Giay, Ha Noi', '001122334456', 'nguoi_cho_thue'),
('Nguyen Quang Minh', 'minh@gmail.com', '123456', '0901234567', 'Tay Ho, Ha Noi', '012345678901', 'khach_hang'),
('Pham Thu Trang', 'trang@gmail.com', '123456', '0912345678', 'Gia Lam, Ha Noi', '012345678902', 'khach_hang'),
('Tran Gia Bao', 'bao@gmail.com', '123456', '0923456789', 'Bai Chay, Ha Long', '012345678903', 'khach_hang');

INSERT INTO chi_nhanh (nguoi_cho_thue_id, ten_chi_nhanh, dia_chi, vi_do, kinh_do, mo_ta) VALUES
(2, 'Trung tam Ha Noi', '12 Ly Thuong Kiet, Hoan Kiem, Ha Noi', 21.0247000, 105.8566000, 'Chi nhanh trung tam, xu ly don noi thanh va doanh nghiep.'),
(2, 'Noi Bai Airport', 'San bay quoc te Noi Bai, Soc Son, Ha Noi', 21.2142000, 105.8042000, 'Diem giao nhan xe 24/7 cho khach san bay.'),
(2, 'My Dinh', '18 Pham Hung, Nam Tu Liem, Ha Noi', 21.0285000, 105.7792000, 'Tap trung SUV va xe phuc vu cong tac.'),
(2, 'Long Bien', '268 Nguyen Van Cu, Long Bien, Ha Noi', 21.0474000, 105.8898000, 'Phu song giao nhan Long Bien, Gia Lam va tinh lan can.');

INSERT INTO xe (nguoi_cho_thue_id, ten_xe, hang_xe, bien_so, anh_xe, vi_do_hien_tai, kinh_do_hien_tai, so_cho, nhien_lieu, hop_so, gia_thue_ngay, chi_nhanh_id, trang_thai, mo_ta) VALUES
(2, 'Toyota Veloz Cross', 'Toyota', '30K-168.92', 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=1200&q=80', 21.0301000, 105.8509000, 7, 'Xang', 'So tu dong', 1250000, 1, 'san_sang', 'Xe gia dinh 7 cho, phu hop di du lich va cong tac ngan ngay.'),
(2, 'Kia Carnival Signature', 'Kia', '51H-235.18', 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1200&q=80', 21.2058000, 105.8010000, 8, 'Dau', 'So tu dong', 2450000, 2, 'dang_thue', 'Mau MPV cao cap cho khach doanh nghiep va gia dinh dong nguoi.'),
(2, 'Ford Everest Titanium', 'Ford', '51K-889.21', 'https://images.unsplash.com/photo-1511919884226-fd3cad34687c?auto=format&fit=crop&w=1200&q=80', 21.0289000, 105.7805000, 7, 'Dau', 'So tu dong', 2150000, 3, 'bao_duong', 'SUV may dau, van hanh duong dai tot, hien dang bao duong.'),
(2, 'Hyundai Accent Premium', 'Hyundai', '29A-456.77', 'https://images.unsplash.com/photo-1553440569-bcc63803a83d?auto=format&fit=crop&w=1200&q=80', 21.0228000, 105.8543000, 4, 'Xang', 'So tu dong', 890000, 1, 'san_sang', 'Sedan tiet kiem nhien lieu, phu hop di noi thanh va di cong tac.'),
(2, 'VinFast VF 8 Eco', 'VinFast', '30L-220.68', 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1200&q=80', 21.0295000, 105.7768000, 5, 'Dien', 'Dien 1 cap', 1780000, 3, 'dang_thue', 'SUV dien cho khach can trai nghiem xe dien va giao nhan noi thanh.'),
(2, 'Mazda CX-5 Deluxe', 'Mazda', '30G-315.09', 'https://images.unsplash.com/photo-1494905998402-395d579af36f?auto=format&fit=crop&w=1200&q=80', 21.0495000, 105.8921000, 5, 'Xang', 'So tu dong', 1420000, 4, 'san_sang', 'SUV 5 cho, can bang giua gia va tien nghi.'),
(2, 'Mitsubishi Xpander AT', 'Mitsubishi', '29H-765.12', 'https://images.unsplash.com/photo-1502161254066-6c74afbf07aa?auto=format&fit=crop&w=1200&q=80', 21.0453000, 105.8864000, 7, 'Xang', 'So tu dong', 1180000, 4, 'san_sang', 'Xe 7 cho phuc vu gia dinh va hop dong du lich ngan ngay.'),
(2, 'Mercedes C300 AMG', 'Mercedes', '30A-998.86', 'https://images.unsplash.com/photo-1507136566006-cfc505b114fc?auto=format&fit=crop&w=1200&q=80', 21.0255000, 105.8581000, 4, 'Xang', 'So tu dong', 3200000, 1, 'san_sang', 'Sedan hang sang cho khach VIP va su kien cao cap.');

INSERT INTO dat_xe (nguoi_dung_id, xe_id, chi_nhanh_nhan_id, chi_nhanh_tra_id, dia_chi_khach, vi_do_khach, kinh_do_khach, ngay_nhan, ngay_tra, thoi_diem_tra_thuc_te, tong_tien, phi_phat_tra_muon, ghi_chu, trang_thai, created_at) VALUES
(2, 2, 2, 1, 'Ho Tay Residence, Tay Ho, Ha Noi', 21.0576000, 105.8194000, '2026-03-31 15:30:00', '2026-04-03 10:00:00', NULL, 7350000, 0, 'Cho xac nhan dat coc va giao xe san bay.', 'cho_xac_nhan', '2026-03-31 10:15:00'),
(4, 1, 1, 1, 'Bai Chay, Ha Long, Quang Ninh', 20.9563000, 107.0440000, '2026-03-31 09:00:00', '2026-04-02 21:00:00', NULL, 2500000, 0, 'Tu lai di Ha Long.', 'dang_thue', '2026-03-30 20:00:00'),
(3, 6, 4, 4, 'Vinhomes Ocean Park, Gia Lam, Ha Noi', 20.9982000, 105.9447000, '2026-03-31 13:00:00', '2026-04-01 22:00:00', '2026-04-02 09:15:00', 1420000, 426000, 'Da giao xe tai Long Bien.', 'hoan_thanh', '2026-03-30 12:00:00'),
(2, 8, 1, 1, 'JW Marriott Hanoi, Nam Tu Liem, Ha Noi', 21.0077000, 105.7837000, '2026-04-05 08:00:00', '2026-04-06 20:00:00', NULL, 3200000, 0, 'Can lai xe su kien doanh nghiep.', 'da_xac_nhan', '2026-03-31 08:00:00');
