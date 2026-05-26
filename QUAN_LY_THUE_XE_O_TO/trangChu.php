<?php
/** @var mysqli $conn */
$sqlThongKe = "SELECT
    (SELECT COUNT(*) FROM xe) AS tong_xe,
    (SELECT COUNT(*) FROM xe WHERE trang_thai = 'san_sang') AS xe_san_sang,
    (SELECT COUNT(*) FROM chi_nhanh) AS tong_chi_nhanh,
    (SELECT COUNT(*) FROM dat_xe WHERE trang_thai IN ('da_xac_nhan', 'dang_thue', 'hoan_thanh')) AS tong_luot_thue";
$ketQuaThongKe = mysqli_query($conn, $sqlThongKe);
$thongKe = $ketQuaThongKe ? mysqli_fetch_assoc($ketQuaThongKe) : [
    "tong_xe" => 0,
    "xe_san_sang" => 0,
    "tong_chi_nhanh" => 0,
    "tong_luot_thue" => 0,
];

$ketQuaXe = mysqli_query($conn, "SELECT x.*, c.ten_chi_nhanh
    FROM xe x
    LEFT JOIN chi_nhanh c ON x.chi_nhanh_id = c.id
    ORDER BY x.id DESC
    LIMIT 6");
?>

<style>
    .khung-trang-chu {
        padding: 34px 0 12px;
    }

    .hop-gioi-thieu-trang-chu {
        padding: 36px;
        border-radius: 24px;
        background:
            radial-gradient(circle at top right, rgba(7, 165, 254, 0.16), transparent 32%),
            linear-gradient(135deg, #ffffff 0%, #f7fbff 100%);
        box-shadow: 0 16px 40px rgba(17, 31, 53, 0.08);
    }

    .dong-gioi-thieu-trang-chu {
        display: grid;
        grid-template-columns: 1.35fr 0.9fr;
        gap: 20px;
        align-items: start;
    }

    .gioi-thieu-trang-chu p {
        margin: 0;
        color: var(--muted-color);
        line-height: 1.7;
    }

    .gioi-thieu-trang-chu h1 {
        margin: 16px 0;
        font-size: clamp(34px, 4vw, 54px);
        line-height: 1.08;
    }

    .cum-nut-trang-chu {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
        margin-top: 26px;
    }

    .nut-chinh-trang-chu,
    .nut-phu-trang-chu {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 18px;
        border-radius: 14px;
        font-weight: 700;
    }

    .nut-chinh-trang-chu {
        background: var(--primary-color);
        color: #ffffff;
        box-shadow: 0 10px 20px rgba(7, 165, 254, 0.22);
    }

    .nut-phu-trang-chu {
        background: #ffffff;
        color: var(--text-color);
        border: 1px solid #dbe5f2;
    }

    .cum-thong-ke-trang-chu {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
        margin-top: 26px;
    }

    .o-thong-ke-trang-chu {
        min-width: 180px;
        flex: 1;
        padding: 18px;
        border-radius: 18px;
        background: #f8fbff;
        border: 1px solid #e4edf7;
    }

    .o-thong-ke-trang-chu span,
    .hop-viec-khach-lam p,
    .dau-muc-trang-chu p,
    .tieu-muc-trang-chu {
        color: var(--muted-color);
    }

    .o-thong-ke-trang-chu strong {
        display: block;
        font-size: 24px;
        margin: 8px 0;
    }

    .hop-viec-khach-lam {
        padding: 26px;
        border-radius: 22px;
        background: #ffffff;
        box-shadow: 0 16px 40px rgba(17, 31, 53, 0.08);
    }

    .hop-viec-khach-lam h2 {
        margin: 0 0 16px;
    }

    .hop-viec-khach-lam ul {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .hop-viec-khach-lam li {
        padding: 14px 0;
        border-bottom: 1px solid #edf2f8;
        color: var(--muted-color);
    }

    .hop-viec-khach-lam li:last-child {
        border-bottom: 0;
    }

    .cum-noi-dung-trang-chu {
        margin-top: 34px;
    }

    .dau-muc-trang-chu {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
    }

    .dau-muc-trang-chu h2 {
        margin: 8px 0 0;
        font-size: 30px;
    }

    .nut-xem-them-trang-chu {
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

    .luoi-xe-noi-bat {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
    }

    .the-xe-noi-bat {
        padding: 24px;
        background: #ffffff;
        border-radius: 22px;
        box-shadow: 0 16px 40px rgba(17, 31, 53, 0.08);
        transition: 0.25s ease;
    }

    .the-xe-noi-bat:hover {
        transform: translateY(-4px);
    }

    .anh-xe-noi-bat {
        margin: -24px -24px 18px;
        height: 220px;
        overflow: hidden;
        border-radius: 22px 22px 18px 18px;
        background: #eaf2fb;
    }

    .anh-xe-noi-bat img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .the-xe-noi-bat h3 {
        margin: 12px 0 8px;
        font-size: 22px;
    }

    .dong-phu-xe {
        color: var(--muted-color);
        font-size: 14px;
    }

    .luoi-thong-so-xe {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin: 18px 0;
    }

    .o-thong-so-xe {
        padding: 12px 14px;
        border-radius: 14px;
        background: #f8fbff;
        border: 1px solid #e7eef8;
        color: var(--muted-color);
    }

    .o-thong-so-xe strong {
        display: block;
        margin-top: 6px;
        color: var(--text-color);
    }

    .chan-the-xe {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }

    .gia-xe-trang-chu {
        font-size: 28px;
        font-weight: 900;
    }

    .nut-chi-tiet-xe {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 18px;
        border-radius: 14px;
        background: var(--primary-color);
        color: #ffffff;
        font-weight: 700;
        box-shadow: 0 10px 20px rgba(7, 165, 254, 0.22);
    }

    .nhan-trang-thai-xe {
        display: inline-flex;
        align-items: center;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 700;
    }

    .nhan-trang-thai-xe.mau-xanh {
        background: rgba(22, 196, 127, 0.12);
        color: #16c47f;
    }

    .nhan-trang-thai-xe.mau-duong {
        background: rgba(7, 165, 254, 0.12);
        color: var(--primary-color);
    }

    .nhan-trang-thai-xe.mau-cam {
        background: rgba(255, 159, 67, 0.16);
        color: #b97726;
    }

    .nhan-trang-thai-xe.mau-xam {
        background: rgba(109, 121, 136, 0.14);
        color: #526072;
    }

    @media (max-width: 1080px) {
        .luoi-xe-noi-bat {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 860px) {
        .hop-gioi-thieu-trang-chu,
        .hop-viec-khach-lam {
            padding: 22px;
        }

        .dong-gioi-thieu-trang-chu {
            grid-template-columns: 1fr;
        }

        .luoi-xe-noi-bat,
        .luoi-thong-so-xe {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="khung-trang-chu">
    <div class="main__content">
        <div class="hop-gioi-thieu-trang-chu">
            <div class="dong-gioi-thieu-trang-chu">
                <div class="gioi-thieu-trang-chu">
                    <p>Nền tảng đặt xe ô tô</p>
                    <h1>Chọn xe nhanh, đặt lịch rõ ràng và theo dõi hành trình thuê ngay trên một giao diện dành cho khách hàng</h1>
                    <p>Tìm xe theo nhu cầu, xem điểm giao nhận, đặt xe và theo dõi đơn thuê của bạn trong một khu vực công khai chỉ dành cho người đi thuê xe.</p>
                    <div class="cum-nut-trang-chu">
                        <a class="nut-chinh-trang-chu" href="./index.php?chuyen_trang=danhSachXe">Đặt xe ngay</a>
                        <a class="nut-phu-trang-chu" href="./index.php?chuyen_trang=banDoVanHanh">Xem bản đồ vận hành</a>
                    </div>
                    <div class="cum-thong-ke-trang-chu">
                        <article class="o-thong-ke-trang-chu">
                            <span>Tổng xe</span>
                            <strong><?php echo esc($thongKe["tong_xe"]); ?></strong>
                            <small class="tieu-muc-trang-chu">Nhiều phân khúc để lựa chọn</small>
                        </article>
                        <article class="o-thong-ke-trang-chu">
                            <span>Xe sẵn sàng</span>
                            <strong><?php echo esc($thongKe["xe_san_sang"]); ?></strong>
                            <small class="tieu-muc-trang-chu">Có thể nhận xe trong ngày</small>
                        </article>
                        <article class="o-thong-ke-trang-chu">
                            <span>Chi nhánh</span>
                            <strong><?php echo esc($thongKe["tong_chi_nhanh"]); ?></strong>
                            <small class="tieu-muc-trang-chu">Điểm giao nhận xe trên bản đồ</small>
                        </article>
                        <article class="o-thong-ke-trang-chu">
                            <span>Lượt thuê</span>
                            <strong><?php echo esc($thongKe["tong_luot_thue"]); ?></strong>
                            <small class="tieu-muc-trang-chu">Lịch sử đặt xe đã phục vụ</small>
                        </article>
                    </div>
                </div>

                <aside class="hop-viec-khach-lam">
                    <h2>Khách thuê xe làm được gì</h2>
                    <ul>
                        <li>Xem xe theo ảnh, giá thuê, trạng thái và vị trí hiện tại.</li>
                        <li>Chọn điểm của khách trên bản đồ để đặt xe nhanh hơn.</li>
                        <li>Xem xe gần nhất với vị trí khách và tuyến đường liên quan.</li>
                        <li>Theo dõi tiến trình đơn thuê của mình sau khi đăng nhập.</li>
                    </ul>
                </aside>
            </div>
        </div>

        <div class="cum-noi-dung-trang-chu">
            <div class="dau-muc-trang-chu">
                <div>
                    <p>Đội xe nổi bật</p>
                    <h2>Xe được đặt nhiều</h2>
                </div>
                <a class="nut-xem-them-trang-chu" href="./index.php?chuyen_trang=danhSachXe">Xem tất cả</a>
            </div>

            <div class="luoi-xe-noi-bat">
                <?php while ($dongXe = mysqli_fetch_assoc($ketQuaXe)): ?>
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
                    <article class="the-xe-noi-bat">
                        <div class="anh-xe-noi-bat">
                            <img src="<?php echo esc($dongXe["anh_xe"]); ?>" alt="<?php echo esc($dongXe["ten_xe"]); ?>">
                        </div>
                        <span class="nhan-trang-thai-xe <?php echo esc($mauTrangThaiXe); ?>">
                            <?php echo esc($tenTrangThaiXe); ?>
                        </span>
                        <h3><?php echo esc($dongXe["ten_xe"]); ?></h3>
                        <div class="dong-phu-xe"><?php echo esc($dongXe["bien_so"]); ?> | <?php echo esc($dongXe["hang_xe"]); ?></div>
                        <div class="luoi-thong-so-xe">
                            <div class="o-thong-so-xe">Chỗ ngồi<strong><?php echo esc($dongXe["so_cho"]); ?> chỗ</strong></div>
                            <div class="o-thong-so-xe">Chi nhánh<strong><?php echo esc($dongXe["ten_chi_nhanh"]); ?></strong></div>
                            <div class="o-thong-so-xe">Nhiên liệu<strong><?php echo esc($dongXe["nhien_lieu"]); ?></strong></div>
                            <div class="o-thong-so-xe">Hộp số<strong><?php echo esc($dongXe["hop_so"]); ?></strong></div>
                        </div>
                        <div class="chan-the-xe">
                            <div class="gia-xe-trang-chu"><?php echo format_money($dongXe["gia_thue_ngay"]); ?>/ngày</div>
                            <a class="nut-chi-tiet-xe" href="./index.php?chuyen_trang=chiTietXe&id=<?php echo (int) $dongXe["id"]; ?>">Chi tiết</a>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</section>
