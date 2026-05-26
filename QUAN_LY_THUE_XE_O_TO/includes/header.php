<?php
/** @var mysqli $conn */
$nguoiDungDangNhap = null;
$trangDangMo = $chuyenTrang ?? ($_GET["chuyen_trang"] ?? "trangChu");

if (isset($_SESSION["idNguoiDung"])) {
    $idNguoiDung = (int) $_SESSION["idNguoiDung"];
    $sqlLayNguoiDung = "SELECT * FROM nguoi_dung WHERE id = $idNguoiDung LIMIT 1";
    $ketQuaLayNguoiDung = mysqli_query($conn, $sqlLayNguoiDung);
    if ($ketQuaLayNguoiDung && mysqli_num_rows($ketQuaLayNguoiDung) > 0) {
        $nguoiDungDangNhap = mysqli_fetch_assoc($ketQuaLayNguoiDung);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarRent | Đặt xe ô tô trực tuyến</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link
      rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""
    >
    <script
      src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
      integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
      crossorigin=""
    ></script>
    <script>
      if (typeof window.L === "undefined") {
        document.write('<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"><\/script>');
      }
    </script>
    <link rel="stylesheet" href="./assets/css/style.css">
    <style>
        :root {
            --primary-color: #07a5fe;
            --primary-dark: #0588d0;
            --dark-color: #2c2c2c;
            --bg-color: #f4f6fb;
            --card-color: #ffffff;
            --line-color: #dfe5f0;
            --text-color: #18202a;
            --muted-color: #6d7988;
            --warning-color: #ff9f43;
            --success-color: #16c47f;
            --danger-color: #ff5f5f;
            --info-color: #3a86ff;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            font-family: "Roboto", sans-serif;
            background: linear-gradient(180deg, #f6f8fc 0%, #edf2f9 100%);
            color: var(--text-color);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input,
        select,
        textarea {
            font-family: inherit;
        }

        button {
            border: 0;
            cursor: pointer;
        }

        img {
            max-width: 100%;
        }

        .main__content {
            width: min(1180px, calc(100vw - 40px));
            margin: 0 auto;
        }

        .khung-menu-chinh {
            position: sticky;
            top: 0;
            z-index: 1200;
            background: #f4f6fb;
            border-bottom: 1px solid rgba(223, 229, 240, 0.9);
            box-shadow: 0 10px 24px rgba(17, 31, 53, 0.08);
        }

        .noi-dung-menu {
            min-height: 86px;
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            align-items: center;
            column-gap: 14px;
        }

        .thuong-hieu-trang {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 0 0 auto;
        }

        .logo-thuong-hieu {
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

        .chu-thuong-hieu strong {
            display: block;
            font-size: 16px;
        }

        .chu-thuong-hieu {
            max-width: 190px;
        }

        .chu-thuong-hieu span {
            display: block;
            margin-top: 2px;
            color: var(--muted-color);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            line-height: 1.3;
        }

        .cum-link-menu,
        .cum-nut-menu {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: nowrap;
        }

        .cum-link-menu {
            width: 100%;
            justify-content: center;
            min-width: 0;
        }

        .cum-link-menu a {
            flex: 0 0 auto;
            padding: 10px 11px;
            border-radius: 999px;
            color: var(--muted-color);
            white-space: nowrap;
            font-size: 15px;
        }

        .cum-link-menu a:hover,
        .cum-link-menu a.dang-mo {
            color: var(--primary-color);
            background: rgba(7, 165, 254, 0.1);
        }

        .nut-trang-chu,
        .nut-phu-trang {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 15px;
            border-radius: 14px;
            font-weight: 700;
            white-space: nowrap;
        }

        .nut-trang-chu {
            background: var(--primary-color);
            color: #ffffff;
            box-shadow: 0 10px 20px rgba(7, 165, 254, 0.22);
        }

        .nut-phu-trang {
            background: #ffffff;
            color: var(--text-color);
            border: 1px solid #dbe5f2;
        }

        .cum-nut-menu {
            justify-self: end;
        }

        .chan-trang-chinh {
            margin-top: 48px;
            background: #2c2c2c;
            color: #ffffff;
            padding: 40px 0;
        }

        .dong-chan-trang {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
            flex-wrap: wrap;
        }

        .cot-chan-trang {
            min-width: 220px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .cot-chan-trang h3,
        .cot-chan-trang h4,
        .cot-chan-trang p {
            margin: 0;
        }

        @media (max-width: 1280px) {
            .chu-thuong-hieu span {
                display: none;
            }
        }

        @media (max-width: 1100px) {
            .noi-dung-menu {
                display: flex;
                align-items: flex-start;
                padding: 18px 0;
                flex-direction: column;
                flex-wrap: wrap;
            }

            .cum-link-menu,
            .cum-nut-menu {
                flex-wrap: wrap;
            }

            .cum-link-menu {
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>
    <header class="khung-menu-chinh">
        <div class="main__content">
            <div class="noi-dung-menu">
                <a href="./index.php?chuyen_trang=trangChu" class="thuong-hieu-trang">
                    <div class="logo-thuong-hieu">OG</div>
                    <div class="chu-thuong-hieu">
                        <strong>Ô TÔ GO</strong>
                        <span>Đặt xe ô tô siêu tiện</span>
                    </div>
                </a>

                <nav class="cum-link-menu">
                    <a class="<?php echo $trangDangMo === "trangChu" ? "dang-mo" : ""; ?>" href="./index.php?chuyen_trang=trangChu">Trang chủ</a>
                    <a class="<?php echo $trangDangMo === "danhSachXe" ? "dang-mo" : ""; ?>" href="./index.php?chuyen_trang=danhSachXe">Danh sách xe</a>
                    <a class="<?php echo $trangDangMo === "banDoVanHanh" ? "dang-mo" : ""; ?>" href="./index.php?chuyen_trang=banDoVanHanh">Bản đồ vận hành</a>
                    <a class="<?php echo $trangDangMo === "chiNhanh" ? "dang-mo" : ""; ?>" href="./index.php?chuyen_trang=chiNhanh">Chi nhánh</a>
                    <a class="<?php echo $trangDangMo === "donCuaToi" ? "dang-mo" : ""; ?>" href="./index.php?chuyen_trang=donCuaToi">Đơn của tôi</a>
                </nav>

                <div class="cum-nut-menu">
                    <?php if ($nguoiDungDangNhap): ?>
                        <a class="nut-phu-trang" href="./index.php?chuyen_trang=taiKhoan"><?php echo esc($nguoiDungDangNhap["ho_ten"]); ?></a>
                        <a class="nut-trang-chu" href="./auth/dangXuat.php">Đăng xuất</a>
                    <?php else: ?>
                        <a class="nut-phu-trang" href="./auth/dangNhap.php">Đăng nhập</a>
                        <a class="nut-trang-chu" href="./auth/dangKy.php">Đăng ký</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    <main>
