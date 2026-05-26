<?php
session_start();
include "../includes/connect.php";
include "../includes/bootstrap.php";
/** @var mysqli $conn */
$thongBaoLoi = "";

if (isset($_SESSION["hoTen"], $_SESSION["vaiTro"])) {
    if ($_SESSION["vaiTro"] === "admin" || $_SESSION["vaiTro"] === "nguoi_cho_thue") {
        header("Location: ../admin/index.php");
    } else {
        header("Location: ../index.php?chuyen_trang=trangChu");
    }
    exit;
}

if (isset($_POST["email"])) {
    $email = mysqli_real_escape_string($conn, trim($_POST["email"]));
    $matKhau = trim($_POST["mat_khau"] ?? "");
    $sqlDangNhap = "SELECT * FROM nguoi_dung WHERE email = '$email' LIMIT 1";
    $ketQuaDangNhap = mysqli_query($conn, $sqlDangNhap);

    if (!$ketQuaDangNhap || mysqli_num_rows($ketQuaDangNhap) === 0) {
        $thongBaoLoi = "Tài khoản không tồn tại.";
    } else {
        $nguoiDung = mysqli_fetch_assoc($ketQuaDangNhap);

        if (($nguoiDung["vai_tro"] ?? "") !== "khach_hang") {
            $thongBaoLoi = "Tài khoản vận hành sử dụng khu đăng nhập riêng tại /admin/dangNhap.php.";
        } elseif ($matKhau !== $nguoiDung["mat_khau"] && !password_verify($matKhau, $nguoiDung["mat_khau"])) {
            $thongBaoLoi = "Mật khẩu không chính xác.";
        } else {
            $_SESSION["hoTen"] = $nguoiDung["ho_ten"];
            $_SESSION["idNguoiDung"] = $nguoiDung["id"];
            $_SESSION["vaiTro"] = $nguoiDung["vai_tro"];
            header("Location: ../index.php?chuyen_trang=trangChu");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập đặt xe | CarRent</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .trang-dang-nhap-khach {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .khung-dang-nhap-khach {
            width: min(520px, 100%);
            padding: 30px;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 16px 40px rgba(17, 31, 53, 0.08);
        }

        .khung-dang-nhap-khach p {
            margin: 0;
            color: var(--muted-color);
        }

        .khung-dang-nhap-khach h1 {
            margin: 8px 0 12px;
            font-size: 32px;
        }

        .dong-nhap-khach {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 16px;
        }

        .dong-nhap-khach label {
            font-weight: 600;
        }

        .dong-nhap-khach input {
            width: 100%;
            padding: 13px 14px;
            border-radius: 14px;
            border: 1px solid #d9e3ef;
            font-family: inherit;
        }

        .cum-nut-dang-nhap-khach {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .nut-dang-nhap-khach,
        .nut-quay-lai-khach {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 18px;
            border-radius: 14px;
            font-weight: 700;
        }

        .nut-dang-nhap-khach {
            background: var(--primary-color);
            color: #ffffff;
            box-shadow: 0 10px 20px rgba(7, 165, 254, 0.22);
        }

        .nut-quay-lai-khach {
            border: 1px solid #dbe5f2;
            background: #ffffff;
            color: var(--text-color);
        }

        .thong-bao-loi-khach {
            margin: 18px 0;
            padding: 14px 16px;
            border-radius: 16px;
            background: rgba(255, 95, 95, 0.12);
            color: #cf3636;
        }

        .ghi-chu-dang-nhap-khach {
            margin-top: 18px;
            color: var(--muted-color);
            line-height: 1.7;
        }
    </style>
</head>
<body>
    <div class="trang-dang-nhap-khach">
        <div class="khung-dang-nhap-khach">
            <p>CarRent</p>
            <h1>Đăng nhập để đặt xe</h1>
            <p>Trang này chỉ dành cho khách thuê xe: xem đơn của tôi, cập nhật hồ sơ và tiếp tục đặt xe nhanh hơn.</p>

            <?php if ($thongBaoLoi !== ""): ?>
                <div class="thong-bao-loi-khach"><?php echo esc($thongBaoLoi); ?></div>
            <?php endif; ?>

            <form action="" method="post">
                <div class="dong-nhap-khach">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="dong-nhap-khach">
                    <label>Mật khẩu</label>
                    <input type="password" name="mat_khau" required>
                </div>
                <div class="cum-nut-dang-nhap-khach">
                    <button class="nut-dang-nhap-khach" type="submit">Đăng nhập</button>
                    <a class="nut-quay-lai-khach" href="../index.php?chuyen_trang=trangChu">Quay lại</a>
                </div>
            </form>

            <div class="ghi-chu-dang-nhap-khach">Đăng ký mới sẽ tạo tài khoản khách thuê xe. Tài khoản vận hành được đăng nhập tại khu riêng.</div>
        </div>
    </div>
</body>
</html>
