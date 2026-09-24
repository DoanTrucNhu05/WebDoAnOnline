<?php
session_start();
include '../config/db.php'; 

// 1. Kiểm tra quyền Admin (Giả sử QUYEN = 1 là Admin)
if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../login.php");
    exit();
}

$tableName = "users";

// 2. XỬ LÝ CẬP NHẬT TRẠNG THÁI (KHÓA/MỞ KHÓA)
if (isset($_POST['toggle_status_id'])) {
    $id = (int)$_POST['toggle_status_id'];
    $current_status = (int)$_POST['current_status'];
    $new_status = ($current_status == 1) ? 0 : 1;
    
    $sql_update = "UPDATE $tableName SET TRANGTHAI = $new_status WHERE MAUSER = $id AND QUYEN = 0";
    $conn->query($sql_update);
    header("Location: customers.php?msg=updated");
    exit();
}

// 3. XỬ LÝ XÓA
if (isset($_POST['delete_id'])) {
    $id_xoa = (int)$_POST['delete_id'];
    $sql_xoa = "DELETE FROM $tableName WHERE MAUSER = $id_xoa AND QUYEN = 0";
    $conn->query($sql_xoa);
    header("Location: customers.php?msg=deleted");
    exit();
}

// 4. LẤY SỐ LIỆU THỐNG KÊ (QUYEN = 0 là khách hàng)
$total_count = $conn->query("SELECT COUNT(*) as total FROM $tableName WHERE QUYEN = 0")->fetch_assoc()['total'];
$active_count = $conn->query("SELECT COUNT(*) as total FROM $tableName WHERE QUYEN = 0 AND TRANGTHAI = 1")->fetch_assoc()['total'];
$locked_count = $conn->query("SELECT COUNT(*) as total FROM $tableName WHERE QUYEN = 0 AND TRANGTHAI = 0")->fetch_assoc()['total'];

// 5. TRUY VẤN DANH SÁCH & TÌM KIẾM
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$sql = "SELECT * FROM $tableName WHERE QUYEN = 0";
if (!empty($search)) {
    $sql .= " AND (HOTEN LIKE '%$search%' OR TENUSER LIKE '%$search%' OR EMAIL LIKE '%$search%')";
}
$sql .= " ORDER BY MAUSER DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị - Khách hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Inter', sans-serif; }
        .stats-card { border: none; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .stats-label { font-size: 0.85rem; color: #6c757d; font-weight: 600; text-transform: uppercase; }
        .stats-value { font-size: 1.8rem; font-weight: 800; }
        .user-avatar { width: 40px; height: 40px; background: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #495057; }
        .status-pill { padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
    </style>
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row">
        <!-- Gọi Sidebar giống file index sản phẩm -->
        <?php include 'sidebar.php'; ?>

        <!-- Nội dung chính (col-md-10) -->
        <div class="col-md-10 p-4">
            
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold text-dark mb-0">Quản lý khách hàng</h3>
                    <p class="text-muted small">Xem và quản lý tài khoản người dùng đã đăng ký</p>
                </div>
                
                <div class="d-flex gap-2">
                    <form method="GET" class="d-flex shadow-sm">
                        <input type="text" name="search" class="form-control" style="width: 300px;" placeholder="Tìm tên, email hoặc tài khoản..." value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                        <?php if(!empty($search)): ?>
                            <a href="customers.php" class="btn btn-secondary"><i class="bi bi-x-lg"></i></a>
                        <?php endif; ?>
                    </form>
                    <button onclick="window.location.reload()" class="btn btn-white border shadow-sm"><i class="bi bi-arrow-clockwise"></i></button>
                </div>
            </div>

            <!-- Thống kê -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card stats-card p-3 border-start border-primary border-4">
                        <div class="stats-label">Tổng khách hàng</div>
                        <div class="stats-value"><?php echo $total_count; ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stats-card p-3 border-start border-success border-4">
                        <div class="stats-label text-success">Đang hoạt động</div>
                        <div class="stats-value text-success"><?php echo $active_count; ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stats-card p-3 border-start border-danger border-4">
                        <div class="stats-label text-danger">Đã khóa</div>
                        <div class="stats-value text-danger"><?php echo $locked_count; ?></div>
                    </div>
                </div>
            </div>

            <?php if(isset($_GET['msg'])): ?>
                <div class="alert alert-info border-0 shadow-sm mb-4">
                    <i class="bi bi-info-circle me-2"></i>
                    <?php if($_GET['msg'] == 'deleted') echo "Đã xóa khách hàng khỏi hệ thống!"; ?>
                </div>
            <?php endif; ?>

            <!-- Danh sách -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th class="ps-3">KHÁCH HÀNG</th>
                                    <th>THÔNG TIN LIÊN HỆ</th>
                                    <th>TÊN ĐĂNG NHẬP</th>
                                    <th class="text-center">TRẠNG THÁI</th>
                                    <th class="text-end pe-3">THAO TÁC</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($result->num_rows > 0): ?>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar me-3">
                                                    <?php echo strtoupper(substr($row['HOTEN'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($row['HOTEN']); ?></div>
                                                    <div class="text-muted small">ID: #<?php echo $row['MAUSER']; ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small"><i class="bi bi-envelope me-2 text-muted"></i><?php echo htmlspecialchars($row['EMAIL'] ?? 'N/A'); ?></div>
                                            <div class="small text-muted"><i class="bi bi-phone me-2"></i><?php echo htmlspecialchars($row['SDT'] ?? 'N/A'); ?></div>
                                        </td>
                                        <td><code class="text-primary"><?php echo htmlspecialchars($row['TENUSER']); ?></code></td>
                                        <td class="text-center">
                                            <?php if($row['TRANGTHAI'] == 1): ?>
                                                <span class="status-pill bg-success-subtle text-success">Hoạt động</span>
                                            <?php else: ?>
                                                <span class="status-pill bg-danger-subtle text-danger">Đang khóa</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="d-flex justify-content-end gap-2">
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="toggle_status_id" value="<?php echo $row['MAUSER']; ?>">
                                                    <input type="hidden" name="current_status" value="<?php echo $row['TRANGTHAI']; ?>">
                                                    <button type="submit" class="btn btn-sm <?php echo $row['TRANGTHAI'] == 1 ? 'btn-outline-warning' : 'btn-outline-success'; ?>" title="Khóa/Mở khóa">
                                                        <i class="bi <?php echo $row['TRANGTHAI'] == 1 ? 'bi-lock-fill' : 'bi-unlock-fill'; ?>"></i>
                                                    </button>
                                                </form>
                                                
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Xóa khách hàng này?')">
                                                    <input type="hidden" name="delete_id" value="<?php echo $row['MAUSER']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center py-5 text-muted">Không tìm thấy khách hàng nào.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div> <!-- Kết thúc col-md-10 -->
    </div> <!-- Kết thúc row -->
</div> <!-- Kết thúc container-fluid -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>