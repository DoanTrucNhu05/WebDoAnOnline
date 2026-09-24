<?php
session_start();
include '../config/db.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../login.php");
    exit();
}

// Xử lý cập nhật trạng thái đơn hàng (Sửa bảng HOADON)
if (isset($_POST['update_status'])) {
    $id_dh = $_POST['order_id'];
    $status = $_POST['status'];
    $sql_update = "UPDATE HOADON SET TRANGTHAI = '$status' WHERE MAHOADON = $id_dh";
    $conn->query($sql_update);
    header("Location: order_list.php?msg=updated");
}

// --- Đoạn code xử lý xóa đơn hàng ---
if (isset($_GET['delete_id'])) {
    $id_xoa = $_GET['delete_id'];

    // 1. Xóa tất cả món ăn trong hóa đơn này trước (bảng con)
    $sql_xoa_chitiet = "DELETE FROM HOADON_CHITIET WHERE MAHOADON = $id_xoa";
    $conn->query($sql_xoa_chitiet);

    // 2. Sau đó mới xóa hóa đơn chính (bảng cha)
    $sql_xoa_hoadon = "DELETE FROM HOADON WHERE MAHOADON = $id_xoa";
    
    if ($conn->query($sql_xoa_hoadon)) {
        header("Location: order_list.php?msg=deleted");
        exit();
    } else {
        echo "Lỗi khi xóa: " . $conn->error;
    }
}


// Lấy danh sách từ bảng HOADON, JOIN với bảng Users để lấy tên khách
$filter = isset($_GET['filter']) ? $_GET['filter'] : ''; //Lấy giá trị filter từ URL
$sql = "SELECT h.*, u.HOTEN FROM HOADON h LEFT JOIN Users u ON h.MAUSER = u.MAUSER"; //Lấy tất cả đơn hàng + nếu có user thì lấy thêm tên khách
if ($filter != '') { //Nếu có lọc 
    // Lưu ý: Dùng mysqli_real_escape_string để bảo mật hơn
    $filter_safe = $conn->real_escape_string($filter);
    $sql .= " WHERE h.TRANGTHAI = '$filter_safe'"; //Thêm điều kiện lọc
	}
$sql .= " ORDER BY h.MAHOADON DESC"; //đơn mới nhất lên trước
$result = $conn->query($sql);


if (!$result) {
    die("Lỗi truy vấn: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý đơn hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Gọi Sidebar từ file riêng -->
		<?php include 'sidebar.php'; ?>

        <div class="col-md-10 p-4">
            <h3 class="mb-4">Danh sách đơn hàng</h3>
			<!--Tạo ra các nút bấm (Link) gửi kèm tham số ?filter=... lên thanh địa chỉ để PHP nhận diện.-->
            <div class="mb-3 d-flex align-items-center gap-2">
				<span class="fw-bold"><i class="bi bi-funnel"></i> Lọc theo:</span>
				<a href="order_list.php" class="btn btn-outline-dark btn-sm">Tất cả</a>
				<a href="order_list.php?filter=Pending" class="btn btn-outline-warning btn-sm">Chờ xử lý</a>
				<a href="order_list.php?filter=Processing" class="btn btn-outline-info btn-sm">Đang giao</a>
				<a href="order_list.php?filter=Completed" class="btn btn-outline-success btn-sm">Đã giao</a>
				<a href="order_list.php?filter=Cancelled" class="btn btn-outline-danger btn-sm">Đã hủy</a>
			</div>
            <?php if(isset($_GET['msg'])) echo "<div class='alert alert-success'>Cập nhật trạng thái thành công!</div>"; ?>

            <table class="table table-hover border shadow-sm">
                <thead class="table-dark">
                    <tr>
                        <th>Mã ĐH</th>
                        <th>Khách hàng</th>
                        <th>Tổng tiền</th>
                        <th>Ngày đặt</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $row['MAHOADON']; ?></td>
                        <td><?php echo $row['HOTEN'] ? $row['HOTEN'] : "Khách vãng lai"; ?></td>
                        <td class="fw-bold text-danger"><?php echo number_format($row['TONGTIEN'], 0, ',', '.'); ?>đ</td>
                        <td><?php echo date('d/m/Y H:i', strtotime($row['NGAY'])); ?></td>
                        <td>
                            <form method="POST" class="d-flex gap-2">
                                <input type="hidden" name="order_id" value="<?php echo $row['MAHOADON']; ?>">
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()"> <!--Khi thay đổi status thì submit-->
                                    <option value="Pending" <?php if($row['TRANGTHAI']=='Pending') echo 'selected'; ?>>Chờ xử lý (Pending)</option>
                                    <option value="Processing" <?php if($row['TRANGTHAI']=='Processing') echo 'selected'; ?>>Đang giao (Processing)</option>
                                    <option value="Completed" <?php if($row['TRANGTHAI']=='Completed') echo 'selected'; ?>>Đã giao (Completed)</option>
                                    <option value="Cancelled" <?php if($row['TRANGTHAI']=='Cancelled') echo 'selected'; ?>>Đã hủy (Cancelled)</option>
                                </select>
                                <input type="hidden" name="update_status">
                            </form>
                        </td>
                        <td>
                            <a href="order_detail.php?id=<?php echo $row['MAHOADON']; ?>" class="btn btn-info btn-sm text-white">
                                <i class="bi bi-eye"></i> Xem
                            </a>
							<a href="order_list.php?delete_id=<?php echo $row['MAHOADON']; ?>" 
							   class="btn btn-danger btn-sm" 
							   onclick="return confirm('Bạn có chắc chắn muốn xóa đơn hàng #<?php echo $row['MAHOADON']; ?> này không? Thao tác này không thể hoàn tác!')">
							   <i class="bi bi-trash"></i> Xóa
							</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>