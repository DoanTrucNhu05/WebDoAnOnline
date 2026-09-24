<?php
	session_start();
	include '../config/db.php'; 

	// 1. Kiểm tra quyền Admin
	if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
		header("Location: ../login.php");
		exit();
	}

	// 2. XỬ LÝ THÊM DANH MỤC
	if (isset($_POST['btnLuuLoai'])) {
		$tenloai = mysqli_real_escape_string($conn, $_POST['tenloai']);
		$sql_them = "INSERT INTO LOAISP (TENLOAI) VALUES ('$tenloai')";
		if ($conn->query($sql_them)) {
			header("Location: categories.php?msg=success");
			exit();
		}
	}

	// 3. XỬ LÝ CẬP NHẬT (EDIT)
	if (isset($_POST['btnCapNhatLoai'])) {
		$maloai_edit = $_POST['maloai_edit'];
		$tenloai_edit = mysqli_real_escape_string($conn, $_POST['tenloai_edit']);
		$sql_update = "UPDATE LOAISP SET TENLOAI = '$tenloai_edit' WHERE MALOAI = $maloai_edit";
		if ($conn->query($sql_update)) {
			header("Location: categories.php?msg=updated");
			exit();
		}
	}

	// 4. XỬ LÝ XÓA
	if (isset($_GET['delete_id'])) {
		$id_xoa = $_GET['delete_id'];
		$sql_xoa = "DELETE FROM LOAISP WHERE MALOAI = $id_xoa";
		if ($conn->query($sql_xoa)) {
			header("Location: categories.php?msg=deleted");
			exit();
		}
	}

	// 5. LẤY DỮ LIỆU HIỂN THỊ
	$sql = "SELECT * FROM LOAISP ORDER BY MALOAI DESC";
	$result = $conn->query($sql);

	// Lấy thông tin để sửa nếu có tham số edit_id trên URL
	$edit_data = null;
	if (isset($_GET['edit_id'])) {
		$id_sua = $_GET['edit_id'];
		$res_sua = $conn->query("SELECT * FROM LOAISP WHERE MALOAI = $id_sua");
		$edit_data = $res_sua->fetch_assoc();
	}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản trị - Danh mục món ăn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .form-card {  
			border-left: 5px solid #198754; 
			background-color: #e9f7ef; /* xanh nhạt */
		}
		.form-card-edit { 
			border-left-color: #ffc107; 
			background-color: #fff8e1; /* vàng nhạt */
		}
		/* Style cho link danh mục*/
        .category-link { text-decoration: none; color: #212529; transition: 0.2s; }
        .category-link:hover { color: #198754; text-decoration: underline; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">

        <!-- Gọi Sidebar từ file riêng -->
		<?php include 'sidebar.php'; ?>

        <!-- Nội dung chính -->
        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Quản lý danh mục</h3>
                <?php if(!isset($_GET['show_form']) && !isset($_GET['edit_id'])): ?>
                    <a href="categories.php?show_form=1" class="btn btn-success"><i class="bi bi-plus-circle"></i> Thêm loại mới</a>
                <?php endif; ?>
            </div>

            <!-- FORM THÊM DANH MỤC -->
            <?php if(isset($_GET['show_form'])): ?>
            <div class="card shadow-sm mb-4 form-card">
                <div class="card-body">
                    <h5 class="card-title text-success mb-3"><i class="bi bi-pencil-square"></i> Thêm loại món ăn mới</h5>
                    <form method="POST">
                        <div class="row align-items-end">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Tên loại danh mục</label>
                                <input type="text" name="tenloai" class="form-control" placeholder="Nhập tên loại..." required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" name="btnLuuLoai" class="btn btn-success">Lưu danh mục</button>
                                <a href="categories.php" class="btn btn-outline-secondary">Hủy</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- FORM CHỈNH SỬA (EDIT) -->
            <?php if(isset($_GET['edit_id']) && $edit_data): ?>
            <div class="card shadow-sm mb-4 form-card form-card-edit">
                <div class="card-body">
                    <h5 class="card-title text-warning mb-3"><i class="bi bi-pencil-square"></i> Chỉnh sửa danh mục: <?php echo $edit_data['TENLOAI']; ?></h5>
                    <form method="POST">
                        <input type="hidden" name="maloai_edit" value="<?php echo $edit_data['MALOAI']; ?>">
                        <div class="row align-items-end">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Tên loại mới</label>
                                <input type="text" name="tenloai_edit" class="form-control" value="<?php echo $edit_data['TENLOAI']; ?>" required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" name="btnCapNhatLoai" class="btn btn-warning">Cập nhật ngay</button>
                                <a href="categories.php" class="btn btn-outline-secondary">Hủy</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Thông báo -->
            <?php 
                if(isset($_GET['msg'])) {
                    $m = $_GET['msg'];
                    if($m == 'success') echo "<div class='alert alert-success border-0 shadow-sm'>Đã thêm thành công!</div>";
                    if($m == 'updated') echo "<div class='alert alert-warning border-0 shadow-sm'>Đã cập nhật thay đổi!</div>";
                    if($m == 'deleted') echo "<div class='alert alert-danger border-0 shadow-sm'>Đã xóa danh mục!</div>";
                }
            ?>

            <!-- Bảng danh sách -->
            <div class="col-md-8">
                <table class="table table-hover table-bordered shadow-sm bg-white">
                    <thead class="table-dark">
                        <tr>
                            <th width="80" class="text-center">STT</th> <!-- Đã đổi thành STT -->
                            <th>Tên Loại Danh Mục</th>
                            <th width="180">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $stt = 1; // Khởi tạo biến đếm số thứ tự
                        while($row = $result->fetch_assoc()): 
                        ?>
                        <tr class="<?php echo (isset($_GET['edit_id']) && $_GET['edit_id'] == $row['MALOAI']) ? 'table-warning' : ''; ?>">
							<!-- Hiển thị STT -->
                            <td class="text-center fw-bold text-muted"><?php echo $stt++; ?></td> 
							<!--LINK  dẫn sang SẢN PHẨM (index.php) kèm mã loại -->
                            <td class="fw-bold">
                                <a href="index.php?maloai=<?php echo $row['MALOAI']; ?>" class="category-link">
                                    <i class="bi bi-folder2-open me-2 text-primary"></i>
                                    <?php echo $row['TENLOAI']; ?>
                                </a>
                            </td>
							<!-- Btn Sửa, Xóa-->
                            <td>
                                <a href="categories.php?edit_id=<?php echo $row['MALOAI']; ?>" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil"></i> Sửa
                                </a>
                                <a href="categories.php?delete_id=<?php echo $row['MALOAI']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa loại này sẽ ảnh hưởng đến các sản phẩm liên quan. Bạn chắc chắn chứ?')">
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
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>