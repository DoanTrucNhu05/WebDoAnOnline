<?php
	include 'config/db.php';
	session_start();

	// 1. Kiểm tra nếu chưa đăng nhập thì chuyển về trang login
	if (!isset($_SESSION['user_id'])) {
		header("Location: login.php");
		exit();
	}

	$user_id = $_SESSION['user_id'];

	// 2. Truy vấn danh sách đơn hàng 
	$sql = "SELECT * FROM HOADON WHERE MAUSER = ? ORDER BY NGAY DESC";
	$stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        die("Lỗi truy vấn: " . $conn->error);
    }

	$stmt->bind_param("i", $user_id);
	$stmt->execute();
	$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử đặt hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .status-pending { color: #f39c12; font-weight: bold; }
        .status-completed { color: #2ecc71; font-weight: bold; }
        .status-cancelled { color: #e74c3c; font-weight: bold; }
        .table-v-align td { vertical-align: middle; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="fa fa-utensils me-2"></i>OnlineFoodShop</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link text-white" href="index.php">Quay lại mua sắm</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-4">
            <h3 class="mb-4 text-uppercase fw-bold text-center text-dark">Lịch sử đặt hàng</h3>

            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover bg-white table-v-align">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th>Mã đơn</th>
                                <th>Ngày đặt</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold text-primary">#<?php echo $row['MAHOADON']; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['NGAY'])); ?></td>
                                    <td class="text-danger fw-bold"><?php echo number_format($row['TONGTIEN'], 0, ',', '.'); ?>đ</td>
                                    <td>
                                        <?php 
                                            $status = $row['TRANGTHAI'];
												if ($status == 'Pending') {
													echo '<span class="status-pending"><i class="fa fa-clock"></i> Chờ xử lý</span>';
												} elseif ($status == 'Processing') {
													echo '<span class="status-pending"><i class="fa fa-truck"></i> Đang giao</span>';
												} elseif ($status == 'Completed') {
													echo '<span class="status-completed"><i class="fa fa-check-circle"></i> Đã giao</span>';
												} elseif ($status == 'Cancelled') {
													echo '<span class="status-cancelled"><i class="fa fa-times-circle"></i> Đã hủy</span>';
												}
                                        ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fa fa-shopping-bag fa-4x text-muted mb-3 opacity-50"></i>
                    <p class="lead text-muted">Bạn chưa có đơn hàng nào.</p>
                    <a href="index.php" class="btn btn-primary rounded-pill px-4">Mua sắm ngay</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer class="mt-5 py-4 text-center text-muted border-top bg-white">
    <p class="mb-0">&copy; 2024 Online Food Shop - Lịch sử mua hàng</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>