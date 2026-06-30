<?php
session_start();
require_once('../config/db_connect.php');

if (!isset($_SESSION['user']) || !isset($_GET['order_id'])) {
    header('Location: index.php');
    exit;
}

$customer_id = $_SESSION['user']['customer_id'];
$order_id = intval($_GET['order_id']);

// Fetch order details with customer information
$sql = "SELECT o.*, c.name as customer_name, c.email, c.phone_number
        FROM orders o
        JOIN customers c ON o.customer_id = c.customer_id
        WHERE o.order_id = ? AND o.customer_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $customer_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: index.php');
    exit;
}

// Fetch order items
$sql = "SELECT od.*, p.name as product_name, p.image_url
        FROM orderdetails od
        JOIN products p ON od.product_id = p.product_id
        WHERE od.order_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thực phẩm Mộc - Đặt hàng thành công</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="../images/Logo.png">
    <style>
        .success-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success-header {
            text-align: center;
            color: #2ecc71;
            margin-bottom: 2rem;
        }
        .success-header .checkmark-circle {
            width: 80px;
            height: 80px;
            position: relative;
            display: inline-block;
            vertical-align: top;
            margin-bottom: 1rem;
        }
        .success-header .checkmark-circle .background {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #2ecc71;
            position: absolute;
            animation: background-fade-in 0.6s ease-in-out;
        }
        .success-header .checkmark-circle .checkmark {
            border-right: 6px solid #fff;
            border-bottom: 6px solid #fff;
            width: 32px;
            height: 60px;
            position: absolute;
            left: 24px;
            top: 8px;
            transform-origin: 50% 50%;
            transform: rotate(45deg) scale(0);
            animation: checkmark-appear 0.4s ease-in-out 0.6s forwards,
                       checkmark-bounce 0.3s ease-in-out 1s;
        }
        @keyframes background-fade-in {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        @keyframes checkmark-appear {
            0% {
                transform: rotate(45deg) scale(0);
            }
            100% {
                transform: rotate(45deg) scale(1);
            }
        }
        @keyframes checkmark-bounce {
            0%, 100% {
                transform: rotate(45deg) scale(1);
            }
            50% {
                transform: rotate(45deg) scale(1.2);
            }
        }
        .success-header h1 {
            margin-top: 1rem;
            animation: fade-in-up 0.6s ease-out 0.3s both;
        }
        .success-header p {
            animation: fade-in-up 0.6s ease-out 0.6s both;
        }
        @keyframes fade-in-up {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .order-section {
            margin-bottom: 2rem;
            padding: 1rem;
            border: 1px solid #eee;
            border-radius: 4px;
        }
        .section-title {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .customer-info, .order-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        .info-item {
            margin-bottom: 1rem;
        }
        .info-label {
            color: #7f8c8d;
            margin-bottom: 0.25rem;
        }
        .info-value {
            color: #2c3e50;
            font-weight: 500;
        }
        .order-items {
            margin-top: 1rem;
        }
        .order-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }
        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            margin-right: 1rem;
            border-radius: 4px;
        }
        .item-details {
            flex: 1;
        }
        .order-summary {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 2px solid #eee;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        .summary-row.total {
            font-size: 1.2rem;
            font-weight: bold;
            color: #2c3e50;
            border-top: 2px solid #3498db;
            padding-top: 1rem;
            margin-top: 1rem;
        }
        .btn-continue {
            display: block;
            width: 100%;
            padding: 1rem;
            background: #3498db;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 2rem;
            transition: background 0.3s ease;
        }
        .btn-continue:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <?php include 'slide-menu.php'; ?>

    <div class="success-container">
        <div class="success-header">
            <div class="checkmark-circle">
                <div class="background"></div>
                <div class="checkmark"></div>
            </div>
            <h1>Đặt hàng thành công!</h1>
            <p>Cảm ơn bạn đã đặt hàng. Mã đơn hàng của bạn là: #<?php echo $order_id; ?></p>
        </div>

        <!-- Customer Information -->
        <div class="order-section">
            <h2 class="section-title"><i class="fas fa-user"></i> Thông tin đặt hàng</h2>
            <div class="customer-info">
                <div class="info-item">
                    <div class="info-label">Họ tên:</div>
                    <div class="info-value"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email:</div>
                    <div class="info-value"><?php echo htmlspecialchars($order['email']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Số điện thoại:</div>
                    <div class="info-value"><?php echo htmlspecialchars($order['phone_number']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Địa chỉ giao hàng:</div>
                    <div class="info-value"><?php echo htmlspecialchars($order['shipping_address']); ?></div>
                </div>
            </div>
        </div>

        <!-- Order Details -->
        <div class="order-section">
            <h2 class="section-title"><i class="fas fa-shopping-cart"></i> Chi tiết đơn hàng</h2>
            <div class="order-items">
                <?php foreach ($order_items as $item): ?>
                <div class="order-item">
                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="item-image">
                    <div class="item-details">
                        <h3><?php echo htmlspecialchars($item['product_name']); ?></h3>
                        <p>Số lượng: <?php echo $item['quantity']; ?></p>
                        <p>Đơn giá: <?php echo number_format($item['unit_price'], 0, ',', '.'); ?>đ</p>
                        <p>Thành tiền: <?php echo number_format($item['subtotal'], 0, ',', '.'); ?>đ</p>
                        <?php if ($order['status'] === 'Completed'): ?>
                            <a href="san-pham-chi-tiet.php?id=<?php echo $item['product_id']; ?>#review-section" class="btn-rate">
                                <i class="fas fa-star"></i> Đánh giá sản phẩm
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="order-summary">
                <div class="summary-row">
                    <span>Tạm tính:</span>
                    <span><?php echo number_format($order['total_amount'] + $order['discount_amount'], 0, ',', '.'); ?>đ</span>
                </div>
                <div class="summary-row">
                    <span>Giảm giá:</span>
                    <span><?php echo number_format($order['discount_amount'], 0, ',', '.'); ?>đ</span>
                </div>
                <div class="summary-row">
                    <span>Phí vận chuyển:</span>
                    <span><?php echo number_format($order['shipping_fee'], 0, ',', '.'); ?>đ</span>
                </div>
                <div class="summary-row total">
                    <span>Tổng cộng:</span>
                    <span><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</span>
                </div>
            </div>
        </div>

        <a href="index.php" class="btn-continue">
            <i class="fas fa-shopping-cart"></i> Tiếp tục mua sắm
        </a>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html> 