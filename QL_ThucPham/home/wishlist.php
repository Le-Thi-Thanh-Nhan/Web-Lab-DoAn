<?php
session_start();
require_once('../config/db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: ../auth/auth.php');
    exit;
}

$customer_id = $_SESSION['user']['customer_id'];
$success_message = '';
$error_message = '';

// Handle remove from wishlist
if (isset($_POST['remove_from_wishlist']) && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $stmt = $conn->prepare("DELETE FROM wishlists WHERE customer_id = ? AND product_id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $customer_id, $product_id);
        if ($stmt->execute()) {
            $success_message = "Đã xóa sản phẩm khỏi danh sách yêu thích!";
        } else {
            $error_message = "Không thể xóa sản phẩm. Vui lòng thử lại!";
        }
        $stmt->close();
    }
}

// Handle clear all wishlist
if (isset($_POST['clear_wishlist'])) {
    $stmt = $conn->prepare("DELETE FROM wishlists WHERE customer_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $customer_id);
        if ($stmt->execute()) {
            $success_message = "Đã xóa tất cả sản phẩm khỏi danh sách yêu thích!";
        } else {
            $error_message = "Không thể xóa danh sách. Vui lòng thử lại!";
        }
        $stmt->close();
    }
}

// Get wishlist items
$query = "SELECT p.*, w.added_at, c.name as category_name
          FROM wishlists w 
          JOIN products p ON w.product_id = p.product_id 
          JOIN subcategories s ON p.subcategory_id = s.subcategory_id
          JOIN categories c ON s.category_id = c.category_id
          WHERE w.customer_id = ?
          ORDER BY w.added_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$wishlist_items = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách yêu thích - Thực Phẩm Mộc</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/wishlist.css">
    <link rel="icon" type="image/png" href="../images/Logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'slide-menu.php'; ?>

    <!-- Alert Container -->
    <div class="alert-container">
        <?php if ($success_message): ?>
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle success-icon"></i>
                <div class="alert-message"><?php echo $success_message; ?></div>
                <button type="button" class="alert-close" onclick="closeAlert(this.parentElement)">
                    <i class="fas fa-times"></i>
                </button>
                <div class="alert-progress">
                    <div class="alert-progress-bar"></div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-error" role="alert">
                <i class="fas fa-exclamation-circle error-icon"></i>
                <div class="alert-message"><?php echo $error_message; ?></div>
                <button type="button" class="alert-close" onclick="closeAlert(this.parentElement)">
                    <i class="fas fa-times"></i>
                </button>
                <div class="alert-progress">
                    <div class="alert-progress-bar"></div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Main Content -->
    <main class="wishlist-container">
        <div class="wishlist-header">
            <h1>Danh sách yêu thích</h1>
            <div class="wishlist-stats">
                <div class="stat-item">
                    <i class="fas fa-heart"></i>
                    <span><?php echo $wishlist_items->num_rows; ?> sản phẩm</span>
                </div>
                <?php if ($wishlist_items->num_rows > 0): ?>
                    <div class="stat-item">
                        <i class="fas fa-clock"></i>
                        <span>Cập nhật lần cuối: <?php 
                            $first_item = $wishlist_items->fetch_assoc();
                            $wishlist_items->data_seek(0);
                            echo date('d/m/Y H:i', strtotime($first_item['added_at'])); 
                        ?></span>
                    </div>
                    <form method="POST" onsubmit="return confirmClearAll()">
                        <button type="submit" name="clear_wishlist" class="clear-all-btn">
                            <i class="fas fa-trash-alt"></i> Xóa tất cả
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($wishlist_items->num_rows > 0): ?>
            <div class="products-grid">
                <?php while ($item = $wishlist_items->fetch_assoc()): ?>
                    <div class="product-card">
                        <button type="button" 
                                class="delete-btn"
                                onclick="confirmRemove(<?php echo $item['product_id']; ?>, '<?php echo htmlspecialchars($item['name']); ?>')">
                            <i class="fas fa-trash"></i>
                        </button>
                        <div class="product-image">
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                                 onerror="this.src='../images/no-image.png';">
                        </div>
                        <div class="product-info">
                            <h3 class="product-name">
                                <?php echo htmlspecialchars($item['name']); ?>
                            </h3>
                        </div>
                        <div class="product-actions">
                            <a href="san-pham-chi-tiet.php?id=<?php echo $item['product_id']; ?>" 
                               class="action-btn view-btn">
                                <i class="fas fa-eye"></i> Xem chi tiết
                            </a>
                            <form id="remove-form-<?php echo $item['product_id']; ?>" 
                                  method="POST" style="display: none;">
                                <input type="hidden" name="product_id" 
                                       value="<?php echo $item['product_id']; ?>">
                                <input type="hidden" name="remove_from_wishlist" value="1">
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-wishlist">
                <i class="far fa-heart"></i>
                <h2>Danh sách yêu thích của bạn đang trống</h2>
                <p>Hãy thêm những sản phẩm bạn yêu thích vào đây</p>
                <a href="san-pham.php" class="browse-products-btn">
                    <i class="fas fa-shopping-basket"></i> Xem sản phẩm
                </a>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'footer.php'; ?>

    <script>
    function confirmRemove(productId, productName) {
        if (confirm(`Bạn có chắc chắn muốn xóa "${productName}" khỏi danh sách yêu thích không?`)) {
            document.getElementById(`remove-form-${productId}`).submit();
        }
    }

    function confirmClearAll() {
        return confirm('Bạn có chắc chắn muốn xóa tất cả sản phẩm khỏi danh sách yêu thích không?');
    }

    // Xử lý thông báo
    function closeAlert(alert) {
        alert.classList.add('fade-out');
        setTimeout(() => {
            alert.remove();
        }, 300);
    }

    // Tự động đóng thông báo sau 5 giây
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        const duration = 5000; // 5 giây

        alerts.forEach(alert => {
            const progressBar = alert.querySelector('.alert-progress-bar');
            
            // Thiết lập animation cho progress bar
            progressBar.style.width = '100%';
            progressBar.style.transitionDuration = `${duration}ms`;
            
            setTimeout(() => {
                progressBar.style.width = '0%';
            }, 100);

            // Tự động đóng thông báo
            setTimeout(() => {
                if (alert && alert.parentElement) {
                    closeAlert(alert);
                }
            }, duration);
        });
    });
    </script>
</body>
</html> 