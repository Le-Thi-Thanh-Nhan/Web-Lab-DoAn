<?php
session_start();

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
    <title><?php echo $page_title ?? 'Admin Panel'; ?></title>
    <link rel="icon" type="image/png" href="../images/Logo.png">
    <!-- Common CSS -->
    <link rel="stylesheet" href="assets/css/admin-sidebar.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bootstrap CSS (if needed) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Page specific CSS -->
    <?php if (isset($page_specific_css)): ?>
        <?php echo $page_specific_css; ?>
    <?php endif; ?>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <h1 class="page-title">
            <?php if (isset($page_icon)): ?>
                <i class="<?php echo $page_icon; ?>"></i>
            <?php endif; ?>
            <?php echo $page_title ?? 'Trang quản trị'; ?>
        </h1>

        <!-- Page Content -->
        <?php if (isset($content)): ?>
            <?php echo $content; ?>
        <?php endif; ?>
    </main>

    <!-- Bootstrap JS (if needed) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Page specific JS -->
    <?php if (isset($page_specific_js)): ?>
        <?php echo $page_specific_js; ?>
    <?php endif; ?>
</body>
</html> 