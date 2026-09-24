<?php
	session_start();
	include '../config/db.php'; 

	// 1. Kiểm tra quyền Admin
	if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) { // NẾu chưa DN hoặc Role khác 1
		header("Location: ../login.php");
		exit();
	}
	
	// 2. XỬ LÝ THÊM SẢN PHẨM
	if (isset($_POST['btnLuuSP'])) {
		$tensp = mysqli_real_escape_string($conn, $_POST['tensp']);
		$gia = $_POST['gia'];
		$gia_von = $_POST['gia_von']; 
		$soluong = $_POST['soluong'];
		$maloai = $_POST['maloai'];
		$mota = mysqli_real_escape_string($conn, $_POST['mota']); // bỏ các ký tự khôn hợp lệ
		//Lấy tên file + đường dẫn lưu
		$hinhanh = $_FILES['hinhanh']['name'];
		$target = "../assets/images/" . basename($hinhanh);
		
		if (move_uploaded_file($_FILES['hinhanh']['tmp_name'], $target)) { //Upload ảnh vào server
			$sql_them = "INSERT INTO SANPHAM (TENSP, GIA, GIA_VON, SOLUONG, IMAGE, MOTA, MALOAI) VALUES ('$tensp', '$gia', '$gia_von', '$soluong', '$hinhanh', '$mota', '$maloai')";
			if ($conn->query($sql_them)) {
				// Thêm maloai vào link để sau khi thêm xong nó lọc đúng loại đó luôn
				header("Location: index.php?msg=success&maloai=" . $maloai);
				exit();
			}
		}
	}

	// 3. XỬ LÝ CẬP NHẬT SẢN PHẨM
	if (isset($_POST['btnCapNhatSP'])) {
		$masp = $_POST['masp'];
		$tensp = mysqli_real_escape_string($conn, $_POST['tensp']);
		$gia = $_POST['gia'];
		$gia_von = $_POST['gia_von']; 
		$soluong = $_POST['soluong'];
		$maloai = $_POST['maloai'];
		$mota = mysqli_real_escape_string($conn, $_POST['mota']);
		//Chọn ảnh
		if (!empty($_FILES['hinhanh']['name'])) {
			$hinhanh = $_FILES['hinhanh']['name'];
			move_uploaded_file($_FILES['hinhanh']['tmp_name'], "../assets/images/" . $hinhanh);
			//ảnh mới
			$sql_update = "UPDATE SANPHAM SET TENSP='$tensp', GIA='$gia', GIA_VON='$gia_von', SOLUONG='$soluong', IMAGE='$hinhanh', MOTA='$mota', MALOAI='$maloai' WHERE MASP=$masp";
		} else {
			//Ảnh cũ
			$sql_update = "UPDATE SANPHAM SET TENSP='$tensp', GIA='$gia', GIA_VON='$gia_von', SOLUONG='$soluong', MOTA='$mota', MALOAI='$maloai' WHERE MASP=$masp";
		}

		if ($conn->query($sql_update)) {
			//Giữ lại mã loại sau khi cập nhật
			header("Location: index.php?msg=updated&maloai=" . $maloai);
			exit();
		}
	}
	
	// SỬ LÝ SỬA
	$edit_sp = null;
	if (isset($_GET['edit_id'])) {
		$id_edit = $_GET['edit_id'];
		$edit_sp = $conn->query("SELECT * FROM SANPHAM WHERE MASP = $id_edit")->fetch_assoc();
	}
	
	// 4. XỬ LÝ XÓA
	if (isset($_POST['delete_id'])) {
    $id_xoa = (int)$_POST['delete_id'];
	// Lấy lại mã loại từ input hidden hoặc URL để quay về đúng trang
    $maloai_quay_ve = isset($_GET['maloai']) ? $_GET['maloai'] : "";
    $sql_xoa = "DELETE FROM SANPHAM WHERE MASP = $id_xoa";
    
    if ($conn->query($sql_xoa)) {
		//Xóa xong vẫn đứng lại ở danh mục đó
        header("Location: index.php?msg=deleted&maloai=" . $maloai_quay_ve);
        exit();
		}
	}

	// 5. LẤY DỮ LIỆU ĐỂ HIỂN THỊ (CẬP NHẬT LOGIC LỌC & TÌM KIẾM)
    $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
    $maloai_filter = isset($_GET['maloai']) ? mysqli_real_escape_string($conn, $_GET['maloai']) : "";
    
    // Xây dựng điều kiện WHERE động
    $conditions = [];
    if (!empty($search)) {
        $conditions[] = "sp.TENSP LIKE '%$search%'";
    }
    if (!empty($maloai_filter)) {
        $conditions[] = "sp.MALOAI = '$maloai_filter'";
    }

    $where_clause = "";
    if (count($conditions) > 0) {
        $where_clause = " WHERE " . implode(" AND ", $conditions); // Nối 2 điều kiện trong mảng conditions = AND (WHERE sp.TENSP LIKE '%Banh_mi%' AND sp.MALOAI = '2')
    }

    $order_priority = "";
    if (!empty($search)) {
        $order_priority = " (CASE WHEN sp.TENSP LIKE '$search%' THEN 0 ELSE 1 END), ";
    }

	$sql = "SELECT sp.*, loai.TENLOAI 
            FROM SANPHAM sp 
            JOIN LOAISP loai ON sp.MALOAI = loai.MALOAI 
            $where_clause
            ORDER BY $order_priority sp.MASP DESC";
	$result = $conn->query($sql);
	
	//Danh Sách loại SP 
	$list_loai = $conn->query("SELECT * FROM LOAISP");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản trị - Sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
	<style>
        .form-card {  
			border-left: 5px solid #198754; 
			background-color: #e9f7ef;
		}
		.form-card-edit { 
			border-left: 5px solid #ffc107; 
			background-color: #fff8e1;
		}
    </style>
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold text-dark mb-0">Quản lý sản phẩm</h3>
					<!--Hiển thị tên loại đang lọc lên giao diện-->
                    <?php if(!empty($maloai_filter)): // Nếu người dùng có chọn loại
                        $list_loai->data_seek(0);     // Reset đọc lại từ dòng đầu
                        while($l = $list_loai->fetch_assoc()) {  //Nếu loại trong List = loại người dùng chọn 
                            if($l['MALOAI'] == $maloai_filter) {
                                echo "<span class='badge bg-primary mt-2'>Đang lọc: ".$l['TENLOAI']."</span>"; // Hiển thị tên Loại đang lọc
                                break;
                            }
                        }
                    endif; ?>
                </div>
				
				<div class="d-flex gap-2">
					<form method="GET" class="d-flex shadow-sm">
						<input type="text" name="search" class="form-control" placeholder="Tìm tên món ăn..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
						
						<!--DROPDOWN lọc nhanh -->
                        <select name="maloai" class="form-select border-0 border-start" style="width: 150px;" onchange="this.form.submit()">
                            <option value="">Tất cả loại</option>
                            <?php 
                            $list_loai->data_seek(0);
                            while($l = $list_loai->fetch_assoc()): ?>
                                <option value="<?php echo $l['MALOAI']; ?>" <?php echo ($maloai_filter == $l['MALOAI']) ? 'selected' : ''; ?>>
                                    <?php echo $l['TENLOAI']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
						<!--BUTTON SEARRCH -->
						<button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
						<!--BUTTON CLEAR -->
						<?php if(!empty($search) || !empty($maloai_filter)): ?> 						<!--Nếu có search hoặc lọc -->
							<a href="index.php" class="btn btn-secondary"><i class="bi bi-x-lg"></i></a><!--Mới hiện nút CLEAR -->
						<?php endif; ?>
					</form>
					
					
					<!--BUTTON THÊM MÓN Chỉ hiện khi  Không mở form Thêm + Không đang Sửa + mang theo mã loại nếu đang lọc-->
					<?php if(!isset($_GET['show_form']) && !isset($_GET['edit_id'])): ?>
						<a href="index.php?show_form=1&maloai=<?php echo $maloai_filter; ?>&search=<?php echo urlencode($search); ?>" class="btn btn-success shadow-sm">
						<i class="bi bi-plus-circle"></i> Thêm món mới
						</a>
					<?php endif; ?>
				</div>
				
				
            
            </div>

            <?php if(isset($_GET['show_form']) || isset($_GET['edit_id'])): 
                $is_edit = isset($_GET['edit_id']);
                $title = $is_edit ? "Chỉnh sửa sản phẩm: " . $edit_sp['TENSP'] : "Thêm món ăn mới";
                $btn_name = $is_edit ? "btnCapNhatSP" : "btnLuuSP";
            ?>
			<!-- Form nhập liêu Sửa - Thêm-->
            <div class="card shadow-sm mb-4 border-0 <?php echo $is_edit ? 'form-card-edit' : 'form-card'; ?>">
                <div class="card-body">
                    <h5 class="card-title <?php echo $is_edit ? 'text-warning' : 'text-success'; ?> mb-4 fw-bold">
                        <i class="bi <?php echo $is_edit ? 'bi-pencil-square' : 'bi-plus-circle'; ?>"></i> <?php echo $title; ?>
                    </h5>
					
                    <form method="POST" enctype="multipart/form-data">
                        <?php if($is_edit): ?>
                            <input type="hidden" name="masp" value="<?php echo $edit_sp['MASP']; ?>">
                        <?php endif; ?>
                        
                        <div class="row g-3 mb-3">
							<!-- Tên SP-->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tên món ăn</label>
                                <input type="text" name="tensp" class="form-control" value="<?php echo $is_edit ? $edit_sp['TENSP'] : ''; ?>" required placeholder="Nhập tên món...">
                            </div>
							<!-- Loại SP-->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Danh mục</label>
                                <select name="maloai" class="form-select" required>
                                    <option value="">-- Chọn danh mục --</option>
									<?php 
									$list_loai->data_seek(0); 
									while($l = $list_loai->fetch_assoc()): 
										// BIẾN KIỂM TRA ĐIỀU KIỆN SELECTED
										$selected = "";
										if ($is_edit) {
											// Nếu đang sửa: So khớp với MALOAI của sản phẩm đang sửa
											if ($edit_sp['MALOAI'] == $l['MALOAI']) $selected = "selected";
										} else {
											// Nếu đang thêm mới: So khớp với MALOAI đang lọc trên URL
											if (isset($_GET['maloai']) && $_GET['maloai'] == $l['MALOAI']) $selected = "selected";
										}
									?>
										<option value="<?php echo $l['MALOAI']; ?>" <?php echo $selected; ?>> 
											<!-- Tạo option trong dropdown ,  Nếu MALOAI SP được chọn = MALOAI trong option , selcted hiển thị giá trị trùng-->
                                            <?php echo $l['TENLOAI']; ?>
                                        </option>
									 <?php endwhile; ?>																		
                                </select>
                            </div>
							
                            <div class="col-md-2">
                                <label class="form-label fw-bold text-danger">Giá bán (VNĐ)</label>
                                <input type="number" name="gia" class="form-control border-danger" value="<?php echo $is_edit ? $edit_sp['GIA'] : ''; ?>" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold text-primary">Giá vốn (VNĐ)</label>
                                <input type="number" name="gia_von" class="form-control border-primary" value="<?php echo $is_edit ? $edit_sp['GIA_VON'] : '0'; ?>" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold text-success">Số lượng</label>
                                <input type="number" name="soluong" class="form-control border-success" value="<?php echo $is_edit ? $edit_sp['SOLUONG'] : '0'; ?>" required min="0">
                            </div>
                        </div>
						<!--Hình ảnh-->
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Hình ảnh</label>
                                <input type="file" name="hinhanh" class="form-control" <?php echo $is_edit ? '' : 'required'; ?>>
                                <?php if($is_edit): ?>
                                    <div class="mt-2 small text-muted">
                                        Ảnh hiện tại: <span class="badge bg-secondary"><?php echo $edit_sp['IMAGE']; ?></span> <!--Đường dẫn ảnh hiện tại -->
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Mô tả món ăn</label>
                                <textarea name="mota" class="form-control" rows="2" placeholder="Nhập mô tả chi tiết..."><?php echo $is_edit ? $edit_sp['MOTA'] : ''; ?></textarea>
                            </div>
                        </div>
<!--BUTTON LƯU , HỦY-->
                        <div class="mt-4 text-end">
                            <a href="index.php" class="btn btn-light border px-4">Hủy bỏ</a>
                            <button type="submit" name="<?php echo $btn_name; ?>" class="btn <?php echo $is_edit ? 'btn-warning' : 'btn-success'; ?> px-5">
                                <i class="bi bi-check-lg"></i> <?php echo $is_edit ? 'Cập nhật' : 'Lưu sản phẩm'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <?php if(isset($_GET['msg'])): ?>
                <div class="alert alert-info border-0 shadow-sm mb-4">
                    <i class="bi bi-info-circle me-2"></i>
                    <?php 
                        if($_GET['msg'] == 'success') echo "Thêm món mới thành công!";
                        if($_GET['msg'] == 'updated') echo "Đã cập nhật thông tin sản phẩm!";
                        if($_GET['msg'] == 'deleted') echo "Đã xóa sản phẩm khỏi hệ thống!";
                    ?>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-3">STT</th> <!-- Đổi tiêu đề ID thành STT -->
                                <th>Hình ảnh</th>
                                <th>Tên món</th>
                                <th>Danh mục</th>
                                <th class="text-primary">Giá vốn</th>
                                <th class="text-danger">Giá bán</th>
                                <th class="text-center">Số lượng</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $stt = 1; // Khởi tạo biến đếm
                            while($row = $result->fetch_assoc()): 
                            ?>
                            <tr class="<?php echo (isset($_GET['edit_id']) && $_GET['edit_id'] == $row['MASP']) ? 'table-warning' : ''; ?>">
                                <td class="ps-3"><?php echo $stt++; ?></td> <!-- In STT tự tăng -->
                                <td><img src="../assets/images/<?php echo $row['IMAGE']; ?>" width="55" height="55" class="rounded shadow-sm" style="object-fit: cover;"></td>
                                <td class="fw-bold text-dark"><?php echo $row['TENSP']; ?></td>
                                <td><?php echo $row['TENLOAI']; ?></td>
                                <td class="text-primary fw-bold"><?php echo number_format($row['GIA_VON'], 0, ',', '.'); ?>đ</td>
                                <td class="text-danger fw-bold"><?php echo number_format($row['GIA'], 0, ',', '.'); ?>đ</td>
                                <!-- Số lượng -->
                                <td class="text-center">
                                    <?php if($row['SOLUONG'] <= 0): ?>
                                        <span class="badge bg-danger">Hết hàng</span>
                                    <?php elseif($row['SOLUONG'] <= 5): ?>
                                        <span class="badge bg-warning text-dark"><?php echo $row['SOLUONG']; ?> (Sắp hết)</span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><?php echo $row['SOLUONG']; ?></span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <a href="index.php?edit_id=<?php echo $row['MASP']; ?>" class="btn btn-warning btn-sm"> <!--đổ dữ liệu vào rm-->
                                        <i class="bi bi-pencil"></i> Sửa
                                    </a>
                                    <form method="POST" style="display:inline;">
										<input type="hidden" name="delete_id" value="<?php echo $row['MASP']; ?>">
										<button class="btn btn-danger btn-sm" onclick="return confirm('Xóa món này?')">
											<i class="bi bi-trash"></i> Xóa
										</button>
									</form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>