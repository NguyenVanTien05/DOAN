<?php
session_start();
include "../includes/connect.php";
include "../includes/bootstrap.php";
/** @var mysqli $conn */
$thongBaoLoi = "";

if (isset($_SESSION["hoTen"], $_SESSION["vaiTro"])) {
    if ($_SESSION["vaiTro"] === "admin" || $_SESSION["vaiTro"] === "nguoi_cho_thue") {
        header("Location: ./index.php");
    } else {
        header("Location: ../index.php?chuyen_trang=trangChu");
    }
    exit;
}

if (isset($_POST["email"])) {
    $email = mysqli_real_escape_string($conn, trim($_POST["email"] ?? ""));
    $matKhau = trim($_POST["mat_khau"] ?? "");
    $sqlDangNhap = "SELECT * FROM nguoi_dung WHERE email = '$email' LIMIT 1";
    $ketQuaDangNhap = mysqli_query($conn, $sqlDangNhap);

    if (!$ketQuaDangNhap || mysqli_num_rows($ketQuaDangNhap) === 0) {
        $thongBaoLoi = "Tài khoản không tồn tại.";
    } else {
        $nguoiDung = mysqli_fetch_assoc($ketQuaDangNhap);

        if (($nguoiDung["vai_tro"] ?? "") !== "admin" && ($nguoiDung["vai_tro"] ?? "") !== "nguoi_cho_thue") {
            $thongBaoLoi = "Tài khoản này chỉ được sử dụng ở khu đặt xe dành cho khách hàng.";
        } elseif ($matKhau !== $nguoiDung["mat_khau"] && !password_verify($matKhau, $nguoiDung["mat_khau"])) {
            $thongBaoLoi = "Mật khẩu không chính xác.";
        } else {
            $_SESSION["hoTen"] = $nguoiDung["ho_ten"];
            $_SESSION["idNguoiDung"] = $nguoiDung["id"];
            $_SESSION["vaiTro"] = $nguoiDung["vai_tro"];
            header("Location: ./index.php");
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
    <title>Đăng nhập vận hành | CarRent</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .trang-dang-nhap-van-hanh {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .khung-dang-nhap-van-hanh {
            width: min(520px, 100%);
            padding: 30px;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 16px 40px rgba(17, 31, 53, 0.08);
        }

        .khung-dang-nhap-van-hanh p {
            margin: 0;
            color: var(--muted-color);
        }

        .khung-dang-nhap-van-hanh h1 {
            margin: 8px 0 12px;
            font-size: 32px;
        }

        .dong-nhap-van-hanh {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 16px;
        }

        .dong-nhap-van-hanh label {
            font-weight: 600;
        }

        .dong-nhap-van-hanh input {
            width: 100%;
            padding: 13px 14px;
            border-radius: 14px;
            border: 1px solid #d9e3ef;
            font-family: inherit;
        }

        .cum-nut-van-hanh {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .nut-vao-dashboard,
        .nut-ve-trang-khach {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 18px;
            border-radius: 14px;
            font-weight: 700;
        }

        .nut-vao-dashboard {
            background: var(--primary-color);
            color: #ffffff;
            box-shadow: 0 10px 20px rgba(7, 165, 254, 0.22);
        }

        .nut-ve-trang-khach {
            border: 1px solid #dbe5f2;
            background: #ffffff;
            color: var(--text-color);
        }

        .thong-bao-loi-van-hanh {
            margin: 18px 0;
            padding: 14px 16px;
            border-radius: 16px;
            background: rgba(255, 95, 95, 0.12);
            color: #cf3636;
        }
    </style>
</head>
<body>
    <div class="trang-dang-nhap-van-hanh">
        <div class="khung-dang-nhap-van-hanh">
            <p>Khu vận hành</p>
            <h1>Đăng nhập quản trị</h1>
            <p>Trang này dành cho admin tổng và người cho thuê xe. Khu đặt xe công khai đã được tách riêng cho khách hàng.</p>

            <?php if ($thongBaoLoi !== ""): ?>
                <div class="thong-bao-loi-van-hanh"><?php echo esc($thongBaoLoi); ?></div>
            <?php endif; ?>

            <form action="" method="post">
                <div class="dong-nhap-van-hanh">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="dong-nhap-van-hanh">
                    <label>Mật khẩu</label>
                    <input type="password" name="mat_khau" required>
                </div>
                <div class="cum-nut-van-hanh">
                    <button class="nut-vao-dashboard" type="submit">Vào dashboard</button>
                    <a class="nut-ve-trang-khach" href="../index.php?chuyen_trang=trangChu">Về trang đặt xe</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
