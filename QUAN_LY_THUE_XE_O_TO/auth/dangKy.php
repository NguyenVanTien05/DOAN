<?php
session_start();
include "../includes/connect.php";
include "../includes/bootstrap.php";
/** @var mysqli $conn */
$thongBaoLoi = "";
$thongBaoThanhCong = "";

if (isset($_SESSION["hoTen"], $_SESSION["vaiTro"])) {
    if ($_SESSION["vaiTro"] === "admin" || $_SESSION["vaiTro"] === "nguoi_cho_thue") {
        header("Location: ../admin/index.php");
    } else {
        header("Location: ../index.php?chuyen_trang=trangChu");
    }
    exit;
}

if (isset($_POST["ho_ten"])) {
    $hoTen = mysqli_real_escape_string($conn, trim($_POST["ho_ten"] ?? ""));
    $email = mysqli_real_escape_string($conn, trim($_POST["email"] ?? ""));
    $matKhau = trim($_POST["mat_khau"] ?? "");
    $soDienThoai = mysqli_real_escape_string($conn, trim($_POST["so_dien_thoai"] ?? ""));
    $diaChi = mysqli_real_escape_string($conn, trim($_POST["dia_chi"] ?? ""));
    $cccd = mysqli_real_escape_string($conn, trim($_POST["cccd"] ?? ""));

    $sqlKiemTraEmail = "SELECT COUNT(*) AS tong_email FROM nguoi_dung WHERE email = '$email'";
    $ketQuaKiemTraEmail = mysqli_query($conn, $sqlKiemTraEmail);
    $dongKiemTraEmail = $ketQuaKiemTraEmail ? mysqli_fetch_assoc($ketQuaKiemTraEmail) : ["tong_email" => 0];

    if ((int) $dongKiemTraEmail["tong_email"] > 0) {
        $thongBaoLoi = "Email đã tồn tại.";
    } else {
        $matKhauDaMaHoa = password_hash($matKhau, PASSWORD_DEFAULT);
        $sqlThemNguoiDung = "INSERT INTO nguoi_dung (
                ho_ten, email, mat_khau, so_dien_thoai, dia_chi, cccd, vai_tro, created_at
            ) VALUES (
                '$hoTen', '$email', '$matKhauDaMaHoa', '$soDienThoai', '$diaChi', '$cccd', 'khach_hang', NOW()
            )";

        if (mysqli_query($conn, $sqlThemNguoiDung)) {
            $thongBaoThanhCong = "Đăng ký thành công. Vui lòng đăng nhập.";
        } else {
            $thongBaoLoi = "Không thể tạo tài khoản.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký | CarRent</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .trang-dang-ky-khach {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .khung-dang-ky-khach {
            width: min(560px, 100%);
            padding: 30px;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 16px 40px rgba(17, 31, 53, 0.08);
        }

        .khung-dang-ky-khach p {
            margin: 0;
            color: var(--muted-color);
        }

        .khung-dang-ky-khach h1 {
            margin: 8px 0 12px;
            font-size: 32px;
        }

        .dong-nhap-dang-ky {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 16px;
        }

        .dong-nhap-dang-ky label {
            font-weight: 600;
        }

        .dong-nhap-dang-ky input {
            width: 100%;
            padding: 13px 14px;
            border-radius: 14px;
            border: 1px solid #d9e3ef;
            font-family: inherit;
        }

        .cum-nut-dang-ky-khach {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .nut-dang-ky-khach,
        .nut-dang-nhap-sau-dang-ky {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 18px;
            border-radius: 14px;
            font-weight: 700;
        }

        .nut-dang-ky-khach {
            background: var(--primary-color);
            color: #ffffff;
            box-shadow: 0 10px 20px rgba(7, 165, 254, 0.22);
        }

        .nut-dang-nhap-sau-dang-ky {
            border: 1px solid #dbe5f2;
            background: #ffffff;
            color: var(--text-color);
        }

        .thong-bao-loi-dang-ky,
        .thong-bao-thanh-cong-dang-ky {
            margin: 18px 0;
            padding: 14px 16px;
            border-radius: 16px;
        }

        .thong-bao-loi-dang-ky {
            background: rgba(255, 95, 95, 0.12);
            color: #cf3636;
        }

        .thong-bao-thanh-cong-dang-ky {
            background: rgba(22, 196, 127, 0.12);
            color: #0f8d5c;
        }
    </style>
</head>
<body>
    <div class="trang-dang-ky-khach">
        <div class="khung-dang-ky-khach">
            <p>Tạo tài khoản</p>
            <h1>Đăng ký khách thuê xe</h1>
            <p>Trang này chỉ dành cho người đi thuê xe. Nhân sự vận hành sử dụng khu đăng nhập riêng.</p>

            <?php if ($thongBaoLoi !== ""): ?>
                <div class="thong-bao-loi-dang-ky"><?php echo esc($thongBaoLoi); ?></div>
            <?php endif; ?>

            <?php if ($thongBaoThanhCong !== ""): ?>
                <div class="thong-bao-thanh-cong-dang-ky"><?php echo esc($thongBaoThanhCong); ?></div>
            <?php endif; ?>

            <form action="" method="post">
                <div class="dong-nhap-dang-ky">
                    <label>Họ tên</label>
                    <input type="text" name="ho_ten" required>
                </div>
                <div class="dong-nhap-dang-ky">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="dong-nhap-dang-ky">
                    <label>Mật khẩu</label>
                    <input type="password" name="mat_khau" required>
                </div>
                <div class="dong-nhap-dang-ky">
                    <label>Số điện thoại</label>
                    <input type="text" name="so_dien_thoai">
                </div>
                <div class="dong-nhap-dang-ky">
                    <label>Địa chỉ</label>
                    <input type="text" name="dia_chi">
                </div>
                <div class="dong-nhap-dang-ky">
                    <label>CCCD</label>
                    <input type="text" name="cccd">
                </div>
                <div class="cum-nut-dang-ky-khach">
                    <button class="nut-dang-ky-khach" type="submit">Đăng ký</button>
                    <a class="nut-dang-nhap-sau-dang-ky" href="./dangNhap.php">Đăng nhập</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
