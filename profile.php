<?php
session_start();
include 'config/db.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$message = "";
$error = "";

// Cấu hình tên bảng và cột dựa theo file login.php
$table_name = "Users";
$col_username = "TENUSER";
$col_password = "MATKHAU";

// --- XỬ LÝ CẬP NHẬT THÔNG TIN CÁ NHÂN ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $new_hoten = $_POST['hoten'];
    $new_email = $_POST['email'];
    $new_sdt   = $_POST['sdt'];
    $new_diachi = $_POST['diachi'];
    
    $sql_update = "UPDATE $table_name SET HOTEN = ?, EMAIL = ?, SDT = ?, DIACHI = ? WHERE $col_username = ?";
    $stmt_up = $conn->prepare($sql_update);
    if ($stmt_up) {
        $stmt_up->bind_param("sssss", $new_hoten, $new_email, $new_sdt, $new_diachi, $username);
        if ($stmt_up->execute()) {
            $message = "Cập nhật thông tin thành công!";
        } else {
            $error = "Lỗi cập nhật thông tin: " . $stmt_up->error;
        }
    }
}

// --- XỬ LÝ ĐỔI MẬT KHẨU ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $old_pass = md5($_POST['old_pass']);
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];

    // Kiểm tra mật khẩu cũ
    $check_sql = "SELECT $col_password FROM $table_name WHERE $col_username = ? AND $col_password = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("ss", $username, $old_pass);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();

    if ($res_check->num_rows > 0) {
        if ($new_pass === $confirm_pass) {
            $new_pass_md5 = md5($new_pass);
            $sql_pass = "UPDATE $table_name SET $col_password = ? WHERE $col_username = ?";
            $stmt_pass = $conn->prepare($sql_pass);
            $stmt_pass->bind_param("ss", $new_pass_md5, $username);
            if ($stmt_pass->execute()) {
                $message = "Đổi mật khẩu thành công!";
            } else {
                $error = "Lỗi khi cập nhật mật khẩu.";
            }
        } else {
            $error = "Mật khẩu mới và xác nhận không khớp!";
        }
    } else {
        $error = "Mật khẩu cũ không chính xác!";
    }
}

// Lấy dữ liệu hiển thị
$sql_user = "SELECT * FROM $table_name WHERE $col_username = ?";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param("s", $username);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

if (!$user_data) {
    die("Lỗi: Không tìm thấy người dùng.");
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ & Đổi mật khẩu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .nav-pills .nav-link.active { background-color: #198754; }
        .nav-pills .nav-link { color: #495057; font-weight: 500; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <?php if($message): ?>
                <div class="alert alert-success alert-dismissible fade show"><?= $message ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="alert alert-danger alert-dismissible fade show"><?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>

            <div class="card p-4">
                <h3 class="mb-4 text-center text-success">Quản Lý Tài Khoản</h3>
                
                <!-- Tab điều hướng -->
                <ul class="nav nav-pills mb-4 justify-content-center" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-info-tab" data-bs-toggle="pill" data-bs-target="#pills-info" type="button" role="tab">Thông tin cá nhân</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-pass-tab" data-bs-toggle="pill" data-bs-target="#pills-pass" type="button" role="tab">Đổi mật khẩu</button>
                    </li>
                </ul>

                <div class="tab-content" id="pills-tabContent">
                    <!-- Tab 1: Thông tin cá nhân -->
                    <div class="tab-pane fade show active" id="pills-info" role="tabpanel">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Tên đăng nhập</label>
                                    <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($user_data['TENUSER']) ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Họ và Tên</label>
                                    <input type="text" name="hoten" class="form-control" value="<?= htmlspecialchars($user_data['HOTEN'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Email</label>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user_data['EMAIL'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Số điện thoại</label>
                                    <input type="text" name="sdt" class="form-control" value="<?= htmlspecialchars($user_data['SDT'] ?? '') ?>">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Địa chỉ</label>
                                    <textarea name="diachi" class="form-control" rows="2"><?= htmlspecialchars($user_data['DIACHI'] ?? '') ?></textarea>
                                </div>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-success w-100 py-2">Lưu Thông Tin</button>
                        </form>
                    </div>

                    <!-- Tab 2: Đổi mật khẩu -->
                    <div class="tab-pane fade" id="pills-pass" role="tabpanel">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Mật khẩu hiện tại</label>
                                <input type="password" name="old_pass" class="form-control" required placeholder="Nhập mật khẩu đang dùng">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Mật khẩu mới</label>
                                <input type="password" name="new_pass" class="form-control" required placeholder="Tối thiểu 6 ký tự">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Xác nhận mật khẩu mới</label>
                                <input type="password" name="confirm_pass" class="form-control" required placeholder="Nhập lại mật khẩu mới">
                            </div>
                            <button type="submit" name="change_password" class="btn btn-warning w-100 py-2 fw-bold text-dark">Đổi Mật Khẩu</button>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="index.php" class="text-muted small text-decoration-none">← Quay lại trang chủ</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>