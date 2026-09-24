<?php
	session_start();
	include 'config/db.php';

	// 1. Truy vấn lấy danh sách loại sản phẩm cho menu
	$sql_loai = "SELECT * FROM LOAISP ORDER BY TENLOAI ASC";
	$result_loai = $conn->query($sql_loai);

	// 2. Lấy dữ liệu tìm kiếm
	$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

	// 3. Truy vấn sản phẩm (Sắp xếp theo Loại để nhóm dễ hơn)
	$sql_sp = "SELECT sp.*, loai.TENLOAI 
               FROM SANPHAM sp 
               JOIN LOAISP loai ON sp.MALOAI = loai.MALOAI 
               WHERE sp.TENSP LIKE '%$search%' 
               ORDER BY loai.TENLOAI ASC, sp.MASP DESC";
	$result_sp = $conn->query($sql_sp);

    // Gom nhóm sản phẩm theo tên loại vào mảng
    $product_groups = [];
    if ($result_sp->num_rows > 0) {
        while($row = $result_sp->fetch_assoc()) {
            $product_groups[$row['TENLOAI']][] = $row;
        }
    }
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnlineFoodShop - Thực đơn món ngon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
	<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top py-2">
    <div class="container">
        <a class="navbar-brand fw-bold text-success fs-3" href="index.php">OnlineFood<span class="text-dark">Shop</span></a>
        
        <form class="search-box d-none d-md-block mx-auto" action="index.php" method="GET">
            <i class="bi bi-search"></i>
            <input type="text" name="search" class="form-control bg-light border-0 shadow-sm" placeholder="Tìm kiếm món ăn yêu thích..." value="<?php echo htmlspecialchars($search); ?>">
        </form>

        <div class="d-flex align-items-center gap-2">
            <a href="cart.php" class="btn btn-light position-relative rounded-circle shadow-sm p-2">
                <i class="bi bi-cart3 text-success fs-5"></i>
                <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                        <?php echo array_sum($_SESSION['cart']); ?>
                    </span>
                <?php endif; ?>
            </a>
            
            <?php if(isset($_SESSION['username'])): ?>
                <div class="dropdown">
                    <button class="btn btn-white border-0 dropdown-toggle fw-bold" type="button" data-bs-toggle="dropdown">
                        Hi, <?php echo $_SESSION['username']; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
						<?php if(isset($_SESSION['role']) && $_SESSION['role'] == 1): ?>
							<li><a class="dropdown-item py-2" href="admin/index.php"><i class="bi bi-speedometer2 me-2 text-primary"></i>Quản trị</a></li>
						<?php endif; ?>
						
						<li><a class="dropdown-item py-2" href="profile.php"><i class="bi bi-person me-2"></i>Tài khoản</a></li>

						<li><a class="dropdown-item py-2" href="order_history.php"><i class="bi bi-clock-history me-2 text-success"></i>Lịch sử mua hàng</a></li>
						
						<li><hr class="dropdown-divider"></li>
						<li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</a></li>
					</ul>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn btn-success px-4 rounded-pill fw-bold">Đăng nhập</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container">
    <!-- BANNER -->
    <div class="hero-banner shadow-sm mb-4">
        <div class="ps-5 text-white">
            <h1 class="fw-bold display-5 mb-2">Đói bụng rồi?</h1>
            <p class="fs-5 opacity-75">Đặt ngay món ngon giao nhanh trong 20 phút</p>
            <div class="mt-4">
                <span class="badge bg-warning text-dark p-2 px-3 fs-6 rounded-pill"><i class="bi bi-lightning-fill"></i> Freeship đơn từ 50k</span>
            </div>
        </div>
    </div>

    <!-- THANH MENU CHỌN LOẠI (QUICK NAV) -->
    <div class="category-nav-wrapper">
        <div class="container d-flex overflow-auto pb-1" style="scrollbar-width: none;">
            <a href="index.php" class="cat-pill <?php echo empty($search) ? 'active' : ''; ?>">Tất cả</a>
            <?php 
            $result_loai->data_seek(0);
            while($cat = $result_loai->fetch_assoc()): 
                // Tạo ID từ tên loại để cuộn trang
                $cat_id = "group-" . md5($cat['TENLOAI']);
            ?>
                <a href="#<?php echo $cat_id; ?>" class="cat-pill"><?php echo $cat['TENLOAI']; ?></a>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- DANH SÁCH MÓN ĂN THEO NHÓM -->
    <?php if (empty($product_groups)): ?>
        <div class="text-center py-5">
            <img src="https://cdn-icons-png.flaticon.com/512/6134/6134065.png" width="150" class="opacity-50">
            <h4 class="mt-4 text-muted">Rất tiếc, không tìm thấy món bạn yêu cầu!</h4>
            <a href="index.php" class="btn btn-success mt-2 rounded-pill">Quay lại thực đơn chính</a>
        </div>
    <?php else: ?>
        <?php foreach($product_groups as $categoryName => $products): ?>
            <!-- Tiêu đề nhóm -->
            <div id="group-<?php echo md5($categoryName); ?>" class="group-title">
                <h3><?php echo $categoryName; ?></h3>
                <div class="line"></div>
                <span class="badge bg-light text-dark border"><?php echo count($products); ?> món</span>
            </div>

            <div class="row g-4 mb-5">
                <?php foreach($products as $row): 
                    $is_out_of_stock = ($row['SOLUONG'] <= 0);
                ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card food-card shadow-sm <?php echo $is_out_of_stock ? 'opacity-75' : ''; ?>">
                        <?php if($is_out_of_stock): ?>
                            <div class="sold-out-badge">Hết món</div>
                        <?php endif; ?>

                        <img src="assets/images/<?php echo $row['IMAGE']; ?>" 
                             class="card-img-top <?php echo $is_out_of_stock ? 'grayscale' : ''; ?>" 
                             alt="<?php echo $row['TENSP']; ?>"
                             onerror="this.src='https://placehold.co/400x300?text=Food'">
                        
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title fw-bold text-dark mb-1 text-truncate"><?php echo $row['TENSP']; ?></h6>
                            <p class="text-muted small mb-3 text-truncate-2" style="font-size: 0.8rem; height: 2.4em; overflow: hidden;">
                                <?php echo $row['MOTA']; ?>
                            </p>
                            
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <span class="price-tag"><?php echo number_format($row['GIA'], 0, ',', '.'); ?>đ</span>
                                
                                <?php if($is_out_of_stock): ?>
                                    <button class="btn btn-light btn-add disabled text-muted"><i class="bi bi-slash-circle"></i></button>
                                <?php else: ?>
                                    <a href="<?php echo isset($_SESSION['username']) ? 'cart.php?action=add&id='.$row['MASP'] : 'login.php'; ?>" 
                                       class="btn btn-success btn-add shadow-sm">
                                        <i class="bi bi-plus-lg fw-bold"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<footer class="text-white pt-5 pb-4 mt-5">
    <div class="container text-center text-md-start">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h4 class="fw-bold text-success mb-3">OnlineFoodShop</h4>
                <p class="small text-secondary">Hệ thống đặt món ăn trực tuyến hàng đầu Long Xuyên. Giao hàng tận nơi, đảm bảo nóng hổi và chất lượng tuyệt đối.</p>
                <div class="d-flex gap-2 justify-content-center justify-content-md-start">
                    <a href="#" class="btn btn-outline-secondary btn-sm rounded-circle"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="btn btn-outline-secondary btn-sm rounded-circle"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="btn btn-outline-secondary btn-sm rounded-circle"><i class="bi bi-tiktok"></i></a>
                </div>
            </div>
            <div class="col-md-2 mb-4">
                <h6 class="fw-bold mb-3">Dịch vụ</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="#" class="footer-link">Giới thiệu</a></li>
                    <li class="mb-2"><a href="#" class="footer-link">Trung tâm hỗ trợ</a></li>
                    <li class="mb-2"><a href="#" class="footer-link">Chính sách bảo mật</a></li>
                </ul>
            </div>
            <div class="col-md-6 mb-4">
                <h6 class="fw-bold mb-3">Liên hệ với chúng tôi</h6>
                <div class="p-3 border border-secondary rounded-4 bg-dark">
                    <div class="small mb-1"><i class="bi bi-geo-alt-fill text-success me-2"></i> QL91, P.Mỹ Thạnh, TP. Long Xuyên, An Giang</div>
                    <div class="small"><i class="bi bi-people-fill text-success me-2"></i> Sinh viên thực hiện: Trúc Như - Yến Nhi</div>
                </div>
            </div>
        </div>
        <hr class="border-secondary mt-4">
        <div class="text-center small text-secondary">
            &copy; 2024 OnlineFoodShop Team. Design with <i class="bi bi-heart-fill text-danger"></i>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>