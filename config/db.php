<?php
    $host = 'localhost';
    $user = 'root'; 
    $pass = 'vertrigo'; 
    $db   = 'OnlineFoodShop';

    // 1. Khởi tạo kết nối
    $conn = new mysqli($host, $user, $pass, $db);

    // 2. Kiểm tra kết nối
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }

    // 3. Thiết lập font chữ tiếng Việt để không bị lỗi hiển thị
    $conn->set_charset("utf8mb4");
?>