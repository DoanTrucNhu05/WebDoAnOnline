<?php
    session_start(); // Phải gọi session_start() thì mới xóa được session

    // 1. Xóa tất cả các biến trong Session
    session_unset();

    // 2. Hủy bỏ hoàn toàn phiên làm việc
    session_destroy();

    // 3. Chuyển hướng về trang chủ hoặc trang đăng nhập
    header("Location: index.php");
    exit();
?>