<?php
session_start();
include "./includes/connect.php";
include "./includes/bootstrap.php";
/** @var mysqli $conn */

$chuyenTrang = $_GET["chuyen_trang"] ?? ($_GET["page"] ?? "trangChu");

$cacTrangCanDangNhap = ["dat_xe", "datXe", "don_cua_toi", "donCuaToi", "tai_khoan", "taiKhoan"];
if (in_array($chuyenTrang, $cacTrangCanDangNhap, true) && !isset($_SESSION["hoTen"], $_SESSION["vaiTro"], $_SESSION["idNguoiDung"])) {
    header("Location: ./auth/dangNhap.php");
    exit;
}

if (($chuyenTrang === "don_cua_toi" || $chuyenTrang === "donCuaToi") && isset($_GET["cancel"], $_SESSION["idNguoiDung"])) {
    $idDon = (int) $_GET["cancel"];
    $idNguoiDung = (int) $_SESSION["idNguoiDung"];
    mysqli_query($conn, "UPDATE dat_xe
        SET trang_thai = 'da_huy'
        WHERE id = $idDon
        AND nguoi_dung_id = $idNguoiDung
        AND trang_thai IN ('cho_xac_nhan', 'da_xac_nhan')");
    header("Location: ./index.php?chuyen_trang=donCuaToi");
    exit;
}

include "./includes/header.php";

switch ($chuyenTrang) {
    case "xe":
    case "danhSachXe":
        include "./cars/danhSachXe.php";
        break;
    case "chi_tiet_xe":
    case "chiTietXe":
        include "./cars/chiTietXe.php";
        break;
    case "ban_do_van_hanh":
    case "banDoVanHanh":
        include "./banDoVanHanh.php";
        break;
    case "dat_xe":
    case "datXe":
        include "./bookings/datXe.php";
        break;
    case "don_cua_toi":
    case "donCuaToi":
        include "./bookings/donCuaToi.php";
        break;
    case "chi_nhanh":
    case "chiNhanh":
        include "./branches/chiNhanh.php";
        break;
    case "tai_khoan":
    case "taiKhoan":
        include "./taiKhoan.php";
        break;
    default:
        include "./trangChu.php";
        break;
}

include "./includes/footer.php";
?>
