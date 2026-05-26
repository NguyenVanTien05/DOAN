<?php
/** @var mysqli $conn */
if (!isset($_SESSION["hoTen"], $_SESSION["vaiTro"], $_SESSION["idNguoiDung"])) {
    header("Location: ./auth/dangNhap.php");
    exit;
}

$idNguoiDung = (int) $_SESSION["idNguoiDung"];
$thongBao = "";
$sqlNguoiDung = "SELECT * FROM nguoi_dung WHERE id = $idNguoiDung LIMIT 1";
$ketQuaNguoiDung = mysqli_query($conn, $sqlNguoiDung);
$nguoiDung = $ketQuaNguoiDung ? mysqli_fetch_assoc($ketQuaNguoiDung) : null;

if (!$nguoiDung) {
    header("Location: ./auth/dangXuat.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["ho_ten"])) {
    $hoTen = mysqli_real_escape_string($conn, trim($_POST["ho_ten"] ?? ""));
    $soDienThoai = mysqli_real_escape_string($conn, trim($_POST["so_dien_thoai"] ?? ""));
    $diaChi = mysqli_real_escape_string($conn, trim($_POST["dia_chi"] ?? ""));
    $cccd = mysqli_real_escape_string($conn, trim($_POST["cccd"] ?? ""));

    $sqlCapNhat = "UPDATE nguoi_dung
        SET ho_ten = '$hoTen',
            so_dien_thoai = '$soDienThoai',
            dia_chi = '$diaChi',
            cccd = '$cccd'
        WHERE id = $idNguoiDung";
    mysqli_query($conn, $sqlCapNhat);

    $_SESSION["hoTen"] = $hoTen;
    $ketQuaNguoiDung = mysqli_query($conn, $sqlNguoiDung);
    $nguoiDung = $ketQuaNguoiDung ? mysqli_fetch_assoc($ketQuaNguoiDung) : $nguoiDung;
    $thongBao = "Cập nhật tài khoản thành công.";
}
?>

<style>
    .khung-trang-tai-khoan {
        padding: 34px 0 12px;
    }

    .hop-tai-khoan {
        padding: 30px;
        border-radius: 24px;
        background: #ffffff;
        box-shadow: 0 16px 40px rgba(17, 31, 53, 0.08);
    }

    .dong-gioi-thieu-tai-khoan {
        margin-bottom: 22px;
    }

    .dong-gioi-thieu-tai-khoan p {
        margin: 0;
        color: var(--muted-color);
    }

    .dong-gioi-thieu-tai-khoan h2 {
        margin: 8px 0 10px;
        font-size: 30px;
    }

    .luoi-thong-tin-tai-khoan {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .o-nhap-tai-khoan {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .o-nhap-tai-khoan label {
        font-weight: 600;
        color: #223042;
    }

    .o-nhap-tai-khoan input {
        width: 100%;
        padding: 13px 14px;
        border-radius: 14px;
        border: 1px solid #d9e3ef;
        background: #ffffff;
        font-size: 15px;
    }

    .o-nhap-tai-khoan input[disabled] {
        background: #f6f9fd;
        color: #647184;
    }

    .nut-luu-tai-khoan {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-top: 22px;
        padding: 12px 20px;
        border-radius: 14px;
        border: 0;
        background: var(--primary-color);
        color: #ffffff;
        font-weight: 700;
        box-shadow: 0 10px 20px rgba(7, 165, 254, 0.22);
    }

    .thong-bao-thanh-cong {
        margin-bottom: 18px;
        padding: 14px 16px;
        border-radius: 16px;
        background: rgba(22, 196, 127, 0.12);
        color: #0f8d5c;
    }

    @media (max-width: 860px) {
        .hop-tai-khoan {
            padding: 22px;
        }

        .luoi-thong-tin-tai-khoan {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="khung-trang-tai-khoan">
    <div class="main__content">
        <div class="hop-tai-khoan">
            <div class="dong-gioi-thieu-tai-khoan">
                <p>Tài khoản</p>
                <h2>Thông tin cá nhân</h2>
                <p>Cập nhật hồ sơ để đặt xe nhanh hơn và nhận xe thuận tiện hơn.</p>
            </div>

            <?php if ($thongBao !== ""): ?>
                <div class="thong-bao-thanh-cong"><?php echo esc($thongBao); ?></div>
            <?php endif; ?>

            <form action="" method="post">
                <div class="luoi-thong-tin-tai-khoan">
                    <div class="o-nhap-tai-khoan">
                        <label>Họ tên</label>
                        <input type="text" name="ho_ten" value="<?php echo esc($nguoiDung["ho_ten"]); ?>" required>
                    </div>
                    <div class="o-nhap-tai-khoan">
                        <label>Email</label>
                        <input type="email" value="<?php echo esc($nguoiDung["email"]); ?>" disabled>
                    </div>
                    <div class="o-nhap-tai-khoan">
                        <label>Số điện thoại</label>
                        <input type="text" name="so_dien_thoai" value="<?php echo esc($nguoiDung["so_dien_thoai"]); ?>">
                    </div>
                    <div class="o-nhap-tai-khoan">
                        <label>Địa chỉ</label>
                        <input type="text" name="dia_chi" value="<?php echo esc($nguoiDung["dia_chi"]); ?>">
                    </div>
                    <div class="o-nhap-tai-khoan">
                        <label>CCCD</label>
                        <input type="text" name="cccd" value="<?php echo esc($nguoiDung["cccd"]); ?>">
                    </div>
                </div>
                <button class="nut-luu-tai-khoan" type="submit">Lưu thay đổi</button>
            </form>
        </div>
    </div>
</section>
