<?php
session_start();
include '../config/db.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../login.php");
    exit();
}

// Lấy mã hóa đơn từ URL
$id_dh = $_GET['id'];

// 1. Lấy thông tin chung của hóa đơn và tên khách hàng (JOIN với bảng Users)
$sql_dh = "SELECT h.*, u.HOTEN, u.TENUSER 
           FROM HOADON h 
           LEFT JOIN Users u ON h.MAUSER = u.MAUSER 
           WHERE h.MAHOADON = $id_dh";
$res_dh = $conn->query($sql_dh);

if (!$res_dh || $res_dh->num_rows == 0) {
    die("Hóa đơn không tồn tại!");
}
$dh = $res_dh->fetch_assoc();

// 2. Lấy danh sách món ăn trong hóa đơn này (Sửa tên bảng thành HOADON_CHITIET)
$sql_ct = "SELECT ct.*, sp.TENSP, sp.IMAGE 
           FROM HOADON_CHITIET ct 
           JOIN SANPHAM sp ON ct.MASP = sp.MASP 
           WHERE ct.MAHOADON = $id_dh";
$result_ct = $conn->query($sql_ct);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết hóa đơn #<?php echo $id_dh; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow border-0">
        <div class="card-header bg-success text-white p-3 d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Chi tiết hóa đơn #<?php echo $id_dh; ?></h4>
            <a href="order_list.php" class="btn btn-light btn-sm">Quay lại</a>
        </div>
        <div class="card-body p-4">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5 class="text-success border-bottom pb-2">Thông tin khách hàng</h5>
                    <p class="mb-1"><strong>Tài khoản:</strong> <?php echo $dh['TENUSER']; ?></p>
                    <p class="mb-1"><strong>Họ tên khách:</strong> <?php echo $dh['HOTEN']; ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h5 class="text-success border-bottom pb-2">Thông tin đơn hàng</h5>
                    <p class="mb-1"><strong>Trạng thái:</strong> 
                        <span class="badge bg-primary"><?php echo $dh['TRANGTHAI']; ?></span>
                    </p>
                    <p class="mb-1"><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($dh['NGAY'])); ?></p>
                </div>
            </div>

            <h5 class="text-success">Danh sách món ăn</h5>
            <table class="table table-bordered align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th>Hình ảnh</th>
                        <th>Tên món ăn</th>
                        <th>Giá lúc mua</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($item = $result_ct->fetch_assoc()): ?>
                    <tr>
                        <td class="text-center">
                            <img src="../assets/images/<?php echo $item['IMAGE']; ?>" width="60" class="rounded shadow-sm">
                        </td>
                        <td class="fw-bold"><?php echo $item['TENSP']; ?></td>
                        <td class="text-center"><?php echo number_format($item['GIA'], 0, ',', '.'); ?>đ</td>
                        <td class="text-center"><?php echo $item['SOLUONG']; ?></td>
                        <td class="text-end fw-bold text-primary">
                            <?php echo number_format($item['GIA'] * $item['SOLUONG'], 0, ',', '.'); ?>đ
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end fs-5">TỔNG TIỀN THANH TOÁN:</th>
                        <th class="text-danger fs-5 text-end"><?php echo number_format($dh['TONGTIEN'], 0, ',', '.'); ?>đ</th>
                    </tr>
                </tfoot>
            </table>
            
            <div class="mt-4 no-print">
                <button onclick="window.print()" class="btn btn-outline-dark"><i class="bi bi-printer"></i> In hóa đơn</button>
            </div>
        </div>
    </div>
</div>
</body>
</html>