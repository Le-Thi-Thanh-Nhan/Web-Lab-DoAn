<?php
if (!isset($_SESSION)) {
    session_start();
}

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['admin'])) {
    header('Location: ../auth/auth.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="assets/css/admin-sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="index.php">
                <img src="../images/Logo.png" alt="Logo" class="sidebar-logo">
                <h2>Admin Panel</h2>
            </a>
        </div>        
        <nav class="sidebar-nav">
            <ul>
                <li>
                    <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-line"></i>
                        <span>Trang Chủ</span>
                    </a>
                </li>
                <li>
                    <a href="revenue.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'revenue.php' ? 'active' : ''; ?>">
                        <i class="fas fa-money-bill"></i>
                        <span>Doanh Thu</span>
                    </a>
                </li>
                <li>
                    <a href="inventory.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'inventory.php' ? 'active' : ''; ?>">
                        <i class="fas fa-warehouse"></i>
                        <span>Quản Lý Tồn Kho</span>
                    </a>
                </li>
                <li>
                    <a href="categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
                        <i class="fas fa-tags"></i>
                        <span>Danh Mục</span>
                    </a>
                </li>
                <li>
                    <a href="products.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
                        <i class="fas fa-box"></i>
                        <span>Sản Phẩm</span>
                    </a>
                </li>
                <li>
                    <a href="ma-giam-gia.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'ma-giam-gia.php' ? 'active' : ''; ?>">
                        <i class="fas fa-percent"></i>
                        <span>Mã Giảm Giá</span>
                    </a>
                </li>
                <li>
                    <a href="notifications.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'active' : ''; ?>">
                        <i class="fas fa-bell"></i>
                        <span>Thông Báo</span>
                    </a>
                </li>
                <li>
                    <a href="support.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'support.php' ? 'active' : ''; ?>">
                        <i class="fas fa-headset"></i>
                        <span>Hỗ Trợ</span>
                    </a>
                </li>
                <li>
                    <a href="reviews.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'active' : ''; ?>">
                        <i class="fas fa-star"></i>
                        <span>Đánh Giá</span>
                    </a>
                </li>
                <li>
                    <a href="orders.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Đơn Hàng</span>
                    </a>
                </li>
                <li>
                    <a href="customers.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        <span>Khách Hàng</span>
                    </a>
                </li>
                <li>
                    <a href="administrators.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'administrators.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user-shield"></i>
                        <span>Quản Trị Viên</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php" class="logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Đăng Xuất</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>
</body>
</html> 