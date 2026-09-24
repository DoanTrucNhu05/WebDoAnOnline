<?php
// Tự động lấy tên file đang chạy (ví dụ: index.php)
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Khung Sidebar -->
<div class="col-md-2 bg-dark min-vh-100 p-3 text-white shadow">
    <div class="text-center mb-4">
        <h4 class="text-success fw-bold"><i class="bi bi-shield-lock me-2"></i>ADMIN CP</h4>
        <hr class="border-secondary">
    </div>

    <ul class="nav flex-column">
        <!-- Trang Sản phẩm -->
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="index.php">
                <i class="bi bi-box2-fill"></i> Sản phẩm
            </a>
        </li>

        <!-- Trang Danh mục -->
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'categories.php') ? 'active' : ''; ?>" href="categories.php">
                <i class="bi bi-tags-fill"></i> Danh mục
            </a>
        </li>

        <!-- Trang Đơn hàng (Sáng đèn cả khi ở trang danh sách và trang chi tiết) -->
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'order_list.php' || $current_page == 'order_detail.php') ? 'active' : ''; ?>" href="order_list.php">
                <i class="bi bi-cart-fill"></i> Đơn hàng
            </a>
        </li>
		<!-- Trang Khách Hàng -->
		<li class="nav-item">
			<a class="nav-link <?php echo ($current_page == 'customers.php') ? 'active' : ''; ?>" href="customers.php">
				<i class="bi bi-people-fill"></i> Khách hàng
			</a>
		</li>
        <!-- Trang Thống kê -->
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'statistics.php') ? 'active' : ''; ?>" href="statistics.php">
                <i class="bi bi-pie-chart-fill"></i> Thống kê
            </a>
        </li>

        <hr class="border-secondary mt-4">

        <!-- Quay lại Website -->
        <li class="nav-item">
            <a class="nav-link text-info" href="../index.php" target="_blank">
                <i class="bi bi-globe2"></i> Xem Website
            </a>
        </li>

        <!-- Nút Đăng xuất -->
        <li class="nav-item mt-auto">
            <a class="nav-link text-danger fw-bold" href="../logout.php">
                <i class="bi bi-box-arrow-left"></i> Đăng xuất
            </a>
        </li>
    </ul>
</div>

<!-- CSS dành riêng cho Sidebar -->
<style>
    /* Chỉnh màu mặc định cho các link */
    .nav-link {
        color: rgba(255, 255, 255, 0.7);
        padding: 12px 15px;
        margin-bottom: 5px;
        border-radius: 8px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
    }

    /* Icon cách chữ một khoảng */
    .nav-link i {
        margin-right: 12px;
        font-size: 1.1rem;
    }

    /* Hiệu ứng khi rê chuột qua (Hover) */
    .nav-link:hover {
        color: #198754 !important;
        background: rgba(255, 255, 255, 0.05);
        transform: translateX(5px);
    }

    /* Hiệu ứng khi trang đang được chọn (Active) */
    .nav-link.active {
        background-color: rgba(25, 135, 84, 0.15) !important;
        color: #28a745 !important;
        font-weight: 600;
        border-right: 4px solid #28a745; /* Thêm vạch xanh bên phải cho chuyên nghiệp */
    }
</style>