<?php
/** @var mysqli $conn */
$tuKhoaChiNhanh = trim($_GET["q"] ?? "");
$khuVucChon = trim($_GET["khu_vuc"] ?? "");

$sqlChiNhanh = "SELECT
        c.*,
        n.so_dien_thoai,
        COUNT(x.id) AS tong_xe,
        SUM(CASE WHEN x.trang_thai = 'san_sang' THEN 1 ELSE 0 END) AS xe_san_sang
    FROM chi_nhanh c
    LEFT JOIN nguoi_dung n ON c.nguoi_cho_thue_id = n.id
    LEFT JOIN xe x ON x.chi_nhanh_id = c.id
    GROUP BY c.id
    ORDER BY c.id ASC";
$ketQuaChiNhanh = mysqli_query($conn, $sqlChiNhanh);

$danhSachChiNhanh = [];
$danhSachKhuVuc = [];
$soChiNhanhHoatDong = 0;
$tongXeTheoLoc = 0;

while ($dongChiNhanh = mysqli_fetch_assoc($ketQuaChiNhanh)) {
    $tachDiaChi = array_values(array_filter(array_map("trim", explode(",", (string) $dongChiNhanh["dia_chi"]))));
    $khuVuc = $tachDiaChi ? $tachDiaChi[count($tachDiaChi) - 1] : "";
    $trangThai = (int) $dongChiNhanh["tong_xe"] > 0 ? "dang_hoat_dong" : "tam_ngung";
    $tenTrangThai = $trangThai;
    $mauTrangThai = "mau-xanh";
    if ($trangThai === "dang_hoat_dong") {
        $tenTrangThai = "Đang hoạt động";
        $mauTrangThai = "mau-xanh";
    } elseif ($trangThai === "tam_ngung") {
        $tenTrangThai = "Tạm ngưng";
        $mauTrangThai = "mau-xam";
    }
    $dungTuKhoa = $tuKhoaChiNhanh === ""
        || stripos((string) $dongChiNhanh["ten_chi_nhanh"], $tuKhoaChiNhanh) !== false
        || stripos((string) $dongChiNhanh["dia_chi"], $tuKhoaChiNhanh) !== false;
    $dungKhuVuc = $khuVucChon === "" || $khuVuc === $khuVucChon;

    if ($khuVuc !== "") {
        $danhSachKhuVuc[$khuVuc] = $khuVuc;
    }

    if (!$dungTuKhoa || !$dungKhuVuc) {
        continue;
    }

    if ($trangThai === "dang_hoat_dong") {
        $soChiNhanhHoatDong++;
    }

    $tongXeTheoLoc += (int) $dongChiNhanh["tong_xe"];
    $danhSachChiNhanh[] = [
        "id" => (int) $dongChiNhanh["id"],
        "ten" => $dongChiNhanh["ten_chi_nhanh"],
        "dia_chi" => $dongChiNhanh["dia_chi"],
        "mo_ta" => $dongChiNhanh["mo_ta"] ?? "",
        "so_dien_thoai" => $dongChiNhanh["so_dien_thoai"] ?? "",
        "vi_do" => $dongChiNhanh["vi_do"],
        "kinh_do" => $dongChiNhanh["kinh_do"],
        "khu_vuc" => $khuVuc,
        "trang_thai" => $trangThai,
        "nhan_trang_thai" => $tenTrangThai,
        "mau_trang_thai" => $mauTrangThai,
        "tong_xe" => (int) $dongChiNhanh["tong_xe"],
        "xe_san_sang" => (int) $dongChiNhanh["xe_san_sang"],
    ];
}

natcasesort($danhSachKhuVuc);
?>

<style>
    .khung-trang-chi-nhanh {
        padding: 34px 0 12px;
    }

    .dau-muc-chi-nhanh {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 20px;
    }

    .dau-muc-chi-nhanh p {
        margin: 0;
        color: var(--muted-color);
    }

    .dau-muc-chi-nhanh h2 {
        margin: 8px 0 0;
        font-size: 30px;
    }

    .nut-ban-do-chi-nhanh {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 18px;
        border-radius: 14px;
        border: 1px solid #dbe5f2;
        background: #ffffff;
        color: var(--text-color);
        font-weight: 700;
    }

    .hop-loc-chi-nhanh {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        margin-bottom: 22px;
        padding: 18px;
        border-radius: 20px;
        background: #ffffff;
        border: 1px solid #e5ebf5;
    }

    .o-loc-chi-nhanh {
        flex: 1;
        min-width: 180px;
    }

    .o-loc-chi-nhanh label {
        display: block;
        margin-bottom: 8px;
        color: var(--muted-color);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .o-loc-chi-nhanh input,
    .o-loc-chi-nhanh select {
        width: 100%;
        padding: 12px 14px;
        border-radius: 12px;
        border: 1px solid #d8e2ef;
        background: #ffffff;
        font-family: inherit;
    }

    .cum-nut-loc-chi-nhanh {
        display: flex;
        gap: 12px;
        align-items: end;
    }

    .nut-ap-dung-chi-nhanh,
    .nut-dat-lai-chi-nhanh {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 18px;
        border-radius: 14px;
        font-weight: 700;
    }

    .nut-ap-dung-chi-nhanh {
        background: var(--primary-color);
        color: #ffffff;
        box-shadow: 0 10px 20px rgba(7, 165, 254, 0.22);
    }

    .nut-dat-lai-chi-nhanh {
        border: 1px solid #dbe5f2;
        background: #ffffff;
        color: var(--text-color);
    }

    .cum-thong-ke-chi-nhanh {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 18px;
    }

    .o-thong-ke-chi-nhanh {
        padding: 16px;
        border-radius: 18px;
        border: 1px solid #e8eef7;
        background: #fbfdff;
    }

    .o-thong-ke-chi-nhanh span {
        color: var(--muted-color);
    }

    .o-thong-ke-chi-nhanh strong {
        display: block;
        margin: 8px 0 4px;
        font-size: 26px;
    }

    .khung-noi-dung-chi-nhanh {
        display: grid;
        grid-template-columns: 360px 1fr;
        gap: 20px;
        align-items: stretch;
    }

    .cot-danh-sach-chi-nhanh,
    .cot-ban-do-chi-nhanh {
        padding: 18px;
        border-radius: 22px;
        background: #ffffff;
        box-shadow: 0 16px 40px rgba(17, 31, 53, 0.08);
    }

    .cot-danh-sach-chi-nhanh {
        height: 596px;
        overflow-y: auto;
    }

    .the-chi-nhanh {
        padding: 18px;
        border-radius: 18px;
        border: 1px solid #e8eef7;
        margin-bottom: 14px;
        cursor: pointer;
        transition: 0.25s ease;
    }

    .the-chi-nhanh:hover,
    .the-chi-nhanh.active {
        background: rgba(7, 165, 254, 0.08);
        border-color: rgba(7, 165, 254, 0.35);
    }

    .dong-ten-chi-nhanh {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .dong-ten-chi-nhanh h3 {
        margin: 0;
    }

    .dong-phu-chi-nhanh {
        margin-top: 8px;
        color: var(--muted-color);
        font-size: 14px;
        line-height: 1.6;
    }

    .chan-the-chi-nhanh {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 18px;
    }

    .nut-xem-xe-chi-nhanh {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 16px;
        border-radius: 12px;
        border: 1px solid #dbe5f2;
        background: #ffffff;
        color: var(--text-color);
        font-weight: 700;
    }

    .nhan-trang-thai-chi-nhanh {
        display: inline-flex;
        align-items: center;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 700;
    }

    .nhan-trang-thai-chi-nhanh.mau-xanh {
        background: rgba(22, 196, 127, 0.12);
        color: #16c47f;
    }

    .nhan-trang-thai-chi-nhanh.mau-xam {
        background: rgba(109, 121, 136, 0.14);
        color: #526072;
    }

    #branchMap {
        height: 560px;
        width: 100%;
        border-radius: 20px;
    }

    .ghi-chu-trong {
        padding: 14px 16px;
        border-radius: 14px;
        border: 1px dashed #d6e0ec;
        color: var(--muted-color);
        background: #ffffff;
    }

    @media (max-width: 860px) {
        .dau-muc-chi-nhanh,
        .khung-noi-dung-chi-nhanh {
            grid-template-columns: 1fr;
            display: grid;
        }

        .cum-thong-ke-chi-nhanh {
            grid-template-columns: 1fr;
        }

        .cot-danh-sach-chi-nhanh {
            height: auto;
            max-height: 520px;
        }

        .hop-loc-chi-nhanh {
            padding: 16px;
        }
    }
</style>

<section class="khung-trang-chi-nhanh">
    <div class="main__content">
        <div class="dau-muc-chi-nhanh">
            <div>
                <p>Chi nhánh</p>
                <h2>Tìm điểm giao nhận xe phù hợp</h2>
                <p>Trang này giữ vai trò danh sách chi nhánh. Bản đồ chỉ để hỗ trợ xem nhanh vị trí, không thay đổi luồng dữ liệu gốc.</p>
            </div>
            <a class="nut-ban-do-chi-nhanh" href="./index.php?chuyen_trang=banDoVanHanh">Mở bản đồ vận hành</a>
        </div>

        <form class="hop-loc-chi-nhanh" method="get">
            <input type="hidden" name="chuyen_trang" value="chiNhanh">
            <div class="o-loc-chi-nhanh">
                <label>Tìm chi nhánh</label>
                <input type="text" name="q" value="<?php echo esc($tuKhoaChiNhanh); ?>" placeholder="Tên chi nhánh hoặc địa chỉ">
            </div>
            <div class="o-loc-chi-nhanh">
                <label>Khu vực</label>
                <select name="khu_vuc">
                    <option value="">Tất cả khu vực</option>
                    <?php foreach ($danhSachKhuVuc as $khuVuc): ?>
                        <option value="<?php echo esc($khuVuc); ?>" <?php echo $khuVucChon === $khuVuc ? "selected" : ""; ?>><?php echo esc($khuVuc); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="cum-nut-loc-chi-nhanh">
                <button class="nut-ap-dung-chi-nhanh" type="submit">Áp dụng</button>
                <a class="nut-dat-lai-chi-nhanh" href="./index.php?chuyen_trang=chiNhanh">Đặt lại</a>
            </div>
        </form>

        <div class="cum-thong-ke-chi-nhanh">
            <article class="o-thong-ke-chi-nhanh">
                <span>Chi nhánh hiển thị</span>
                <strong><?php echo esc(count($danhSachChiNhanh)); ?></strong>
                <small style="color: var(--muted-color);">Sau khi áp dụng bộ lọc</small>
            </article>
            <article class="o-thong-ke-chi-nhanh">
                <span>Đang hoạt động</span>
                <strong><?php echo esc($soChiNhanhHoatDong); ?></strong>
                <small style="color: var(--muted-color);">Có xe sẵn sàng để phục vụ</small>
            </article>
            <article class="o-thong-ke-chi-nhanh">
                <span>Tổng xe</span>
                <strong><?php echo esc($tongXeTheoLoc); ?></strong>
                <small style="color: var(--muted-color);">Gắn với các chi nhánh đang hiển thị</small>
            </article>
        </div>

        <div class="khung-noi-dung-chi-nhanh">
            <div class="cot-danh-sach-chi-nhanh">
                <?php if (!$danhSachChiNhanh): ?>
                    <div class="ghi-chu-trong">Không tìm thấy chi nhánh phù hợp.</div>
                <?php else: ?>
                    <?php foreach ($danhSachChiNhanh as $dongChiNhanh): ?>
                        <article
                            class="the-chi-nhanh"
                            data-chi-nhanh-map="1"
                            <?php if ($dongChiNhanh["vi_do"] !== null && $dongChiNhanh["kinh_do"] !== null): ?>
                                data-lat="<?php echo esc($dongChiNhanh["vi_do"]); ?>"
                                data-lng="<?php echo esc($dongChiNhanh["kinh_do"]); ?>"
                                data-title="<?php echo esc($dongChiNhanh["ten"]); ?>"
                                data-address="<?php echo esc($dongChiNhanh["dia_chi"]); ?>"
                            <?php endif; ?>
                        >
                            <div class="dong-ten-chi-nhanh">
                                <h3><?php echo esc($dongChiNhanh["ten"]); ?></h3>
                                <span class="nhan-trang-thai-chi-nhanh <?php echo esc($dongChiNhanh["mau_trang_thai"]); ?>"><?php echo esc($dongChiNhanh["nhan_trang_thai"]); ?></span>
                            </div>
                            <div class="dong-phu-chi-nhanh"><?php echo esc($dongChiNhanh["dia_chi"]); ?></div>
                            <?php if ($dongChiNhanh["so_dien_thoai"] !== ""): ?>
                                <div class="dong-phu-chi-nhanh">Hotline: <?php echo esc($dongChiNhanh["so_dien_thoai"]); ?></div>
                            <?php endif; ?>
                            <div class="dong-phu-chi-nhanh"><?php echo esc($dongChiNhanh["mo_ta"] ?: "Chi nhánh giao nhận xe trong hệ thống."); ?></div>
                            <div class="chan-the-chi-nhanh">
                                <span class="dong-phu-chi-nhanh" style="margin-top:0;"><?php echo esc($dongChiNhanh["xe_san_sang"]); ?> / <?php echo esc($dongChiNhanh["tong_xe"]); ?> xe sẵn sàng</span>
                                <a class="nut-xem-xe-chi-nhanh" href="./index.php?chuyen_trang=danhSachXe&chi_nhanh_id=<?php echo (int) $dongChiNhanh["id"]; ?>">Xem xe</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="cot-ban-do-chi-nhanh">
                <?php if ($danhSachChiNhanh): ?>
                    <div id="branchMap"></div>
                <?php else: ?>
                    <div class="ghi-chu-trong">Không có dữ liệu chi nhánh để hiển thị trên bản đồ.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
    (function () {
        var oBanDo = document.getElementById("branchMap");
        var danhSachChiNhanh = document.querySelectorAll("[data-chi-nhanh-map][data-lat]");

        if (!oBanDo || !danhSachChiNhanh.length || typeof L === "undefined") {
            return;
        }

        function taoIconChiNhanh() {
            return L.divIcon({
                className: "",
                html: '<span class="map-marker map-marker--parking"><i class="fa-solid fa-warehouse"></i></span>',
                iconSize: [34, 34],
                iconAnchor: [17, 17],
                popupAnchor: [0, -10],
            });
        }

        var banDo = L.map("branchMap", {
            zoomControl: true,
            scrollWheelZoom: true,
        }).setView([21.03, 105.84], 11);

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        }).addTo(banDo);

        var danhSachMarker = [];

        for (var i = 0; i < danhSachChiNhanh.length; i++) {
            var oChiNhanh = danhSachChiNhanh[i];
            var viDo = Number(oChiNhanh.dataset.lat);
            var kinhDo = Number(oChiNhanh.dataset.lng);
            var tenChiNhanh = oChiNhanh.dataset.title || "Chi nhánh";
            var diaChi = oChiNhanh.dataset.address || "";

            var marker = L.marker([viDo, kinhDo], {
                icon: taoIconChiNhanh(),
            }).addTo(banDo);

            marker.bindPopup(
                '<div class="map__popup"><h3>' + tenChiNhanh + '</h3><p>' + diaChi + "</p></div>"
            );

            danhSachMarker.push(marker);

            oChiNhanh.addEventListener("click", function () {
                for (var j = 0; j < danhSachChiNhanh.length; j++) {
                    danhSachChiNhanh[j].classList.remove("active");
                }
                oChiNhanh.classList.add("active");
                banDo.setView([viDo, kinhDo], 14, { animate: true });
                marker.openPopup();
            });
        }

        if (danhSachMarker.length) {
            var danhSachToaDo = [];
            for (var k = 0; k < danhSachMarker.length; k++) {
                danhSachToaDo.push(danhSachMarker[k].getLatLng());
            }
            banDo.fitBounds(L.latLngBounds(danhSachToaDo), { padding: [30, 30] });
        }
    })();
</script>
