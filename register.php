<?php
include 'config/db.php';

$error = "";
if (isset($_POST['btnRegister'])) {
    $username = $_POST['username'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email']; 
    $password = $_POST['password'];
    $re_password = $_POST['re_password'];
    $phone = $_POST['phone']; 
    $address = $_POST['address']; 

    // Kiểm tra xem tên đăng nhập đã tồn tại chưa
    $stmt_check = $conn->prepare("SELECT * FROM Users WHERE TENUSER = ?");
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $res = $stmt_check->get_result();

    if ($res->num_rows > 0) {
        $error = "Tên đăng nhập này đã có người sử dụng!";
    } elseif ($password != $re_password) {
        $error = "Mật khẩu xác nhận không khớp!";
    } else {
        // Mã hóa MD5 cho mật khẩu
        $pass_md5 = md5($password);
        
        // QUYEN mặc định là 0 cho khách hàng, TRANGTHAI mặc định là 1 (Hoạt động)
        $sql = "INSERT INTO Users (TENUSER, MATKHAU, HOTEN, EMAIL, SDT, DIACHI, QUYEN, TRANGTHAI) VALUES (?, ?, ?, ?, ?, ?, 0, 1)";
        $stmt_insert = $conn->prepare($sql);
        
        if ($stmt_insert) {
            // Bind 6 tham số 
            $stmt_insert->bind_param("ssssss", $username, $pass_md5, $fullname, $email, $phone, $address);
            if ($stmt_insert->execute()) {
                header("Location: login.php?msg=registered");
                exit();
            } else {
                $error = "Lỗi đăng ký: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f1f3f5; }
        .register-container { max-width: 450px; margin: 50px auto; }
        .card { border-radius: 15px; }
        .form-control { border-radius: 8px; padding: 10px 15px; }
        .btn-success { border-radius: 8px; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <div class="register-container card shadow-sm p-4 border-0">
        <h3 class="text-center text-success fw-bold mb-4">TẠO TÀI KHOẢN</h3>
        
        <?php if($error != ""): ?>
            <div class="alert alert-danger small py-2"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label small fw-bold">Họ và tên</label>
                    <input type="text" name="fullname" class="form-control" placeholder="Nguyễn Văn A" required>
                </div>
                
                <!-- Bổ sung trường nhập Email -->
                <div class="col-md-12 mb-3">
                    <label class="form-label small fw-bold">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="example@gmail.com" required>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label small fw-bold">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control" placeholder="090xxxxxxx" required>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label small fw-bold">Địa chỉ giao hàng</label>
                    <input type="text" name="address" class="form-control" placeholder="Số nhà, tên đường, quận/huyện..." required>
                </div>

                <hr class="my-3 text-muted">

                <div class="col-md-12 mb-3">
                    <label class="form-label small fw-bold">Tên đăng nhập</label>
                    <input type="text" name="username" class="form-control" placeholder="user123" required>
                </div>

                <div class="col-6 mb-3">
                    <label class="form-label small fw-bold">Mật khẩu</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="col-6 mb-3">
                    <label class="form-label small fw-bold">Xác nhận</label>
                    <input type="password" name="re_password" class="form-control" required>
                </div>
            </div>

            <button type="submit" name="btnRegister" class="btn btn-success w-100 py-2 mt-2">ĐĂNG KÝ NGAY</button>
            
            <div class="text-center mt-3">
                <small class="text-muted">Đã có tài khoản? <a href="login.php" class="text-success text-decoration-none fw-bold">Đăng nhập</a></small>
            </div>
        </form>
    </div>
</div>
</body>
</html>