<?php
session_start();
include "../includes/connect.php";
include "../includes/bootstrap.php";
/** @var mysqli $conn */

if (!isset($_SESSION["hoTen"], $_SESSION["vaiTro"], $_SESSION["idNguoiDung"])) {
    header("Location: ./dangNhap.php");
    exit;
}

if ($_SESSION["vaiTro"] !== "admin" && $_SESSION["vaiTro"] !== "nguoi_cho_thue") {
    header("Location: ../index.php?chuyen_trang=trangChu");
    exit;
}

if (isset($_GET["page_layout"]) && $_GET["page_layout"] === "dangXuat") {
    session_unset();
    session_destroy();
    header("Location: ./dangNhap.php");
    exit;
}

$idNhanSu = (int) $_SESSION["idNguoiDung"];
$sqlNhanSu = "SELECT * FROM nguoi_dung WHERE id = $idNhanSu LIMIT 1";
$ketQuaNhanSu = mysqli_query($conn, $sqlNhanSu);
$nhanSuDangNhap = $ketQuaNhanSu ? mysqli_fetch_assoc($ketQuaNhanSu) : null;

if (!$nhanSuDangNhap) {
    header("Location: ./dangNhap.php");
    exit;
}

$laAdminTong = ($nhanSuDangNhap["vai_tro"] ?? "") === "admin";
$pageLayout = $_GET["page_layout"] ?? "trangTongQuan";
if (
    $pageLayout !== "trangTongQuan"
    && $pageLayout !== "trangXe"
    && $pageLayout !== "trangDonDatXe"
    && $pageLayout !== "trangChiNhanh"
    && $pageLayout !== "trangNguoiDung"
) {
    $pageLayout = "trangTongQuan";
}

if (!$laAdminTong && $pageLayout === "trangNguoiDung") {
    $pageLayout = "trangTongQuan";
}

if (!$laAdminTong && $pageLayout === "trangChiNhanh") {
    $pageLayout = "trangTongQuan";
}

$tenTrangHienTai = "Tổng quan";
if ($pageLayout === "trangXe") {
    $tenTrangHienTai = "Quản lý xe";
} elseif ($pageLayout === "trangDonDatXe") {
    $tenTrangHienTai = "Quản lý đặt xe";
} elseif ($pageLayout === "trangChiNhanh") {
    $tenTrangHienTai = "Quản lý chi nhánh";
} elseif ($pageLayout === "trangNguoiDung") {
    $tenTrangHienTai = "Quản lý tài khoản";
}

$thongBao = "";
$soLieuTongQuan = [
    "tong_xe" => 0,
    "tong_don" => 0,
    "tong_nguoi_dung" => 0,
    "tong_chi_nhanh" => 0,
];
$danhSachXe = [];
$danhSachChiNhanh = [];
$danhSachNguoiDung = [];
$danhSachDon = [];
$danhSachLuaChonChiNhanh = [];
$danhSachLuaChonNguoiChoThue = [];
$danhSachLuaChonKhach = [];
$danhSachLuaChonXe = [];
$xeDangSua = null;
$chiNhanhDangSua = null;
$nguoiDungDangSua = null;
$donDangSua = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $loaiForm = $_POST["form_type"] ?? "";

    if ($loaiForm === "car_save") {
        $idXe = (int) ($_POST["id"] ?? 0);
        $tenXe = mysqli_real_escape_string($conn, trim($_POST["ten_xe"] ?? ""));
        $hangXe = mysqli_real_escape_string($conn, trim($_POST["hang_xe"] ?? ""));
        $bienSo = mysqli_real_escape_string($conn, trim($_POST["bien_so"] ?? ""));
        $anhXe = mysqli_real_escape_string($conn, trim($_POST["anh_xe"] ?? ""));
        $viDoXe = (float) ($_POST["vi_do_hien_tai"] ?? 0);
        $kinhDoXe = (float) ($_POST["kinh_do_hien_tai"] ?? 0);
        $soCho = (int) ($_POST["so_cho"] ?? 0);
        $nhienLieu = mysqli_real_escape_string($conn, trim($_POST["nhien_lieu"] ?? ""));
        $hopSo = mysqli_real_escape_string($conn, trim($_POST["hop_so"] ?? ""));
        $giaThueNgay = (float) ($_POST["gia_thue_ngay"] ?? 0);
        $idNguoiChoThue = $laAdminTong ? (int) ($_POST["nguoi_cho_thue_id"] ?? $idNhanSu) : $idNhanSu;
        $idChiNhanh = (int) ($_POST["chi_nhanh_id"] ?? 0);
        $trangThaiXe = mysqli_real_escape_string($conn, trim($_POST["trang_thai"] ?? "san_sang"));
        $moTaXe = mysqli_real_escape_string($conn, trim($_POST["mo_ta"] ?? ""));

        $sqlKiemTraChiNhanh = "SELECT * FROM chi_nhanh WHERE id = $idChiNhanh";
        if (!$laAdminTong) {
            $sqlKiemTraChiNhanh .= " AND nguoi_cho_thue_id = $idNhanSu";
        }
        $sqlKiemTraChiNhanh .= " LIMIT 1";
        $ketQuaKiemTraChiNhanh = mysqli_query($conn, $sqlKiemTraChiNhanh);
        $dongChiNhanh = $ketQuaKiemTraChiNhanh ? mysqli_fetch_assoc($ketQuaKiemTraChiNhanh) : null;

        if (!$dongChiNhanh) {
            $thongBao = "Chi nhánh không hợp lệ với phạm vi được quản lý.";
        } elseif ((int) ($dongChiNhanh["nguoi_cho_thue_id"] ?? 0) !== $idNguoiChoThue) {
            $thongBao = "Chi nhánh không thuộc người cho thuê xe đã chọn.";
        } elseif ($idXe > 0) {
            $sqlCapNhatXe = "UPDATE xe SET
                ten_xe = '$tenXe',
                nguoi_cho_thue_id = $idNguoiChoThue,
                hang_xe = '$hangXe',
                bien_so = '$bienSo',
                anh_xe = '$anhXe',
                vi_do_hien_tai = $viDoXe,
                kinh_do_hien_tai = $kinhDoXe,
                so_cho = $soCho,
                nhien_lieu = '$nhienLieu',
                hop_so = '$hopSo',
                gia_thue_ngay = $giaThueNgay,
                chi_nhanh_id = $idChiNhanh,
                trang_thai = '$trangThaiXe',
                mo_ta = '$moTaXe'
                WHERE id = $idXe";
            if (!$laAdminTong) {
                $sqlCapNhatXe .= " AND nguoi_cho_thue_id = $idNhanSu";
            }
            mysqli_query($conn, $sqlCapNhatXe);
            $thongBao = mysqli_affected_rows($conn) > 0
                ? "Cập nhật xe thành công."
                : "Bạn không có quyền cập nhật xe này hoặc không có thay đổi nào.";
        } else {
            $sqlThemXe = "INSERT INTO xe (
                    ten_xe, nguoi_cho_thue_id, hang_xe, bien_so, anh_xe,
                    vi_do_hien_tai, kinh_do_hien_tai, so_cho, nhien_lieu,
                    hop_so, gia_thue_ngay, chi_nhanh_id, trang_thai, mo_ta, created_at
                ) VALUES (
                    '$tenXe', $idNguoiChoThue, '$hangXe', '$bienSo', '$anhXe',
                    $viDoXe, $kinhDoXe, $soCho, '$nhienLieu',
                    '$hopSo', $giaThueNgay, $idChiNhanh, '$trangThaiXe', '$moTaXe', NOW()
                )";
            mysqli_query($conn, $sqlThemXe);
            $thongBao = "Thêm xe thành công.";
        }

        $pageLayout = "trangXe";
    }

    if ($laAdminTong && $loaiForm === "branch_save") {
        $idChiNhanh = (int) ($_POST["id"] ?? 0);
        $idNguoiChoThue = $laAdminTong ? (int) ($_POST["nguoi_cho_thue_id"] ?? $idNhanSu) : $idNhanSu;
        $tenChiNhanh = mysqli_real_escape_string($conn, trim($_POST["ten_chi_nhanh"] ?? ""));
        $diaChi = mysqli_real_escape_string($conn, trim($_POST["dia_chi"] ?? ""));
        $viDo = (float) ($_POST["vi_do"] ?? 0);
        $kinhDo = (float) ($_POST["kinh_do"] ?? 0);
        $moTaChiNhanh = mysqli_real_escape_string($conn, trim($_POST["mo_ta"] ?? ""));

        if ($idChiNhanh > 0) {
            $sqlCapNhatChiNhanh = "UPDATE chi_nhanh SET
                nguoi_cho_thue_id = $idNguoiChoThue,
                ten_chi_nhanh = '$tenChiNhanh',
                dia_chi = '$diaChi',
                vi_do = $viDo,
                kinh_do = $kinhDo,
                mo_ta = '$moTaChiNhanh'
                WHERE id = $idChiNhanh";
            if (!$laAdminTong) {
                $sqlCapNhatChiNhanh .= " AND nguoi_cho_thue_id = $idNhanSu";
            }
            mysqli_query($conn, $sqlCapNhatChiNhanh);
            $thongBao = mysqli_affected_rows($conn) > 0
                ? "Cập nhật chi nhánh thành công."
                : "Bạn không có quyền cập nhật chi nhánh này hoặc không có thay đổi nào.";
        } else {
            $sqlThemChiNhanh = "INSERT INTO chi_nhanh (
                    nguoi_cho_thue_id, ten_chi_nhanh, dia_chi, vi_do, kinh_do, mo_ta, created_at
                ) VALUES (
                    $idNguoiChoThue, '$tenChiNhanh', '$diaChi', $viDo, $kinhDo, '$moTaChiNhanh', NOW()
                )";
            mysqli_query($conn, $sqlThemChiNhanh);
            $thongBao = "Thêm chi nhánh thành công.";
        }

        $pageLayout = "trangChiNhanh";
    }

    if ($laAdminTong && $loaiForm === "user_save") {
        $idNguoiDung = (int) ($_POST["id"] ?? 0);
        $hoTen = mysqli_real_escape_string($conn, trim($_POST["ho_ten"] ?? ""));
        $email = mysqli_real_escape_string($conn, trim($_POST["email"] ?? ""));
        $soDienThoai = mysqli_real_escape_string($conn, trim($_POST["so_dien_thoai"] ?? ""));
        $diaChi = mysqli_real_escape_string($conn, trim($_POST["dia_chi"] ?? ""));
        $vaiTro = mysqli_real_escape_string($conn, trim($_POST["vai_tro"] ?? "khach_hang"));
        $matKhau = trim($_POST["mat_khau"] ?? "");

        if ($idNguoiDung > 0) {
            $sqlCapNhatNguoiDung = "UPDATE nguoi_dung SET
                ho_ten = '$hoTen',
                email = '$email',
                so_dien_thoai = '$soDienThoai',
                dia_chi = '$diaChi',
                vai_tro = '$vaiTro'";
            if ($matKhau !== "") {
                $matKhauDaMaHoa = password_hash($matKhau, PASSWORD_DEFAULT);
                $sqlCapNhatNguoiDung .= ", mat_khau = '$matKhauDaMaHoa'";
            }
            $sqlCapNhatNguoiDung .= " WHERE id = $idNguoiDung";
            mysqli_query($conn, $sqlCapNhatNguoiDung);
            $thongBao = "Cập nhật tài khoản thành công.";
        } else {
            $matKhauDaMaHoa = password_hash($matKhau !== "" ? $matKhau : "123456", PASSWORD_DEFAULT);
            $sqlThemNguoiDung = "INSERT INTO nguoi_dung (
                    ho_ten, email, mat_khau, so_dien_thoai, dia_chi, vai_tro, created_at
                ) VALUES (
                    '$hoTen', '$email', '$matKhauDaMaHoa', '$soDienThoai', '$diaChi', '$vaiTro', NOW()
                )";
            mysqli_query($conn, $sqlThemNguoiDung);
            $thongBao = "Thêm tài khoản thành công.";
        }

        $pageLayout = "trangNguoiDung";
    }

    if ($loaiForm === "booking_save") {
        $idDon = (int) ($_POST["id"] ?? 0);
        $idNguoiDung = (int) ($_POST["nguoi_dung_id"] ?? 0);
        $idXe = (int) ($_POST["xe_id"] ?? 0);
        $idChiNhanhNhan = (int) ($_POST["chi_nhanh_nhan_id"] ?? 0);
        $idChiNhanhTra = (int) ($_POST["chi_nhanh_tra_id"] ?? 0);
        $diaChiKhach = mysqli_real_escape_string($conn, trim($_POST["dia_chi_khach"] ?? ""));
        $viDoKhach = (float) ($_POST["vi_do_khach"] ?? 0);
        $kinhDoKhach = (float) ($_POST["kinh_do_khach"] ?? 0);
        $ngayNhan = mysqli_real_escape_string($conn, trim($_POST["ngay_nhan"] ?? ""));
        $ngayTra = mysqli_real_escape_string($conn, trim($_POST["ngay_tra"] ?? ""));
        $tongTien = (float) ($_POST["tong_tien"] ?? 0);
        $phiPhatTre = (float) ($_POST["phi_phat_tra_muon"] ?? 0);
        $ghiChu = mysqli_real_escape_string($conn, trim($_POST["ghi_chu"] ?? ""));
        $trangThaiDon = mysqli_real_escape_string($conn, trim($_POST["trang_thai"] ?? "cho_xac_nhan"));
        $ngayTraThucTe = trim($_POST["thoi_diem_tra_thuc_te"] ?? "");
        $ngayNhanLuu = $ngayNhan !== "" ? $ngayNhan . " 00:00:00" : "";
        $ngayTraLuu = $ngayTra !== "" ? $ngayTra . " 00:00:00" : "";
        $thoiDiemTraSql = $ngayTraThucTe !== ""
            ? "'" . mysqli_real_escape_string($conn, $ngayTraThucTe . " 00:00:00") . "'"
            : "NULL";

        $sqlKiemTraXe = "SELECT * FROM xe WHERE id = $idXe";
        if (!$laAdminTong) {
            $sqlKiemTraXe .= " AND nguoi_cho_thue_id = $idNhanSu";
        }
        $sqlKiemTraXe .= " LIMIT 1";
        $ketQuaKiemTraXe = mysqli_query($conn, $sqlKiemTraXe);
        $dongXe = $ketQuaKiemTraXe ? mysqli_fetch_assoc($ketQuaKiemTraXe) : null;

        $sqlKiemTraChiNhanhNhan = "SELECT * FROM chi_nhanh WHERE id = $idChiNhanhNhan";
        if (!$laAdminTong) {
            $sqlKiemTraChiNhanhNhan .= " AND nguoi_cho_thue_id = $idNhanSu";
        }
        $sqlKiemTraChiNhanhNhan .= " LIMIT 1";
        $ketQuaKiemTraChiNhanhNhan = mysqli_query($conn, $sqlKiemTraChiNhanhNhan);
        $dongChiNhanhNhan = $ketQuaKiemTraChiNhanhNhan ? mysqli_fetch_assoc($ketQuaKiemTraChiNhanhNhan) : null;

        $sqlKiemTraChiNhanhTra = "SELECT * FROM chi_nhanh WHERE id = $idChiNhanhTra";
        if (!$laAdminTong) {
            $sqlKiemTraChiNhanhTra .= " AND nguoi_cho_thue_id = $idNhanSu";
        }
        $sqlKiemTraChiNhanhTra .= " LIMIT 1";
        $ketQuaKiemTraChiNhanhTra = mysqli_query($conn, $sqlKiemTraChiNhanhTra);
        $dongChiNhanhTra = $ketQuaKiemTraChiNhanhTra ? mysqli_fetch_assoc($ketQuaKiemTraChiNhanhTra) : null;

        $sqlLayDon = "SELECT dx.*, x.ten_xe, x.bien_so, x.vi_do_hien_tai, x.kinh_do_hien_tai, x.nguoi_cho_thue_id, x.gia_thue_ngay
            FROM dat_xe dx
            LEFT JOIN xe x ON dx.xe_id = x.id
            WHERE dx.id = $idDon";
        if (!$laAdminTong) {
            $sqlLayDon .= " AND x.nguoi_cho_thue_id = $idNhanSu";
        }
        $sqlLayDon .= " LIMIT 1";
        $ketQuaLayDon = mysqli_query($conn, $sqlLayDon);
        $dongDonHienTai = $ketQuaLayDon ? mysqli_fetch_assoc($ketQuaLayDon) : null;

        if ($idDon <= 0) {
            $thongBao = "Đơn thuê phải do khách thuê xe tạo từ giao diện đặt xe.";
        } elseif (!$dongXe || !$dongChiNhanhNhan || !$dongChiNhanhTra) {
            $thongBao = "Đơn thuê không hợp lệ với phạm vi xe hoặc chi nhánh được quản lý.";
        } elseif (!$dongDonHienTai) {
            $thongBao = "Bạn không có quyền cập nhật đơn thuê này.";
        } else {
            if (!$laAdminTong) {
                $trangThaiCu = $dongDonHienTai["trang_thai"] ?? "";
                $duocChuyenTrangThai = false;

                if ($trangThaiDon === $trangThaiCu) {
                    $duocChuyenTrangThai = true;
                } elseif ($trangThaiCu === "cho_xac_nhan" && ($trangThaiDon === "da_xac_nhan" || $trangThaiDon === "da_huy")) {
                    $duocChuyenTrangThai = true;
                } elseif ($trangThaiCu === "da_xac_nhan" && ($trangThaiDon === "dang_thue" || $trangThaiDon === "da_huy")) {
                    $duocChuyenTrangThai = true;
                } elseif ($trangThaiCu === "dang_thue" && $trangThaiDon === "hoan_thanh") {
                    $duocChuyenTrangThai = true;
                }

                if (!$duocChuyenTrangThai) {
                    $thongBao = "Chuyển trạng thái không hợp lệ theo quy trình nghiệp vụ.";
                }
            }

            if ($thongBao === "") {
                $giaThueXe = (float) ($dongXe["gia_thue_ngay"] ?? $dongDonHienTai["gia_thue_ngay"] ?? 0);

                if ($trangThaiDon === "hoan_thanh" && $ngayTraThucTe === "") {
                    $ngayTraThucTe = date("Y-m-d H:i:s");
                    $thoiDiemTraSql = "'" . $ngayTraThucTe . "'";
                }

                if ($trangThaiDon === "hoan_thanh" || $ngayTraThucTe !== "") {
                    $phiPhatTre = calculate_late_fee(
                        $laAdminTong ? $ngayTraLuu : ($dongDonHienTai["ngay_tra"] ?? ""),
                        $ngayTraThucTe !== "" ? $ngayTraThucTe . " 00:00:00" : null,
                        $giaThueXe,
                        $trangThaiDon
                    );
                }

                if ($laAdminTong) {
                    $sqlCapNhatDon = "UPDATE dat_xe SET
                        nguoi_dung_id = $idNguoiDung,
                        xe_id = $idXe,
                        chi_nhanh_nhan_id = $idChiNhanhNhan,
                        chi_nhanh_tra_id = $idChiNhanhTra,
                        ngay_nhan = '$ngayNhanLuu',
                        ngay_tra = '$ngayTraLuu',
                        thoi_diem_tra_thuc_te = $thoiDiemTraSql,
                        tong_tien = $tongTien,
                        phi_phat_tra_muon = $phiPhatTre,
                        ghi_chu = '$ghiChu',
                        trang_thai = '$trangThaiDon'
                        WHERE id = $idDon";
                } else {
                    $sqlCapNhatDon = "UPDATE dat_xe SET
                        thoi_diem_tra_thuc_te = $thoiDiemTraSql,
                        phi_phat_tra_muon = $phiPhatTre,
                        ghi_chu = '$ghiChu',
                        trang_thai = '$trangThaiDon'
                        WHERE id = $idDon";
                }

                if (!$laAdminTong) {
                    $sqlCapNhatDon .= " AND xe_id IN (SELECT id FROM xe WHERE nguoi_cho_thue_id = $idNhanSu)";
                }

                mysqli_query($conn, $sqlCapNhatDon);

                if ($trangThaiDon === "dang_thue") {
                    mysqli_query($conn, "UPDATE xe SET trang_thai = 'dang_thue' WHERE id = $idXe");
                }

                if (in_array($trangThaiDon, ["hoan_thanh", "da_huy"], true)) {
                    $ketQuaDonDangHoatDong = mysqli_query($conn, "SELECT id FROM dat_xe
                        WHERE xe_id = $idXe
                        AND trang_thai IN ('da_xac_nhan', 'dang_thue')
                        AND id <> $idDon
                        LIMIT 1");
                    $donDangHoatDong = $ketQuaDonDangHoatDong ? mysqli_fetch_assoc($ketQuaDonDangHoatDong) : null;

                    if (!$donDangHoatDong) {
                        mysqli_query($conn, "UPDATE xe SET trang_thai = 'san_sang' WHERE id = $idXe AND trang_thai <> 'bao_duong'");
                    }
                }

                $thongBao = mysqli_affected_rows($conn) > 0
                    ? "Cập nhật đơn đặt xe thành công."
                    : "Không có thay đổi nào được áp dụng.";
            }
        }

        $pageLayout = "trangDonDatXe";
    }
}

if (isset($_GET["delete"], $_GET["id"])) {
    $idXoa = (int) $_GET["id"];
    $loaiXoa = $_GET["delete"];

    if ($loaiXoa === "car") {
        $sqlXoaXe = "DELETE FROM xe WHERE id = $idXoa";
        if (!$laAdminTong) {
            $sqlXoaXe .= " AND nguoi_cho_thue_id = $idNhanSu";
        }
        mysqli_query($conn, $sqlXoaXe);
        $thongBao = "Đã xóa xe.";
        $pageLayout = "trangXe";
    }

    if ($laAdminTong && $loaiXoa === "branch") {
        $sqlXoaChiNhanh = "DELETE FROM chi_nhanh WHERE id = $idXoa";
        if (!$laAdminTong) {
            $sqlXoaChiNhanh .= " AND nguoi_cho_thue_id = $idNhanSu";
        }
        mysqli_query($conn, $sqlXoaChiNhanh);
        $thongBao = "Đã xóa chi nhánh.";
        $pageLayout = "trangChiNhanh";
    }

    if ($laAdminTong && $loaiXoa === "user") {
        mysqli_query($conn, "DELETE FROM nguoi_dung WHERE id = $idXoa");
        $thongBao = "Đã xóa tài khoản.";
        $pageLayout = "trangNguoiDung";
    }

    if ($laAdminTong && $loaiXoa === "booking") {
        mysqli_query($conn, "DELETE FROM dat_xe WHERE id = $idXoa");
        $thongBao = mysqli_affected_rows($conn) > 0 ? "Đã xóa đơn đặt xe." : "Không thể xóa đơn này.";
        $pageLayout = "trangDonDatXe";
    }
}

if ($pageLayout === "trangTongQuan") {
    $dieuKienXeThongKe = $laAdminTong ? "1=1" : "nguoi_cho_thue_id = $idNhanSu";
    $dieuKienDonThongKe = $laAdminTong ? "1=1" : "x.nguoi_cho_thue_id = $idNhanSu";
    $dieuKienChiNhanhThongKe = $laAdminTong ? "1=1" : "nguoi_cho_thue_id = $idNhanSu";

    $sqlThongKe = "SELECT
        (SELECT COUNT(*) FROM xe WHERE $dieuKienXeThongKe) AS tong_xe,
        (SELECT COUNT(*) FROM dat_xe dx LEFT JOIN xe x ON dx.xe_id = x.id WHERE $dieuKienDonThongKe) AS tong_don,
        (SELECT COUNT(DISTINCT dx.nguoi_dung_id) FROM dat_xe dx LEFT JOIN xe x ON dx.xe_id = x.id WHERE $dieuKienDonThongKe) AS tong_nguoi_dung,
        (SELECT COUNT(*) FROM chi_nhanh WHERE $dieuKienChiNhanhThongKe) AS tong_chi_nhanh";
    $ketQuaThongKe = mysqli_query($conn, $sqlThongKe);
    $thongKeTongQuan = $ketQuaThongKe ? mysqli_fetch_assoc($ketQuaThongKe) : null;

    if ($thongKeTongQuan) {
        $soLieuTongQuan = $thongKeTongQuan;
    }
}

if ($pageLayout === "trangXe") {
    if (isset($_GET["edit"])) {
        $idSuaXe = (int) $_GET["edit"];
        $sqlXeDangSua = "SELECT * FROM xe WHERE id = $idSuaXe";
        if (!$laAdminTong) {
            $sqlXeDangSua .= " AND nguoi_cho_thue_id = $idNhanSu";
        }
        $sqlXeDangSua .= " LIMIT 1";
        $ketQuaXeDangSua = mysqli_query($conn, $sqlXeDangSua);
        $xeDangSua = $ketQuaXeDangSua ? mysqli_fetch_assoc($ketQuaXeDangSua) : null;
    }

    $sqlDanhSachXe = "SELECT
            x.*,
            c.ten_chi_nhanh,
            u.ho_ten AS ten_nguoi_cho_thue
        FROM xe x
        LEFT JOIN chi_nhanh c ON x.chi_nhanh_id = c.id
        LEFT JOIN nguoi_dung u ON x.nguoi_cho_thue_id = u.id";
    if (!$laAdminTong) {
        $sqlDanhSachXe .= " WHERE x.nguoi_cho_thue_id = $idNhanSu";
    }
    $sqlDanhSachXe .= " ORDER BY x.id DESC";
    $ketQuaDanhSachXe = mysqli_query($conn, $sqlDanhSachXe);
    while ($dongXe = $ketQuaDanhSachXe ? mysqli_fetch_assoc($ketQuaDanhSachXe) : null) {
        $danhSachXe[] = $dongXe;
    }

    $sqlLuaChonChiNhanh = "SELECT id, ten_chi_nhanh FROM chi_nhanh";
    if (!$laAdminTong) {
        $sqlLuaChonChiNhanh .= " WHERE nguoi_cho_thue_id = $idNhanSu";
    }
    $sqlLuaChonChiNhanh .= " ORDER BY ten_chi_nhanh";
    $ketQuaLuaChonChiNhanh = mysqli_query($conn, $sqlLuaChonChiNhanh);
    while ($dongChiNhanh = $ketQuaLuaChonChiNhanh ? mysqli_fetch_assoc($ketQuaLuaChonChiNhanh) : null) {
        $danhSachLuaChonChiNhanh[] = $dongChiNhanh;
    }

    if ($laAdminTong) {
        $ketQuaLuaChonNguoiChoThue = mysqli_query($conn, "SELECT id, ho_ten FROM nguoi_dung WHERE vai_tro = 'nguoi_cho_thue' ORDER BY ho_ten");
        while ($dongNguoiChoThue = $ketQuaLuaChonNguoiChoThue ? mysqli_fetch_assoc($ketQuaLuaChonNguoiChoThue) : null) {
            $danhSachLuaChonNguoiChoThue[] = $dongNguoiChoThue;
        }
    }
}

if ($pageLayout === "trangChiNhanh") {
    if (isset($_GET["edit"])) {
        $idSuaChiNhanh = (int) $_GET["edit"];
        $sqlChiNhanhDangSua = "SELECT * FROM chi_nhanh WHERE id = $idSuaChiNhanh";
        if (!$laAdminTong) {
            $sqlChiNhanhDangSua .= " AND nguoi_cho_thue_id = $idNhanSu";
        }
        $sqlChiNhanhDangSua .= " LIMIT 1";
        $ketQuaChiNhanhDangSua = mysqli_query($conn, $sqlChiNhanhDangSua);
        $chiNhanhDangSua = $ketQuaChiNhanhDangSua ? mysqli_fetch_assoc($ketQuaChiNhanhDangSua) : null;
    }

    $sqlDanhSachChiNhanh = "SELECT * FROM chi_nhanh";
    if (!$laAdminTong) {
        $sqlDanhSachChiNhanh .= " WHERE nguoi_cho_thue_id = $idNhanSu";
    }
    $sqlDanhSachChiNhanh .= " ORDER BY id DESC";
    $ketQuaDanhSachChiNhanh = mysqli_query($conn, $sqlDanhSachChiNhanh);
    while ($dongChiNhanh = $ketQuaDanhSachChiNhanh ? mysqli_fetch_assoc($ketQuaDanhSachChiNhanh) : null) {
        $danhSachChiNhanh[] = $dongChiNhanh;
    }

    if ($laAdminTong) {
        $ketQuaLuaChonNguoiChoThue = mysqli_query($conn, "SELECT id, ho_ten FROM nguoi_dung WHERE vai_tro = 'nguoi_cho_thue' ORDER BY ho_ten");
        while ($dongNguoiChoThue = $ketQuaLuaChonNguoiChoThue ? mysqli_fetch_assoc($ketQuaLuaChonNguoiChoThue) : null) {
            $danhSachLuaChonNguoiChoThue[] = $dongNguoiChoThue;
        }
    }
}

if ($pageLayout === "trangNguoiDung" && $laAdminTong) {
    if (isset($_GET["edit"])) {
        $idSuaNguoiDung = (int) $_GET["edit"];
        $ketQuaNguoiDungDangSua = mysqli_query($conn, "SELECT * FROM nguoi_dung WHERE id = $idSuaNguoiDung LIMIT 1");
        $nguoiDungDangSua = $ketQuaNguoiDungDangSua ? mysqli_fetch_assoc($ketQuaNguoiDungDangSua) : null;
    }

    $ketQuaDanhSachNguoiDung = mysqli_query($conn, "SELECT * FROM nguoi_dung ORDER BY id DESC");
    while ($dongNguoiDung = $ketQuaDanhSachNguoiDung ? mysqli_fetch_assoc($ketQuaDanhSachNguoiDung) : null) {
        $danhSachNguoiDung[] = $dongNguoiDung;
    }
}

if ($pageLayout === "trangDonDatXe") {
    if (isset($_GET["edit"])) {
        $idSuaDon = (int) $_GET["edit"];
        $sqlDonDangSua = "SELECT
                dx.*,
                x.ten_xe,
                x.bien_so,
                x.vi_do_hien_tai,
                x.kinh_do_hien_tai,
                x.gia_thue_ngay
            FROM dat_xe dx
            LEFT JOIN xe x ON dx.xe_id = x.id
            WHERE dx.id = $idSuaDon";
        if (!$laAdminTong) {
            $sqlDonDangSua .= " AND x.nguoi_cho_thue_id = $idNhanSu";
        }
        $sqlDonDangSua .= " LIMIT 1";
        $ketQuaDonDangSua = mysqli_query($conn, $sqlDonDangSua);
        $donDangSua = $ketQuaDonDangSua ? mysqli_fetch_assoc($ketQuaDonDangSua) : null;
    }

    $sqlDanhSachDon = "SELECT
            dx.*,
            nd.ho_ten,
            nd.so_dien_thoai,
            nd.dia_chi AS dia_chi_nguoi_dung,
            x.ten_xe,
            x.bien_so,
            x.gia_thue_ngay,
            x.vi_do_hien_tai,
            x.kinh_do_hien_tai
        FROM dat_xe dx
        LEFT JOIN nguoi_dung nd ON dx.nguoi_dung_id = nd.id
        LEFT JOIN xe x ON dx.xe_id = x.id";
    if (!$laAdminTong) {
        $sqlDanhSachDon .= " WHERE x.nguoi_cho_thue_id = $idNhanSu";
    }
    $sqlDanhSachDon .= " ORDER BY dx.id DESC";
    $ketQuaDanhSachDon = mysqli_query($conn, $sqlDanhSachDon);
    while ($dongDon = $ketQuaDanhSachDon ? mysqli_fetch_assoc($ketQuaDanhSachDon) : null) {
        $danhSachDon[] = $dongDon;
    }

    $sqlLuaChonChiNhanh = "SELECT id, ten_chi_nhanh FROM chi_nhanh";
    if (!$laAdminTong) {
        $sqlLuaChonChiNhanh .= " WHERE nguoi_cho_thue_id = $idNhanSu";
    }
    $sqlLuaChonChiNhanh .= " ORDER BY ten_chi_nhanh";
    $ketQuaLuaChonChiNhanh = mysqli_query($conn, $sqlLuaChonChiNhanh);
    while ($dongChiNhanh = $ketQuaLuaChonChiNhanh ? mysqli_fetch_assoc($ketQuaLuaChonChiNhanh) : null) {
        $danhSachLuaChonChiNhanh[] = $dongChiNhanh;
    }

    if ($laAdminTong) {
        $ketQuaLuaChonKhach = mysqli_query($conn, "SELECT id, ho_ten FROM nguoi_dung WHERE vai_tro = 'khach_hang' ORDER BY ho_ten");
        while ($dongKhach = $ketQuaLuaChonKhach ? mysqli_fetch_assoc($ketQuaLuaChonKhach) : null) {
            $danhSachLuaChonKhach[] = $dongKhach;
        }

        $sqlLuaChonXe = "SELECT id, ten_xe, bien_so FROM xe ORDER BY ten_xe";
        $ketQuaLuaChonXe = mysqli_query($conn, $sqlLuaChonXe);
        while ($dongXe = $ketQuaLuaChonXe ? mysqli_fetch_assoc($ketQuaLuaChonXe) : null) {
            $danhSachLuaChonXe[] = $dongXe;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $laAdminTong ? "Admin Tổng" : "Người Cho Thuê Xe"; ?> | CarRent</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .khung-admin {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }

        .cot-menu-admin {
            position: sticky;
            top: 0;
            height: 100vh;
            padding: 24px 18px;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 16px 40px rgba(17, 31, 53, 0.08);
        }

        .thuong-hieu-admin {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 20px;
        }

        .logo-admin {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: linear-gradient(145deg, var(--primary-color), #5fc8ff);
            color: #ffffff;
            font-weight: 900;
            box-shadow: 0 12px 22px rgba(7, 165, 254, 0.24);
        }

        .chu-thuong-hieu-admin strong {
            display: block;
            font-size: 18px;
        }

        .chu-thuong-hieu-admin span {
            display: block;
            margin-top: 2px;
            color: var(--muted-color);
            font-size: 13px;
        }

        .menu-admin a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 12px;
            color: var(--muted-color);
            margin-bottom: 8px;
        }

        .menu-admin a:hover,
        .menu-admin a.dang-mo {
            background: rgba(7, 165, 254, 0.1);
            color: var(--primary-color);
        }

        .cot-noi-dung-admin {
            margin: 20px;
            padding: 24px;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 16px 40px rgba(17, 31, 53, 0.08);
        }

        .dau-trang-admin p {
            margin: 0;
            color: var(--muted-color);
        }

        .dau-trang-admin h1 {
            margin: 8px 0 0;
            font-size: 30px;
        }

        .thong-bao-admin {
            margin: 18px 0;
            padding: 14px 16px;
            border-radius: 16px;
            background: rgba(22, 196, 127, 0.12);
            color: #0f8d5c;
        }

        .luoi-thong-ke-admin {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
        }

        .the-thong-ke-admin {
            padding: 24px;
            border-radius: 22px;
            background: #ffffff;
            border: 1px solid #e6edf7;
            box-shadow: 0 16px 40px rgba(17, 31, 53, 0.08);
        }

        .the-thong-ke-admin span {
            color: var(--muted-color);
        }

        .the-thong-ke-admin strong {
            display: block;
            margin: 10px 0 6px;
            font-size: 30px;
        }

        .khung-2-cot-admin {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 22px;
        }

        .hop-bang-admin,
        .hop-form-admin {
            padding: 24px;
            border-radius: 22px;
            background: #ffffff;
            border: 1px solid #e6edf7;
            box-shadow: 0 16px 40px rgba(17, 31, 53, 0.08);
        }

        .hop-bang-admin h2,
        .hop-form-admin h2 {
            margin: 0 0 18px;
            font-size: 26px;
        }

        .vung-bang-admin {
            overflow: auto;
        }

        .bang-admin {
            width: 100%;
            border-collapse: collapse;
        }

        .bang-admin th {
            text-align: left;
            padding: 0 0 14px;
            font-size: 13px;
            text-transform: uppercase;
            color: var(--muted-color);
        }

        .bang-admin td {
            padding: 16px 12px 16px 0;
            border-top: 1px solid #edf2f8;
            vertical-align: top;
        }

        .dong-phu-admin {
            display: block;
            margin-top: 6px;
            color: var(--muted-color);
            font-size: 14px;
            line-height: 1.5;
        }

        .dong-anh-admin {
            width: 90px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
            display: block;
        }

        .dong-input-admin {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 16px;
        }

        .dong-input-admin label {
            font-weight: 600;
        }

        .dong-input-admin input,
        .dong-input-admin select,
        .dong-input-admin textarea {
            width: 100%;
            padding: 13px 14px;
            border-radius: 14px;
            border: 1px solid #d9e3ef;
            font-family: inherit;
        }

        .dong-input-admin textarea {
            min-height: 110px;
            resize: vertical;
        }

        .luoi-input-admin {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .nut-luu-admin {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            border-radius: 14px;
            border: 0;
            background: var(--primary-color);
            color: #ffffff;
            font-weight: 700;
            box-shadow: 0 10px 20px rgba(7, 165, 254, 0.22);
        }

        .nhan-admin {
            display: inline-flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
        }

        .nhan-admin.mau-xanh {
            background: rgba(22, 196, 127, 0.12);
            color: #16c47f;
        }

        .nhan-admin.mau-duong {
            background: rgba(7, 165, 254, 0.12);
            color: var(--primary-color);
        }

        .nhan-admin.mau-cam {
            background: rgba(255, 159, 67, 0.16);
            color: #b97726;
        }

        .nhan-admin.mau-xam {
            background: rgba(109, 121, 136, 0.14);
            color: #526072;
        }

        .nhan-admin.mau-xanh-duong {
            background: rgba(58, 134, 255, 0.12);
            color: #3a86ff;
        }

        .nhan-admin.mau-do {
            background: rgba(255, 95, 95, 0.14);
            color: #ff5f5f;
        }

        .hop-vi-tri-admin {
            margin: 16px 0 18px;
            padding: 16px 18px;
            border-radius: 18px;
            border: 1px solid #dbe6f4;
            background: linear-gradient(180deg, #f8fbff 0%, #f2f7fd 100%);
        }

        .hop-vi-tri-admin strong {
            display: block;
            font-size: 18px;
            margin-bottom: 6px;
        }

        .duong-link-admin {
            display: inline-flex;
            align-items: center;
            margin-top: 12px;
            color: var(--primary-color);
            font-weight: 700;
        }

        .ghi-chu-nho-admin {
            color: var(--muted-color);
            font-size: 14px;
            line-height: 1.5;
        }

        .dong-thao-tac-admin a {
            color: var(--primary-color);
        }

        @media (max-width: 1080px) {
            .luoi-thong-ke-admin {
                grid-template-columns: repeat(2, 1fr);
            }

            .khung-2-cot-admin {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 860px) {
            .khung-admin {
                grid-template-columns: 1fr;
            }

            .cot-menu-admin {
                position: relative;
                height: auto;
            }

            .cot-noi-dung-admin {
                margin: 0;
            }

            .luoi-thong-ke-admin,
            .luoi-input-admin {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="khung-admin">
        <aside class="cot-menu-admin">
            <div class="thuong-hieu-admin">
                <div class="logo-admin">CR</div>
                <div class="chu-thuong-hieu-admin">
                    <strong><?php echo $laAdminTong ? "Admin tổng" : "Admin người cho thuê"; ?></strong>
                    <span><?php echo $laAdminTong ? "Quản lý toàn hệ thống" : "Quản lý dữ liệu của bạn"; ?></span>
                </div>
            </div>

            <div class="menu-admin">
                <a class="<?php echo $pageLayout === "trangTongQuan" ? "dang-mo" : ""; ?>" href="./index.php?page_layout=trangTongQuan"><i class="fa fa-chart-line"></i> Tổng quan</a>
                <a class="<?php echo $pageLayout === "trangXe" ? "dang-mo" : ""; ?>" href="./index.php?page_layout=trangXe"><i class="fa fa-car"></i> Quản lý xe</a>
                <a class="<?php echo $pageLayout === "trangDonDatXe" ? "dang-mo" : ""; ?>" href="./index.php?page_layout=trangDonDatXe"><i class="fa fa-calendar-check"></i> Quản lý đặt xe</a>
                <?php if ($laAdminTong): ?>
                    <a class="<?php echo $pageLayout === "trangChiNhanh" ? "dang-mo" : ""; ?>" href="./index.php?page_layout=trangChiNhanh"><i class="fa fa-location-dot"></i> Quản lý chi nhánh</a>
                <?php endif; ?>
                <?php if ($laAdminTong): ?>
                    <a class="<?php echo $pageLayout === "trangNguoiDung" ? "dang-mo" : ""; ?>" href="./index.php?page_layout=trangNguoiDung"><i class="fa fa-users"></i> Quản lý tài khoản</a>
                <?php endif; ?>
                <a href="../index.php?chuyen_trang=trangChu"><i class="fa fa-globe"></i> Về khu khách thuê xe</a>
                <a href="./index.php?page_layout=dangXuat"><i class="fa fa-right-from-bracket"></i> Đăng xuất</a>
            </div>
        </aside>

        <div class="cot-noi-dung-admin">
            <div class="dau-trang-admin">
                <p><?php echo $laAdminTong ? "Admin tổng hệ thống" : "Khu người cho thuê xe"; ?></p>
                <h1><?php echo esc($_SESSION["hoTen"]); ?> - <?php echo esc($tenTrangHienTai); ?></h1>
            </div>

            <?php if ($thongBao !== ""): ?>
                <div class="thong-bao-admin"><?php echo esc($thongBao); ?></div>
            <?php endif; ?>

            <?php if ($pageLayout === "trangTongQuan"): ?>
                <div class="luoi-thong-ke-admin">
                    <div class="the-thong-ke-admin">
                        <span>Tổng xe</span>
                        <strong><?php echo esc($soLieuTongQuan["tong_xe"]); ?></strong>
                        <small class="ghi-chu-nho-admin"><?php echo $laAdminTong ? "Tất cả xe trong hệ thống" : "Xe thuộc tài khoản của bạn"; ?></small>
                    </div>
                    <div class="the-thong-ke-admin">
                        <span>Tổng đơn</span>
                        <strong><?php echo esc($soLieuTongQuan["tong_don"]); ?></strong>
                        <small class="ghi-chu-nho-admin">Bao gồm cả đơn đã hủy</small>
                    </div>
                    <div class="the-thong-ke-admin">
                        <span>Tài khoản</span>
                        <strong><?php echo esc($soLieuTongQuan["tong_nguoi_dung"]); ?></strong>
                        <small class="ghi-chu-nho-admin"><?php echo $laAdminTong ? "Admin tổng, người cho thuê và khách thuê xe" : "Khách thuê xe trong phạm vi của bạn"; ?></small>
                    </div>
                    <?php if ($laAdminTong): ?>
                        <div class="the-thong-ke-admin">
                            <span>Chi nhánh</span>
                            <strong><?php echo esc($soLieuTongQuan["tong_chi_nhanh"]); ?></strong>
                            <small class="ghi-chu-nho-admin">Điểm giao nhận xe</small>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($pageLayout === "trangXe"): ?>
                <div class="khung-2-cot-admin">
                    <div class="hop-bang-admin">
                        <h2>Danh sách xe</h2>
                        <div class="vung-bang-admin">
                            <table class="bang-admin">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tên xe</th>
                                        <th>Ảnh</th>
                                        <th>Biển số</th>
                                        <th>Chi nhánh</th>
                                        <th>Tọa độ</th>
                                        <th>Giá ngày</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($danhSachXe as $dongXe): ?>
                                        <tr>
                                            <td><?php echo (int) $dongXe["id"]; ?></td>
                                            <td><?php echo esc($dongXe["ten_xe"]); ?></td>
                                            <td>
                                                <?php if (!empty($dongXe["anh_xe"])): ?>
                                                    <img class="dong-anh-admin" src="<?php echo esc($dongXe["anh_xe"]); ?>" alt="<?php echo esc($dongXe["ten_xe"]); ?>">
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo esc($dongXe["bien_so"]); ?></td>
                                            <td><?php echo esc($dongXe["ten_chi_nhanh"]); ?></td>
                                            <td><?php echo esc($dongXe["vi_do_hien_tai"]); ?><span class="dong-phu-admin"><?php echo esc($dongXe["kinh_do_hien_tai"]); ?></span></td>
                                            <td><?php echo format_money($dongXe["gia_thue_ngay"]); ?></td>
                                            <td>
                                                <?php
                                                $tenTrangThaiXe = (string) $dongXe["trang_thai"];
                                                $mauTrangThaiXe = "mau-duong";
                                                if ($tenTrangThaiXe === "san_sang") {
                                                    $tenTrangThaiXe = "Sẵn sàng";
                                                    $mauTrangThaiXe = "mau-xanh";
                                                } elseif ($tenTrangThaiXe === "dang_thue") {
                                                    $tenTrangThaiXe = "Đang thuê";
                                                    $mauTrangThaiXe = "mau-duong";
                                                } elseif ($tenTrangThaiXe === "bao_duong") {
                                                    $tenTrangThaiXe = "Bảo dưỡng";
                                                    $mauTrangThaiXe = "mau-cam";
                                                } elseif ($tenTrangThaiXe === "tam_ngung") {
                                                    $tenTrangThaiXe = "Tạm ngưng";
                                                    $mauTrangThaiXe = "mau-xam";
                                                }
                                                ?>
                                                <span class="nhan-admin <?php echo esc($mauTrangThaiXe); ?>">
                                                    <?php echo esc($tenTrangThaiXe); ?>
                                                </span>
                                            </td>
                                            <td class="dong-thao-tac-admin">
                                                <a href="./index.php?page_layout=trangXe&edit=<?php echo (int) $dongXe["id"]; ?>">Sửa</a> |
                                                <a href="./index.php?page_layout=trangXe&delete=car&id=<?php echo (int) $dongXe["id"]; ?>" onclick="return confirm('Xóa xe này?');">Xóa</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="hop-form-admin">
                        <h2><?php echo $xeDangSua ? "Cập nhật xe" : "Thêm xe"; ?></h2>
                        <form method="post">
                            <input type="hidden" name="form_type" value="car_save">
                            <input type="hidden" name="id" value="<?php echo esc($xeDangSua["id"] ?? "0"); ?>">
                            <div class="dong-input-admin"><label>Tên xe</label><input type="text" name="ten_xe" value="<?php echo esc($xeDangSua["ten_xe"] ?? ""); ?>" required></div>
                            <div class="dong-input-admin"><label>Hãng xe</label><input type="text" name="hang_xe" value="<?php echo esc($xeDangSua["hang_xe"] ?? ""); ?>" required></div>
                            <div class="dong-input-admin"><label>Biển số</label><input type="text" name="bien_so" value="<?php echo esc($xeDangSua["bien_so"] ?? ""); ?>" required></div>
                            <div class="dong-input-admin"><label>Link ảnh xe</label><input type="text" name="anh_xe" value="<?php echo esc($xeDangSua["anh_xe"] ?? ""); ?>" placeholder="https://..."></div>
                            <div class="luoi-input-admin">
                                <div class="dong-input-admin"><label>Vĩ độ xe hiện tại</label><input type="text" name="vi_do_hien_tai" value="<?php echo esc($xeDangSua["vi_do_hien_tai"] ?? ""); ?>" placeholder="21.0247000"></div>
                                <div class="dong-input-admin"><label>Kinh độ xe hiện tại</label><input type="text" name="kinh_do_hien_tai" value="<?php echo esc($xeDangSua["kinh_do_hien_tai"] ?? ""); ?>" placeholder="105.8566000"></div>
                            </div>
                            <div class="luoi-input-admin">
                                <div class="dong-input-admin"><label>Số chỗ</label><input type="number" name="so_cho" value="<?php echo esc($xeDangSua["so_cho"] ?? "4"); ?>" required></div>
                                <div class="dong-input-admin"><label>Giá thuê ngày</label><input type="number" name="gia_thue_ngay" value="<?php echo esc($xeDangSua["gia_thue_ngay"] ?? "0"); ?>" required></div>
                            </div>
                            <div class="luoi-input-admin">
                                <div class="dong-input-admin"><label>Nhiên liệu</label><input type="text" name="nhien_lieu" value="<?php echo esc($xeDangSua["nhien_lieu"] ?? "Xăng"); ?>"></div>
                                <div class="dong-input-admin"><label>Hộp số</label><input type="text" name="hop_so" value="<?php echo esc($xeDangSua["hop_so"] ?? "Số tự động"); ?>"></div>
                            </div>
                            <?php if ($laAdminTong): ?>
                                <div class="dong-input-admin">
                                    <label>Người cho thuê xe</label>
                                    <select name="nguoi_cho_thue_id">
                                        <?php foreach ($danhSachLuaChonNguoiChoThue as $dongNguoiChoThue): ?>
                                            <option value="<?php echo (int) $dongNguoiChoThue["id"]; ?>" <?php echo isset($xeDangSua["nguoi_cho_thue_id"]) && (int) $xeDangSua["nguoi_cho_thue_id"] === (int) $dongNguoiChoThue["id"] ? "selected" : ""; ?>>
                                                <?php echo esc($dongNguoiChoThue["ho_ten"]); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                            <div class="dong-input-admin">
                                <label>Chi nhánh</label>
                                <select name="chi_nhanh_id" required>
                                    <?php foreach ($danhSachLuaChonChiNhanh as $dongChiNhanh): ?>
                                        <option value="<?php echo (int) $dongChiNhanh["id"]; ?>" <?php echo isset($xeDangSua["chi_nhanh_id"]) && (int) $xeDangSua["chi_nhanh_id"] === (int) $dongChiNhanh["id"] ? "selected" : ""; ?>>
                                            <?php echo esc($dongChiNhanh["ten_chi_nhanh"]); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php if ($xeDangSua): ?>
                                <div class="hop-vi-tri-admin">
                                    <strong>Vị trí xe hiện tại</strong>
                                    <?php if (($xeDangSua["vi_do_hien_tai"] ?? null) !== null && ($xeDangSua["kinh_do_hien_tai"] ?? null) !== null): ?>
                                        <?php echo esc($xeDangSua["vi_do_hien_tai"]); ?>, <?php echo esc($xeDangSua["kinh_do_hien_tai"]); ?>
                                        <div class="ghi-chu-nho-admin">Theo tọa độ hiện tại của xe trong hệ thống.</div>
                                        <a class="duong-link-admin" href="https://www.google.com/maps?q=<?php echo urlencode((string) $xeDangSua["vi_do_hien_tai"] . "," . (string) $xeDangSua["kinh_do_hien_tai"]); ?>" target="_blank" rel="noopener noreferrer">Mở trên bản đồ</a>
                                    <?php else: ?>
                                        <div class="ghi-chu-nho-admin">Xe này chưa được cập nhật vị trí hiện tại.</div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <div class="dong-input-admin">
                                <label>Trạng thái</label>
                                <select name="trang_thai">
                                    <?php foreach (["san_sang", "dang_thue", "bao_duong"] as $trangThaiXe): ?>
                                        <?php
                                        $tenTrangThaiXe = $trangThaiXe;
                                        if ($trangThaiXe === "san_sang") {
                                            $tenTrangThaiXe = "Sẵn sàng";
                                        } elseif ($trangThaiXe === "dang_thue") {
                                            $tenTrangThaiXe = "Đang thuê";
                                        } elseif ($trangThaiXe === "bao_duong") {
                                            $tenTrangThaiXe = "Bảo dưỡng";
                                        }
                                        ?>
                                        <option value="<?php echo $trangThaiXe; ?>" <?php echo (($xeDangSua["trang_thai"] ?? "san_sang") === $trangThaiXe) ? "selected" : ""; ?>>
                                            <?php echo esc($tenTrangThaiXe); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="dong-input-admin"><label>Mô tả</label><textarea name="mo_ta"><?php echo esc($xeDangSua["mo_ta"] ?? ""); ?></textarea></div>
                            <button class="nut-luu-admin" type="submit">Lưu xe</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($pageLayout === "trangChiNhanh"): ?>
                <div class="khung-2-cot-admin">
                    <div class="hop-bang-admin">
                        <h2>Danh sách chi nhánh</h2>
                        <div class="vung-bang-admin">
                            <table class="bang-admin">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tên chi nhánh</th>
                                        <th>Địa chỉ</th>
                                        <th>Tọa độ</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($danhSachChiNhanh as $dongChiNhanh): ?>
                                        <tr>
                                            <td><?php echo (int) $dongChiNhanh["id"]; ?></td>
                                            <td><?php echo esc($dongChiNhanh["ten_chi_nhanh"]); ?></td>
                                            <td><?php echo esc($dongChiNhanh["dia_chi"]); ?></td>
                                            <td><?php echo esc($dongChiNhanh["vi_do"]); ?>, <?php echo esc($dongChiNhanh["kinh_do"]); ?></td>
                                            <td class="dong-thao-tac-admin">
                                                <a href="./index.php?page_layout=trangChiNhanh&edit=<?php echo (int) $dongChiNhanh["id"]; ?>">Sửa</a> |
                                                <a href="./index.php?page_layout=trangChiNhanh&delete=branch&id=<?php echo (int) $dongChiNhanh["id"]; ?>" onclick="return confirm('Xóa chi nhánh?');">Xóa</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="hop-form-admin">
                        <h2><?php echo $chiNhanhDangSua ? "Cập nhật chi nhánh" : "Thêm chi nhánh"; ?></h2>
                        <form method="post">
                            <input type="hidden" name="form_type" value="branch_save">
                            <input type="hidden" name="id" value="<?php echo esc($chiNhanhDangSua["id"] ?? "0"); ?>">
                            <?php if ($laAdminTong): ?>
                                <div class="dong-input-admin">
                                    <label>Người cho thuê xe</label>
                                    <select name="nguoi_cho_thue_id">
                                        <?php foreach ($danhSachLuaChonNguoiChoThue as $dongNguoiChoThue): ?>
                                            <option value="<?php echo (int) $dongNguoiChoThue["id"]; ?>" <?php echo isset($chiNhanhDangSua["nguoi_cho_thue_id"]) && (int) $chiNhanhDangSua["nguoi_cho_thue_id"] === (int) $dongNguoiChoThue["id"] ? "selected" : ""; ?>>
                                                <?php echo esc($dongNguoiChoThue["ho_ten"]); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                            <div class="dong-input-admin"><label>Tên chi nhánh</label><input type="text" name="ten_chi_nhanh" value="<?php echo esc($chiNhanhDangSua["ten_chi_nhanh"] ?? ""); ?>" required></div>
                            <div class="dong-input-admin"><label>Địa chỉ</label><input type="text" name="dia_chi" value="<?php echo esc($chiNhanhDangSua["dia_chi"] ?? ""); ?>" required></div>
                            <div class="luoi-input-admin">
                                <div class="dong-input-admin"><label>Vĩ độ</label><input type="text" name="vi_do" value="<?php echo esc($chiNhanhDangSua["vi_do"] ?? ""); ?>" required></div>
                                <div class="dong-input-admin"><label>Kinh độ</label><input type="text" name="kinh_do" value="<?php echo esc($chiNhanhDangSua["kinh_do"] ?? ""); ?>" required></div>
                            </div>
                            <div class="dong-input-admin"><label>Mô tả</label><textarea name="mo_ta"><?php echo esc($chiNhanhDangSua["mo_ta"] ?? ""); ?></textarea></div>
                            <button class="nut-luu-admin" type="submit">Lưu chi nhánh</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($pageLayout === "trangNguoiDung" && $laAdminTong): ?>
                <div class="khung-2-cot-admin">
                    <div class="hop-bang-admin">
                        <h2>Danh sách tài khoản</h2>
                        <div class="vung-bang-admin">
                            <table class="bang-admin">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Họ tên</th>
                                        <th>Email</th>
                                        <th>Số điện thoại</th>
                                        <th>Địa chỉ</th>
                                        <th>Vai trò hệ thống</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($danhSachNguoiDung as $dongNguoiDung): ?>
                                        <tr>
                                            <td><?php echo (int) $dongNguoiDung["id"]; ?></td>
                                            <td><?php echo esc($dongNguoiDung["ho_ten"]); ?></td>
                                            <td><?php echo esc($dongNguoiDung["email"]); ?></td>
                                            <td><?php echo esc($dongNguoiDung["so_dien_thoai"]); ?></td>
                                            <td><?php echo esc($dongNguoiDung["dia_chi"]); ?></td>
                                            <td>
                                                <?php
                                                $tenVaiTroNguoiDung = (string) $dongNguoiDung["vai_tro"];
                                                if ($tenVaiTroNguoiDung === "admin") {
                                                    $tenVaiTroNguoiDung = "Admin tổng";
                                                } elseif ($tenVaiTroNguoiDung === "nguoi_cho_thue") {
                                                    $tenVaiTroNguoiDung = "Người cho thuê xe";
                                                } elseif ($tenVaiTroNguoiDung === "khach_hang") {
                                                    $tenVaiTroNguoiDung = "Khách thuê xe";
                                                }
                                                echo esc($tenVaiTroNguoiDung);
                                                ?>
                                            </td>
                                            <td class="dong-thao-tac-admin">
                                                <a href="./index.php?page_layout=trangNguoiDung&edit=<?php echo (int) $dongNguoiDung["id"]; ?>">Sửa</a> |
                                                <a href="./index.php?page_layout=trangNguoiDung&delete=user&id=<?php echo (int) $dongNguoiDung["id"]; ?>" onclick="return confirm('Xóa tài khoản này?');">Xóa</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="hop-form-admin">
                        <h2><?php echo $nguoiDungDangSua ? "Cập nhật tài khoản" : "Thêm khách thuê xe"; ?></h2>
                        <form method="post">
                            <input type="hidden" name="form_type" value="user_save">
                            <input type="hidden" name="id" value="<?php echo esc($nguoiDungDangSua["id"] ?? "0"); ?>">
                            <div class="dong-input-admin"><label>Họ tên</label><input type="text" name="ho_ten" value="<?php echo esc($nguoiDungDangSua["ho_ten"] ?? ""); ?>"></div>
                            <div class="dong-input-admin"><label>Email</label><input type="email" name="email" value="<?php echo esc($nguoiDungDangSua["email"] ?? ""); ?>"></div>
                            <div class="dong-input-admin"><label>Số điện thoại</label><input type="text" name="so_dien_thoai" value="<?php echo esc($nguoiDungDangSua["so_dien_thoai"] ?? ""); ?>"></div>
                            <div class="dong-input-admin"><label>Địa chỉ</label><input type="text" name="dia_chi" value="<?php echo esc($nguoiDungDangSua["dia_chi"] ?? ""); ?>"></div>
                            <div class="dong-input-admin"><label>Mật khẩu <?php echo $nguoiDungDangSua ? "(bỏ trống nếu không đổi)" : ""; ?></label><input type="text" name="mat_khau" value=""></div>
                            <div class="dong-input-admin">
                                <label>Vai trò hệ thống</label>
                                <select name="vai_tro">
                                    <option value="khach_hang" <?php echo (($nguoiDungDangSua["vai_tro"] ?? "khach_hang") === "khach_hang") ? "selected" : ""; ?>>Khách thuê xe</option>
                                    <option value="nguoi_cho_thue" <?php echo (($nguoiDungDangSua["vai_tro"] ?? "") === "nguoi_cho_thue") ? "selected" : ""; ?>>Người cho thuê xe</option>
                                    <option value="admin" <?php echo (($nguoiDungDangSua["vai_tro"] ?? "") === "admin") ? "selected" : ""; ?>>Admin tổng</option>
                                </select>
                            </div>
                            <button class="nut-luu-admin" type="submit">Lưu tài khoản</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($pageLayout === "trangDonDatXe"): ?>
                <div class="khung-2-cot-admin">
                    <div class="hop-bang-admin">
                        <h2>Quản lý đơn đặt xe</h2>
                        <div class="vung-bang-admin">
                            <table class="bang-admin">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Khách</th>
                                        <th>Xe</th>
                                        <th>Thời gian</th>
                                        <th>Địa chỉ khách</th>
                                        <th>Chi phí</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($danhSachDon as $dongDon): ?>
                                        <tr>
                                            <td>#<?php echo (int) $dongDon["id"]; ?></td>
                                            <td>
                                                <strong><?php echo esc($dongDon["ho_ten"]); ?></strong>
                                                <span class="dong-phu-admin"><?php echo esc($dongDon["so_dien_thoai"]); ?></span>
                                            </td>
                                            <td>
                                                <strong><?php echo esc($dongDon["ten_xe"]); ?></strong>
                                                <?php if (!empty($dongDon["bien_so"])): ?>
                                                    <span class="dong-phu-admin"><?php echo esc($dongDon["bien_so"]); ?></span>
                                                <?php endif; ?>
                                                <?php if (($dongDon["trang_thai"] ?? "") === "dang_thue" && $dongDon["vi_do_hien_tai"] !== null && $dongDon["kinh_do_hien_tai"] !== null): ?>
                                                    <span class="dong-phu-admin">Vị trí xe: <?php echo esc($dongDon["vi_do_hien_tai"]); ?>, <?php echo esc($dongDon["kinh_do_hien_tai"]); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo date("d/m/Y", strtotime($dongDon["ngay_nhan"])); ?>
                                                <span class="dong-phu-admin"><?php echo date("d/m/Y", strtotime($dongDon["ngay_tra"])); ?></span>
                                                <?php if (!empty($dongDon["thoi_diem_tra_thuc_te"])): ?>
                                                    <span class="dong-phu-admin">Trả thực tế: <?php echo date("d/m/Y", strtotime($dongDon["thoi_diem_tra_thuc_te"])); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo esc($dongDon["dia_chi_khach"]); ?></td>
                                            <td>
                                                <strong><?php echo format_money($dongDon["tong_tien"]); ?></strong>
                                                <?php if ((float) $dongDon["phi_phat_tra_muon"] > 0): ?>
                                                    <span class="dong-phu-admin">Phạt trễ: <?php echo format_money($dongDon["phi_phat_tra_muon"]); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $tenTrangThaiDon = (string) $dongDon["trang_thai"];
                                                $mauTrangThaiDon = "mau-duong";
                                                $moTaTrangThaiDon = "Trạng thái đang được cập nhật.";
                                                if ($dongDon["trang_thai"] === "cho_xac_nhan") {
                                                    $tenTrangThaiDon = "Chờ xác nhận";
                                                    $mauTrangThaiDon = "mau-cam";
                                                    $moTaTrangThaiDon = "Đơn đã gửi. Bạn đang chờ người cho thuê xe xác nhận.";
                                                } elseif ($dongDon["trang_thai"] === "da_xac_nhan") {
                                                    $tenTrangThaiDon = "Đã xác nhận";
                                                    $mauTrangThaiDon = "mau-xanh-duong";
                                                    $moTaTrangThaiDon = "Đơn đã được chấp nhận. Bạn có thể đến nhận xe theo lịch.";
                                                } elseif ($dongDon["trang_thai"] === "dang_thue") {
                                                    $tenTrangThaiDon = "Đang thuê";
                                                    $mauTrangThaiDon = "mau-duong";
                                                    $moTaTrangThaiDon = "Khách đang trong thời gian thuê xe.";
                                                } elseif ($dongDon["trang_thai"] === "hoan_thanh") {
                                                    $tenTrangThaiDon = "Hoàn thành";
                                                    $mauTrangThaiDon = "mau-xanh";
                                                    $moTaTrangThaiDon = "Đơn đã hoàn tất và xe đã được trả.";
                                                } elseif ($dongDon["trang_thai"] === "da_huy") {
                                                    $tenTrangThaiDon = "Đã hủy";
                                                    $mauTrangThaiDon = "mau-do";
                                                    $moTaTrangThaiDon = "Đơn đã bị hủy hoặc không tiếp tục xử lý.";
                                                }
                                                ?>
                                                <span class="nhan-admin <?php echo esc($mauTrangThaiDon); ?>">
                                                    <?php echo esc($tenTrangThaiDon); ?>
                                                </span>
                                                <?php if (!$laAdminTong): ?>
                                                    <span class="dong-phu-admin"><?php echo esc($moTaTrangThaiDon); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="dong-thao-tac-admin">
                                                <a href="./index.php?page_layout=trangDonDatXe&edit=<?php echo (int) $dongDon["id"]; ?>"><?php echo $laAdminTong ? "Sửa" : "Xử lý"; ?></a>
                                                <?php if ($laAdminTong): ?>
                                                    | <a href="./index.php?page_layout=trangDonDatXe&delete=booking&id=<?php echo (int) $dongDon["id"]; ?>" onclick="return confirm('Xóa đơn này?');">Xóa</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="hop-form-admin">
                        <h2><?php echo $donDangSua ? ($laAdminTong ? "Cập nhật đơn thuê" : "Xử lý đơn thuê") : "Đơn thuê được tạo bởi khách"; ?></h2>
                        <?php if (!$donDangSua): ?>
                            <div class="ghi-chu-nho-admin" style="margin-bottom: 16px;">Khách thuê xe sẽ tạo đơn từ giao diện đặt xe. Tại đây bạn chỉ xem và cập nhật trạng thái đơn đã có.</div>
                        <?php endif; ?>
                        <form method="post">
                            <input type="hidden" name="form_type" value="booking_save">
                            <input type="hidden" name="id" value="<?php echo esc($donDangSua["id"] ?? "0"); ?>">
                            <?php if ($donDangSua): ?>
                                <div class="dong-input-admin">
                                    <label>Khách thuê</label>
                                    <?php if ($laAdminTong): ?>
                                        <select name="nguoi_dung_id" required>
                                            <?php foreach ($danhSachLuaChonKhach as $dongKhach): ?>
                                                <option value="<?php echo (int) $dongKhach["id"]; ?>" <?php echo isset($donDangSua["nguoi_dung_id"]) && (int) $donDangSua["nguoi_dung_id"] === (int) $dongKhach["id"] ? "selected" : ""; ?>>
                                                    <?php echo esc($dongKhach["ho_ten"]); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else: ?>
                                        <input type="hidden" name="nguoi_dung_id" value="<?php echo esc($donDangSua["nguoi_dung_id"]); ?>">
                                        <input type="text" value="<?php echo esc($donDangSua["nguoi_dung_id"]); ?> - khách trong đơn hiện tại" disabled>
                                    <?php endif; ?>
                                </div>

                                <div class="dong-input-admin">
                                    <label>Xe</label>
                                    <?php if ($laAdminTong): ?>
                                        <select name="xe_id" required>
                                            <?php foreach ($danhSachLuaChonXe as $dongXe): ?>
                                                <option value="<?php echo (int) $dongXe["id"]; ?>" <?php echo isset($donDangSua["xe_id"]) && (int) $donDangSua["xe_id"] === (int) $dongXe["id"] ? "selected" : ""; ?>>
                                                    <?php echo esc($dongXe["ten_xe"] . " - " . $dongXe["bien_so"]); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else: ?>
                                        <input type="hidden" name="xe_id" value="<?php echo esc($donDangSua["xe_id"]); ?>">
                                        <input type="text" value="<?php echo esc($donDangSua["xe_id"]); ?> - xe trong đơn hiện tại" disabled>
                                    <?php endif; ?>
                                </div>

                                <div class="dong-input-admin">
                                    <label>Chi nhánh nhận xe</label>
                                    <?php if ($laAdminTong): ?>
                                        <select name="chi_nhanh_nhan_id" required>
                                            <?php foreach ($danhSachLuaChonChiNhanh as $dongChiNhanh): ?>
                                                <option value="<?php echo (int) $dongChiNhanh["id"]; ?>" <?php echo isset($donDangSua["chi_nhanh_nhan_id"]) && (int) $donDangSua["chi_nhanh_nhan_id"] === (int) $dongChiNhanh["id"] ? "selected" : ""; ?>>
                                                    <?php echo esc($dongChiNhanh["ten_chi_nhanh"]); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else: ?>
                                        <input type="hidden" name="chi_nhanh_nhan_id" value="<?php echo esc($donDangSua["chi_nhanh_nhan_id"]); ?>">
                                        <input type="text" value="<?php echo esc($donDangSua["chi_nhanh_nhan_id"]); ?> - chi nhánh nhận xe" disabled>
                                    <?php endif; ?>
                                </div>

                                <div class="dong-input-admin">
                                    <label>Chi nhánh trả xe</label>
                                    <?php if ($laAdminTong): ?>
                                        <select name="chi_nhanh_tra_id" required>
                                            <?php foreach ($danhSachLuaChonChiNhanh as $dongChiNhanh): ?>
                                                <option value="<?php echo (int) $dongChiNhanh["id"]; ?>" <?php echo isset($donDangSua["chi_nhanh_tra_id"]) && (int) $donDangSua["chi_nhanh_tra_id"] === (int) $dongChiNhanh["id"] ? "selected" : ""; ?>>
                                                    <?php echo esc($dongChiNhanh["ten_chi_nhanh"]); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else: ?>
                                        <input type="hidden" name="chi_nhanh_tra_id" value="<?php echo esc($donDangSua["chi_nhanh_tra_id"]); ?>">
                                        <input type="text" value="<?php echo esc($donDangSua["chi_nhanh_tra_id"]); ?> - chi nhánh trả xe" disabled>
                                    <?php endif; ?>
                                </div>

                                <div class="dong-input-admin">
                                    <label>Địa chỉ khách</label>
                                    <input type="hidden" name="dia_chi_khach" value="<?php echo esc($donDangSua["dia_chi_khach"] ?? ""); ?>">
                                    <input type="text" value="<?php echo esc($donDangSua["dia_chi_khach"] ?? ""); ?>" disabled>
                                    <small class="ghi-chu-nho-admin">Vị trí khách được ghi từ lúc đặt xe và không chỉnh sửa tại dashboard.</small>
                                </div>

                                <div class="hop-vi-tri-admin">
                                    <strong>Vị trí xe hiện tại</strong>
                                    <?php if ($donDangSua["vi_do_hien_tai"] !== null && $donDangSua["kinh_do_hien_tai"] !== null): ?>
                                        <?php echo esc($donDangSua["vi_do_hien_tai"]); ?>, <?php echo esc($donDangSua["kinh_do_hien_tai"]); ?>
                                        <div class="ghi-chu-nho-admin">Theo tọa độ hiện tại của xe trong hệ thống.</div>
                                        <a class="duong-link-admin" href="https://www.google.com/maps?q=<?php echo urlencode((string) $donDangSua["vi_do_hien_tai"] . "," . (string) $donDangSua["kinh_do_hien_tai"]); ?>" target="_blank" rel="noopener noreferrer">Mở trên bản đồ</a>
                                    <?php else: ?>
                                        <div class="ghi-chu-nho-admin">Xe này chưa được cập nhật vị trí hiện tại.</div>
                                    <?php endif; ?>
                                </div>

                                <div class="luoi-input-admin">
                                    <div class="dong-input-admin">
                                        <label>Vĩ độ khách</label>
                                        <input type="hidden" name="vi_do_khach" value="<?php echo esc($donDangSua["vi_do_khach"] ?? ""); ?>">
                                        <input type="text" value="<?php echo esc($donDangSua["vi_do_khach"] ?? ""); ?>" disabled>
                                    </div>
                                    <div class="dong-input-admin">
                                        <label>Kinh độ khách</label>
                                        <input type="hidden" name="kinh_do_khach" value="<?php echo esc($donDangSua["kinh_do_khach"] ?? ""); ?>">
                                        <input type="text" value="<?php echo esc($donDangSua["kinh_do_khach"] ?? ""); ?>" disabled>
                                    </div>
                                </div>

                                <div class="luoi-input-admin">
                                    <div class="dong-input-admin">
                                        <label>Ngày nhận</label>
                                        <?php if ($laAdminTong): ?>
                                            <input type="date" name="ngay_nhan" value="<?php echo isset($donDangSua["ngay_nhan"]) ? date("Y-m-d", strtotime($donDangSua["ngay_nhan"])) : ""; ?>" required>
                                        <?php else: ?>
                                            <input type="hidden" name="ngay_nhan" value="<?php echo isset($donDangSua["ngay_nhan"]) ? date("Y-m-d", strtotime($donDangSua["ngay_nhan"])) : ""; ?>">
                                            <input type="text" value="<?php echo isset($donDangSua["ngay_nhan"]) ? date("d/m/Y", strtotime($donDangSua["ngay_nhan"])) : ""; ?>" disabled>
                                        <?php endif; ?>
                                    </div>
                                    <div class="dong-input-admin">
                                        <label>Ngày trả</label>
                                        <?php if ($laAdminTong): ?>
                                            <input type="date" name="ngay_tra" value="<?php echo isset($donDangSua["ngay_tra"]) ? date("Y-m-d", strtotime($donDangSua["ngay_tra"])) : ""; ?>" required>
                                        <?php else: ?>
                                            <input type="hidden" name="ngay_tra" value="<?php echo isset($donDangSua["ngay_tra"]) ? date("Y-m-d", strtotime($donDangSua["ngay_tra"])) : ""; ?>">
                                            <input type="text" value="<?php echo isset($donDangSua["ngay_tra"]) ? date("d/m/Y", strtotime($donDangSua["ngay_tra"])) : ""; ?>" disabled>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="luoi-input-admin">
                                    <div class="dong-input-admin">
                                        <label>Tổng tiền</label>
                                        <?php if ($laAdminTong): ?>
                                            <input type="number" name="tong_tien" value="<?php echo esc($donDangSua["tong_tien"] ?? "0"); ?>" required>
                                        <?php else: ?>
                                            <input type="hidden" name="tong_tien" value="<?php echo esc($donDangSua["tong_tien"] ?? "0"); ?>">
                                            <input type="text" value="<?php echo esc($donDangSua["tong_tien"] ?? "0"); ?>" disabled>
                                        <?php endif; ?>
                                    </div>
                                    <div class="dong-input-admin">
                                        <label>Phí phạt trả muộn</label>
                                        <?php if ($laAdminTong): ?>
                                            <input type="number" name="phi_phat_tra_muon" value="<?php echo esc($donDangSua["phi_phat_tra_muon"] ?? "0"); ?>" step="1000">
                                        <?php else: ?>
                                            <input type="number" name="phi_phat_tra_muon" value="<?php echo esc($donDangSua["phi_phat_tra_muon"] ?? "0"); ?>" readonly>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="dong-input-admin">
                                    <label>Thời điểm trả thực tế</label>
                                    <input type="date" name="thoi_diem_tra_thuc_te" value="<?php echo !empty($donDangSua["thoi_diem_tra_thuc_te"]) ? date("Y-m-d", strtotime($donDangSua["thoi_diem_tra_thuc_te"])) : ""; ?>">
                                    <small class="ghi-chu-nho-admin">Nhập khi xe đã được trả. Hệ thống sẽ tự tính phạt nếu quá hạn.</small>
                                </div>

                                <div class="dong-input-admin">
                                    <label>Trạng thái</label>
                                    <select name="trang_thai">
                                        <?php foreach (["cho_xac_nhan", "da_xac_nhan", "dang_thue", "hoan_thanh", "da_huy"] as $trangThaiDon): ?>
                                            <?php
                                            $tenTrangThaiDon = $trangThaiDon;
                                            if ($trangThaiDon === "cho_xac_nhan") {
                                                $tenTrangThaiDon = "Chờ xác nhận";
                                            } elseif ($trangThaiDon === "da_xac_nhan") {
                                                $tenTrangThaiDon = "Đã xác nhận";
                                            } elseif ($trangThaiDon === "dang_thue") {
                                                $tenTrangThaiDon = "Đang thuê";
                                            } elseif ($trangThaiDon === "hoan_thanh") {
                                                $tenTrangThaiDon = "Hoàn thành";
                                            } elseif ($trangThaiDon === "da_huy") {
                                                $tenTrangThaiDon = "Đã hủy";
                                            }
                                            ?>
                                            <option value="<?php echo $trangThaiDon; ?>" <?php echo (($donDangSua["trang_thai"] ?? "cho_xac_nhan") === $trangThaiDon) ? "selected" : ""; ?>>
                                                <?php echo esc($tenTrangThaiDon); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (!$laAdminTong): ?>
                                        <small class="ghi-chu-nho-admin">Luồng xử lý: Chờ xác nhận -> Đã xác nhận -> Đang thuê -> Hoàn thành. Đơn có thể hủy trước khi giao xe.</small>
                                    <?php endif; ?>
                                </div>

                                <div class="dong-input-admin"><label>Ghi chú</label><textarea name="ghi_chu"><?php echo esc($donDangSua["ghi_chu"] ?? ""); ?></textarea></div>
                                <button class="nut-luu-admin" type="submit"><?php echo $laAdminTong ? "Lưu đơn thuê" : "Cập nhật trạng thái"; ?></button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
