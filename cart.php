<?php
session_start();
include 'config/db.php';

// 1. Xử lý các hành động trong giỏ hàng
if (isset($_GET['action'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];

    switch ($action) {
        case 'add': // Thêm mới hoặc tăng từ trang chủ
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = array();
            if (isset($_SESSION['cart'][$id])) $_SESSION['cart'][$id]++;
            else $_SESSION['cart'][$id] = 1;
            break;

        case 'inc': // Nút cộng trong giỏ hàng
            if (isset($_SESSION['cart'][$id])) $_SESSION['cart'][$id]++;
            break;

        case 'dec': // Nút trừ trong giỏ hàng
            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id]--;
                // Nếu giảm xuống 0 thì xóa luôn món đó
                if ($_SESSION['cart'][$id] < 1) unset($_SESSION['cart'][$id]);
            }
            break;

        case 'delete': // Nút xóa hẳn món ăn
            unset($_SESSION['cart'][$id]);
            break;
    }
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Giỏ hàng của bạn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h3 class="fw-bold mb-4"><i class="bi bi-cart3 text-success"></i> Giỏ hàng của bạn</h3>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <?php if (!empty($_SESSION['cart'])): ?>
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Món ăn</th>
                                    <th>Giá</th>
                                    <th style="width: 100px;">Số lượng</th>
                                    <th>Thành tiền</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total_money = 0;
                                foreach ($_SESSION['cart'] as $id => $quantity):
                                    $sql = "SELECT * FROM SANPHAM WHERE MASP = $id";
                                    $res = $conn->query($sql);
                                    $row = $res->fetch_assoc();
                                    $subtotal = $row['GIA'] * $quantity;
                                    $total_money += $subtotal;
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="assets/images/<?php echo $row['IMAGE']; ?>" width="50" height="50" class="rounded me-3" style="object-fit: cover;">
                                            <span class="fw-bold"><?php echo $row['TENSP']; ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo number_format($row['GIA'], 0, ',', '.'); ?>đ</td>
									
                                    <td><!--Cộng Trừ Số Lượng-->
										<div class="d-flex align-items-center bg-light rounded-pill p-1" style="width: fit-content; border: 1px solid #dee2e6;">
											<a href="cart.php?action=dec&id=<?php echo $id; ?>" class="btn btn-sm btn-light rounded-circle shadow-sm border-0">
												<i class="bi bi-dash-lg"></i>
											</a>
											
											<span class="mx-3 fw-bold"><?php echo $quantity; ?></span>
											
											<a href="cart.php?action=inc&id=<?php echo $id; ?>" class="btn btn-sm btn-light rounded-circle shadow-sm border-0">
												<i class="bi bi-plus-lg text-success"></i>
											</a>
										</div>
									</td>
									
                                    <td class="text-success fw-bold"><?php echo number_format($subtotal, 0, ',', '.'); ?>đ</td>
                                    <td>
                                        <a href="cart.php?action=delete&id=<?php echo $id; ?>" class="text-danger"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-cart-x fs-1 text-muted"></i>
                            <p class="mt-2">Giỏ hàng đang trống.</p>
                            <a href="index.php" class="btn btn-success rounded-pill px-4">Tiếp tục mua sắm</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Tóm tắt đơn hàng</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tạm tính:</span>
                        <span><?php echo number_format($total_money ?? 0, 0, ',', '.'); ?>đ</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Phí vận chuyển:</span>
                        <span class="text-success">Miễn phí</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold fs-5">Tổng cộng:</span>
                        <span class="fw-bold fs-5 text-danger"><?php echo number_format($total_money ?? 0, 0, ',', '.'); ?>đ</span>
                    </div>
                    
                    <?php if (!empty($_SESSION['cart'])): ?>
                        <a href="checkout.php" class="btn btn-success w-100 btn-lg rounded-pill fw-bold">THANH TOÁN NGAY</a>
                    <?php endif; ?>
                    <a href="index.php" class="btn btn-outline-secondary w-100 mt-2 rounded-pill">Quay lại mua thêm</a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>