<?php
/** @var mysqli $conn */
$ketQuaXe = mysqli_query($conn, "SELECT
        x.*,
        c.ten_chi_nhanh
    FROM xe x
    LEFT JOIN chi_nhanh c ON x.chi_nhanh_id = c.id
    ORDER BY FIELD(x.trang_thai, 'san_sang', 'dang_thue', 'bao_duong'), x.gia_thue_ngay ASC, x.id DESC");

$ketQuaChiNhanh = mysqli_query($conn, "SELECT
        c.*,
        COUNT(x.id) AS tong_xe
    FROM chi_nhanh c
    LEFT JOIN xe x ON x.chi_nhanh_id = c.id
    GROUP BY c.id
    ORDER BY c.id ASC");

$danhSachXe = [];
$danhSachChiNhanh = [];
$soXeSanSang = 0;
$soXeDangBan = 0;

while ($dongXe = mysqli_fetch_assoc($ketQuaXe)) {
    if ($dongXe["vi_do_hien_tai"] === null || $dongXe["kinh_do_hien_tai"] === null) {
        continue;
    }

    if (($dongXe["trang_thai"] ?? "") === "san_sang") {
        $soXeSanSang++;
    } else {
        $soXeDangBan++;
    }

    $danhSachXe[] = $dongXe;
}

while ($dongChiNhanh = mysqli_fetch_assoc($ketQuaChiNhanh)) {
    if ($dongChiNhanh["vi_do"] === null || $dongChiNhanh["kinh_do"] === null) {
        continue;
    }

    $danhSachChiNhanh[] = $dongChiNhanh;
}
?>

<style>
    .khung-ban-do-van-hanh {
        padding: 34px 0 12px;
    }

    .dau-muc-ban-do-van-hanh {
        margin-bottom: 20px;
    }

    .dau-muc-ban-do-van-hanh p {
        margin: 0;
        color: var(--muted-color);
    }

    .dau-muc-ban-do-van-hanh h2 {
        margin: 8px 0 10px;
        font-size: 30px;
    }

    .dau-muc-ban-do-van-hanh .mo-ta-ban-do {
        max-width: 880px;
        color: var(--muted-color);
        line-height: 1.7;
    }

    .cum-thong-ke-ban-do {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 18px;
    }

    .o-thong-ke-ban-do {
        padding: 16px;
        border-radius: 18px;
        border: 1px solid #e8eef7;
        background: #fbfdff;
    }

    .o-thong-ke-ban-do span {
        color: var(--muted-color);
    }

    .o-thong-ke-ban-do strong {
        display: block;
        margin: 8px 0 4px;
        font-size: 26px;
    }

    .bo-cuc-ban-do-van-hanh {
        display: grid;
        grid-template-columns: 420px 1fr;
        gap: 20px;
        align-items: start;
    }

    .cot-trai-ban-do,
    .cot-phai-ban-do {
        padding: 18px;
        border-radius: 22px;
        background: #ffffff;
        box-shadow: 0 16px 40px rgba(17, 31, 53, 0.08);
    }

    .cot-trai-ban-do {
        height: 716px;
        overflow-y: auto;
    }

    .nhom-ban-do + .nhom-ban-do {
        margin-top: 20px;
    }

    .nhom-ban-do h3 {
        margin: 0 0 12px;
    }

    .cum-nut-loc-ban-do {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .nut-loc-ban-do {
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

    .nut-loc-ban-do.is-active {
        background: rgba(7, 165, 254, 0.1);
        color: var(--primary-color);
        border-color: rgba(7, 165, 254, 0.24);
    }

    .the-diem-ban-do {
        padding: 16px;
        border: 1px solid #e8eef7;
        border-radius: 16px;
        margin-bottom: 12px;
        cursor: pointer;
        transition: 0.25s ease;
        background: #fbfdff;
    }

    .the-diem-ban-do:hover,
    .the-diem-ban-do.active {
        background: rgba(7, 165, 254, 0.08);
        border-color: rgba(7, 165, 254, 0.35);
    }

    .dong-diem-ban-do {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .dong-diem-ban-do strong {
        font-size: 16px;
    }

    .chu-phu-ban-do {
        margin-top: 10px;
        color: var(--muted-color);
        line-height: 1.6;
    }

    .chan-diem-ban-do {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 12px;
    }

    .nhan-xe-ban-do,
    .nhan-chi-nhanh-ban-do {
        display: inline-flex;
        align-items: center;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 700;
    }

    .nhan-xe-ban-do.mau-xanh {
        background: rgba(22, 196, 127, 0.12);
        color: #16c47f;
    }

    .nhan-xe-ban-do.mau-duong {
        background: rgba(7, 165, 254, 0.12);
        color: var(--primary-color);
    }

    .nhan-xe-ban-do.mau-cam {
        background: rgba(255, 159, 67, 0.16);
        color: #b97726;
    }

    .nhan-xe-ban-do.mau-xam {
        background: rgba(109, 121, 136, 0.14);
        color: #526072;
    }

    .nhan-chi-nhanh-ban-do {
        background: rgba(22, 196, 127, 0.12);
        color: #16c47f;
    }

    .khung-thong-bao-ban-do {
        padding: 14px 16px;
        border-radius: 14px;
        border: 1px dashed #d6e0ec;
        color: var(--muted-color);
        background: #ffffff;
    }

    .cum-thao-tac-ban-do {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .nut-lay-vi-tri-ban-do {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 18px;
        border-radius: 14px;
        border: 0;
        background: var(--primary-color);
        color: #ffffff;
        font-weight: 700;
        box-shadow: 0 10px 20px rgba(7, 165, 254, 0.22);
    }

    .chu-thich-ban-do {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .thanh-dieu-khien-ban-do {
        margin-bottom: 14px;
    }

    .cot-phai-ban-do > .cum-thao-tac-ban-do,
    .cot-phai-ban-do > .chu-thich-ban-do {
        display: none;
    }

    .muc-chu-thich-ban-do {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--muted-color);
        font-size: 14px;
    }

    .cham-chu-thich-ban-do {
        width: 12px;
        height: 12px;
        border-radius: 999px;
        display: inline-block;
    }

    .cham-xe-san-sang {
        background: var(--primary-color);
    }

    .cham-xe-khong-san-sang {
        background: var(--warning-color);
    }

    .cham-chi-nhanh {
        background: var(--success-color);
    }

    .cham-nguoi-dung {
        background: var(--danger-color);
    }

    #userMap {
        height: 680px;
        width: 100%;
        border-radius: 20px;
    }

    .duong-dan-ban-do {
        margin-top: 10px;
        color: var(--muted-color);
        font-size: 14px;
    }

    @media (max-width: 860px) {
        .cum-thong-ke-ban-do,
        .bo-cuc-ban-do-van-hanh {
            grid-template-columns: 1fr;
        }

        .cot-trai-ban-do {
            height: auto;
            max-height: 520px;
        }
    }
</style>

<section class="khung-ban-do-van-hanh">
    <div class="main__content">
        <div class="dau-muc-ban-do-van-hanh">
            <p>Bản đồ vận hành</p>
            <h2>Theo dõi xe và chi nhánh trên cùng một bản đồ</h2>
            <div class="mo-ta-ban-do">Trang này dùng GPS để xác định vị trí của bạn, xem xe gần nhất, tìm chi nhánh và focus nhanh từng điểm trên bản đồ.</div>
        </div>

        <div class="cum-thong-ke-ban-do">
            <article class="o-thong-ke-ban-do">
                <span>Tổng điểm</span>
                <strong><?php echo esc(count($danhSachXe) + count($danhSachChiNhanh)); ?></strong>
                <small style="color: var(--muted-color);">Xe và chi nhánh có tọa độ</small>
            </article>
            <article class="o-thong-ke-ban-do">
                <span>Xe sẵn sàng</span>
                <strong><?php echo esc($soXeSanSang); ?></strong>
                <small style="color: var(--muted-color);">Có thể đặt ngay</small>
            </article>
            <article class="o-thong-ke-ban-do">
                <span>Chi nhánh</span>
                <strong><?php echo esc(count($danhSachChiNhanh)); ?></strong>
                <small style="color: var(--muted-color);">Điểm giao nhận xe</small>
            </article>
        </div>

        <div class="thanh-dieu-khien-ban-do">
            <div class="cum-thao-tac-ban-do">
                <button class="nut-lay-vi-tri-ban-do" type="button" id="detectUserLocation">Lấy vị trí của bạn</button>
            </div>
            <div class="chu-thich-ban-do">
                <div class="muc-chu-thich-ban-do"><span class="cham-chu-thich-ban-do cham-xe-san-sang"></span> Xe sẵn sàng</div>
                <div class="muc-chu-thich-ban-do"><span class="cham-chu-thich-ban-do cham-xe-khong-san-sang"></span> Xe không sẵn sàng</div>
                <div class="muc-chu-thich-ban-do"><span class="cham-chu-thich-ban-do cham-chi-nhanh"></span> Chi nhánh</div>
                <div class="muc-chu-thich-ban-do"><span class="cham-chu-thich-ban-do cham-nguoi-dung"></span> Vị trí của bạn</div>
            </div>
        </div>

        <div class="bo-cuc-ban-do-van-hanh">
            <aside class="cot-trai-ban-do">
                <div class="nhom-ban-do">
                    <h3>Bộ lọc nhanh</h3>
                    <div class="cum-nut-loc-ban-do">
                        <button class="nut-loc-ban-do is-active" type="button" data-map-filter="all">Tất cả</button>
                        <button class="nut-loc-ban-do" type="button" data-map-filter="ready">Xe sẵn sàng</button>
                        <button class="nut-loc-ban-do" type="button" data-map-filter="busy">Xe không sẵn sàng</button>
                        <button class="nut-loc-ban-do" type="button" data-map-filter="parking">Chi nhánh</button>
                    </div>
                    <div id="selectedUserInfo" class="khung-thong-bao-ban-do">Chưa có vị trí của bạn. Bấm nút GPS để xem gợi ý gần nhất.</div>
                    <div id="userGeoStatus" class="duong-dan-ban-do">Hệ thống sẽ ưu tiên xe sẵn sàng gần bạn nhất.</div>
                    <div id="userMapRouteInfo" class="duong-dan-ban-do">Chưa có tuyến đường.</div>
                </div>

                <div class="nhom-ban-do">
                    <h3>Xe gần bạn</h3>
                    <div id="nearbyRentalCarsList">
                        <div class="khung-thong-bao-ban-do">Lấy vị trí của bạn để xem danh sách xe gần nhất.</div>
                    </div>
                </div>

                <div class="nhom-ban-do">
                    <h3>Chi nhánh gần bạn</h3>
                    <div id="nearestBranchInfo">
                        <div class="khung-thong-bao-ban-do">Lấy vị trí của bạn để xem chi nhánh gần nhất.</div>
                    </div>
                </div>

                <div class="nhom-ban-do">
                    <h3>Danh sách điểm trên map</h3>
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
                        <article
                            class="the-diem-ban-do"
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
                        >
                            <div class="dong-diem-ban-do">
                                <strong><?php echo esc($dongXe["ten_xe"]); ?></strong>
                                <span class="nhan-xe-ban-do <?php echo esc($mauTrangThaiXe); ?>">
                                    <?php echo esc($tenTrangThaiXe); ?>
                                </span>
                            </div>
                            <div class="chu-phu-ban-do"><?php echo esc($dongXe["bien_so"]); ?> | <?php echo esc($dongXe["ten_chi_nhanh"] ?: "Đang cập nhật chi nhánh"); ?></div>
                            <div class="chan-diem-ban-do">
                                <span class="chu-phu-ban-do" style="margin-top:0;"><?php echo format_money($dongXe["gia_thue_ngay"]); ?>/ngày</span>
                            </div>
                        </article>
                    <?php endforeach; ?>

                    <?php foreach ($danhSachChiNhanh as $dongChiNhanh): ?>
                        <article
                            class="the-diem-ban-do"
                            data-item-id="parking-<?php echo (int) $dongChiNhanh["id"]; ?>"
                            data-map-target="parking"
                            data-chi-nhanh-id="<?php echo (int) $dongChiNhanh["id"]; ?>"
                            data-ten-chi-nhanh="<?php echo esc($dongChiNhanh["ten_chi_nhanh"]); ?>"
                            data-dia-chi="<?php echo esc($dongChiNhanh["dia_chi"]); ?>"
                            data-mo-ta="<?php echo esc($dongChiNhanh["mo_ta"] ?? ""); ?>"
                            data-so-xe="<?php echo (int) $dongChiNhanh["tong_xe"]; ?>"
                            data-vi-do="<?php echo esc($dongChiNhanh["vi_do"]); ?>"
                            data-kinh-do="<?php echo esc($dongChiNhanh["kinh_do"]); ?>"
                        >
                            <div class="dong-diem-ban-do">
                                <strong><?php echo esc($dongChiNhanh["ten_chi_nhanh"]); ?></strong>
                                <span class="nhan-chi-nhanh-ban-do">Chi nhánh</span>
                            </div>
                            <div class="chu-phu-ban-do"><?php echo esc($dongChiNhanh["dia_chi"]); ?></div>
                            <div class="chan-diem-ban-do">
                                <span class="chu-phu-ban-do" style="margin-top:0;"><?php echo esc((int) $dongChiNhanh["tong_xe"]); ?> xe tại điểm này</span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </aside>

            <div class="cot-phai-ban-do">
                <div class="cum-thao-tac-ban-do">
                    <button class="nut-lay-vi-tri-ban-do" type="button" id="detectUserLocation">Lấy vị trí của bạn</button>
                </div>
                <div class="chu-thich-ban-do">
                    <div class="muc-chu-thich-ban-do"><span class="cham-chu-thich-ban-do cham-xe-san-sang"></span> Xe sẵn sàng</div>
                    <div class="muc-chu-thich-ban-do"><span class="cham-chu-thich-ban-do cham-xe-khong-san-sang"></span> Xe không sẵn sàng</div>
                    <div class="muc-chu-thich-ban-do"><span class="cham-chu-thich-ban-do cham-chi-nhanh"></span> Chi nhánh</div>
                    <div class="muc-chu-thich-ban-do"><span class="cham-chu-thich-ban-do cham-nguoi-dung"></span> Vị trí của bạn</div>
                </div>
                <div id="userMap"></div>
            </div>
        </div>
    </div>
</section>

<script>
    (function () {
        var oBanDo = document.getElementById("userMap");
        var oThongTinViTri = document.getElementById("selectedUserInfo");
        var oThongBaoGps = document.getElementById("userGeoStatus");
        var oThongTinDuongDi = document.getElementById("userMapRouteInfo");
        var oDanhSachXeGan = document.getElementById("nearbyRentalCarsList");
        var oChiNhanhGan = document.getElementById("nearestBranchInfo");
        var nutLayViTri = document.getElementById("detectUserLocation");
        var cacNutLoc = document.querySelectorAll("[data-map-filter]");
        var danhSachThe = document.querySelectorAll("[data-item-id][data-map-target]");

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

        function taoIcon(loai, bienThe) {
            var tenClass = "map-marker map-marker--vehicle";
            var bieuTuong = '<i class="fa-solid fa-car-side"></i>';

            if (loai === "chi_nhanh") {
                tenClass = "map-marker map-marker--parking";
                bieuTuong = '<i class="fa-solid fa-warehouse"></i>';
            }
            if (loai === "nguoi_dung") {
                tenClass = "map-marker map-marker--user";
                bieuTuong = '<i class="fa-solid fa-location-dot"></i>';
            }
            if (loai === "xe" && bienThe === "busy") {
                tenClass = "map-marker map-marker--vehicle-busy";
            }

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

        var danhSachXeMap = [];
        var danhSachChiNhanhMap = [];

        for (var i = 0; i < danhSachThe.length; i++) {
            if (danhSachThe[i].dataset.mapTarget === "vehicle") {
                danhSachXeMap.push({
                    id: Number(danhSachThe[i].dataset.xeId),
                    ten_xe: danhSachThe[i].dataset.tenXe || "",
                    bien_so: danhSachThe[i].dataset.bienSo || "",
                    ten_chi_nhanh: danhSachThe[i].dataset.tenChiNhanh || "",
                    gia_thue_ngay: Number(danhSachThe[i].dataset.giaThueNgay || 0),
                    trang_thai: danhSachThe[i].dataset.trangThai || "",
                    vi_do_hien_tai: Number(danhSachThe[i].dataset.viDo || 0),
                    kinh_do_hien_tai: Number(danhSachThe[i].dataset.kinhDo || 0)
                });
                continue;
            }

            danhSachChiNhanhMap.push({
                id: Number(danhSachThe[i].dataset.chiNhanhId),
                ten_chi_nhanh: danhSachThe[i].dataset.tenChiNhanh || "",
                dia_chi: danhSachThe[i].dataset.diaChi || "",
                mo_ta: danhSachThe[i].dataset.moTa || "",
                tong_xe: Number(danhSachThe[i].dataset.soXe || 0),
                vi_do: Number(danhSachThe[i].dataset.viDo || 0),
                kinh_do: Number(danhSachThe[i].dataset.kinhDo || 0)
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

        var danhSachMarkerXe = [];
        var danhSachMarkerChiNhanh = [];
        var viTriKhach = null;
        var markerKhach = null;
        var duongDiHienTai = null;
        var idXeDangChon = 0;
        var idChiNhanhDangChon = 0;

        function capNhatDanhSachXeGan() {
            if (!oDanhSachXeGan || !viTriKhach) {
                return;
            }

            var danhSachSapXep = [];
            for (var i = 0; i < danhSachXeMap.length; i++) {
                danhSachSapXep.push({
                    id: danhSachXeMap[i].id,
                    ten_xe: danhSachXeMap[i].ten_xe,
                    bien_so: danhSachXeMap[i].bien_so,
                    ten_chi_nhanh: danhSachXeMap[i].ten_chi_nhanh,
                    gia_thue_ngay: danhSachXeMap[i].gia_thue_ngay,
                    trang_thai: danhSachXeMap[i].trang_thai,
                    vi_do_hien_tai: danhSachXeMap[i].vi_do_hien_tai,
                    kinh_do_hien_tai: danhSachXeMap[i].kinh_do_hien_tai,
                    khoang_cach: tinhKhoangCach(
                        viTriKhach.lat,
                        viTriKhach.lng,
                        Number(danhSachXeMap[i].vi_do_hien_tai),
                        Number(danhSachXeMap[i].kinh_do_hien_tai)
                    )
                });
            }

            danhSachSapXep.sort(function (a, b) {
                return a.khoang_cach - b.khoang_cach;
            });

            var xeSanSang = [];
            for (var j = 0; j < danhSachSapXep.length; j++) {
                if ((danhSachSapXep[j].trang_thai || "") === "san_sang") {
                    xeSanSang.push(danhSachSapXep[j]);
                }
            }
            var danhSachHienThi = (xeSanSang.length ? xeSanSang : danhSachSapXep).slice(0, 5);

            if (!danhSachHienThi.length) {
                oDanhSachXeGan.innerHTML = '<div class="khung-thong-bao-ban-do">Chưa có xe nào được cập nhật vị trí trên bản đồ.</div>';
                return;
            }

            var htmlXeGan = "";
            for (var k = 0; k < danhSachHienThi.length; k++) {
                htmlXeGan += '' +
                    '<button class="nearby__item" type="button" data-xe-gan="' + danhSachHienThi[k].id + '">' +
                        '<strong>' + lamSach(danhSachHienThi[k].ten_xe) + '</strong>' +
                        '<div class="card__meta">' + lamSach(danhSachHienThi[k].bien_so) + " | " + lamSach(danhSachHienThi[k].ten_chi_nhanh || "Đang cập nhật chi nhánh") + '</div>' +
                        '<div class="card__meta">' + Number(danhSachHienThi[k].gia_thue_ngay).toLocaleString("vi-VN") + 'đ/ngày</div>' +
                        '<div class="card__meta">Cách bạn ~ ' + danhSachHienThi[k].khoang_cach.toFixed(2) + ' km</div>' +
                    "</button>";
            }
            oDanhSachXeGan.innerHTML = htmlXeGan;

            var danhSachNutXeGan = oDanhSachXeGan.querySelectorAll("[data-xe-gan]");
            for (var n = 0; n < danhSachNutXeGan.length; n++) {
                danhSachNutXeGan[n].addEventListener("click", function () {
                    moDuongToiXe(Number(this.dataset.xeGan));
                });
            }
        }

        function capNhatChiNhanhGan() {
            if (!oChiNhanhGan || !viTriKhach) {
                return;
            }

            if (!danhSachChiNhanhMap.length) {
                oChiNhanhGan.innerHTML = '<div class="khung-thong-bao-ban-do">Chưa có chi nhánh nào được cập nhật vị trí.</div>';
                return;
            }

            var chiNhanhGan = null;
            for (var p = 0; p < danhSachChiNhanhMap.length; p++) {
                var thongTinChiNhanh = {
                    id: danhSachChiNhanhMap[p].id,
                    ten_chi_nhanh: danhSachChiNhanhMap[p].ten_chi_nhanh,
                    dia_chi: danhSachChiNhanhMap[p].dia_chi,
                    vi_do: danhSachChiNhanhMap[p].vi_do,
                    kinh_do: danhSachChiNhanhMap[p].kinh_do,
                    khoang_cach: tinhKhoangCach(
                        viTriKhach.lat,
                        viTriKhach.lng,
                        Number(danhSachChiNhanhMap[p].vi_do),
                        Number(danhSachChiNhanhMap[p].kinh_do)
                    )
                };

                if (!chiNhanhGan || thongTinChiNhanh.khoang_cach < chiNhanhGan.khoang_cach) {
                    chiNhanhGan = thongTinChiNhanh;
                }
            }

            oChiNhanhGan.innerHTML = '' +
                "<strong>Chi nhánh gần bạn nhất</strong>" +
                '<div class="card__meta">' + lamSach(chiNhanhGan.ten_chi_nhanh) + "</div>" +
                '<div class="card__meta">' + lamSach(chiNhanhGan.dia_chi) + "</div>" +
                '<div class="card__meta">Cách bạn ~ ' + chiNhanhGan.khoang_cach.toFixed(2) + ' km</div>' +
                '<div class="map__mini-actions">' +
                    '<button class="map__mini-action" type="button" data-chi-nhanh-gan="' + chiNhanhGan.id + '">Chỉ đường tới đây</button>' +
                    '<a class="map__mini-link" href="./index.php?chuyen_trang=danhSachXe&chi_nhanh_id=' + chiNhanhGan.id + '">Xem xe tại đây</a>' +
                "</div>";

            var danhSachNutChiNhanhGan = oChiNhanhGan.querySelectorAll("[data-chi-nhanh-gan]");
            for (var q = 0; q < danhSachNutChiNhanhGan.length; q++) {
                danhSachNutChiNhanhGan[q].addEventListener("click", function () {
                    moDuongToiChiNhanh(Number(this.dataset.chiNhanhGan));
                });
            }
        }

        function capNhatViTriKhach(viTri, nhanNguon) {
            viTriKhach = viTri;

            if (markerKhach) {
                banDo.removeLayer(markerKhach);
            }

            markerKhach = L.marker([viTri.lat, viTri.lng], {
                icon: taoIcon("nguoi_dung")
            }).addTo(banDo);
            markerKhach.bindPopup('<div class="map__popup"><h3>Vị trí của bạn</h3><p>' + lamSach(nhanNguon) + "</p></div>").openPopup();

            if (oThongTinViTri) {
                oThongTinViTri.innerHTML = "<strong>Vị trí hiện tại của bạn</strong><br>" + viTri.lat.toFixed(6) + ", " + viTri.lng.toFixed(6);
            }

            if (oThongBaoGps) {
                oThongBaoGps.textContent = "Đã cập nhật vị trí của bạn.";
            }

            capNhatDanhSachXeGan();
            capNhatChiNhanhGan();

            if (idXeDangChon > 0) {
                moDuongToiXe(idXeDangChon);
                return;
            }

            if (idChiNhanhDangChon > 0) {
                moDuongToiChiNhanh(idChiNhanhDangChon);
                return;
            }

            if (oThongTinDuongDi) {
                oThongTinDuongDi.textContent = "Vị trí đã được cập nhật. Chọn xe hoặc chi nhánh để xem đường đi.";
            }
        }

        async function moDuongToiXe(idXe) {
            var xeDangChon = null;
            for (var i = 0; i < danhSachXeMap.length; i++) {
                if (Number(danhSachXeMap[i].id) === Number(idXe)) {
                    xeDangChon = danhSachXeMap[i];
                    break;
                }
            }

            if (!xeDangChon) {
                return;
            }

            idXeDangChon = Number(idXe);
            idChiNhanhDangChon = 0;
            for (var h = 0; h < danhSachThe.length; h++) {
                if (danhSachThe[h].dataset.itemId === "vehicle-" + xeDangChon.id) {
                    danhSachThe[h].classList.add("active");
                } else {
                    danhSachThe[h].classList.remove("active");
                }
            }

            var diemXe = {
                lat: Number(xeDangChon.vi_do_hien_tai),
                lng: Number(xeDangChon.kinh_do_hien_tai)
            };
            var markerXe = null;
            for (var j = 0; j < danhSachMarkerXe.length; j++) {
                if (danhSachMarkerXe[j].id === xeDangChon.id) {
                    markerXe = danhSachMarkerXe[j];
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
                var duongDan = "https://router.project-osrm.org/route/v1/driving/" + viTriKhach.lng + "," + viTriKhach.lat + ";" + diemXe.lng + "," + diemXe.lat + "?overview=full&geometries=geojson";
                var phanHoi = await fetch(duongDan);
                if (!phanHoi.ok) {
                    throw new Error("loi_duong_di");
                }
                var duLieu = await phanHoi.json();
                if (!duLieu.routes || !duLieu.routes.length) {
                    throw new Error("khong_co_duong");
                }
                var duLieuDuong = duLieu.routes[0];
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

        async function moDuongToiChiNhanh(idChiNhanh) {
            var chiNhanhDangChon = null;
            for (var n = 0; n < danhSachChiNhanhMap.length; n++) {
                if (Number(danhSachChiNhanhMap[n].id) === Number(idChiNhanh)) {
                    chiNhanhDangChon = danhSachChiNhanhMap[n];
                    break;
                }
            }

            if (!chiNhanhDangChon) {
                return;
            }

            idChiNhanhDangChon = Number(idChiNhanh);
            idXeDangChon = 0;
            for (var h = 0; h < danhSachThe.length; h++) {
                if (danhSachThe[h].dataset.itemId === "parking-" + chiNhanhDangChon.id) {
                    danhSachThe[h].classList.add("active");
                } else {
                    danhSachThe[h].classList.remove("active");
                }
            }

            var diemChiNhanh = {
                lat: Number(chiNhanhDangChon.vi_do),
                lng: Number(chiNhanhDangChon.kinh_do)
            };
            var markerChiNhanh = null;
            for (var p = 0; p < danhSachMarkerChiNhanh.length; p++) {
                if (danhSachMarkerChiNhanh[p].id === chiNhanhDangChon.id) {
                    markerChiNhanh = danhSachMarkerChiNhanh[p];
                    break;
                }
            }

            if (markerChiNhanh) {
                markerChiNhanh.marker.openPopup();
            }

            if (!viTriKhach) {
                banDo.setView([diemChiNhanh.lat, diemChiNhanh.lng], 14, { animate: true });
                if (oThongTinDuongDi) {
                    oThongTinDuongDi.textContent = "Hãy chọn vị trí của bạn trước khi chỉ đường tới chi nhánh.";
                }
                return;
            }

            if (duongDiHienTai) {
                banDo.removeLayer(duongDiHienTai);
            }

            try {
                var duongDan = "https://router.project-osrm.org/route/v1/driving/" + viTriKhach.lng + "," + viTriKhach.lat + ";" + diemChiNhanh.lng + "," + diemChiNhanh.lat + "?overview=full&geometries=geojson";
                var phanHoi = await fetch(duongDan);
                if (!phanHoi.ok) {
                    throw new Error("loi_duong_di");
                }
                var duLieu = await phanHoi.json();
                if (!duLieu.routes || !duLieu.routes.length) {
                    throw new Error("khong_co_duong");
                }
                var duLieuDuong = duLieu.routes[0];
                var toaDo = [];
                for (var q = 0; q < duLieuDuong.geometry.coordinates.length; q++) {
                    toaDo.push([duLieuDuong.geometry.coordinates[q][1], duLieuDuong.geometry.coordinates[q][0]]);
                }
                duongDiHienTai = L.polyline(toaDo, {
                    color: "#16c47f",
                    weight: 4
                }).addTo(banDo);
                if (oThongTinDuongDi) {
                    oThongTinDuongDi.textContent = "Đường đến " + chiNhanhDangChon.ten_chi_nhanh + ": " + (duLieuDuong.distance / 1000).toFixed(2) + " km";
                }
            } catch (loi) {
                duongDiHienTai = L.polyline([
                    [viTriKhach.lat, viTriKhach.lng],
                    [diemChiNhanh.lat, diemChiNhanh.lng]
                ], {
                    color: "#16c47f",
                    weight: 4
                }).addTo(banDo);
                if (oThongTinDuongDi) {
                    oThongTinDuongDi.textContent = "Đường đến " + chiNhanhDangChon.ten_chi_nhanh + ": không lấy được chỉ đường thực tế, đang hiển thị đường tạm.";
                }
            }

            banDo.fitBounds(L.latLngBounds([
                [viTriKhach.lat, viTriKhach.lng],
                [diemChiNhanh.lat, diemChiNhanh.lng]
            ]), { padding: [40, 40] });
        }

        function apDungBoLoc(boLoc) {
            for (var i = 0; i < cacNutLoc.length; i++) {
                cacNutLoc[i].classList.toggle("is-active", cacNutLoc[i].dataset.mapFilter === boLoc);
            }

            var danhSachDiem = [];

            for (var j = 0; j < danhSachMarkerXe.length; j++) {
                var hienXe = true;
                if (boLoc === "parking") {
                    hienXe = false;
                } else if (boLoc === "ready") {
                    hienXe = danhSachMarkerXe[j].trang_thai === "san_sang";
                } else if (boLoc === "busy") {
                    hienXe = danhSachMarkerXe[j].trang_thai !== "san_sang";
                }

                if (hienXe) {
                    danhSachMarkerXe[j].marker.addTo(banDo);
                    danhSachDiem.push(danhSachMarkerXe[j].marker.getLatLng());
                } else {
                    banDo.removeLayer(danhSachMarkerXe[j].marker);
                }
            }

            for (var k = 0; k < danhSachMarkerChiNhanh.length; k++) {
                var hienChiNhanh = boLoc === "all" || boLoc === "parking";
                if (hienChiNhanh) {
                    danhSachMarkerChiNhanh[k].marker.addTo(banDo);
                    danhSachDiem.push(danhSachMarkerChiNhanh[k].marker.getLatLng());
                } else {
                    banDo.removeLayer(danhSachMarkerChiNhanh[k].marker);
                }
            }

            if (markerKhach) {
                danhSachDiem.push(markerKhach.getLatLng());
            }

            if (danhSachDiem.length) {
                banDo.fitBounds(L.latLngBounds(danhSachDiem), { padding: [40, 40] });
            }
        }

        for (var r = 0; r < danhSachXeMap.length; r++) {
            var xe = danhSachXeMap[r];
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
                icon: taoIcon("xe", xeSanSang ? "ready" : "busy")
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

            danhSachMarkerXe.push({
                id: xe.id,
                trang_thai: xe.trang_thai,
                marker: markerXe
            });
        }

        for (var s = 0; s < danhSachChiNhanhMap.length; s++) {
            var chiNhanh = danhSachChiNhanhMap[s];
            var markerChiNhanh = L.marker([Number(chiNhanh.vi_do), Number(chiNhanh.kinh_do)], {
                icon: taoIcon("chi_nhanh")
            }).addTo(banDo);

            markerChiNhanh.bindPopup(
                '<div class="map__popup">' +
                    "<h3>" + lamSach(chiNhanh.ten_chi_nhanh) + "</h3>" +
                    "<p>" + lamSach(chiNhanh.dia_chi) + "</p>" +
                    "<p>" + lamSach(chiNhanh.mo_ta || "Điểm giao nhận xe đã được xác định trên bản đồ.") + "</p>" +
                    '<div class="map__popup-actions">' +
                        '<button class="map__popup-action" type="button" data-route-chi-nhanh="' + chiNhanh.id + '">Chỉ đường tới đây</button>' +
                        '<button class="map__popup-action" type="button" data-open-route-chi-nhanh="' + chiNhanh.id + '">Mở bên ngoài</button>' +
                        '<a class="map__popup-link" href="./index.php?chuyen_trang=danhSachXe&chi_nhanh_id=' + chiNhanh.id + '">Xem xe tại đây</a>' +
                    "</div>" +
                "</div>"
            );

            danhSachMarkerChiNhanh.push({
                id: chiNhanh.id,
                marker: markerChiNhanh
            });
        }

        if (danhSachMarkerXe.length || danhSachMarkerChiNhanh.length) {
            var tatCaDiem = [];
            for (var t = 0; t < danhSachMarkerXe.length; t++) {
                tatCaDiem.push(danhSachMarkerXe[t].marker.getLatLng());
            }
            for (var u = 0; u < danhSachMarkerChiNhanh.length; u++) {
                tatCaDiem.push(danhSachMarkerChiNhanh[u].marker.getLatLng());
            }
            banDo.fitBounds(L.latLngBounds(tatCaDiem), { padding: [40, 40] });
        }

        for (var n = 0; n < danhSachThe.length; n++) {
            danhSachThe[n].addEventListener("click", function () {
                if (this.dataset.mapTarget === "vehicle") {
                    moDuongToiXe(Number(this.dataset.xeId));
                    return;
                }
                moDuongToiChiNhanh(Number(this.dataset.chiNhanhId));
            });
        }

        for (var p = 0; p < cacNutLoc.length; p++) {
            cacNutLoc[p].addEventListener("click", function () {
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

            var danhSachNutRouteChiNhanh = oPopup.querySelectorAll("[data-route-chi-nhanh]");
            for (var j = 0; j < danhSachNutRouteChiNhanh.length; j++) {
                danhSachNutRouteChiNhanh[j].addEventListener("click", function () {
                    moDuongToiChiNhanh(Number(this.dataset.routeChiNhanh));
                });
            }

            var danhSachNutMoNgoaiXe = oPopup.querySelectorAll("[data-open-route-xe]");
            for (var k = 0; k < danhSachNutMoNgoaiXe.length; k++) {
                danhSachNutMoNgoaiXe[k].addEventListener("click", function () {
                    if (!viTriKhach) {
                        if (oThongTinDuongDi) {
                            oThongTinDuongDi.textContent = "Hãy chọn vị trí của bạn trước khi mở chỉ đường ngoài.";
                        }
                        return;
                    }

                    var xeDangChon = null;
                    for (var m = 0; m < danhSachXeMap.length; m++) {
                        if (Number(danhSachXeMap[m].id) === Number(this.dataset.openRouteXe)) {
                            xeDangChon = danhSachXeMap[m];
                            break;
                        }
                    }
                    if (!xeDangChon) {
                        return;
                    }

                    var duongDanNgoai = "https://www.openstreetmap.org/directions?engine=fossgis_osrm_car&route=" + viTriKhach.lat + "%2C" + viTriKhach.lng + "%3B" + Number(xeDangChon.vi_do_hien_tai) + "%2C" + Number(xeDangChon.kinh_do_hien_tai);
                    window.open(duongDanNgoai, "_blank", "noopener,noreferrer");
                });
            }

            var danhSachNutMoNgoaiChiNhanh = oPopup.querySelectorAll("[data-open-route-chi-nhanh]");
            for (var n = 0; n < danhSachNutMoNgoaiChiNhanh.length; n++) {
                danhSachNutMoNgoaiChiNhanh[n].addEventListener("click", function () {
                    if (!viTriKhach) {
                        if (oThongTinDuongDi) {
                            oThongTinDuongDi.textContent = "Hãy chọn vị trí của bạn trước khi mở chỉ đường ngoài.";
                        }
                        return;
                    }

                    var chiNhanhDangChon = null;
                    for (var p = 0; p < danhSachChiNhanhMap.length; p++) {
                        if (Number(danhSachChiNhanhMap[p].id) === Number(this.dataset.openRouteChiNhanh)) {
                            chiNhanhDangChon = danhSachChiNhanhMap[p];
                            break;
                        }
                    }
                    if (!chiNhanhDangChon) {
                        return;
                    }

                    var duongDanNgoai = "https://www.openstreetmap.org/directions?engine=fossgis_osrm_car&route=" + viTriKhach.lat + "%2C" + viTriKhach.lng + "%3B" + Number(chiNhanhDangChon.vi_do) + "%2C" + Number(chiNhanhDangChon.kinh_do);
                    window.open(duongDanNgoai, "_blank", "noopener,noreferrer");
                });
            }
        });

        if (nutLayViTri) {
            nutLayViTri.addEventListener("click", function () {
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
                        var viTriMoi = {
                            lat: viTri.coords.latitude,
                            lng: viTri.coords.longitude
                        };
                        capNhatViTriKhach(viTriMoi, "Lấy từ GPS của trình duyệt.");
                        banDo.setView([viTriMoi.lat, viTriMoi.lng], 14, { animate: true });
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
            });
        }
    })();
</script>
