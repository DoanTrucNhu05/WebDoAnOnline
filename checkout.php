<?php
session_start();
include 'config/db.php';

// 1. Kiểm tra nếu giỏ hàng trống
if (empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit();
}

// 2. Lấy thông tin người dùng từ database dựa trên file SQL (bảng Users)
$user_info = [
    'MAUSER' => null,
    'HOTEN'  => '',
    'SDT'    => '',
    'DIACHI' => ''
];

if (isset($_SESSION['username'])) {
    $uname = $_SESSION['username'];
    // Khớp với bảng Users: TENUSER, HOTEN, SDT, DIACHI
    $sql_user = "SELECT MAUSER, HOTEN, SDT, DIACHI FROM Users WHERE TENUSER = ?";
    $stmt_user = $conn->prepare($sql_user);
    if ($stmt_user) {
        $stmt_user->bind_param("s", $uname);
        $stmt_user->execute();
        $result_user = $stmt_user->get_result();
        if ($row_u = $result_user->fetch_assoc()) {
            $user_info = $row_u;
        }
    }
}

// 3. Xử lý đặt hàng
if (isset($_POST['btn_confirm'])) {
    // Lưu ý: Vì bảng HOADON của bạn không có cột HOTEN, SDT, DIACHI 
    // nên chúng ta sẽ ưu tiên dùng MAUSER. 
    // Nếu bạn muốn lưu thông tin giao hàng khác với thông tin đăng ký, 
    // bạn nên ALTER TABLE HOADON ADD COLUMN DIACHI_GIAO TEXT...
    
    $mauser = $user_info['MAUSER']; 
    $tongtien = $_POST['txt_tongtien'];
    $trangthai = 'Pending'; // Khớp với DEFAULT trong SQL

    // Bước A: Lưu vào bảng HOADON (Khớp với file SQL của bạn)
    $sql_hd = "INSERT INTO HOADON (MAUSER, TONGTIEN, TRANGTHAI) VALUES (?, ?, ?)";
    $stmt_hd = $conn->prepare($sql_hd);
    
    if ($stmt_hd === false) {
        die("Lỗi SQL: " . $conn->error);
    }

    $stmt_hd->bind_param("ids", $mauser, $tongtien, $trangthai);
    
    if ($stmt_hd->execute()) {
        $mahoadon = $conn->insert_id;

        // Bước B: Lưu vào bảng HOADON_CHITIET
        foreach ($_SESSION['cart'] as $id_sp => $soluong) {
            $id_sp = (int)$id_sp;
            
            // Lấy giá sản phẩm hiện tại
            $res_sp = $conn->query("SELECT GIA FROM SANPHAM WHERE MASP = $id_sp");
            if ($res_sp && $sp = $res_sp->fetch_assoc()) {
                $gia = $sp['GIA'];

                $sql_ct = "INSERT INTO HOADON_CHITIET (MAHOADON, MASP, SOLUONG, GIA) VALUES (?, ?, ?, ?)";
                $stmt_ct = $conn->prepare($sql_ct);
                if ($stmt_ct) {
                    $stmt_ct->bind_param("iiid", $mahoadon, $id_sp, $soluong, $gia);
                    $stmt_ct->execute();
                }
            }
        }

        // Bước C: Xóa giỏ hàng
        unset($_SESSION['cart']);
        echo "<script>alert('Đặt hàng thành công! Mã hóa đơn: #$mahoadon'); window.location.href='index.php';</script>";
        exit();
    } else {
        echo "Lỗi thực thi: " . $stmt_hd->error;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh toán - Online Food Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border-radius: 12px; border: none; }
        .btn-confirm { background: #ff4757; color: white; border: none; transition: 0.3s; }
        .btn-confirm:hover { background: #ff6b81; color: white; transform: translateY(-2px); }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm p-4 mb-4">
                <h4 class="fw-bold mb-4">Thông tin nhận hàng</h4>
                
                <?php if(!$user_info['MAUSER']): ?>
                    <div class="alert alert-warning">
                        Bạn chưa đăng nhập. Vui lòng <a href="login.php">Đăng nhập</a> để đặt hàng chính xác nhất.
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small uppercase">Họ tên</label>
                            <p class="fw-bold"><?= htmlspecialchars($user_info['HOTEN']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Số điện thoại</label>
                            <p class="fw-bold"><?= htmlspecialchars($user_info['SDT']) ?></p>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small">Địa chỉ mặc định</label>
                            <p class="fw-bold"><?= htmlspecialchars($user_info['DIACHI']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm p-4">
                <h4 class="fw-bold mb-4">Chi tiết đơn hàng</h4>
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <tbody>
                            <?php
                            $total = 0;
                            foreach ($_SESSION['cart'] as $id => $qty):
                                $id = (int)$id;
                                $res = $conn->query("SELECT * FROM SANPHAM WHERE MASP = $id");
                                if($row = $res->fetch_assoc()):
                                    $sub = $row['GIA'] * $qty;
                                    $total += $sub;
                            ?>
                            <tr>
                                <td class="px-0">
                                    <span class="d-block fw-bold"><?= $row['TENSP'] ?></span>
                                    <small class="text-muted">SL: <?= $qty ?></small>
                                </td>
                                <td class="text-end px-0 fw-bold">
                                    <?= number_format($sub, 0, ',', '.') ?>đ
                                </td>
                            </tr>
                            <?php endif; endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="fs-5">Tổng tiền:</span>
                    <span class="fs-4 fw-bold text-danger"><?= number_format($total, 0, ',', '.') ?>đ</span>
                </div>

                <form method="POST">
                    <input type="hidden" name="txt_tongtien" value="<?= $total ?>">
                    <button type="submit" name="btn_confirm" class="btn btn-confirm w-100 py-3 fw-bold rounded-pill shadow-sm" 
                            <?= !$user_info['MAUSER'] ? 'disabled' : '' ?>>
                        XÁC NHẬN THANH TOÁN
                    </button>
                </form>
                
                <a href="cart.php" class="btn btn-link w-100 text-secondary mt-2">Quay lại giỏ hàng</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>