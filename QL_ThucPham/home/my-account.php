<?php
session_start();
require_once('../config/db_connect.php');

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header('Location: ../auth/auth.php');
    exit;
}

$customer_id = $_SESSION['user']['customer_id'];
$success_message = '';
$error_message = '';

// Handle form submission for updating customer info
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_info'])) {
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        $email = trim($_POST['email']);
        $address = trim($_POST['address']);
        
        if (!empty($name)) {
            $stmt = $conn->prepare("UPDATE customers SET name = ?, phone_number = ?, email = ?, address = ? WHERE customer_id = ?");
            $stmt->bind_param("ssssi", $name, $phone, $email, $address, $customer_id);
            
            if ($stmt->execute()) {
                $_SESSION['user']['name'] = $name;
                $_SESSION['user']['email'] = $email;
                $success_message = "Thông tin đã được cập nhật thành công!";
            } else {
                $error_message = "Có lỗi xảy ra khi cập nhật thông tin.";
            }
            $stmt->close();
        }
    } elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM customers WHERE customer_id = ?");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && $current_password === $user['password']) {
            if ($new_password === $confirm_password) {
                // Update password
                $stmt = $conn->prepare("UPDATE customers SET password = ? WHERE customer_id = ?");
                $stmt->bind_param("si", $new_password, $customer_id);
                
                if ($stmt->execute()) {
                    $success_message = "Mật khẩu đã được thay đổi thành công!";
                } else {
                    $error_message = "Có lỗi xảy ra khi cập nhật mật khẩu.";
                }
                $stmt->close();
            } else {
                $error_message = "Mật khẩu mới không khớp.";
            }
        } else {
            $error_message = "Mật khẩu hiện tại không đúng.";
        }
    }
}

// Fetch customer details
$stmt = $conn->prepare("SELECT * FROM customers WHERE customer_id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch customer's orders
$stmt = $conn->prepare("
    SELECT o.*, 
           COUNT(od.order_detail_id) as total_items,
           GROUP_CONCAT(CONCAT(p.name, ' (x', od.quantity, ')') SEPARATOR ', ') as products
    FROM orders o
    LEFT JOIN orderdetails od ON o.order_id = od.order_id
    LEFT JOIN products p ON od.product_id = p.product_id
    WHERE o.customer_id = ?
    GROUP BY o.order_id
    ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch customer's reviews and products that can be reviewed
$reviews_query = "
    SELECT 
        pr.review_id,
        pr.product_id,
        pr.rating,
        pr.comment,
        pr.created_at,
        p.name as product_name,
        p.image_url
    FROM product_reviews pr
    JOIN products p ON pr.product_id = p.product_id
    WHERE pr.customer_id = ?
    ORDER BY pr.created_at DESC
";
$stmt = $conn->prepare($reviews_query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$my_reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch products that can be reviewed (completed orders but not yet reviewed)
$to_review_query = "
    SELECT DISTINCT 
        p.product_id,
        p.name as product_name,
        p.image_url,
        o.order_id,
        o.created_at as purchase_date
    FROM orders o
    JOIN orderdetails od ON o.order_id = od.order_id
    JOIN products p ON od.product_id = p.product_id
    LEFT JOIN product_reviews pr ON p.product_id = pr.product_id AND pr.customer_id = o.customer_id
    WHERE o.customer_id = ? 
    AND o.status = 'Completed'
    AND pr.review_id IS NULL
    ORDER BY o.created_at DESC
";
$stmt = $conn->prepare($to_review_query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$products_to_review = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch customer's available discount codes
$stmt = $conn->prepare("
    SELECT 
        d.*,
        cdc.expiry_status,
        CASE 
            WHEN cdu.usage_id IS NOT NULL THEN 'used'
            WHEN d.end_date < CURRENT_DATE OR cdc.expiry_status = 'expired' THEN 'expired'
            WHEN cdc.expiry_status = 'used' THEN 'used'
            ELSE 'available'
        END as status,
        cdu.used_at
    FROM discount_codes d
    INNER JOIN customer_discount_codes cdc 
        ON d.code_id = cdc.code_id 
        AND cdc.customer_id = ?
    LEFT JOIN customer_discount_usage cdu 
        ON d.code_id = cdu.code_id 
        AND cdu.customer_id = ?
    WHERE d.is_active = 1 
    ORDER BY 
        CASE 
            WHEN d.end_date >= CURRENT_DATE AND cdu.usage_id IS NULL AND cdc.expiry_status = 'active' THEN 1
            WHEN cdu.usage_id IS NOT NULL OR cdc.expiry_status = 'used' THEN 2
            ELSE 3
        END,
        d.end_date DESC
");
if (!$stmt) {
    die("Lỗi chuẩn bị câu lệnh mã giảm giá: " . $conn->error);
}
$stmt->bind_param("ii", $customer_id, $customer_id);
$stmt->execute();
$discounts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài khoản của tôi - Thực Phẩm Mộc</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/my-account.css">
    <link rel="stylesheet" href="css/order-details.css">
    <link rel="icon" type="image/png" href="../images/Logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Reviews tab styles */
        .review-section {
            margin-bottom: 2rem;
            background: #fff;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .review-section h3 {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 1.5rem;
            padding-bottom: 0.8rem;
            border-bottom: 2px solid #eee;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .review-section h3 i {
            color: #ffd700;
        }
        .products-to-review,
        .my-reviews {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        .product-review-card,
        .review-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .product-review-card:hover,
        .review-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .product-review-card img,
        .review-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid #eee;
        }
        .product-info,
        .review-content {
            padding: 1.2rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .product-info h4,
        .review-content h4 {
            margin: 0 0 0.8rem;
            color: #333;
            font-size: 1.1rem;
            line-height: 1.4;
            height: 2.8em;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .purchase-date,
        .review-date {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .purchase-date i,
        .review-date i {
            color: #999;
        }
        .rating {
            margin-bottom: 1rem;
            display: flex;
            gap: 0.3rem;
        }
        .rating .fa-star {
            font-size: 1.1rem;
        }
        .rating .fa-star.active {
            color: #ffd700;
        }
        .rating .fa-star:not(.active) {
            color: #ddd;
        }
        .review-text {
            color: #666;
            margin-bottom: 1rem;
            line-height: 1.5;
            flex: 1;
            font-size: 0.95rem;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }
        .btn-rate,
        .btn-edit-review {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.8rem 1.2rem;
            border-radius: 6px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            margin-top: auto;
            width: 100%;
        }
        .btn-rate {
            background: #ffd700;
            color: #333;
        }
        .btn-rate:hover {
            background: #ffc800;
            transform: translateY(-2px);
        }
        .btn-edit-review {
            background: #f0f0f0;
            color: #333;
        }
        .btn-edit-review:hover {
            background: #e5e5e5;
            transform: translateY(-2px);
        }
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #666;
        }
        .empty-state i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 1rem;
        }
        .empty-state p {
            font-size: 1.1rem;
            margin-bottom: 0;
        }
        @media (max-width: 768px) {
            .products-to-review,
            .my-reviews {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 1rem;
            }
            .product-review-card img,
            .review-card img {
                height: 180px;
            }
            .product-info,
            .review-content {
                padding: 1rem;
            }
            .btn-rate,
            .btn-edit-review {
                padding: 0.7rem 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'slide-menu.php'; ?>

    <div class="profile-container">
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="profile-nav">
            <a href="#" class="profile-nav-item active" onclick="openTab(event, 'info')">
                <i class="fas fa-user-circle"></i> Thông tin tài khoản
            </a>
            <a href="#" class="profile-nav-item" onclick="openTab(event, 'edit')">
                <i class="fas fa-user-edit"></i> Chỉnh sửa thông tin
            </a>
            <a href="#" class="profile-nav-item" onclick="openTab(event, 'orders')">
                <i class="fas fa-box-open"></i> Đơn hàng của tôi
            </a>
            <a href="#" class="profile-nav-item" onclick="openTab(event, 'reviews')">
                <i class="fas fa-star"></i> Đánh giá của tôi
            </a>
            <a href="#" class="profile-nav-item" onclick="openTab(event, 'discounts')">
                <i class="fas fa-ticket-alt"></i> Mã giảm giá của tôi
            </a>
            <a href="#" class="profile-nav-item" onclick="openTab(event, 'security')">
                <i class="fas fa-shield-alt"></i> Bảo mật
            </a>
        </div>

        <div id="info" class="tab-content active">
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="profile-info">
                    <h2 class="profile-name"><?php echo htmlspecialchars($customer['name']); ?></h2>
                    <p class="profile-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo htmlspecialchars($customer['address'] ?? 'Chưa cập nhật địa chỉ'); ?>
                    </p>
                    <div class="profile-details">
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-user-tag"></i>
                                Tên đăng nhập
                            </div>
                            <div class="detail-value"><?php echo htmlspecialchars($customer['username']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-user"></i>
                                Họ và tên
                            </div>
                            <div class="detail-value"><?php echo htmlspecialchars($customer['name']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-phone"></i>
                                Số điện thoại
                            </div>
                            <div class="detail-value"><?php echo htmlspecialchars($customer['phone_number'] ?? 'Chưa cập nhật'); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-envelope"></i>
                                Email
                            </div>
                            <div class="detail-value"><?php echo htmlspecialchars($customer['email']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-map-marked-alt"></i>
                                Địa chỉ
                            </div>
                            <div class="detail-value"><?php echo htmlspecialchars($customer['address'] ?? 'Chưa cập nhật'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="edit" class="tab-content">
            <div class="edit-form">
                <h3><i class="fas fa-user-edit"></i> Chỉnh sửa thông tin</h3>
                <form method="POST">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Họ và tên</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($customer['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Số điện thoại</label>
                        <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($customer['phone_number'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($customer['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Địa chỉ</label>
                        <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($customer['address'] ?? ''); ?>">
                    </div>
                    <button type="submit" name="update_info" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu thay đổi
                    </button>
                </form>
            </div>
        </div>

        <div id="orders" class="tab-content">
            <div class="account-section">
                <h2><i class="fas fa-box-open"></i> Đơn hàng của tôi</h2>
                <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <p>Bạn chưa có đơn hàng nào.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Mã đơn hàng</th>
                                    <th>Ngày đặt</th>
                                    <th>Sản phẩm</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['order_id']; ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <?php 
                                            $products = explode(', ', $order['products']);
                                            if (count($products) > 2) {
                                                echo htmlspecialchars($products[0]) . ', ' . htmlspecialchars($products[1]) . ' và ' . (count($products) - 2) . ' sản phẩm khác';
                                            } else {
                                                echo htmlspecialchars($order['products']); 
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                                <?php 
                                                $status_map = [
                                                    'Pending' => 'Chờ xử lý',
                                                    'Processing' => 'Đang xử lý',
                                                    'Shipping' => 'Đang giao hàng',
                                                    'Completed' => 'Đã hoàn thành',
                                                    'Cancelled' => 'Đã hủy'
                                                ];
                                                echo $status_map[$order['status']] ?? $order['status'];
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button onclick="viewOrderDetails(<?php echo $order['order_id']; ?>)" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> Chi tiết
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div id="reviews" class="tab-content">
            <div class="account-section">
                <h2><i class="fas fa-star"></i> Đánh giá của tôi</h2>
                
                <!-- Products waiting for review -->
                <?php if (!empty($products_to_review)): ?>
                    <div class="review-section">
                        <h3><i class="fas fa-clock"></i> Sản phẩm chờ đánh giá</h3>
                        <div class="products-to-review">
                            <?php foreach ($products_to_review as $product): ?>
                                <div class="product-review-card waiting">
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                                         onerror="this.src='../images/no-image.jpg'">
                                    <div class="product-info">
                                        <h4><?php echo htmlspecialchars($product['product_name']); ?></h4>
                                        <p class="purchase-date">
                                            <i class="fas fa-shopping-cart"></i> 
                                            Đã mua ngày: <?php echo date('d/m/Y', strtotime($product['purchase_date'])); ?>
                                        </p>
                                        <a href="san-pham-chi-tiet.php?id=<?php echo $product['product_id']; ?>#review-section" 
                                           class="btn-rate">
                                            <i class="fas fa-star"></i> Đánh giá ngay
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Existing reviews -->
                <div class="review-section">
                    <h3><i class="fas fa-history"></i> Đánh giá đã viết</h3>
                    <?php if (empty($my_reviews)): ?>
                        <div class="empty-state">
                            <i class="fas fa-star"></i>
                            <p>Bạn chưa có đánh giá nào</p>
                        </div>
                    <?php else: ?>
                        <div class="my-reviews">
                            <?php foreach ($my_reviews as $review): ?>
                                <div class="review-card">
                                    <img src="<?php echo htmlspecialchars($review['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($review['product_name']); ?>"
                                         onerror="this.src='../images/no-image.jpg'">
                                    <div class="review-content">
                                        <h4><?php echo htmlspecialchars($review['product_name']); ?></h4>
                                        <div class="rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'active' : ''; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <p class="review-text"><?php echo htmlspecialchars($review['comment']); ?></p>
                                        <p class="review-date">
                                            <i class="fas fa-calendar-alt"></i>
                                            Đã đánh giá: <?php echo date('d/m/Y', strtotime($review['created_at'])); ?>
                                        </p>
                                        <a href="san-pham-chi-tiet.php?id=<?php echo $review['product_id']; ?>#review-section" 
                                           class="btn-edit-review">
                                            <i class="fas fa-edit"></i> Chỉnh sửa
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div id="discounts" class="tab-content">
            <div class="account-section">
                <h2><i class="fas fa-ticket-alt"></i> Mã giảm giá của tôi</h2>
                <?php
                // Fetch customer's discount codes
                $discount_codes_sql = "
                    SELECT d.*, cdc.collected_at, cdc.expiry_status,
                           CASE WHEN cdu.usage_id IS NOT NULL THEN 'Đã sử dụng' ELSE 'Chưa sử dụng' END as usage_status
                    FROM discount_codes d
                    INNER JOIN customer_discount_codes cdc ON d.code_id = cdc.code_id
                    LEFT JOIN customer_discount_usage cdu 
                        ON d.code_id = cdu.code_id 
                        AND cdu.customer_id = ?
                    WHERE cdc.customer_id = ?
                    ORDER BY cdc.expiry_status ASC, d.end_date ASC";
                
                $stmt = $conn->prepare($discount_codes_sql);
                $stmt->bind_param("ii", $customer_id, $customer_id);
                $stmt->execute();
                $discount_codes = $stmt->get_result();
                ?>

                <?php if ($discount_codes->num_rows > 0): ?>
                    <div class="discount-list">
                        <?php while ($code = $discount_codes->fetch_assoc()): ?>
                            <div class="discount-card <?php echo strtolower(str_replace(' ', '-', $code['usage_status'])); ?>">
                                <div class="discount-header">
                                    <div class="discount-code"><?php echo htmlspecialchars($code['code']); ?></div>
                                    <div class="discount-status">
                                        <i class="fas <?php echo $code['usage_status'] === 'Đã sử dụng' ? 'fa-check-circle' : 'fa-circle'; ?>"></i>
                                        <?php echo $code['usage_status']; ?>
                                    </div>
                                </div>
                                <div class="discount-details">
                                    <p><i class="fas fa-tag"></i> Giảm <?php echo $code['discount_type'] == 'percentage' ? 
                                        number_format($code['discount_value'], 0) . '%' : 
                                        number_format($code['discount_value'], 0, ',', '.') . 'đ'; ?></p>
                                    <p><i class="fas fa-shopping-cart"></i> Đơn tối thiểu: <?php echo number_format($code['min_order_value'], 0, ',', '.'); ?>đ</p>
                                    <p><i class="fas fa-calendar-plus"></i> Thu thập: <?php echo date('d/m/Y', strtotime($code['collected_at'])); ?></p>
                                    <p><i class="fas fa-calendar-times"></i> Hết hạn: <?php echo date('d/m/Y', strtotime($code['end_date'])); ?></p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-ticket-alt"></i>
                        <p>Bạn chưa có mã giảm giá nào</p>
                        <a href="ma-giam-gia.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Thu thập mã giảm giá
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div id="security" class="tab-content">
            <div class="security-form">
                <h3><i class="fas fa-shield-alt"></i> Đổi mật khẩu</h3>
                <form method="POST">
                    <div class="form-group">
                        <label><i class="fas fa-key"></i> Mật khẩu hiện tại</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Mật khẩu mới</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Xác nhận mật khẩu mới</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="fas fa-save"></i> Đổi mật khẩu
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <!-- Modal for Order Details -->
    <div id="orderDetailsModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="orderDetailsContent"></div>
        </div>
    </div>

    <script>
        function openTab(evt, tabName) {
            evt.preventDefault();
            var i, tabcontent, tablinks;
            
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            
            tablinks = document.getElementsByClassName("profile-nav-item");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("active");
            }
            
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.classList.add("active");
        }

        // Password visibility toggle
        document.querySelectorAll('.password-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // View order details function
        function viewOrderDetails(orderId) {
            const modal = document.getElementById('orderDetailsModal');
            const content = document.getElementById('orderDetailsContent');
            
            // Show loading state
            content.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>';
            modal.style.display = "block";
            
            // Fetch order details
            fetch(`get_order_details.php?order_id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    const order = data.order;
                    const items = data.items;

                    let html = `
                        <div class="order-details">
                            <div class="order-header">
                                <h2>Chi tiết đơn hàng #${order.order_id}</h2>
                                <div class="order-status" style="color: ${order.status.color}">
                                    <i class="fas fa-${order.status.icon}"></i> ${order.status.text}
                                </div>
                            </div>

                            <div class="order-info">
                                <div class="info-section">
                                    <h3><i class="fas fa-info-circle"></i> Thông tin đơn hàng</h3>
                                    <p><strong>Ngày đặt:</strong> ${order.order_date}</p>
                                    <p><strong>Phương thức thanh toán:</strong> ${order.payment.method}</p>
                                    ${order.notes ? `<p><strong>Ghi chú:</strong> ${order.notes}</p>` : ''}
                                </div>

                                <div class="info-section">
                                    <h3><i class="fas fa-shipping-fast"></i> Thông tin giao hàng</h3>
                                    <p><strong>Người nhận:</strong> ${order.customer.name}</p>
                                    <p><strong>Số điện thoại:</strong> ${order.customer.phone}</p>
                                    <p><strong>Địa chỉ:</strong> ${order.customer.shipping_address}</p>
                                </div>
                            </div>

                            <div class="order-items">
                                <h3><i class="fas fa-shopping-cart"></i> Sản phẩm</h3>
                                <div class="items-list">`;

                    items.forEach(item => {
                        html += `
                            <div class="order-item">
                                <img src="${item.image_url}" alt="${item.name}" onerror="this.src='../images/no-image.jpg'">
                                <div class="item-details">
                                    <h4>${item.name}</h4>
                                    <p>Số lượng: ${item.quantity}</p>
                                    <p>Đơn giá: ${formatCurrency(item.unit_price)}</p>
                                    <p>Thành tiền: ${formatCurrency(item.subtotal)}</p>
                                    ${item.can_rate ? `
                                    <a href="san-pham-chi-tiet.php?id=${item.product_id}#review-section" class="btn-rate">
                                        <i class="fas fa-star"></i> Đánh giá sản phẩm
                                    </a>
                                    ` : ''}
                                </div>
                            </div>`;
                    });

                    html += `
                                </div>
                            </div>

                            <div class="order-summary">
                                <div class="summary-row">
                                    <span>Tạm tính:</span>
                                    <span>${formatCurrency(order.payment.subtotal)}</span>
                                </div>`;

                    if (order.payment.discount > 0) {
                        html += `
                                <div class="summary-row discount">
                                    <span>Giảm giá:</span>
                                    <span>-${formatCurrency(order.payment.discount)}</span>
                                </div>`;
                    }

                    if (order.payment.shipping_fee > 0) {
                        html += `
                                <div class="summary-row">
                                    <span>Phí vận chuyển:</span>
                                    <span>${formatCurrency(order.payment.shipping_fee)}</span>
                                </div>`;
                    }

                    html += `
                                <div class="summary-row total">
                                    <span>Tổng cộng:</span>
                                    <span>${formatCurrency(order.payment.total)}</span>
                                </div>
                            </div>
                        </div>`;

                    content.innerHTML = html;
                })
                .catch(error => {
                    content.innerHTML = `
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <p>Có lỗi xảy ra khi tải thông tin đơn hàng: ${error.message}</p>
                        </div>`;
                });
        }

        // Format currency function
        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount);
        }

        // Close modal when clicking on the close button or outside the modal
        const modal = document.getElementById('orderDetailsModal');
        const closeBtn = document.querySelector('.close');

        closeBtn.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Auto-hide alerts after 3 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.classList.add('hide');
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                }, 3000);
            });
        });

        // Initialize the first tab
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.profile-nav-item').click();
        });

        function copyDiscountCode(code) {
            navigator.clipboard.writeText(code).then(() => {
                alert('Đã sao chép mã: ' + code);
            }).catch(err => {
                console.error('Không thể sao chép mã: ', err);
                alert('Không thể sao chép mã. Vui lòng thử lại.');
            });
        }

        // Show active tab based on URL hash
        window.onload = function() {
            const hash = window.location.hash.substring(1) || 'info';
            const tab = document.querySelector(`.profile-nav-item[onclick*="${hash}"]`);
            if (tab) {
                tab.click();
            }
        }
    </script>
</body>
</html> 