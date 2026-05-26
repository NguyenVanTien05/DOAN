<?php
/** @var mysqli $conn */
if (!isset($_SESSION["hoTen"], $_SESSION["vaiTro"], $_SESSION["idNguoiDung"])) {
    echo '<section class="khung-don-cua-toi"><div class="main__content"><div class="hop-don-cua-toi">Bạn cần đăng nhập để xem đơn thuê.</div></div></section>';
    return;
}

$idNguoiDung = (int) $_SESSION["idNguoiDung"];

$sqlDon = "SELECT dx.*, x.ten_xe, x.bien_so, x.gia_thue_ngay,
        cn1.ten_chi_nhanh AS chi_nhanh_nhan,
        cn2.ten_chi_nhanh AS chi_nhanh_tra
    FROM dat_xe dx
    LEFT JOIN xe x ON dx.xe_id = x.id
    LEFT JOIN chi_nhanh cn1 ON dx.chi_nhanh_nhan_id = cn1.id
    LEFT JOIN chi_nhanh cn2 ON dx.chi_nhanh_tra_id = cn2.id
    WHERE dx.nguoi_dung_id = $idNguoiDung
    ORDER BY dx.id DESC";
$ketQuaDon = mysqli_query($conn, $sqlDon);
$danhSachDon = [];
$loiTaiDon = "";

if ($ketQuaDon) {
    while ($dongDon = mysqli_fetch_assoc($ketQuaDon)) {
        $danhSachDon[] = $dongDon;
    }
} else {
    $loiTaiDon = "Không tải được danh sách đơn thuê. Vui lòng kiểm tra lại dữ liệu đơn đặt xe.";
}

$hienThiNgay = function ($giaTriNgay) {
    if (empty($giaTriNgay)) {
        return "Đang cập nhật";
    }

    $mocThoiGian = strtotime((string) $giaTriNgay);
    return $mocThoiGian ? date("d/m/Y", $mocThoiGian) : "Đang cập nhật";
};
?>

<style>
    .khung-don-cua-toi {
        padding: 34px 0 12px;
    }

    .hop-don-cua-toi {
        padding: 28px;
        border-radius: 24px;
        background: #ffffff;
        box-shadow: 0 16px 40px rgba(17, 31, 53, 0.08);
    }

    .dau-trang-don-cua-toi p {
        margin: 0;
        color: var(--muted-color);
    }

    .dau-trang-don-cua-toi h2 {
        margin: 8px 0 0;
        font-size: 30px;
    }

    .bang-don-cua-toi {
        width: 100%;
        margin-top: 22px;
        border-collapse: collapse;
    }

    .bang-don-cua-toi th {
        padding: 0 0 14px;
        text-align: left;
        font-size: 13px;
        text-transform: uppercase;
        color: var(--muted-color);
    }

    .bang-don-cua-toi td {
        padding: 16px 12px 16px 0;
        border-top: 1px solid #edf2f8;
        vertical-align: top;
    }

    .khung-bang-don {
        overflow: auto;
    }

    .ghi-nho {
        display: block;
        margin-top: 6px;
        color: var(--muted-color);
        font-size: 14px;
        line-height: 1.5;
    }

    .nhan-don {
        display: inline-flex;
        align-items: center;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 700;
    }

    .nhan-don.mau-cam {
        background: rgba(255, 159, 67, 0.16);
        color: #b97726;
    }

    .nhan-don.mau-xanh-duong {
        background: rgba(58, 134, 255, 0.12);
        color: #3a86ff;
    }

    .nhan-don.mau-duong {
        background: rgba(7, 165, 254, 0.12);
        color: var(--primary-color);
    }

    .nhan-don.mau-xanh-la {
        background: rgba(22, 196, 127, 0.12);
        color: #16c47f;
    }

    .nhan-don.mau-do {
        background: rgba(255, 95, 95, 0.14);
        color: #ff5f5f;
    }

    .cum-buoc-don {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }

    .buoc-don {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        color: var(--muted-color);
        background: #eef3fa;
    }

    .buoc-don.dang-sang {
        color: #ffffff;
        background: var(--primary-color);
    }

    .nut-huy-don {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 14px;
        border-radius: 12px;
        border: 1px solid #dbe5f2;
        background: #ffffff;
        color: var(--text-color);
        font-weight: 600;
    }

    .trang-thai-trong-don {
        margin-top: 22px;
        padding: 16px;
        border-radius: 16px;
        border: 1px dashed #d6e0ec;
        color: var(--muted-color);
        background: #fbfdff;
    }

    .thong-bao-loi-don {
        margin-top: 18px;
        padding: 14px 16px;
        border-radius: 16px;
        background: rgba(255, 95, 95, 0.12);
        color: #cf3636;
    }

    @media (max-width: 860px) {
        .hop-don-cua-toi {
            padding: 22px;
        }
    }
</style>

<section class="khung-don-cua-toi">
    <div class="main__content">
        <div class="hop-don-cua-toi">
            <div class="dau-trang-don-cua-toi">
                <p>Theo dõi đặt xe</p>
                <h2>Đơn thuê của tôi</h2>
            </div>

            <?php if ($loiTaiDon !== ""): ?>
                <div class="thong-bao-loi-don"><?php echo esc($loiTaiDon); ?></div>
            <?php elseif (!$danhSachDon): ?>
                <div class="trang-thai-trong-don">Bạn chưa có đơn thuê xe nào.</div>
            <?php else: ?>
            <div class="khung-bang-don">
                <table class="bang-don-cua-toi">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Xe</th>
                            <th>Thời gian</th>
                            <th>Chi nhánh</th>
                            <th>Tổng tiền</th>
                            <th>Tiến trình</th>
                            <th>Trả muộn</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($danhSachDon as $dongDon): ?>
                            <?php
                            $soNgayTre = late_return_days($dongDon["ngay_tra"], $dongDon["thoi_diem_tra_thuc_te"] ?? null, $dongDon["trang_thai"]);
                            $phiTre = isset($dongDon["phi_phat_tra_muon"]) ? (float) $dongDon["phi_phat_tra_muon"] : 0;
                            if ($phiTre <= 0) {
                                $phiTre = calculate_late_fee($dongDon["ngay_tra"], $dongDon["thoi_diem_tra_thuc_te"] ?? null, $dongDon["gia_thue_ngay"] ?? 0, $dongDon["trang_thai"]);
                            }
                            $trangThaiDon = (string) $dongDon["trang_thai"];
                            $mocNgayTra = !empty($dongDon["ngay_tra"]) ? strtotime((string) $dongDon["ngay_tra"]) : false;
                            $tenTrangThaiDon = $trangThaiDon;
                            $mauTrangThaiDon = "mau-xanh-duong";
                            $moTaTrangThaiDon = "Trạng thái đang được cập nhật.";
                            $buocHienTai = 0;
                            if ($trangThaiDon === "cho_xac_nhan") {
                                $tenTrangThaiDon = "Chờ xác nhận";
                                $mauTrangThaiDon = "mau-cam";
                                $moTaTrangThaiDon = "Đơn đã gửi. Bạn đang chờ người cho thuê xe xác nhận.";
                                $buocHienTai = 1;
                            } elseif ($trangThaiDon === "da_xac_nhan") {
                                $tenTrangThaiDon = "Đã xác nhận";
                                $mauTrangThaiDon = "mau-xanh-duong";
                                $moTaTrangThaiDon = "Đơn đã được chấp nhận. Bạn có thể đến nhận xe theo lịch.";
                                $buocHienTai = 2;
                            } elseif ($trangThaiDon === "dang_thue") {
                                $tenTrangThaiDon = "Đang thuê";
                                $mauTrangThaiDon = "mau-duong";
                                $moTaTrangThaiDon = "Bạn đang trong thời gian thuê xe.";
                                $buocHienTai = 3;
                            } elseif ($trangThaiDon === "hoan_thanh") {
                                $tenTrangThaiDon = "Hoàn thành";
                                $mauTrangThaiDon = "mau-xanh-la";
                                $moTaTrangThaiDon = "Đơn đã hoàn tất và xe đã được trả.";
                                $buocHienTai = 4;
                            } elseif ($trangThaiDon === "da_huy") {
                                $tenTrangThaiDon = "Đã hủy";
                                $mauTrangThaiDon = "mau-do";
                                $moTaTrangThaiDon = "Đơn đã bị hủy hoặc không tiếp tục xử lý.";
                            }
                            ?>
                            <tr>
                                <td>#<?php echo (int) $dongDon["id"]; ?></td>
                                <td>
                                    <strong><?php echo esc($dongDon["ten_xe"] ?: "Xe đã cập nhật"); ?></strong>
                                    <span class="ghi-nho"><?php echo esc($dongDon["bien_so"] ?: "Đang cập nhật biển số"); ?></span>
                                </td>
                                <td>
                                    <?php echo esc($hienThiNgay($dongDon["ngay_nhan"] ?? "")); ?>
                                    <span class="ghi-nho"><?php echo esc($hienThiNgay($dongDon["ngay_tra"] ?? "")); ?></span>
                                </td>
                                <td>
                                    <strong><?php echo esc($dongDon["chi_nhanh_nhan"] ?: "Đang cập nhật"); ?></strong>
                                    <span class="ghi-nho"><?php echo esc($dongDon["chi_nhanh_tra"] ?: "Đang cập nhật"); ?></span>
                                </td>
                                <td>
                                    <strong><?php echo format_money($dongDon["tong_tien"]); ?></strong>
                                    <?php if ($phiTre > 0): ?>
                                        <span class="ghi-nho">Tổng tạm tính: <?php echo format_money($dongDon["tong_tien"] + $phiTre); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="nhan-don <?php echo esc($mauTrangThaiDon); ?>">
                                        <?php echo esc($tenTrangThaiDon); ?>
                                    </span>
                                    <span class="ghi-nho"><?php echo esc($moTaTrangThaiDon); ?></span>
                                    <?php if ($dongDon["trang_thai"] !== "da_huy"): ?>
                                        <div class="cum-buoc-don">
                                            <span class="buoc-don <?php echo $buocHienTai >= 1 ? "dang-sang" : ""; ?>">Gửi đơn</span>
                                            <span class="buoc-don <?php echo $buocHienTai >= 2 ? "dang-sang" : ""; ?>">Xác nhận</span>
                                            <span class="buoc-don <?php echo $buocHienTai >= 3 ? "dang-sang" : ""; ?>">Đang thuê</span>
                                            <span class="buoc-don <?php echo $buocHienTai >= 4 ? "dang-sang" : ""; ?>">Hoàn tất</span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($phiTre > 0): ?>
                                        <span class="nhan-don mau-do">Trễ <?php echo (int) $soNgayTre; ?> ngày</span>
                                        <span class="ghi-nho">Phạt trả muộn: <?php echo format_money($phiTre); ?></span>
                                    <?php elseif (in_array($dongDon["trang_thai"], ["da_xac_nhan", "dang_thue"], true) && $mocNgayTra && $mocNgayTra < time()): ?>
                                        <?php $phiTamTinh = calculate_late_fee($dongDon["ngay_tra"], null, $dongDon["gia_thue_ngay"] ?? 0, $dongDon["trang_thai"]); ?>
                                        <span class="nhan-don mau-cam">Quá hạn</span>
                                        <span class="ghi-nho">Tạm tính nếu trả ngay: <?php echo format_money($phiTamTinh); ?></span>
                                    <?php else: ?>
                                        <span class="ghi-nho">Không</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (in_array($dongDon["trang_thai"], ["cho_xac_nhan", "da_xac_nhan"], true)): ?>
                                        <a class="nut-huy-don" href="./index.php?chuyen_trang=donCuaToi&cancel=<?php echo (int) $dongDon["id"]; ?>" onclick="return confirm('Bạn chắc chắn muốn hủy đơn?');">Hủy đơn</a>
                                    <?php else: ?>
                                        <span class="ghi-nho">Không có</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
