<?php
/** @var mysqli $conn */
if (!isset($_SESSION["hoTen"], $_SESSION["vaiTro"], $_SESSION["idNguoiDung"])) {
    header("Location: ./auth/dangNhap.php");
    exit;
}

if (($_SESSION["vaiTro"] ?? "") !== "khach_hang") {
    echo '<section class="page__section"><div class="main__content"><div class="alert alert--danger">Chỉ tài khoản khách thuê xe mới được tạo đơn đặt xe.</div></div></section>';
    return;
}

$idXe = (int) ($_GET["xe_id"] ?? 0);
$sqlXe = "SELECT x.*, c.ten_chi_nhanh
    FROM xe x
    LEFT JOIN chi_nhanh c ON x.chi_nhanh_id = c.id
    WHERE x.id = $idXe
    LIMIT 1";
$ketQuaXe = mysqli_query($conn, $sqlXe);
$xe = $ketQuaXe ? mysqli_fetch_assoc($ketQuaXe) : null;

if (!$xe) {
    echo '<section class="page__section"><div class="main__content"><div class="alert alert--danger">Xe không tồn tại.</div></div></section>';
    return;
}

$thongBao = "";
$loi = "";
$idNguoiChoThue = (int) $xe["nguoi_cho_thue_id"];
$ketQuaChiNhanh = mysqli_query(
    $conn,
    "SELECT id, ten_chi_nhanh, dia_chi, vi_do, kinh_do
    FROM chi_nhanh
    WHERE nguoi_cho_thue_id = $idNguoiChoThue
    ORDER BY ten_chi_nhanh"
);

$danhSachChiNhanh = [];
while ($ketQuaChiNhanh && ($dongChiNhanh = mysqli_fetch_assoc($ketQuaChiNhanh))) {
    $danhSachChiNhanh[] = $dongChiNhanh;
}

$nhanTrangThaiXe = [
    "san_sang" => "Sẵn sàng",
    "dang_thue" => "Đang thuê",
    "bao_duong" => "Bảo dưỡng",
    "tam_ngung" => "Tạm ngưng",
];
$tenTrangThaiXe = $nhanTrangThaiXe[$xe["trang_thai"] ?? ""] ?? "Đang cập nhật";

$giaThueNgay = (float) ($xe["gia_thue_ngay"] ?? 0);
$giaTheoNgay = format_money($giaThueNgay) . "/ngày";

$giaTriForm = [
    "ngay_nhan" => trim((string) ($_POST["ngay_nhan"] ?? "")),
    "ngay_tra" => trim((string) ($_POST["ngay_tra"] ?? "")),
    "chi_nhanh_nhan_id" => (string) ($_POST["chi_nhanh_nhan_id"] ?? ""),
    "chi_nhanh_tra_id" => (string) ($_POST["chi_nhanh_tra_id"] ?? ""),
    "dia_chi_khach" => trim((string) ($_POST["dia_chi_khach"] ?? "")),
    "vi_do_khach" => trim((string) ($_POST["vi_do_khach"] ?? "")),
    "kinh_do_khach" => trim((string) ($_POST["kinh_do_khach"] ?? "")),
    "ghi_chu" => trim((string) ($_POST["ghi_chu"] ?? "")),
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ngayNhan = $giaTriForm["ngay_nhan"];
    $ngayTra = $giaTriForm["ngay_tra"];
    $idChiNhanhNhan = (int) $giaTriForm["chi_nhanh_nhan_id"];
    $idChiNhanhTra = (int) $giaTriForm["chi_nhanh_tra_id"];
    $diaChiKhach = mysqli_real_escape_string($conn, $giaTriForm["dia_chi_khach"]);
    $viDoKhach = (float) ($giaTriForm["vi_do_khach"] !== "" ? $giaTriForm["vi_do_khach"] : 0);
    $kinhDoKhach = (float) ($giaTriForm["kinh_do_khach"] !== "" ? $giaTriForm["kinh_do_khach"] : 0);
    $ghiChu = mysqli_real_escape_string($conn, $giaTriForm["ghi_chu"]);

    if ($ngayNhan === "" || $ngayTra === "" || strtotime($ngayTra) < strtotime($ngayNhan)) {
        $loi = "Ngày nhận và ngày trả không hợp lệ.";
    } elseif (strtotime($ngayNhan) < strtotime(date("Y-m-d"))) {
        $loi = "Không thể đặt xe với ngày nhận trong quá khứ.";
    } elseif ($viDoKhach < -90 || $viDoKhach > 90 || $kinhDoKhach < -180 || $kinhDoKhach > 180 || ($viDoKhach == 0.0 && $kinhDoKhach == 0.0)) {
        $loi = "Vị trí khách thuê không hợp lệ. Hãy bấm Lấy vị trí hiện tại và cho phép trình duyệt truy cập GPS.";
    } elseif (($xe["trang_thai"] ?? "") === "bao_duong") {
        $loi = "Xe đang bảo dưỡng, vui lòng chọn xe khác.";
    } else {
        $soNgayThue = booking_days_count($ngayNhan, $ngayTra);
        $tongTien = $soNgayThue * $giaThueNgay;
        $idNguoiDung = (int) $_SESSION["idNguoiDung"];
        $kiemTraNhan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM chi_nhanh WHERE id = $idChiNhanhNhan AND nguoi_cho_thue_id = $idNguoiChoThue LIMIT 1"));
        $kiemTraTra = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM chi_nhanh WHERE id = $idChiNhanhTra AND nguoi_cho_thue_id = $idNguoiChoThue LIMIT 1"));
        $sqlKiemTraLich = "SELECT COUNT(*) AS tong_don
            FROM dat_xe
            WHERE xe_id = $idXe
            AND trang_thai IN ('cho_xac_nhan', 'da_xac_nhan', 'dang_thue')
            AND ('$ngayNhan' < ngay_tra AND '$ngayTra' > ngay_nhan)";
        $dongKiemTraLich = mysqli_fetch_assoc(mysqli_query($conn, $sqlKiemTraLich));

        if (!$kiemTraNhan || !$kiemTraTra) {
            $loi = "Chi nhánh nhận/trả xe không hợp lệ với người cho thuê của xe này.";
        } elseif ((int) ($dongKiemTraLich["tong_don"] ?? 0) > 0) {
            $loi = "Xe đã có lịch trong khoảng thời gian này.";
        } else {
            $ngayNhanLuu = $ngayNhan . " 00:00:00";
            $ngayTraLuu = $ngayTra . " 00:00:00";
            $sqlThemDon = "INSERT INTO dat_xe (
                    nguoi_dung_id, xe_id, chi_nhanh_nhan_id, chi_nhanh_tra_id,
                    dia_chi_khach, vi_do_khach, kinh_do_khach,
                    ngay_nhan, ngay_tra, thoi_diem_tra_thuc_te,
                    tong_tien, phi_phat_tra_muon, ghi_chu, trang_thai, created_at
                ) VALUES (
                    $idNguoiDung, $idXe, $idChiNhanhNhan, $idChiNhanhTra,
                    '$diaChiKhach', $viDoKhach, $kinhDoKhach,
                    '$ngayNhanLuu', '$ngayTraLuu', NULL,
                    $tongTien, 0, '$ghiChu', 'cho_xac_nhan', NOW()
                )";

            if (mysqli_query($conn, $sqlThemDon)) {
                $thongBao = "Đặt xe thành công. Đơn của bạn đang chờ xác nhận.";
                $giaTriForm = [
                    "ngay_nhan" => "",
                    "ngay_tra" => "",
                    "chi_nhanh_nhan_id" => "",
                    "chi_nhanh_tra_id" => "",
                    "dia_chi_khach" => "",
                    "vi_do_khach" => "",
                    "kinh_do_khach" => "",
                    "ghi_chu" => "",
                ];
            } else {
                $loi = "Không thể tạo đơn đặt xe.";
            }
        }
    }
}
?>

<style>
    .khung-dat-xe {
        padding: 34px 0 12px;
    }

    .noi-dung-dat-xe {
        display: grid;
        grid-template-columns: 1.1fr 0.9fr;
        gap: 22px;
    }

    .cot-thong-tin-dat-xe,
    .cot-form-dat-xe {
        padding: 28px;
        border-radius: 24px;
        background: #ffffff;
        box-shadow: 0 16px 40px rgba(17, 31, 53, 0.08);
    }

    .dau-muc-dat-xe {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
    }

    .dau-muc-dat-xe p {
        margin: 0;
        color: var(--muted-color);
    }

    .dau-muc-dat-xe h1,
    .dau-muc-dat-xe h2 {
        margin: 8px 0 0;
    }

    .gia-dat-xe {
        font-size: 30px;
        font-weight: 900;
    }

    .anh-xe-dat-xe {
        width: 100%;
        aspect-ratio: 16 / 9;
        margin-top: 20px;
        overflow: hidden;
        border-radius: 20px;
        background: #eaf2fb;
    }

    .anh-xe-dat-xe img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
    }

    .anh-trong-dat-xe {
        width: 100%;
        height: 100%;
        display: grid;
        place-items: center;
        color: var(--muted-color);
        font-weight: 700;
    }

    .luoi-thong-tin-dat-xe {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin: 22px 0;
    }

    .o-thong-tin-dat-xe {
        padding: 14px 15px;
        border-radius: 16px;
        border: 1px solid #e5edf7;
        background: #f8fbff;
        color: var(--muted-color);
    }

    .o-thong-tin-dat-xe strong {
        display: block;
        margin-top: 6px;
        color: var(--text-color);
    }

    .hop-quy-dinh-dat-xe {
        margin-top: 18px;
        padding: 20px;
        border-radius: 20px;
        background: #f8fbff;
        border: 1px solid #e5edf7;
    }

    .hop-quy-dinh-dat-xe h3 {
        margin: 0 0 12px;
    }

    .hop-quy-dinh-dat-xe ul {
        margin: 0;
        padding-left: 18px;
        color: var(--muted-color);
        line-height: 1.8;
    }

    .dong-nhap-dat-xe {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 16px;
    }

    .dong-nhap-dat-xe label {
        font-weight: 600;
    }

    .dong-nhap-dat-xe input,
    .dong-nhap-dat-xe select,
    .dong-nhap-dat-xe textarea {
        width: 100%;
        padding: 13px 14px;
        border-radius: 14px;
        border: 1px solid #d9e3ef;
        background: #ffffff;
        font-size: 15px;
        font-family: inherit;
    }

    .dong-nhap-dat-xe textarea {
        min-height: 110px;
        resize: vertical;
    }

    .luoi-toa-do-khach {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .hop-ban-do-khach {
        padding: 16px;
        border-radius: 18px;
        border: 1px solid #e5edf7;
        background: #fbfdff;
    }

    .nut-ban-do-khach {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 14px;
        border-radius: 12px;
        border: 1px solid #dbe5f2;
        background: #ffffff;
        color: var(--text-color);
        font-weight: 700;
    }

    .cum-thao-tac-ban-do-khach {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin: 10px 0 12px;
    }

    .ghi-chu-ban-do-khach {
        color: var(--muted-color);
        font-size: 14px;
        line-height: 1.6;
    }

    .duong-di-khach {
        margin-top: 10px;
        color: var(--muted-color);
        font-size: 14px;
    }

    #bookingLocationMap {
        height: 320px;
        width: 100%;
        margin-top: 10px;
        border-radius: 18px;
    }

    .thong-bao-loi,
    .thong-bao-thanh-cong {
        margin-bottom: 18px;
        padding: 14px 16px;
        border-radius: 16px;
    }

    .thong-bao-loi {
        background: rgba(255, 95, 95, 0.12);
        color: #cf3636;
    }

    .thong-bao-thanh-cong {
        background: rgba(22, 196, 127, 0.12);
        color: #0f8d5c;
    }

    .nut-xac-nhan-dat-xe {
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

    @media (max-width: 860px) {
        .noi-dung-dat-xe {
            grid-template-columns: 1fr;
        }

        .cot-thong-tin-dat-xe,
        .cot-form-dat-xe {
            padding: 22px;
        }

        .luoi-thong-tin-dat-xe,
        .luoi-toa-do-khach {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="khung-dat-xe">
    <div class="main__content">
        <div class="noi-dung-dat-xe">
            <div class="cot-thong-tin-dat-xe">
                <div class="dau-muc-dat-xe">
                    <div>
                        <p>Thông tin xe đang đặt</p>
                        <h1><?php echo esc($xe["ten_xe"]); ?></h1>
                    </div>
                    <div class="gia-dat-xe"><?php echo esc($giaTheoNgay); ?></div>
                </div>

                <div class="anh-xe-dat-xe">
                    <?php if (!empty($xe["anh_xe"])): ?>
                        <img src="<?php echo esc($xe["anh_xe"]); ?>" alt="<?php echo esc($xe["ten_xe"]); ?>">
                    <?php else: ?>
                        <div class="anh-trong-dat-xe">Xe chưa có ảnh</div>
                    <?php endif; ?>
                </div>

                <div class="luoi-thong-tin-dat-xe">
                    <div class="o-thong-tin-dat-xe">Biển số<strong><?php echo esc($xe["bien_so"]); ?></strong></div>
                    <div class="o-thong-tin-dat-xe">Chi nhánh<strong><?php echo esc($xe["ten_chi_nhanh"]); ?></strong></div>
                    <div class="o-thong-tin-dat-xe">Số chỗ<strong><?php echo esc($xe["so_cho"]); ?> chỗ</strong></div>
                    <div class="o-thong-tin-dat-xe">Trạng thái<strong><?php echo esc($tenTrangThaiXe); ?></strong></div>
                </div>

                <p style="margin:0; color: var(--muted-color); line-height: 1.7;"><?php echo nl2br(esc($xe["mo_ta"] ?? "")); ?></p>

                <div class="hop-quy-dinh-dat-xe">
                    <h3>Quy định đặt xe</h3>
                    <ul>
                        <li>Khách gửi đơn trước, người cho thuê xe sẽ xác nhận sau.</li>
                        <li>Xe chỉ chuyển sang trạng thái đang thuê khi bên cho thuê bàn giao xe.</li>
                        <li>Trả muộn hiện tính phạt 30% giá thuê ngày cho mỗi ngày trễ.</li>
                    </ul>
                </div>
            </div>

            <div class="cot-form-dat-xe">
                <p style="margin:0; color: var(--muted-color);">Tạo đơn thuê</p>
                <h2 style="margin:8px 0 18px;">Đặt xe nhanh</h2>

                <?php if ($thongBao !== ""): ?>
                    <div class="thong-bao-thanh-cong"><?php echo esc($thongBao); ?></div>
                <?php endif; ?>

                <?php if ($loi !== ""): ?>
                    <div class="thong-bao-loi"><?php echo esc($loi); ?></div>
                <?php endif; ?>

                <form action="" method="post">
                    <div class="dong-nhap-dat-xe">
                        <label>Ngày nhận xe</label>
                        <input type="date" name="ngay_nhan" value="<?php echo esc($giaTriForm["ngay_nhan"]); ?>" required>
                    </div>

                    <div class="dong-nhap-dat-xe">
                        <label>Ngày trả xe</label>
                        <input type="date" name="ngay_tra" value="<?php echo esc($giaTriForm["ngay_tra"]); ?>" required>
                    </div>

                    <div class="dong-nhap-dat-xe">
                        <label>Chi nhánh nhận xe</label>
                        <select name="chi_nhanh_nhan_id" required>
                            <option value="">Chọn chi nhánh</option>
                            <?php foreach ($danhSachChiNhanh as $dongChiNhanh): ?>
                                <option
                                    value="<?php echo (int) $dongChiNhanh["id"]; ?>"
                                    data-ten-chi-nhanh="<?php echo esc($dongChiNhanh["ten_chi_nhanh"]); ?>"
                                    data-dia-chi="<?php echo esc($dongChiNhanh["dia_chi"]); ?>"
                                    data-vi-do="<?php echo esc($dongChiNhanh["vi_do"]); ?>"
                                    data-kinh-do="<?php echo esc($dongChiNhanh["kinh_do"]); ?>"
                                    <?php echo (int) $giaTriForm["chi_nhanh_nhan_id"] === (int) $dongChiNhanh["id"] ? "selected" : ""; ?>
                                ><?php echo esc($dongChiNhanh["ten_chi_nhanh"]); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="dong-nhap-dat-xe">
                        <label>Chi nhánh trả xe</label>
                        <select name="chi_nhanh_tra_id" required>
                            <option value="">Chọn chi nhánh</option>
                            <?php foreach ($danhSachChiNhanh as $dongChiNhanh): ?>
                                <option
                                    value="<?php echo (int) $dongChiNhanh["id"]; ?>"
                                    data-ten-chi-nhanh="<?php echo esc($dongChiNhanh["ten_chi_nhanh"]); ?>"
                                    data-dia-chi="<?php echo esc($dongChiNhanh["dia_chi"]); ?>"
                                    data-vi-do="<?php echo esc($dongChiNhanh["vi_do"]); ?>"
                                    data-kinh-do="<?php echo esc($dongChiNhanh["kinh_do"]); ?>"
                                    <?php echo (int) $giaTriForm["chi_nhanh_tra_id"] === (int) $dongChiNhanh["id"] ? "selected" : ""; ?>
                                ><?php echo esc($dongChiNhanh["ten_chi_nhanh"]); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="dong-nhap-dat-xe">
                        <label>Địa chỉ khách thuê</label>
                        <input
                            id="bookingAddressInput"
                            type="text"
                            name="dia_chi_khach"
                            value="<?php echo esc($giaTriForm["dia_chi_khach"]); ?>"
                            placeholder="Ví dụ: 36 Hoàng Cầu, Đống Đa, Hà Nội"
                            readonly
                            required
                        >
                    </div>

                    <div class="luoi-toa-do-khach">
                        <div class="dong-nhap-dat-xe">
                            <label>Vĩ độ khách</label>
                            <input
                                id="bookingLatInput"
                                type="text"
                                name="vi_do_khach"
                            value="<?php echo esc($giaTriForm["vi_do_khach"]); ?>"
                            placeholder="21.0285110"
                            readonly
                            required
                        >
                        </div>
                        <div class="dong-nhap-dat-xe">
                            <label>Kinh độ khách</label>
                            <input
                                id="bookingLngInput"
                                type="text"
                                name="kinh_do_khach"
                            value="<?php echo esc($giaTriForm["kinh_do_khach"]); ?>"
                            placeholder="105.8048170"
                            readonly
                            required
                        >
                        </div>
                    </div>

                    <div class="hop-ban-do-khach">
                        <label style="font-weight:600;">Vị trí khách trên bản đồ</label>
                        <div class="cum-thao-tac-ban-do-khach">
                            <button class="nut-ban-do-khach" type="button" id="detectCustomerLocation">Lấy vị trí hiện tại</button>
                            <span id="bookingGeoStatus" class="ghi-chu-ban-do-khach">Cho phép trình duyệt lấy vị trí để tự động điền lat/lng.</span>
                        </div>
                        <div
                            id="bookingLocationMap"
                            data-car-id="<?php echo (int) $xe["id"]; ?>"
                            data-car-name="<?php echo esc($xe["ten_xe"]); ?>"
                            data-car-plate="<?php echo esc($xe["bien_so"]); ?>"
                            data-car-lat="<?php echo esc($xe["vi_do_hien_tai"]); ?>"
                            data-car-lng="<?php echo esc($xe["kinh_do_hien_tai"]); ?>"
                        ></div>
                        <div class="ghi-chu-ban-do-khach">Tọa độ khách chỉ lấy từ GPS của trình duyệt và không nhập tay. Bấm vào marker xe hoặc chi nhánh để chỉ đường.</div>
                        <div id="bookingRouteInfo" class="duong-di-khach">Chưa có tuyến đường.</div>
                    </div>

                    <div class="dong-nhap-dat-xe">
                        <label>Ghi chú</label>
                        <textarea name="ghi_chu" placeholder="Yêu cầu giao xe, tài xế riêng, ghế trẻ em..."><?php echo esc($giaTriForm["ghi_chu"]); ?></textarea>
                    </div>

                    <button class="nut-xac-nhan-dat-xe" type="submit">Xác nhận đặt xe</button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
    (function () {
        var oBanDo = document.getElementById("bookingLocationMap");
        var oViDo = document.getElementById("bookingLatInput");
        var oKinhDo = document.getElementById("bookingLngInput");
        var oDiaChi = document.getElementById("bookingAddressInput");
        var oThongTinDuongDi = document.getElementById("bookingRouteInfo");
        var oThongBaoGps = document.getElementById("bookingGeoStatus");
        var nutLayViTri = document.getElementById("detectCustomerLocation");
        var oChonChiNhanhNhan = document.querySelector("select[name='chi_nhanh_nhan_id']");
        var oChonChiNhanhTra = document.querySelector("select[name='chi_nhanh_tra_id']");

        if (!oBanDo || !oViDo || !oKinhDo) {
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

        function coToaDoHopLe(viDo, kinhDo) {
            var lat = Number(viDo);
            var lng = Number(kinhDo);
            return Number.isFinite(lat)
                && Number.isFinite(lng)
                && Math.abs(lat) > 0.000001
                && Math.abs(lng) > 0.000001;
        }

        function taoIcon(loai, bienThe) {
            var bieuTuong = {
                xe: '<i class="fa-solid fa-car-side"></i>',
                chi_nhanh: '<i class="fa-solid fa-warehouse"></i>',
                khach: '<i class="fa-solid fa-location-dot"></i>'
            };

            var tenClass = "map-marker map-marker--vehicle";
            if (loai === "chi_nhanh") {
                tenClass = "map-marker map-marker--parking";
            }
            if (loai === "khach") {
                tenClass = "map-marker map-marker--user";
            }
            if (loai === "xe" && bienThe === "busy") {
                tenClass = "map-marker map-marker--vehicle-busy";
            }

            return L.divIcon({
                className: "",
                html: '<span class="' + tenClass + '">' + (bieuTuong[loai] || "") + "</span>",
                iconSize: [34, 34],
                iconAnchor: [17, 17],
                popupAnchor: [0, -10]
            });
        }

        function hienThongBaoKhongCoMap(noiDung) {
            oBanDo.style.height = "auto";
            oBanDo.innerHTML = '<div class="map__empty">' + lamSach(noiDung) + "</div>";
        }

        function dinhDangKhoangCach(khoangCachMet) {
            return (khoangCachMet / 1000).toFixed(2) + " km";
        }

        function dinhDangThoiGian(thoiLuongGiay) {
            var tongPhut = Math.round(thoiLuongGiay / 60);
            if (tongPhut < 60) {
                return tongPhut + " phút";
            }

            var gio = Math.floor(tongPhut / 60);
            var phut = tongPhut % 60;
            return phut === 0 ? gio + " giờ" : gio + " giờ " + phut + " phút";
        }

        function moDuongDanNgoai(diemBatDau, diemKetThuc) {
            var duongDan = "https://www.openstreetmap.org/directions?engine=fossgis_osrm_car&route="
                + diemBatDau.lat + "%2C" + diemBatDau.lng + "%3B"
                + diemKetThuc.lat + "%2C" + diemKetThuc.lng;
            window.open(duongDan, "_blank", "noopener,noreferrer");
        }

        function layViTriHienTai(callback) {
            if (!navigator.geolocation) {
                if (oThongBaoGps) {
                    oThongBaoGps.textContent = "Trình duyệt không hỗ trợ lấy vị trí hiện tại.";
                }
                return;
            }

            if (typeof window !== "undefined" && window.isSecureContext === false) {
                if (oThongBaoGps) {
                    oThongBaoGps.textContent = "Trình duyệt đang chặn vị trí vì trang này không ở secure context. Hãy mở bằng http://localhost, http://127.0.0.1 hoặc HTTPS.";
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
                function (loi) {
                    if (!oThongBaoGps) {
                        return;
                    }

                    if (loi && loi.code === loi.PERMISSION_DENIED) {
                        oThongBaoGps.textContent = "Bạn đã từ chối quyền vị trí. Hãy cho phép Location rồi thử lại.";
                    } else if (loi && loi.code === loi.POSITION_UNAVAILABLE) {
                        oThongBaoGps.textContent = "Không đọc được vị trí từ thiết bị. Hãy bật GPS/Wi-Fi định vị rồi thử lại.";
                    } else if (loi && loi.code === loi.TIMEOUT) {
                        oThongBaoGps.textContent = "Hết thời gian lấy vị trí. Hãy thử lại ở nơi có tín hiệu tốt hơn.";
                    } else {
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

        async function doiDiaChi(diem) {
            var duongDan = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=" + diem.lat + "&lon=" + diem.lng;
            var phanHoi = await fetch(duongDan, {
                headers: {
                    Accept: "application/json"
                }
            });

            if (!phanHoi.ok) {
                throw new Error("loi_dia_chi");
            }

            var duLieu = await phanHoi.json();
            return duLieu.display_name || "";
        }

        async function layDuongDi(diemBatDau, diemKetThuc) {
            var duongDan = "https://router.project-osrm.org/route/v1/driving/"
                + diemBatDau.lng + "," + diemBatDau.lat + ";"
                + diemKetThuc.lng + "," + diemKetThuc.lat
                + "?overview=full&geometries=geojson";
            var phanHoi = await fetch(duongDan);

            if (!phanHoi.ok) {
                throw new Error("khong_lay_duoc_duong");
            }

            var duLieu = await phanHoi.json();
            if (!duLieu.routes || !duLieu.routes.length) {
                throw new Error("khong_co_duong");
            }

            return duLieu.routes[0];
        }

        function layDanhSachChiNhanh() {
            var tapDaCo = {};
            var danhSach = [];
            var danhSachOption = document.querySelectorAll(
                "select[name='chi_nhanh_nhan_id'] option[value], select[name='chi_nhanh_tra_id'] option[value]"
            );

            for (var i = 0; i < danhSachOption.length; i++) {
                var option = danhSachOption[i];
                if (!option.value) {
                    continue;
                }

                var id = Number(option.value);
                if (!id || tapDaCo[id]) {
                    continue;
                }

                tapDaCo[id] = true;
                danhSach.push({
                    id: id,
                    ten_chi_nhanh: option.dataset.tenChiNhanh || "",
                    dia_chi: option.dataset.diaChi || "",
                    vi_do: Number(option.dataset.viDo || 0),
                    kinh_do: Number(option.dataset.kinhDo || 0)
                });
            }

            return danhSach;
        }

        var duLieuMap = {
            car: {
                id: Number(oBanDo.dataset.carId || 0),
                name: oBanDo.dataset.carName || "",
                plate: oBanDo.dataset.carPlate || "",
                lat: Number(oBanDo.dataset.carLat || 0),
                lng: Number(oBanDo.dataset.carLng || 0)
            },
            branches: layDanhSachChiNhanh()
        };

        if (typeof L === "undefined") {
            hienThongBaoKhongCoMap("Không tải được thư viện bản đồ. Hãy dùng nút Lấy vị trí hiện tại để hệ thống ghi tọa độ GPS.");

            if (nutLayViTri) {
                nutLayViTri.addEventListener("click", function () {
                    layViTriHienTai(function (viTri) {
                        oViDo.value = viTri.lat.toFixed(7);
                        oKinhDo.value = viTri.lng.toFixed(7);
                        if (!oDiaChi.value.trim()) {
                            oDiaChi.value = "Vị trí hiện tại (" + viTri.lat.toFixed(5) + ", " + viTri.lng.toFixed(5) + ")";
                        }
                    });
                });
            }
            return;
        }

        var banDo = L.map("bookingLocationMap", {
            zoomControl: true,
            scrollWheelZoom: true
        }).setView([21.03, 105.84], 11);

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(banDo);

        var danhSachMarker = [];
        var tapMarkerChiNhanh = {};
        var markerKhach = null;
        var duongDiHienTai = null;
        var viTriKhach = null;
        var maYeuCauDuongDi = 0;

        function canGiuaBanDoTheoDiem(danhSachDiem) {
            if (!danhSachDiem.length) {
                return;
            }

            banDo.fitBounds(L.latLngBounds(danhSachDiem), { padding: [30, 30] });
        }

        function capNhatThongTinViTri(viTri, nhanNguon) {
            oViDo.value = viTri.lat.toFixed(7);
            oKinhDo.value = viTri.lng.toFixed(7);

            if (!oDiaChi.value.trim()) {
                oDiaChi.value = "Vị trí GPS hiện tại (" + viTri.lat.toFixed(5) + ", " + viTri.lng.toFixed(5) + ")";
            }

            if (markerKhach) {
                banDo.removeLayer(markerKhach);
            }

            markerKhach = L.marker([viTri.lat, viTri.lng], {
                icon: taoIcon("khach")
            }).addTo(banDo);
            markerKhach.bindPopup(
                '<div class="map__popup"><h3>Vị trí của bạn</h3><p>' + lamSach(nhanNguon) + "</p></div>"
            ).openPopup();

            viTriKhach = {
                lat: viTri.lat,
                lng: viTri.lng
            };

            doiDiaChi(viTriKhach).then(function (diaChi) {
                if (diaChi) {
                    oDiaChi.value = diaChi;
                }
                if (oThongBaoGps && diaChi) {
                    oThongBaoGps.textContent = "Đã xác định địa chỉ: " + diaChi;
                }
            }).catch(function () {
                if (oThongBaoGps) {
                    oThongBaoGps.textContent = "Đã cập nhật vị trí: " + viTri.lat.toFixed(6) + ", " + viTri.lng.toFixed(6);
                }
            });
        }

        async function veDuongDi(diemDen, nhanDuongDi, mauDuong) {
            if (!viTriKhach) {
                if (oThongTinDuongDi) {
                    oThongTinDuongDi.textContent = "Cần lấy vị trí của bạn trước khi chỉ đường.";
                }
                return;
            }

            maYeuCauDuongDi += 1;
            var maYeuCauHienTai = maYeuCauDuongDi;

            if (duongDiHienTai) {
                banDo.removeLayer(duongDiHienTai);
                duongDiHienTai = null;
            }

            try {
                var duLieuDuong = await layDuongDi(viTriKhach, diemDen);
                if (maYeuCauHienTai !== maYeuCauDuongDi) {
                    return;
                }

                var toaDo = [];
                for (var i = 0; i < duLieuDuong.geometry.coordinates.length; i++) {
                    toaDo.push([duLieuDuong.geometry.coordinates[i][1], duLieuDuong.geometry.coordinates[i][0]]);
                }

                duongDiHienTai = L.polyline(toaDo, {
                    color: mauDuong,
                    weight: 4
                }).addTo(banDo);

                if (oThongTinDuongDi) {
                    oThongTinDuongDi.textContent = nhanDuongDi + ": "
                        + dinhDangKhoangCach(duLieuDuong.distance)
                        + " | "
                        + dinhDangThoiGian(duLieuDuong.duration);
                }
            } catch (loi) {
                if (maYeuCauHienTai !== maYeuCauDuongDi) {
                    return;
                }

                duongDiHienTai = L.polyline([
                    [viTriKhach.lat, viTriKhach.lng],
                    [diemDen.lat, diemDen.lng]
                ], {
                    color: mauDuong,
                    weight: 4
                }).addTo(banDo);

                if (oThongTinDuongDi) {
                    oThongTinDuongDi.textContent = nhanDuongDi + ": không lấy được chỉ đường thực tế, đang hiển thị đường tạm.";
                }
            }

            canGiuaBanDoTheoDiem([
                [viTriKhach.lat, viTriKhach.lng],
                [diemDen.lat, diemDen.lng]
            ]);
        }

        function moDuongToiXe() {
            if (!coToaDoHopLe(duLieuMap.car.lat, duLieuMap.car.lng)) {
                if (oThongTinDuongDi) {
                    oThongTinDuongDi.textContent = "Xe này chưa có vị trí hợp lệ trên bản đồ.";
                }
                return;
            }

            var diemXe = {
                lat: Number(duLieuMap.car.lat),
                lng: Number(duLieuMap.car.lng)
            };

            if (!viTriKhach) {
                banDo.setView([diemXe.lat, diemXe.lng], 14, { animate: true });
                if (oThongTinDuongDi) {
                    oThongTinDuongDi.textContent = "Hãy chọn vị trí của bạn trước khi chỉ đường tới xe.";
                }
                return;
            }

            veDuongDi(diemXe, "Vị trí của bạn -> " + duLieuMap.car.name, "#07a5fe");
        }

        function timChiNhanhTheoId(idChiNhanh) {
            for (var i = 0; i < duLieuMap.branches.length; i++) {
                if (Number(duLieuMap.branches[i].id) === Number(idChiNhanh)) {
                    return duLieuMap.branches[i];
                }
            }
            return null;
        }

        function moDuongToiChiNhanh(idChiNhanh) {
            var chiNhanh = timChiNhanhTheoId(idChiNhanh);
            if (!chiNhanh || !coToaDoHopLe(chiNhanh.vi_do, chiNhanh.kinh_do)) {
                if (oThongTinDuongDi) {
                    oThongTinDuongDi.textContent = "Chi nhánh này chưa có vị trí hợp lệ trên bản đồ.";
                }
                return;
            }

            var markerChiNhanh = tapMarkerChiNhanh[idChiNhanh];
            if (markerChiNhanh) {
                markerChiNhanh.openPopup();
            }

            var diemChiNhanh = {
                lat: Number(chiNhanh.vi_do),
                lng: Number(chiNhanh.kinh_do)
            };

            if (!viTriKhach) {
                banDo.setView([diemChiNhanh.lat, diemChiNhanh.lng], 14, { animate: true });
                if (oThongTinDuongDi) {
                    oThongTinDuongDi.textContent = "Hãy chọn vị trí của bạn trước khi chỉ đường tới chi nhánh.";
                }
                return;
            }

            veDuongDi(diemChiNhanh, "Vị trí của bạn -> " + chiNhanh.ten_chi_nhanh, "#16c47f");
        }

        function focusChiNhanhDuocChon(selectElement) {
            if (!selectElement || !selectElement.value) {
                return;
            }

            var chiNhanh = timChiNhanhTheoId(selectElement.value);
            if (!chiNhanh || !coToaDoHopLe(chiNhanh.vi_do, chiNhanh.kinh_do)) {
                return;
            }

            banDo.setView([Number(chiNhanh.vi_do), Number(chiNhanh.kinh_do)], 14, { animate: true });
            if (tapMarkerChiNhanh[chiNhanh.id]) {
                tapMarkerChiNhanh[chiNhanh.id].openPopup();
            }
        }

        if (coToaDoHopLe(duLieuMap.car.lat, duLieuMap.car.lng)) {
            var markerXe = L.marker([Number(duLieuMap.car.lat), Number(duLieuMap.car.lng)], {
                icon: taoIcon("xe"),
                bubblingMouseEvents: false
            }).addTo(banDo);

            markerXe.bindPopup(
                '<div class="map__popup">'
                + "<h3>" + lamSach(duLieuMap.car.name) + "</h3>"
                + "<p>Biển số: <strong>" + lamSach(duLieuMap.car.plate) + "</strong></p>"
                + '<div class="map__popup-actions">'
                + '<button class="map__popup-action" type="button" data-booking-action="route-car">Chỉ đường tới xe</button>'
                + '<button class="map__popup-action" type="button" data-booking-action="external-car">Mở bên ngoài</button>'
                + "</div>"
                + "</div>"
            );

            danhSachMarker.push(markerXe);
        }

        for (var i = 0; i < duLieuMap.branches.length; i++) {
            var chiNhanh = duLieuMap.branches[i];
            if (!coToaDoHopLe(chiNhanh.vi_do, chiNhanh.kinh_do)) {
                continue;
            }

            var markerChiNhanh = L.marker([Number(chiNhanh.vi_do), Number(chiNhanh.kinh_do)], {
                icon: taoIcon("chi_nhanh"),
                bubblingMouseEvents: false
            }).addTo(banDo);

            markerChiNhanh.bindPopup(
                '<div class="map__popup">'
                + "<h3>" + lamSach(chiNhanh.ten_chi_nhanh) + "</h3>"
                + "<p>" + lamSach(chiNhanh.dia_chi) + "</p>"
                + '<div class="map__popup-actions">'
                + '<button class="map__popup-action" type="button" data-booking-action="route-branch" data-branch-id="' + chiNhanh.id + '">Chỉ đường tới chi nhánh</button>'
                + '<button class="map__popup-action" type="button" data-booking-action="external-branch" data-branch-id="' + chiNhanh.id + '">Mở bên ngoài</button>'
                + "</div>"
                + "</div>"
            );

            tapMarkerChiNhanh[chiNhanh.id] = markerChiNhanh;
            danhSachMarker.push(markerChiNhanh);
        }

        if (danhSachMarker.length) {
            var danhSachDiem = [];
            for (var j = 0; j < danhSachMarker.length; j++) {
                danhSachDiem.push(danhSachMarker[j].getLatLng());
            }
            canGiuaBanDoTheoDiem(danhSachDiem);
        }

        banDo.on("popupopen", function (suKien) {
            var oPopup = suKien.popup.getElement();
            if (!oPopup) {
                return;
            }

            if (L.DomEvent) {
                L.DomEvent.disableClickPropagation(oPopup);
                L.DomEvent.disableScrollPropagation(oPopup);
            }

            if (oPopup.dataset.bookingMapBound === "1") {
                return;
            }
            oPopup.dataset.bookingMapBound = "1";

            var danhSachNutRouteXe = oPopup.querySelectorAll("[data-booking-action='route-car']");
            for (var i = 0; i < danhSachNutRouteXe.length; i++) {
                danhSachNutRouteXe[i].addEventListener("click", function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    moDuongToiXe();
                });
            }

            var danhSachNutMoNgoaiXe = oPopup.querySelectorAll("[data-booking-action='external-car']");
            for (var j = 0; j < danhSachNutMoNgoaiXe.length; j++) {
                danhSachNutMoNgoaiXe[j].addEventListener("click", function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    if (!viTriKhach) {
                        if (oThongTinDuongDi) {
                            oThongTinDuongDi.textContent = "Cần lấy vị trí của bạn trước khi mở chỉ đường ngoài.";
                        }
                        return;
                    }

                    if (!coToaDoHopLe(duLieuMap.car.lat, duLieuMap.car.lng)) {
                        return;
                    }

                    moDuongDanNgoai(viTriKhach, {
                        lat: Number(duLieuMap.car.lat),
                        lng: Number(duLieuMap.car.lng)
                    });
                });
            }

            var danhSachNutRouteChiNhanh = oPopup.querySelectorAll("[data-booking-action='route-branch']");
            for (var k = 0; k < danhSachNutRouteChiNhanh.length; k++) {
                danhSachNutRouteChiNhanh[k].addEventListener("click", function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    moDuongToiChiNhanh(Number(this.dataset.branchId));
                });
            }

            var danhSachNutMoNgoaiChiNhanh = oPopup.querySelectorAll("[data-booking-action='external-branch']");
            for (var n = 0; n < danhSachNutMoNgoaiChiNhanh.length; n++) {
                danhSachNutMoNgoaiChiNhanh[n].addEventListener("click", function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    if (!viTriKhach) {
                        if (oThongTinDuongDi) {
                            oThongTinDuongDi.textContent = "Cần lấy vị trí của bạn trước khi mở chỉ đường ngoài.";
                        }
                        return;
                    }

                    var chiNhanh = timChiNhanhTheoId(this.dataset.branchId);
                    if (!chiNhanh || !coToaDoHopLe(chiNhanh.vi_do, chiNhanh.kinh_do)) {
                        return;
                    }

                    moDuongDanNgoai(viTriKhach, {
                        lat: Number(chiNhanh.vi_do),
                        lng: Number(chiNhanh.kinh_do)
                    });
                });
            }
        });

        if (nutLayViTri) {
            nutLayViTri.addEventListener("click", function () {
                layViTriHienTai(function (viTri) {
                    capNhatThongTinViTri(viTri, "Lấy từ GPS của trình duyệt.");
                    banDo.setView([viTri.lat, viTri.lng], 14, { animate: true });

                    if (coToaDoHopLe(duLieuMap.car.lat, duLieuMap.car.lng)) {
                        moDuongToiXe();
                    }
                });
            });
        }

        if (oChonChiNhanhNhan) {
            oChonChiNhanhNhan.addEventListener("change", function () {
                focusChiNhanhDuocChon(this);
            });
        }

        if (oChonChiNhanhTra) {
            oChonChiNhanhTra.addEventListener("change", function () {
                focusChiNhanhDuocChon(this);
            });
        }

        if (coToaDoHopLe(oViDo.value, oKinhDo.value)) {
            capNhatThongTinViTri({
                lat: Number(oViDo.value),
                lng: Number(oKinhDo.value)
            }, "Khôi phục vị trí từ form đặt xe.");
        }

        function dongBoKichThuocMap() {
            banDo.invalidateSize();
        }

        if (typeof requestAnimationFrame === "function") {
            requestAnimationFrame(dongBoKichThuocMap);
        }
        setTimeout(dongBoKichThuocMap, 120);
        window.addEventListener("load", dongBoKichThuocMap);
        window.addEventListener("resize", dongBoKichThuocMap);
    })();
</script>
