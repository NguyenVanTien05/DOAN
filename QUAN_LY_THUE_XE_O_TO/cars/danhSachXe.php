<?php
/** @var mysqli $conn */
$tuKhoaXe = trim($_GET["q"] ?? "");
$idChiNhanhChon = (int) ($_GET["chi_nhanh_id"] ?? 0);
$trangThaiChon = trim($_GET["trang_thai"] ?? "");
$hangXeChon = trim($_GET["hang_xe"] ?? "");

$ketQuaHangXe = mysqli_query($conn, "SELECT DISTINCT hang_xe FROM xe WHERE hang_xe <> '' ORDER BY hang_xe ASC");
$ketQuaChiNhanh = mysqli_query($conn, "SELECT id, ten_chi_nhanh FROM chi_nhanh ORDER BY ten_chi_nhanh ASC");
$ketQuaTong = mysqli_query($conn, "SELECT
        COUNT(*) AS tong_xe,
        SUM(CASE WHEN trang_thai = 'san_sang' THEN 1 ELSE 0 END) AS xe_san_sang,
        SUM(CASE WHEN trang_thai = 'dang_thue' THEN 1 ELSE 0 END) AS xe_dang_thue,
        SUM(CASE WHEN trang_thai = 'bao_duong' THEN 1 ELSE 0 END) AS xe_bao_duong
    FROM xe");
$dongTong = $ketQuaTong ? mysqli_fetch_assoc($ketQuaTong) : [
    "tong_xe" => 0,
    "xe_san_sang" => 0,
    "xe_dang_thue" => 0,
    "xe_bao_duong" => 0,
];

$dieuKien = ["1=1"];

if ($tuKhoaXe !== "") {
    $tuKhoaXeSql = mysqli_real_escape_string($conn, $tuKhoaXe);
    $dieuKien[] = "(x.ten_xe LIKE '%$tuKhoaXeSql%' OR x.bien_so LIKE '%$tuKhoaXeSql%' OR x.hang_xe LIKE '%$tuKhoaXeSql%')";
}

if ($idChiNhanhChon > 0) {
    $dieuKien[] = "x.chi_nhanh_id = $idChiNhanhChon";
}

if ($trangThaiChon !== "") {
    $trangThaiSql = mysqli_real_escape_string($conn, $trangThaiChon);
    $dieuKien[] = "x.trang_thai = '$trangThaiSql'";
}

if ($hangXeChon !== "") {
    $hangXeSql = mysqli_real_escape_string($conn, $hangXeChon);
    $dieuKien[] = "x.hang_xe = '$hangXeSql'";
}

$sqlXe = "SELECT
        x.*,
        c.ten_chi_nhanh,
        c.dia_chi
    FROM xe x
    LEFT JOIN chi_nhanh c ON x.chi_nhanh_id = c.id
    WHERE " . implode(" AND ", $dieuKien) . "
    ORDER BY FIELD(x.trang_thai, 'san_sang', 'dang_thue', 'bao_duong'), x.gia_thue_ngay ASC, x.id DESC";
$ketQuaXe = mysqli_query($conn, $sqlXe);

$danhSachXe = [];
$danhSachXeTrenMap = [];
$chiNhanhDangXem = [];

while ($dongXe = mysqli_fetch_assoc($ketQuaXe)) {
    $danhSachXe[] = $dongXe;

    $idChiNhanh = (int) ($dongXe["chi_nhanh_id"] ?? 0);
    if ($idChiNhanh > 0) {
        $chiNhanhDangXem[$idChiNhanh] = true;
    }

    if ($dongXe["vi_do_hien_tai"] !== null && $dongXe["vi_do_hien_tai"] !== "" && $dongXe["kinh_do_hien_tai"] !== null && $dongXe["kinh_do_hien_tai"] !== "") {
        $danhSachXeTrenMap[] = $dongXe;
    }
}
?>

<style>
    .khung-trang-xe {
        padding: 34px 0 12px;
    }

    .dau-muc-trang-xe {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 18px;
    }

    .dau-muc-trang-xe p {
        margin: 0;
        color: var(--muted-color);
    }

    .dau-muc-trang-xe h2 {
        margin: 8px 0 10px;
        font-size: 30px;
    }

    .mo-ta-trang-xe {
        color: var(--muted-color);
        line-height: 1.7;
        max-width: 860px;
    }

    .nut-ban-do-trang-xe,
    .nut-phu-trang-xe,
    .nut-chinh-trang-xe {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 18px;
        border-radius: 14px;
        font-weight: 700;
    }

    .nut-ban-do-trang-xe,
    .nut-phu-trang-xe {
        border: 1px solid #dbe5f2;
        background: #ffffff;
        color: var(--text-color);
    }

    .nut-chinh-trang-xe {
        background: var(--primary-color);
        color: #ffffff;
        box-shadow: 0 10px 20px rgba(7, 165, 254, 0.22);
    }

    .cum-thong-ke-trang-xe {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 18px;
    }

    .o-thong-ke-trang-xe {
        padding: 16px;
        border-radius: 18px;
        border: 1px solid #e8eef7;
        background: #fbfdff;
    }

    .o-thong-ke-trang-xe span,
    .o-thong-ke-trang-xe small {
        color: var(--muted-color);
    }

    .o-thong-ke-trang-xe strong {
        display: block;
        margin: 8px 0 4px;
        font-size: 26px;
    }

    .hop-loc-xe {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        margin: 22px 0;
        padding: 18px;
        border-radius: 20px;
        background: #ffffff;
        border: 1px solid #e5ebf5;
    }

    .o-loc-xe {
        flex: 1;
        min-width: 180px;
    }

    .o-loc-xe label {
        display: block;
        margin-bottom: 8px;
        color: var(--muted-color);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .o-loc-xe input,
    .o-loc-xe select {
        width: 100%;
        padding: 12px 14px;
        border-radius: 12px;
        border: 1px solid #d8e2ef;
        background: #ffffff;
        font-family: inherit;
    }

    .cum-nut-loc-xe {
        display: flex;
        gap: 12px;
        align-items: end;
    }

    .luoi-danh-sach-xe {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
        margin-top: 22px;
    }

    .the-xe {
        padding: 24px;
        background: #ffffff;
        border-radius: 22px;
        box-shadow: 0 16px 40px rgba(17, 31, 53, 0.08);
        transition: 0.25s ease;
    }

    .the-xe:hover {
        transform: translateY(-4px);
    }

    .anh-the-xe {
        margin: -24px -24px 18px;
        height: 220px;
        overflow: hidden;
        border-radius: 22px 22px 18px 18px;
        background: #eaf2fb;
    }

    .anh-the-xe img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .anh-trong-xe {
        display: grid;
        place-items: center;
        width: 100%;
        height: 100%;
        color: var(--muted-color);
        font-size: 24px;
    }

    .nhan-trang-thai-danh-sach-xe {
        display: inline-flex;
        align-items: center;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 700;
    }

    .nhan-trang-thai-danh-sach-xe.mau-xanh {
        background: rgba(22, 196, 127, 0.12);
        color: #16c47f;
    }

    .nhan-trang-thai-danh-sach-xe.mau-duong {
        background: rgba(7, 165, 254, 0.12);
        color: var(--primary-color);
    }

    .nhan-trang-thai-danh-sach-xe.mau-cam {
        background: rgba(255, 159, 67, 0.16);
        color: #b97726;
    }

    .nhan-trang-thai-danh-sach-xe.mau-xam {
        background: rgba(109, 121, 136, 0.14);
        color: #526072;
    }

    .the-xe h3 {
        margin: 12px 0 8px;
        font-size: 22px;
    }

    .dong-phu-the-xe {
        color: var(--muted-color);
        font-size: 14px;
    }

    .luoi-thong-so-the-xe {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin: 18px 0;
    }

    .o-thong-so-the-xe {
        padding: 12px 14px;
        border-radius: 14px;
        background: #f8fbff;
        border: 1px solid #e7eef8;
        color: var(--muted-color);
    }

    .o-thong-so-the-xe strong {
        display: block;
        margin-top: 6px;
        color: var(--text-color);
    }

    .mo-ta-the-xe {
        margin: 0;
        color: var(--muted-color);
        line-height: 1.7;
    }

    .chan-the-xe {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        margin-top: 18px;
    }

    .gia-the-xe {
        font-size: 28px;
        font-weight: 900;
    }

    .cum-nut-the-xe {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .khung-ket-qua-trong {
        padding: 22px;
        border-radius: 22px;
        background: #ffffff;
        box-shadow: 0 16px 40px rgba(17, 31, 53, 0.08);
    }

    .ghi-chu-trong-xe {
        padding: 14px 16px;
        border-radius: 14px;
        border: 1px dashed #d6e0ec;
        color: var(--muted-color);
        background: #ffffff;
    }

    .khung-map-danh-sach-xe {
        display: grid;
        grid-template-columns: 420px 1fr;
        gap: 20px;
        align-items: start;
        margin-top: 22px;
    }

    .cot-thong-tin-map-xe,
    .cot-ban-do-xe {
        padding: 18px;
        border-radius: 22px;
        background: #ffffff;
        box-shadow: 0 16px 40px rgba(17, 31, 53, 0.08);
    }

    .cot-thong-tin-map-xe {
        height: 716px;
        overflow-y: auto;
    }

    .nhom-map-xe + .nhom-map-xe {
        margin-top: 20px;
    }

    .nhom-map-xe h3 {
        margin: 0 0 12px;
    }

    .cum-nut-loc-map-xe {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .nut-loc-map-xe {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 14px;
        border-radius: 999px;
        border: 1px solid #dbe5f2;
        background: #ffffff;
        color: var(--muted-color);
        font-weight: 700;
    }

    .nut-loc-map-xe.is-active {
        background: rgba(7, 165, 254, 0.1);
        color: var(--primary-color);
        border-color: rgba(7, 165, 254, 0.24);
    }

    .the-xe-tren-map {
        padding: 16px;
        border: 1px solid #e8eef7;
        border-radius: 16px;
        margin-bottom: 12px;
        cursor: pointer;
        transition: 0.25s ease;
        background: #fbfdff;
    }

    .the-xe-tren-map:hover,
    .the-xe-tren-map.active {
        background: rgba(7, 165, 254, 0.08);
        border-color: rgba(7, 165, 254, 0.35);
    }

    .dong-tren-map-xe {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .chu-phu-map-xe {
        margin-top: 10px;
        color: var(--muted-color);
        line-height: 1.6;
    }

    .chan-map-xe {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 12px;
    }

    .khung-thong-bao-map-xe {
        padding: 14px 16px;
        border-radius: 14px;
        border: 1px dashed #d6e0ec;
        color: var(--muted-color);
        background: #ffffff;
    }

    .duong-dan-map-xe {
        margin-top: 10px;
        color: var(--muted-color);
        font-size: 14px;
    }

    .chu-thich-map-xe {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .thanh-chu-thich-map-xe {
        margin-top: 18px;
        margin-bottom: 14px;
    }

    .cot-ban-do-xe > .chu-thich-map-xe {
        display: none;
    }

    .muc-chu-thich-map-xe {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--muted-color);
        font-size: 14px;
    }

    .cham-map-xe {
        width: 12px;
        height: 12px;
        border-radius: 999px;
        display: inline-block;
    }

    .cham-map-xe.mau-xanh {
        background: var(--primary-color);
    }

    .cham-map-xe.mau-cam {
        background: var(--warning-color);
    }

    .cham-map-xe.mau-do {
        background: var(--danger-color);
    }

    #userMap {
        height: 680px;
        width: 100%;
        border-radius: 20px;
    }

    @media (max-width: 1080px) {
        .luoi-danh-sach-xe {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 860px) {
        .cum-thong-ke-trang-xe,
        .luoi-danh-sach-xe,
        .luoi-thong-so-the-xe,
        .khung-map-danh-sach-xe {
            grid-template-columns: 1fr;
        }

        .cot-thong-tin-map-xe {
            height: auto;
            max-height: 520px;
        }
    }
</style>

<section class="khung-trang-xe">
    <div class="main__content">
        <div class="dau-muc-trang-xe">
            <div>
                <p>Danh sách xe</p>
                <h2>Chọn xe theo nhu cầu của bạn</h2>
                <div class="mo-ta-trang-xe">Lọc nhanh theo tên xe, hãng xe, chi nhánh và trạng thái. Phần map vận hành được giữ riêng ở trang bản đồ.</div>
            </div>
            <a class="nut-ban-do-trang-xe" href="./index.php?chuyen_trang=banDoVanHanh">Mở bản đồ vận hành</a>
        </div>

        <div class="cum-thong-ke-trang-xe">
            <article class="o-thong-ke-trang-xe">
                <span>Tổng xe</span>
                <strong><?php echo esc($dongTong["tong_xe"]); ?></strong>
                <small>Toàn bộ đội xe hiện có</small>
            </article>
            <article class="o-thong-ke-trang-xe">
                <span>Sẵn sàng</span>
                <strong><?php echo esc($dongTong["xe_san_sang"]); ?></strong>
                <small>Có thể đặt ngay</small>
            </article>
            <article class="o-thong-ke-trang-xe">
                <span>Đang thuê</span>
                <strong><?php echo esc($dongTong["xe_dang_thue"]); ?></strong>
                <small>Đang trong đơn thuê</small>
            </article>
        </div>

        <form class="hop-loc-xe" method="get">
            <input type="hidden" name="chuyen_trang" value="danhSachXe">
            <div class="o-loc-xe">
                <label>Tìm xe</label>
                <input type="text" name="q" value="<?php echo esc($tuKhoaXe); ?>" placeholder="Tên xe, biển số, hãng xe">
            </div>
            <div class="o-loc-xe">
                <label>Chi nhánh</label>
                <select name="chi_nhanh_id">
                    <option value="">Tất cả chi nhánh</option>
                    <?php while ($dongChiNhanh = mysqli_fetch_assoc($ketQuaChiNhanh)): ?>
                        <option value="<?php echo (int) $dongChiNhanh["id"]; ?>" <?php echo $idChiNhanhChon === (int) $dongChiNhanh["id"] ? "selected" : ""; ?>>
                            <?php echo esc($dongChiNhanh["ten_chi_nhanh"]); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="o-loc-xe">
                <label>Trạng thái</label>
                <select name="trang_thai">
                    <option value="">Tất cả trạng thái</option>
                    <option value="san_sang" <?php echo $trangThaiChon === "san_sang" ? "selected" : ""; ?>>Sẵn sàng</option>
                    <option value="dang_thue" <?php echo $trangThaiChon === "dang_thue" ? "selected" : ""; ?>>Đang thuê</option>
                    <option value="bao_duong" <?php echo $trangThaiChon === "bao_duong" ? "selected" : ""; ?>>Bảo dưỡng</option>
                </select>
            </div>
            <div class="o-loc-xe">
                <label>Hãng xe</label>
                <select name="hang_xe">
                    <option value="">Tất cả hãng xe</option>
                    <?php while ($dongHangXe = mysqli_fetch_assoc($ketQuaHangXe)): ?>
                        <option value="<?php echo esc($dongHangXe["hang_xe"]); ?>" <?php echo $hangXeChon === $dongHangXe["hang_xe"] ? "selected" : ""; ?>>
                            <?php echo esc($dongHangXe["hang_xe"]); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="cum-nut-loc-xe">
                <button class="nut-chinh-trang-xe" type="submit">Áp dụng</button>
                <a class="nut-phu-trang-xe" href="./index.php?chuyen_trang=danhSachXe">Đặt lại</a>
            </div>
        </form>

        <div class="dau-muc-trang-xe">
            <div>
                <p>Kết quả</p>
                <h2><?php echo esc(count($danhSachXe)); ?> xe phù hợp</h2>
            </div>
            <?php if ($idChiNhanhChon > 0 && isset($chiNhanhDangXem[$idChiNhanhChon])): ?>
                <a class="nut-ban-do-trang-xe" href="./index.php?chuyen_trang=banDoVanHanh">Xem chi nhánh này trên bản đồ</a>
            <?php endif; ?>
        </div>

        <?php if (!$danhSachXe): ?>
            <div class="khung-ket-qua-trong">
                <div class="ghi-chu-trong-xe">Không tìm thấy xe phù hợp với bộ lọc hiện tại.</div>
            </div>
        <?php else: ?>
            <div class="luoi-danh-sach-xe">
                <?php foreach ($danhSachXe as $dongXe): ?>
                    <?php
                    $tenTrangThaiXe = (string) $dongXe["trang_thai"];
                    $mauTrangThaiXe = "mau-duong";
                    if (($dongXe["trang_thai"] ?? "") === "san_sang") {
                        $tenTrangThaiXe = "Sẵn sàng";
                        $mauTrangThaiXe = "mau-xanh";
                    } elseif (($dongXe["trang_thai"] ?? "") === "dang_thue") {
                        $tenTrangThaiXe = "Đang thuê";
                        $mauTrangThaiXe = "mau-duong";
                    } elseif (($dongXe["trang_thai"] ?? "") === "bao_duong") {
                        $tenTrangThaiXe = "Bảo dưỡng";
                        $mauTrangThaiXe = "mau-cam";
                    } elseif (($dongXe["trang_thai"] ?? "") === "tam_ngung") {
                        $tenTrangThaiXe = "Tạm ngưng";
                        $mauTrangThaiXe = "mau-xam";
                    }
                    ?>
                    <article class="the-xe">
                        <div class="anh-the-xe">
                            <?php if (!empty($dongXe["anh_xe"])): ?>
                                <img src="<?php echo esc($dongXe["anh_xe"]); ?>" alt="<?php echo esc($dongXe["ten_xe"]); ?>">
                            <?php else: ?>
                                <div class="anh-trong-xe">Không có ảnh</div>
                            <?php endif; ?>
                        </div>
                        <span class="nhan-trang-thai-danh-sach-xe <?php echo esc($mauTrangThaiXe); ?>">
                            <?php echo esc($tenTrangThaiXe); ?>
                        </span>
                        <h3><?php echo esc($dongXe["ten_xe"]); ?></h3>
                        <div class="dong-phu-the-xe"><?php echo esc($dongXe["bien_so"]); ?> | <?php echo esc($dongXe["hang_xe"]); ?></div>
                        <div class="luoi-thong-so-the-xe">
                            <div class="o-thong-so-the-xe">Chi nhánh<strong><?php echo esc($dongXe["ten_chi_nhanh"] ?: "Đang cập nhật"); ?></strong></div>
                            <div class="o-thong-so-the-xe">Số chỗ<strong><?php echo esc($dongXe["so_cho"]); ?> chỗ</strong></div>
                            <div class="o-thong-so-the-xe">Nhiên liệu<strong><?php echo esc($dongXe["nhien_lieu"]); ?></strong></div>
                            <div class="o-thong-so-the-xe">Hộp số<strong><?php echo esc($dongXe["hop_so"]); ?></strong></div>
                        </div>
                        <p class="mo-ta-the-xe"><?php echo esc((string) ($dongXe["mo_ta"] ?? "")); ?></p>
                        <div class="chan-the-xe">
                            <div class="gia-the-xe"><?php echo format_money($dongXe["gia_thue_ngay"]); ?>/ngày</div>
                            <div class="cum-nut-the-xe">
                                <a class="nut-phu-trang-xe" href="./index.php?chuyen_trang=chiTietXe&id=<?php echo (int) $dongXe["id"]; ?>">Chi tiết</a>
                                <?php if (($dongXe["trang_thai"] ?? "") === "san_sang"): ?>
                                    <a class="nut-chinh-trang-xe" href="./index.php?chuyen_trang=datXe&xe_id=<?php echo (int) $dongXe["id"]; ?>">Đặt xe</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="dau-muc-trang-xe" style="margin-top: 30px;">
            <div>
                <p>Bản đồ xe</p>
                <h2>Xem vị trí xe và tìm xe gần bạn</h2>
                <div class="mo-ta-trang-xe">Map này chỉ hiển thị xe trong danh sách phía trên. Bấm GPS để tìm xe gần nhất theo vị trí hiện tại.</div>
            </div>
        </div>

        <?php if (!$danhSachXeTrenMap): ?>
            <div class="khung-ket-qua-trong">
                <div class="ghi-chu-trong-xe">Chưa có xe nào trong bộ lọc hiện tại được cập nhật tọa độ để hiển thị trên bản đồ.</div>
            </div>
        <?php else: ?>
            <div class="thanh-chu-thich-map-xe">
                <div class="chu-thich-map-xe">
                    <div class="muc-chu-thich-map-xe"><span class="cham-map-xe mau-xanh"></span> Xe sẵn sàng</div>
                    <div class="muc-chu-thich-map-xe"><span class="cham-map-xe mau-cam"></span> Xe không sẵn sàng</div>
                    <div class="muc-chu-thich-map-xe"><span class="cham-map-xe mau-do"></span> Vị trí GPS của bạn</div>
                </div>
            </div>
            <div class="khung-map-danh-sach-xe">
                <aside class="cot-thong-tin-map-xe">
                    <div class="nhom-map-xe">
                        <h3>Vị trí của bạn</h3>
                        <div class="cum-nut-loc-map-xe">
                            <button class="nut-chinh-trang-xe" type="button" id="detectUserLocation">Lấy vị trí của bạn</button>
                        </div>
                        <div id="selectedUserInfo" class="khung-thong-bao-map-xe">Chưa có vị trí. Bấm GPS để bắt đầu tìm xe gần nhất.</div>
                        <div id="userGeoStatus" class="duong-dan-map-xe">Hệ thống sẽ ưu tiên xe sẵn sàng gần bạn nhất trong danh sách hiện tại.</div>
                        <div id="userMapRouteInfo" class="duong-dan-map-xe">Chưa có tuyến đường.</div>
                    </div>

                    <div class="nhom-map-xe">
                        <h3>Xe gần bạn</h3>
                        <div id="nearbyRentalCarsList">
                            <div class="khung-thong-bao-map-xe">Lấy vị trí của bạn để xem xe gần nhất.</div>
                        </div>
                    </div>

                    <div class="nhom-map-xe">
                        <h3>Danh sách xe trên map</h3>
                        <div class="cum-nut-loc-map-xe">
                            <button class="nut-loc-map-xe is-active" type="button" data-map-filter="all">Tất cả</button>
                            <button class="nut-loc-map-xe" type="button" data-map-filter="ready">Xe sẵn sàng</button>
                            <button class="nut-loc-map-xe" type="button" data-map-filter="busy">Xe bận</button>
                        </div>

                        <?php foreach ($danhSachXeTrenMap as $dongXe): ?>
                            <?php
                            $tenTrangThaiXe = (string) $dongXe["trang_thai"];
                            $mauTrangThaiXe = "mau-duong";
                            if (($dongXe["trang_thai"] ?? "") === "san_sang") {
                                $tenTrangThaiXe = "Sẵn sàng";
                                $mauTrangThaiXe = "mau-xanh";
                            } elseif (($dongXe["trang_thai"] ?? "") === "dang_thue") {
                                $tenTrangThaiXe = "Đang thuê";
                                $mauTrangThaiXe = "mau-duong";
                            } elseif (($dongXe["trang_thai"] ?? "") === "bao_duong") {
                                $tenTrangThaiXe = "Bảo dưỡng";
                                $mauTrangThaiXe = "mau-cam";
                            } elseif (($dongXe["trang_thai"] ?? "") === "tam_ngung") {
                                $tenTrangThaiXe = "Tạm ngưng";
                                $mauTrangThaiXe = "mau-xam";
                            }
                            ?>
                            <article
                                class="the-xe-tren-map"
                                data-item-id="vehicle-<?php echo (int) $dongXe["id"]; ?>"
                                data-map-target="vehicle"
                                data-xe-id="<?php echo (int) $dongXe["id"]; ?>"
                                data-ten-xe="<?php echo esc($dongXe["ten_xe"]); ?>"
                                data-bien-so="<?php echo esc($dongXe["bien_so"]); ?>"
                                data-ten-chi-nhanh="<?php echo esc($dongXe["ten_chi_nhanh"] ?: "Đang cập nhật chi nhánh"); ?>"
                                data-gia-thue-ngay="<?php echo (float) $dongXe["gia_thue_ngay"]; ?>"
                                data-trang-thai="<?php echo esc($dongXe["trang_thai"]); ?>"
                                data-vi-do="<?php echo esc($dongXe["vi_do_hien_tai"]); ?>"
                                data-kinh-do="<?php echo esc($dongXe["kinh_do_hien_tai"]); ?>"
                                data-link-chi-tiet="./index.php?chuyen_trang=chiTietXe&id=<?php echo (int) $dongXe["id"]; ?>"
                                data-link-dat-xe="<?php echo ($dongXe["trang_thai"] ?? "") === "san_sang" ? "./index.php?chuyen_trang=datXe&xe_id=" . (int) $dongXe["id"] : ""; ?>"
                            >
                                <div class="dong-tren-map-xe">
                                    <strong><?php echo esc($dongXe["ten_xe"]); ?></strong>
                                    <span class="nhan-trang-thai-danh-sach-xe <?php echo esc($mauTrangThaiXe); ?>">
                                        <?php echo esc($tenTrangThaiXe); ?>
                                    </span>
                                </div>
                                <div class="chu-phu-map-xe"><?php echo esc($dongXe["bien_so"]); ?> | <?php echo esc($dongXe["ten_chi_nhanh"] ?: "Đang cập nhật chi nhánh"); ?></div>
                                <div class="chan-map-xe">
                                    <span class="chu-phu-map-xe" style="margin-top:0;"><?php echo format_money($dongXe["gia_thue_ngay"]); ?>/ngày</span>
                                    <a class="nut-phu-trang-xe" href="./index.php?chuyen_trang=chiTietXe&id=<?php echo (int) $dongXe["id"]; ?>">Chi tiết</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </aside>

                <div class="cot-ban-do-xe">
                    <div class="chu-thich-map-xe">
                        <div class="muc-chu-thich-map-xe"><span class="cham-map-xe mau-xanh"></span> Xe sẵn sàng</div>
                        <div class="muc-chu-thich-map-xe"><span class="cham-map-xe mau-cam"></span> Xe không sẵn sàng</div>
                    <div class="muc-chu-thich-map-xe"><span class="cham-map-xe mau-do"></span> Vị trí GPS của bạn</div>
                    </div>
                    <div id="userMap"></div>
                </div>
            </div>

        <?php endif; ?>
    </div>
</section>

<?php if ($danhSachXeTrenMap): ?>
<script>
    (function () {
        var oBanDo = document.getElementById("userMap");
        var oThongTinViTri = document.getElementById("selectedUserInfo");
        var oThongBaoGps = document.getElementById("userGeoStatus");
        var oThongTinDuongDi = document.getElementById("userMapRouteInfo");
        var oDanhSachGan = document.getElementById("nearbyRentalCarsList");
        var nutLayViTri = document.getElementById("detectUserLocation");
        var cacNutLoc = document.querySelectorAll("[data-map-filter]");
        var danhSachTheXe = document.querySelectorAll(".the-xe-tren-map");

        if (!oBanDo || typeof L === "undefined") {
            return;
        }

        function lamSach(giaTri) {
            return String(giaTri || "").replace(/[&<>"']/g, function (kyTu) {
                var bangMa = {
                    "&": "&amp;",
                    "<": "&lt;",
                    ">": "&gt;",
                    '"': "&quot;",
                    "'": "&#39;"
                };
                return bangMa[kyTu] || kyTu;
            });
        }

        function taoIconXe(loai) {
            var tenClass = "map-marker map-marker--vehicle";
            if (loai === "busy") {
                tenClass = "map-marker map-marker--vehicle-busy";
            }
            if (loai === "user") {
                tenClass = "map-marker map-marker--user";
            }

            var bieuTuong = loai === "user"
                ? '<i class="fa-solid fa-location-dot"></i>'
                : '<i class="fa-solid fa-car-side"></i>';

            return L.divIcon({
                className: "",
                html: '<span class="' + tenClass + '">' + bieuTuong + "</span>",
                iconSize: [34, 34],
                iconAnchor: [17, 17],
                popupAnchor: [0, -10],
            });
        }

        function tinhKhoangCach(lat1, lng1, lat2, lng2) {
            function doiSangRad(goc) {
                return (goc * Math.PI) / 180;
            }

            var R = 6371;
            var dLat = doiSangRad(lat2 - lat1);
            var dLng = doiSangRad(lng2 - lng1);
            var a =
                Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(doiSangRad(lat1)) * Math.cos(doiSangRad(lat2)) *
                Math.sin(dLng / 2) * Math.sin(dLng / 2);
            var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        }

        function layViTriHienTai(callback) {
            if (!navigator.geolocation) {
                if (oThongBaoGps) {
                    oThongBaoGps.textContent = "Trình duyệt không hỗ trợ lấy vị trí hiện tại.";
                }
                return;
            }

            if (oThongBaoGps) {
                oThongBaoGps.textContent = "Đang lấy vị trí hiện tại...";
            }

            navigator.geolocation.getCurrentPosition(
                function (viTri) {
                    callback({
                        lat: viTri.coords.latitude,
                        lng: viTri.coords.longitude
                    });
                },
                function () {
                    if (oThongBaoGps) {
                        oThongBaoGps.textContent = "Không lấy được vị trí hiện tại. Hãy cấp quyền vị trí rồi thử lại.";
                    }
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 60000
                }
            );
        }

        async function layDuongDi(diemBatDau, diemKetThuc) {
            var duongDan = "https://router.project-osrm.org/route/v1/driving/" + diemBatDau.lng + "," + diemBatDau.lat + ";" + diemKetThuc.lng + "," + diemKetThuc.lat + "?overview=full&geometries=geojson";
            var phanHoi = await fetch(duongDan);
            if (!phanHoi.ok) {
                throw new Error("loi_duong_di");
            }
            var duLieu = await phanHoi.json();
            if (!duLieu.routes || !duLieu.routes.length) {
                throw new Error("khong_co_duong");
            }
            return duLieu.routes[0];
        }

        function moDuongDanNgoai(diemBatDau, diemKetThuc) {
            var duongDan = "https://www.openstreetmap.org/directions?engine=fossgis_osrm_car&route=" + diemBatDau.lat + "%2C" + diemBatDau.lng + "%3B" + diemKetThuc.lat + "%2C" + diemKetThuc.lng;
            window.open(duongDan, "_blank", "noopener,noreferrer");
        }

        var duLieuMap = {
            vehicles: [],
            parkings: []
        };

        for (var i = 0; i < danhSachTheXe.length; i++) {
            duLieuMap.vehicles.push({
                id: Number(danhSachTheXe[i].dataset.xeId),
                ten_xe: danhSachTheXe[i].dataset.tenXe || "",
                bien_so: danhSachTheXe[i].dataset.bienSo || "",
                ten_chi_nhanh: danhSachTheXe[i].dataset.tenChiNhanh || "",
                gia_thue_ngay: Number(danhSachTheXe[i].dataset.giaThueNgay || 0),
                trang_thai: danhSachTheXe[i].dataset.trangThai || "",
                vi_do_hien_tai: Number(danhSachTheXe[i].dataset.viDo || 0),
                kinh_do_hien_tai: Number(danhSachTheXe[i].dataset.kinhDo || 0),
                link_chi_tiet: danhSachTheXe[i].dataset.linkChiTiet || "",
                link_dat_xe: danhSachTheXe[i].dataset.linkDatXe || ""
            });
        }
        var banDo = L.map("userMap", {
            zoomControl: true,
            scrollWheelZoom: true
        }).setView([21.03, 105.84], 11);

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(banDo);

        var danhSachMarker = [];
        var viTriKhach = null;
        var markerKhach = null;
        var duongDiHienTai = null;
        var idXeDangChon = 0;

        function batTheDangChon(idXe) {
            for (var i = 0; i < danhSachTheXe.length; i++) {
                danhSachTheXe[i].classList.toggle("active", danhSachTheXe[i].dataset.itemId === "vehicle-" + idXe);
            }
        }

        function capNhatDanhSachGan() {
            if (!oDanhSachGan || !viTriKhach) {
                return;
            }

            var sapXepXe = [];
            for (var i = 0; i < duLieuMap.vehicles.length; i++) {
                sapXepXe.push({
                    id: duLieuMap.vehicles[i].id,
                    ten_xe: duLieuMap.vehicles[i].ten_xe,
                    bien_so: duLieuMap.vehicles[i].bien_so,
                    ten_chi_nhanh: duLieuMap.vehicles[i].ten_chi_nhanh,
                    gia_thue_ngay: duLieuMap.vehicles[i].gia_thue_ngay,
                    trang_thai: duLieuMap.vehicles[i].trang_thai,
                    vi_do_hien_tai: duLieuMap.vehicles[i].vi_do_hien_tai,
                    kinh_do_hien_tai: duLieuMap.vehicles[i].kinh_do_hien_tai,
                    khoang_cach: tinhKhoangCach(
                        viTriKhach.lat,
                        viTriKhach.lng,
                        Number(duLieuMap.vehicles[i].vi_do_hien_tai),
                        Number(duLieuMap.vehicles[i].kinh_do_hien_tai)
                    )
                });
            }

            sapXepXe.sort(function (a, b) {
                return a.khoang_cach - b.khoang_cach;
            });

            var xeSanSang = [];
            for (var j = 0; j < sapXepXe.length; j++) {
                if ((sapXepXe[j].trang_thai || "") === "san_sang") {
                    xeSanSang.push(sapXepXe[j]);
                }
            }

            var danhSachHienThi = (xeSanSang.length ? xeSanSang : sapXepXe).slice(0, 5);

            if (!danhSachHienThi.length) {
                oDanhSachGan.innerHTML = '<div class="khung-thong-bao-map-xe">Chưa có xe nào được cập nhật vị trí trên bản đồ.</div>';
                return;
            }

            var html = "";
            for (var k = 0; k < danhSachHienThi.length; k++) {
                html += '' +
                    '<button class="nearby__item" type="button" data-nearby-id="' + danhSachHienThi[k].id + '">' +
                        '<strong>' + lamSach(danhSachHienThi[k].ten_xe) + '</strong>' +
                        '<div class="card__meta">' + lamSach(danhSachHienThi[k].bien_so) + " | " + lamSach(danhSachHienThi[k].ten_chi_nhanh || "Đang cập nhật chi nhánh") + '</div>' +
                        '<div class="card__meta">' + Number(danhSachHienThi[k].gia_thue_ngay).toLocaleString("vi-VN") + 'đ/ngày</div>' +
                        '<div class="card__meta">Cách bạn ~ ' + danhSachHienThi[k].khoang_cach.toFixed(2) + ' km</div>' +
                    "</button>";
            }
            oDanhSachGan.innerHTML = html;

            var danhSachNutGan = oDanhSachGan.querySelectorAll("[data-nearby-id]");
            for (var n = 0; n < danhSachNutGan.length; n++) {
                danhSachNutGan[n].addEventListener("click", function () {
                    moDuongToiXe(Number(this.dataset.nearbyId));
                });
            }
        }

        function capNhatViTriKhach(viTri, nhanNguon) {
            viTriKhach = viTri;

            if (markerKhach) {
                banDo.removeLayer(markerKhach);
            }

            markerKhach = L.marker([viTri.lat, viTri.lng], {
                icon: taoIconXe("user")
            }).addTo(banDo);
            markerKhach.bindPopup('<div class="map__popup"><h3>Vị trí của bạn</h3><p>' + lamSach(nhanNguon) + "</p></div>").openPopup();

            if (oThongTinViTri) {
                oThongTinViTri.innerHTML = "<strong>Vị trí hiện tại của bạn</strong><br>" + viTri.lat.toFixed(6) + ", " + viTri.lng.toFixed(6);
            }

            if (oThongBaoGps) {
                oThongBaoGps.textContent = "Đã cập nhật vị trí của bạn.";
            }

            capNhatDanhSachGan();

            if (idXeDangChon > 0) {
                moDuongToiXe(idXeDangChon);
            } else if (oThongTinDuongDi) {
                oThongTinDuongDi.textContent = "Vị trí đã được cập nhật. Chọn xe để xem đường đi.";
            }
        }

        async function moDuongToiXe(idXe) {
            var xeDangChon = null;
            for (var i = 0; i < duLieuMap.vehicles.length; i++) {
                if (Number(duLieuMap.vehicles[i].id) === Number(idXe)) {
                    xeDangChon = duLieuMap.vehicles[i];
                    break;
                }
            }

            if (!xeDangChon) {
                return;
            }

            idXeDangChon = Number(idXe);
            batTheDangChon(xeDangChon.id);

            var diemXe = {
                lat: Number(xeDangChon.vi_do_hien_tai),
                lng: Number(xeDangChon.kinh_do_hien_tai)
            };

            var markerXe = null;
            for (var j = 0; j < danhSachMarker.length; j++) {
                if (danhSachMarker[j].id === "vehicle-" + xeDangChon.id) {
                    markerXe = danhSachMarker[j];
                    break;
                }
            }
            if (markerXe) {
                markerXe.marker.openPopup();
            }

            if (!viTriKhach) {
                banDo.setView([diemXe.lat, diemXe.lng], 14, { animate: true });
                if (oThongTinDuongDi) {
                    oThongTinDuongDi.textContent = "Hãy chọn vị trí của bạn trước khi chỉ đường tới xe.";
                }
                return;
            }

            if (duongDiHienTai) {
                banDo.removeLayer(duongDiHienTai);
            }

            try {
                var duLieuDuong = await layDuongDi(viTriKhach, diemXe);
                var toaDo = [];
                for (var k = 0; k < duLieuDuong.geometry.coordinates.length; k++) {
                    toaDo.push([duLieuDuong.geometry.coordinates[k][1], duLieuDuong.geometry.coordinates[k][0]]);
                }
                duongDiHienTai = L.polyline(toaDo, {
                    color: "#07a5fe",
                    weight: 4
                }).addTo(banDo);

                if (oThongTinDuongDi) {
                    oThongTinDuongDi.textContent = "Đường đến " + xeDangChon.ten_xe + ": " + (duLieuDuong.distance / 1000).toFixed(2) + " km";
                }
            } catch (loi) {
                duongDiHienTai = L.polyline([
                    [viTriKhach.lat, viTriKhach.lng],
                    [diemXe.lat, diemXe.lng]
                ], {
                    color: "#07a5fe",
                    weight: 4
                }).addTo(banDo);

                if (oThongTinDuongDi) {
                    oThongTinDuongDi.textContent = "Đường đến " + xeDangChon.ten_xe + ": không lấy được chỉ đường thực tế, đang hiển thị đường tạm.";
                }
            }

            banDo.fitBounds(L.latLngBounds([
                [viTriKhach.lat, viTriKhach.lng],
                [diemXe.lat, diemXe.lng]
            ]), { padding: [40, 40] });
        }

        function apDungBoLoc(boLoc) {
            for (var i = 0; i < cacNutLoc.length; i++) {
                cacNutLoc[i].classList.toggle("is-active", cacNutLoc[i].dataset.mapFilter === boLoc);
            }

            var danhSachHienThi = [];

            for (var j = 0; j < danhSachMarker.length; j++) {
                var thongTinMarker = danhSachMarker[j];
                var duocHien = true;
                if (boLoc === "ready") {
                    duocHien = thongTinMarker.trang_thai === "san_sang";
                } else if (boLoc === "busy") {
                    duocHien = thongTinMarker.trang_thai !== "san_sang";
                }

                if (duocHien) {
                    thongTinMarker.marker.addTo(banDo);
                    danhSachHienThi.push(thongTinMarker.marker.getLatLng());
                } else {
                    banDo.removeLayer(thongTinMarker.marker);
                }
            }

            if (markerKhach) {
                danhSachHienThi.push(markerKhach.getLatLng());
            }

            if (danhSachHienThi.length) {
                banDo.fitBounds(L.latLngBounds(danhSachHienThi), { padding: [40, 40] });
            }
        }

        for (var i = 0; i < duLieuMap.vehicles.length; i++) {
            var xe = duLieuMap.vehicles[i];
            var xeSanSang = (xe.trang_thai || "") === "san_sang";
            var tenTrangThaiXe = xe.trang_thai || "Đang cập nhật";
            if (xe.trang_thai === "san_sang") {
                tenTrangThaiXe = "Sẵn sàng";
            } else if (xe.trang_thai === "dang_thue") {
                tenTrangThaiXe = "Đang thuê";
            } else if (xe.trang_thai === "bao_duong") {
                tenTrangThaiXe = "Bảo dưỡng";
            }
            var markerXe = L.marker([Number(xe.vi_do_hien_tai), Number(xe.kinh_do_hien_tai)], {
                icon: taoIconXe(xeSanSang ? "default" : "busy")
            }).addTo(banDo);

            var nutDatXe = xeSanSang
                ? '<a class="map__popup-link" href="./index.php?chuyen_trang=datXe&xe_id=' + xe.id + '">Đặt xe</a>'
                : "";

            markerXe.bindPopup(
                '<div class="map__popup">' +
                    "<h3>" + lamSach(xe.ten_xe) + "</h3>" +
                    "<p>Biển số: <strong>" + lamSach(xe.bien_so) + "</strong></p>" +
                    "<p>Chi nhánh: " + lamSach(xe.ten_chi_nhanh || "Đang cập nhật chi nhánh") + "</p>" +
                    "<p>Giá thuê: " + Number(xe.gia_thue_ngay).toLocaleString("vi-VN") + "đ/ngày</p>" +
                    "<p>Trạng thái: " + lamSach(tenTrangThaiXe) + "</p>" +
                    '<div class="map__popup-actions">' +
                        '<button class="map__popup-action" type="button" data-route-xe="' + xe.id + '">Chỉ đường tới xe</button>' +
                        '<button class="map__popup-action" type="button" data-open-route-xe="' + xe.id + '">Mở bên ngoài</button>' +
                        '<a class="map__popup-link" href="./index.php?chuyen_trang=chiTietXe&id=' + xe.id + '">Xem chi tiết</a>' +
                        nutDatXe +
                    "</div>" +
                "</div>"
            );

            danhSachMarker.push({
                id: "vehicle-" + xe.id,
                trang_thai: xe.trang_thai,
                marker: markerXe
            });
        }

        if (danhSachMarker.length) {
            var tatCaDiem = [];
            for (var j = 0; j < danhSachMarker.length; j++) {
                tatCaDiem.push(danhSachMarker[j].marker.getLatLng());
            }
            banDo.fitBounds(L.latLngBounds(tatCaDiem), { padding: [40, 40] });
        }

        for (var k = 0; k < danhSachTheXe.length; k++) {
            danhSachTheXe[k].addEventListener("click", function () {
                moDuongToiXe(Number(this.dataset.xeId));
            });
        }

        for (var n = 0; n < cacNutLoc.length; n++) {
            cacNutLoc[n].addEventListener("click", function () {
                apDungBoLoc(this.dataset.mapFilter);
            });
        }

        banDo.on("popupopen", function (suKien) {
            var oPopup = suKien.popup.getElement();
            if (!oPopup) {
                return;
            }

            var danhSachNutRouteXe = oPopup.querySelectorAll("[data-route-xe]");
            for (var i = 0; i < danhSachNutRouteXe.length; i++) {
                danhSachNutRouteXe[i].addEventListener("click", function () {
                    moDuongToiXe(Number(this.dataset.routeXe));
                });
            }

            var danhSachNutMoNgoai = oPopup.querySelectorAll("[data-open-route-xe]");
            for (var j = 0; j < danhSachNutMoNgoai.length; j++) {
                danhSachNutMoNgoai[j].addEventListener("click", function () {
                    if (!viTriKhach) {
                        if (oThongTinDuongDi) {
                            oThongTinDuongDi.textContent = "Hãy chọn vị trí của bạn trước khi mở chỉ đường ngoài.";
                        }
                        return;
                    }

                    var xeDangChon = null;
                    for (var k = 0; k < duLieuMap.vehicles.length; k++) {
                        if (Number(duLieuMap.vehicles[k].id) === Number(this.dataset.openRouteXe)) {
                            xeDangChon = duLieuMap.vehicles[k];
                            break;
                        }
                    }
                    if (!xeDangChon) {
                        return;
                    }

                    moDuongDanNgoai(viTriKhach, {
                        lat: Number(xeDangChon.vi_do_hien_tai),
                        lng: Number(xeDangChon.kinh_do_hien_tai)
                    });
                });
            }
        });

        if (nutLayViTri) {
            nutLayViTri.addEventListener("click", function () {
                layViTriHienTai(function (viTri) {
                    capNhatViTriKhach(viTri, "Lấy từ GPS của trình duyệt.");
                    banDo.setView([viTri.lat, viTri.lng], 14, { animate: true });
                });
            });
        }
    })();
</script>
<?php endif; ?>
