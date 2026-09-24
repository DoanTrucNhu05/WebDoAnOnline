<?php
	session_start(); // Bắt đầu phiên làm việc
	include 'config/db.php'; // Kết nối CSDL

	$error = ""; // Biến lưu lỗi nếu đăng nhập thất bại

	if (isset($_POST['btn_login'])) {
		$username = $_POST['txt_user'];
		$password = md5($_POST['txt_pass']); // Mã hóa MD5 để so khớp với DB

		// Truy vấn kiểm tra tài khoản
		$sql = "SELECT * FROM Users WHERE TENUSER = '$username' AND MATKHAU = '$password'";
		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();
			
			// Lưu thông tin vào Session
			// Kiểm tra trạng thái
			if ($row['TRANGTHAI'] == 0) {
				$error = "Tài khoản đã bị khóa!";
			} else {
				// Lưu thông tin vào Session
				$_SESSION['user_id'] = $row['MAUSER'];
				$_SESSION['username'] = $row['TENUSER'];
				$_SESSION['role'] = $row['QUYEN']; // 1 là Admin, 0 là User

				// Chuyển hướng dựa trên quyền
				if ($row['QUYEN'] == 1) {
					header("Location: admin/index.php");
				} else {
					header("Location: index.php");
				}
				exit();
			}
			// --- HẾT ĐOẠN THÊM MỚI ---
		} else {
			$error = "Sai tên đăng nhập hoặc mật khẩu!";
		}
	}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập - Online Food Shop</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4 border p-4 shadow-sm rounded bg-white">
            <h3 class="text-center mb-4">Đăng Nhập</h3>
            <!-- HIỂN THỊ LỖI-->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger text-center" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
			
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Tên đăng nhập:</label>
                    <input type="text" name="txt_user" class="form-control" placeholder="Nhập tên tài khoản" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Mật khẩu:</label>
                    <input type="password" name="txt_pass" class="form-control" placeholder="Nhập mật khẩu" required>
                </div>
                
                <button type="submit" name="btn_login" class="btn btn-success w-100">Đăng nhập</button>
            </form>
            
            <div class="mt-3 text-center">
                <small>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></small>
            </div>
        </div>
    </div>
</div>

</body>
</html>