<?php
	session_start();
	include '../config/db.php';

	// 1. Kiểm tra quyền Admin
	if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
		header("Location: ../login.php");
		exit();
	}

	// 2. Lấy ID sản phẩm cần sửa từ URL
	$id = $_GET['id'];
	$sql_old = "SELECT * FROM SANPHAM WHERE MASP = $id";
	$res_old = $conn->query($sql_old);
	$product = $res_old->fetch_assoc();

	// 3. Lấy danh sách loại để hiện vào dropdown
	$sql_loai = "SELECT * FROM LOAISP";
	$result_loai = $conn->query($sql_loai);

	// 4. Xử lý khi bấm nút Cập nhật
	if (isset($_POST['btn_update'])) {
		$tensp = $_POST['txt_tensp'];
		$maloai = $_POST['txt_maloai'];
		$gia = $_POST['txt_gia'];
		$mota = $_POST['txt_mota'];
		
		// Kiểm tra xem người dùng có chọn ảnh mới không
		if ($_FILES['f_image']['name'] != "") {
			// Nếu có ảnh mới: Upload ảnh mới
			$image = $_FILES['f_image']['name'];
			$image_tmp = $_FILES['f_image']['tmp_name'];
			move_uploaded_file($image_tmp, "../assets/images/".$image);
		} else {
			// Nếu không chọn ảnh mới: Lấy lại tên ảnh cũ từ hidden field
			$image = $_POST['txt_image_old'];
		}

		$sql_update = "UPDATE SANPHAM SET 
						TENSP = '$tensp', 
						MALOAI = '$maloai', 
						GIA = '$gia', 
						IMAGE = '$image', 
						MOTA = '$mota' 
					   WHERE MASP = $id";
		
		if ($conn->query($sql_update)) {
			header("Location: index.php?msg=updated");
		}
	}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow mx-auto" style="max-width: 600px;">
        <div class="card-header bg-warning text-dark fw-bold">SỬA THÔNG TIN MÓN ĂN</div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Tên món:</label>
                    <input type="text" name="txt_tensp" class="form-control" value="<?php echo $product['TENSP']; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Loại sản phẩm:</label>
                    <select name="txt_maloai" class="form-select">
                        <?php while($l = $result_loai->fetch_assoc()): ?>
                            <option value="<?php echo $l['MALOAI']; ?>" <?php if($l['MALOAI'] == $product['MALOAI']) echo 'selected'; ?>>
                                <?php echo $l['TENLOAI']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Giá bán:</label>
                    <input type="number" name="txt_gia" class="form-control" value="<?php echo $product['GIA']; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Hình ảnh hiện tại:</label><br>
                    <img src="../assets/images/<?php echo $product['IMAGE']; ?>" width="100" class="mb-2 border">
                    <input type="hidden" name="txt_image_old" value="<?php echo $product['IMAGE']; ?>">
                    <input type="file" name="f_image" class="form-control" accept="image/*">
                    <small class="text-muted text-italic">(Để trống nếu không muốn đổi ảnh)</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Mô tả:</label>
                    <textarea name="txt_mota" class="form-control" rows="3"><?php echo $product['MOTA']; ?></textarea>
                </div>

                <button type="submit" name="btn_update" class="btn btn-warning w-100 fw-bold">CẬP NHẬT</button>
                <a href="index.php" class="btn btn-secondary w-100 mt-2">HỦY BỎ</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>