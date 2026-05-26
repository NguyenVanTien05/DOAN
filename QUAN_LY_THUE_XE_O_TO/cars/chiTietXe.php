<?php
/** @var mysqli $conn */
$idXe = (int) ($_GET["id"] ?? 0);
$sqlXe = "SELECT x.*, c.ten_chi_nhanh, c.dia_chi
    FROM xe x
    LEFT JOIN chi_nhanh c ON x.chi_nhanh_id = c.id
    WHERE x.id = $idXe
    LIMIT 1";
$ketQuaXe = mysqli_query($conn, $sqlXe);
$xe = $ketQuaXe ? mysqli_fetch_assoc($ketQuaXe) : null;

if (!$xe) {
    echo '<section class="page__section"><div class="main__content"><div class="alert alert--danger">Không tìm thấy xe.</div></div></section>';
    return;
}
$tenTrangThaiXe = (string) $xe["trang_thai"];
$mauTrangThaiXe = "nen-duong";
if (($xe["trang_thai"] ?? "") === "san_sang") {
    $tenTrangThaiXe = "Sẵn sàng";
    $mauTrangThaiXe = "nen-xanh";
} elseif (($xe["trang_thai"] ?? "") === "dang_thue") {
    $tenTrangThaiXe = "Đang thuê";
    $mauTrangThaiXe = "nen-duong";
} elseif (($xe["trang_thai"] ?? "") === "bao_duong") {
    $tenTrangThaiXe = "Bảo dưỡng";
    $mauTrangThaiXe = "nen-cam";
} elseif (($xe["trang_thai"] ?? "") === "tam_ngung") {
    $tenTrangThaiXe = "Tạm ngưng";
    $mauTrangThaiXe = "nen-xam";
}
?>

<style>
    .khung-chi-tiet-xe {
        padding: 34px 0 12px;
    }

    .noi-dung-chi-tiet-xe {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 22px;
    }

    .cot-anh-xe {
        padding: 28px;
        border-radius: 24px;
        background: linear-gradient(135deg, #ffffff 0%, #f7fbff 100%);
        box-shadow: 0 16px 40px rgba(17, 31, 53, 0.08);
    }

    .anh-xe-lon {
        height: 360px;
        overflow: hidden;
        border-radius: 20px;
        background: #edf2f8;
    }

    .anh-xe-lon img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .nen-gioi-thieu-xe {
        margin-top: 18px;
        min-height: 180px;
        border-radius: 20px;
        padding: 26px;
        color: #ffffff;
        display: flex;
        align-items: flex-end;
        background: linear-gradient(135deg, rgba(7, 165, 254, 0.95), rgba(24, 32, 42, 0.72));
    }

    .nen-gioi-thieu-xe h1 {
        margin: 14px 0 10px;
        font-size: 34px;
    }

    .nen-gioi-thieu-xe p {
        margin: 0;
        color: rgba(255, 255, 255, 0.82);
    }

    .cot-thong-tin-xe {
        padding: 28px;
        border-radius: 24px;
        background: #ffffff;
        box-shadow: 0 16px 40px rgba(17, 31, 53, 0.08);
    }

    .dau-muc-thong-tin-xe {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
    }

    .dau-muc-thong-tin-xe p {
        margin: 0;
        color: var(--muted-color);
    }

    .dau-muc-thong-tin-xe h2 {
        margin: 8px 0 0;
        font-size: 30px;
    }

    .gia-thue-xe {
        font-size: 30px;
        font-weight: 900;
    }

    .luoi-thong-tin-xe {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin: 22px 0;
    }

    .o-thong-tin-xe {
        padding: 14px 15px;
        border-radius: 16px;
        border: 1px solid #e5edf7;
        background: #f8fbff;
        color: var(--muted-color);
    }

    .o-thong-tin-xe strong {
        display: block;
        margin-top: 6px;
        color: var(--text-color);
    }

    .mo-ta-xe {
        margin: 0;
        color: var(--muted-color);
        line-height: 1.7;
    }

    .cum-nut-xe {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 24px;
    }

    .nut-chinh-xe,
    .nut-phu-xe {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 18px;
        border-radius: 14px;
        font-weight: 700;
    }

    .nut-chinh-xe {
        background: var(--primary-color);
        color: #ffffff;
        box-shadow: 0 10px 20px rgba(7, 165, 254, 0.22);
    }

    .nut-phu-xe {
        border: 1px solid #dbe5f2;
        background: #ffffff;
        color: var(--text-color);
    }

    .nhan-trang-thai {
        display: inline-flex;
        align-items: center;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 700;
    }

    .nhan-trang-thai.nen-xanh {
        background: rgba(22, 196, 127, 0.12);
        color: #16c47f;
    }

    .nhan-trang-thai.nen-duong {
        background: rgba(7, 165, 254, 0.12);
        color: var(--primary-color);
    }

    .nhan-trang-thai.nen-cam {
        background: rgba(255, 159, 67, 0.16);
        color: #b97726;
    }

    .nhan-trang-thai.nen-xam {
        background: rgba(109, 121, 136, 0.14);
        color: #526072;
    }

    @media (max-width: 860px) {
        .noi-dung-chi-tiet-xe {
            grid-template-columns: 1fr;
        }

        .cot-anh-xe,
        .cot-thong-tin-xe {
            padding: 22px;
        }

        .anh-xe-lon {
            height: 280px;
        }

        .luoi-thong-tin-xe {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="khung-chi-tiet-xe">
    <div class="main__content">
        <div class="noi-dung-chi-tiet-xe">
            <div class="cot-anh-xe">
                <div class="anh-xe-lon">
                    <img src="<?php echo esc($xe["anh_xe"]); ?>" alt="<?php echo esc($xe["ten_xe"]); ?>">
                </div>
                <div class="nen-gioi-thieu-xe">
                    <div>
                        <span class="nhan-trang-thai <?php echo esc($mauTrangThaiXe); ?>">
                            <?php echo esc($tenTrangThaiXe); ?>
                        </span>
                        <h1><?php echo esc($xe["ten_xe"]); ?></h1>
                        <p><?php echo esc($xe["hang_xe"]); ?> | Biển số <?php echo esc($xe["bien_so"]); ?></p>
                    </div>
                </div>
            </div>

            <div class="cot-thong-tin-xe">
                <div class="dau-muc-thong-tin-xe">
                    <div>
                        <p>Thông tin xe</p>
                        <h2><?php echo esc($xe["ten_xe"]); ?></h2>
                    </div>
                    <div class="gia-thue-xe"><?php echo format_money($xe["gia_thue_ngay"]); ?>/ngày</div>
                </div>

                <div class="luoi-thong-tin-xe">
                    <div class="o-thong-tin-xe">Hãng xe<strong><?php echo esc($xe["hang_xe"]); ?></strong></div>
                    <div class="o-thong-tin-xe">Số chỗ<strong><?php echo esc($xe["so_cho"]); ?> chỗ</strong></div>
                    <div class="o-thong-tin-xe">Nhiên liệu<strong><?php echo esc($xe["nhien_lieu"]); ?></strong></div>
                    <div class="o-thong-tin-xe">Hộp số<strong><?php echo esc($xe["hop_so"]); ?></strong></div>
                    <div class="o-thong-tin-xe">Chi nhánh<strong><?php echo esc($xe["ten_chi_nhanh"]); ?></strong></div>
                    <div class="o-thong-tin-xe">Địa chỉ<strong><?php echo esc($xe["dia_chi"]); ?></strong></div>
                </div>

                <p class="mo-ta-xe"><?php echo nl2br(esc($xe["mo_ta"])); ?></p>

                <div class="cum-nut-xe">
                    <a class="nut-chinh-xe" href="./index.php?chuyen_trang=datXe&xe_id=<?php echo (int) $xe["id"]; ?>">Đặt xe này</a>
                    <a class="nut-phu-xe" href="./index.php?chuyen_trang=danhSachXe">Quay lại danh sách</a>
                </div>
            </div>
        </div>
    </div>
</section>
