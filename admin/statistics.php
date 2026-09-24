<?php
session_start();
include '../config/db.php';

// 1. Kiểm tra quyền Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../login.php");
    exit();
}

// Thiết lập múi giờ Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

/**
 * LOGIC THỐNG KÊ DOANH THU & LỢI NHUẬN
 * Lợi nhuận = Tổng tiền bán - (Số lượng x Giá vốn sản phẩm)
 */

// Hàm hỗ trợ lấy số liệu Doanh thu & Lợi nhuận theo điều kiện SQL
function getStats($conn, $condition) {
    $sql = "SELECT 
            SUM(ct.SOLUONG * ct.GIA) as doanh_thu,
            SUM(ct.SOLUONG * IFNULL(sp.GIA_VON, 0)) as tong_gia_von
        FROM HOADON_CHITIET ct 
        INNER JOIN HOADON hd ON ct.MAHOADON = hd.MAHOADON
        INNER JOIN SANPHAM sp ON ct.MASP = sp.MASP
        WHERE hd.TRANGTHAI = 'Completed' AND $condition";
    $res = $conn->query($sql);
    $data = $res->fetch_assoc();
    
    $dt = $data['doanh_thu'] ?? 0;
    $gv = $data['gia_von'] ?? 0;
    return ['doanh_thu' => $dt, 'loi_nhuan' => ($dt - $gv)];
}

// 1. Hôm nay
$today = date('Y-m-d');
$stats_today = getStats($conn, "DATE(hd.NGAY) = '$today'");

// 2. Tuần này
$stats_week = getStats($conn, "YEARWEEK(hd.NGAY, 1) = YEARWEEK(CURDATE(), 1)");

// 3. Tháng này
$current_month = date('m');
$current_year = date('Y');
$stats_month = getStats($conn, "MONTH(hd.NGAY) = '$current_month' AND YEAR(hd.NGAY) = '$current_year'");

// 4. Năm nay
$stats_year = getStats($conn, "YEAR(hd.NGAY) = '$current_year'");

// 5. Thống kê món bán chạy nhất & Lợi nhuận từng món
$top_products = $conn->query("SELECT 
                                sp.TENSP, 
                                SUM(ct.SOLUONG) as total_sold,
                                SUM(ct.SOLUONG * (ct.GIA - sp.GIA_VON)) as profit_item
                             FROM HOADON_CHITIET ct 
                             JOIN HOADON hd ON ct.MAHOADON = hd.MAHOADON
                             JOIN SANPHAM sp ON ct.MASP = sp.MASP 
                             WHERE hd.TRANGTHAI = 'Completed'
                             GROUP BY sp.MASP 
                             ORDER BY total_sold DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo quản trị tài chính</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .stat-card { border: none; border-radius: 15px; transition: 0.3s; color: white; position: relative; overflow: hidden; }
        .stat-card:hover { transform: translateY(-5px); }
        .icon-bg { position: absolute; top: 10px; right: 15px; font-size: 3rem; opacity: 0.2; }
        .profit-text { font-size: 0.9rem; background: rgba(0,0,0,0.1); padding: 2px 8px; border-radius: 5px; }
    </style>
</head>
<body class="bg-light">
<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold mb-1">Báo cáo Tài chính</h3>
                    <p class="text-muted">Doanh thu & Lợi nhuận thực tế (đã trừ giá vốn)</p>
                </div>
                <span class="badge bg-white text-dark border p-2 shadow-sm">
                    <i class="bi bi-clock me-1 text-primary"></i> <?php echo date('d/m/Y H:i'); ?>
                </span>
            </div>
            
            <div class="row g-4 mb-4">
                <!-- Hôm nay -->
                <div class="col-md-3">
                    <div class="card stat-card bg-primary shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-uppercase opacity-75 small">Hôm nay</h6>
                            <h3 class="fw-bold mb-1"><?php echo number_format($stats_today['doanh_thu'], 0, ',', '.'); ?>đ</h3>
                            <div class="profit-text">Lãi: +<?php echo number_format($stats_today['loi_nhuan'], 0, ',', '.'); ?>đ</div>
                            <i class="bi bi-calendar-event icon-bg"></i>
                        </div>
                    </div>
                </div>
                <!-- Tuần này -->
                <div class="col-md-3">
                    <div class="card stat-card bg-info shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-uppercase opacity-75 small">Tuần này</h6>
                            <h3 class="fw-bold mb-1"><?php echo number_format($stats_week['doanh_thu'], 0, ',', '.'); ?>đ</h3>
                            <div class="profit-text">Lãi: +<?php echo number_format($stats_week['loi_nhuan'], 0, ',', '.'); ?>đ</div>
                            <i class="bi bi-calendar3 icon-bg"></i>
                        </div>
                    </div>
                </div>
                <!-- Tháng này -->
                <div class="col-md-3">
                    <div class="card stat-card bg-success shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-uppercase opacity-75 small">Tháng <?php echo $current_month; ?></h6>
                            <h3 class="fw-bold mb-1"><?php echo number_format($stats_month['doanh_thu'], 0, ',', '.'); ?>đ</h3>
                            <div class="profit-text">Lãi: +<?php echo number_format($stats_month['loi_nhuan'], 0, ',', '.'); ?>đ</div>
                            <i class="bi bi-graph-up-arrow icon-bg"></i>
                        </div>
                    </div>
                </div>
                <!-- Năm nay -->
                <div class="col-md-3">
                    <div class="card stat-card bg-dark shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-uppercase opacity-75 small">Năm <?php echo $current_year; ?></h6>
                            <h3 class="fw-bold mb-1"><?php echo number_format($stats_year['doanh_thu'], 0, ',', '.'); ?>đ</h3>
                            <div class="profit-text">Lãi: +<?php echo number_format($stats_year['loi_nhuan'], 0, ',', '.'); ?>đ</div>
                            <i class="bi bi-bank icon-bg"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold">Top 5 món đem lại lợi nhuận cao nhất</h5>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Tên sản phẩm</th>
                                        <th class="text-center">Số lượng bán</th>
                                        <th class="text-end pe-3">Lợi nhuận đóng góp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($p = $top_products->fetch_assoc()): ?>
                                    <tr>
                                        <td class="ps-3 fw-bold"><?php echo $p['TENSP']; ?></td>
                                        <td class="text-center"><?php echo $p['total_sold']; ?></td>
                                        <td class="text-end pe-3 text-success fw-bold">
                                            +<?php echo number_format($p['profit_item'], 0, ',', '.'); ?>đ
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm bg-warning-subtle">
                        <div class="card-body">
                            <h6 class="fw-bold"><i class="bi bi-lightbulb me-2"></i>Ghi chú quản lý</h6>
                            <ul class="small mb-0 ps-3">
                                <li class="mb-2"><b>Giá vốn:</b> Được lấy từ giá trị bạn nhập trong kho hàng hiện tại.</li>
                                <li class="mb-2"><b>Lợi nhuận:</b> Hệ thống lấy Giá bán trừ đi Giá vốn để ra con số chính xác nhất.</li>
                                <li><b>Trạng thái:</b> Chỉ các đơn hàng đã <b>Completed</b> mới được đưa vào báo cáo này.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>