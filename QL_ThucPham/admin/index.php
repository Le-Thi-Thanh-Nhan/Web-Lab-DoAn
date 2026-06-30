<?php
session_start();
require_once '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin']['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get admin info
$admin_id = $_SESSION['admin']['admin_id'];
$admin_name = $_SESSION['admin']['name'];

// Get current week's start and end dates
$week_start = date('Y-m-d 00:00:00', strtotime('monday this week'));
$week_end = date('Y-m-d 23:59:59', strtotime('sunday this week'));

// Initialize stats arrays
$weekly_stats = array(
    'revenue' => 0,
    'orders' => 0,
    'notifications' => 0,
    'discounts' => 0,
    'support' => 0,
    'reviews' => 0,
    'new_customers' => 0
);

$products_stats = array(
    'total_products' => 0,
    'low_stock' => 0,
    'out_of_stock' => 0
);

// Weekly revenue
$revenue_query = "SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE created_at BETWEEN ? AND ?";
if ($stmt = $conn->prepare($revenue_query)) {
    $stmt->bind_param("ss", $week_start, $week_end);
    $stmt->execute();
    $result = $stmt->get_result();
    $weekly_stats['revenue'] = $result->fetch_assoc()['total'];
    $stmt->close();
}

// Weekly orders
$orders_query = "SELECT COUNT(*) as total FROM orders WHERE created_at BETWEEN ? AND ?";
if ($stmt = $conn->prepare($orders_query)) {
    $stmt->bind_param("ss", $week_start, $week_end);
    $stmt->execute();
    $result = $stmt->get_result();
    $weekly_stats['orders'] = $result->fetch_assoc()['total'];
    $stmt->close();
}

// New notifications
$notifications_query = "SELECT COUNT(*) as total FROM notifications WHERE created_at BETWEEN ? AND ?";
if ($stmt = $conn->prepare($notifications_query)) {
    $stmt->bind_param("ss", $week_start, $week_end);
    $stmt->execute();
    $result = $stmt->get_result();
    $weekly_stats['notifications'] = $result->fetch_assoc()['total'];
    $stmt->close();
}

// New discount codes
$discounts_query = "SELECT COUNT(*) as total FROM discount_codes WHERE created_at BETWEEN ? AND ?";
if ($stmt = $conn->prepare($discounts_query)) {
    $stmt->bind_param("ss", $week_start, $week_end);
    $stmt->execute();
    $result = $stmt->get_result();
    $weekly_stats['discounts'] = $result->fetch_assoc()['total'];
    $stmt->close();
}

// New support tickets - Check if table exists first
$table_check = $conn->query("SHOW TABLES LIKE 'support_tickets'");
if ($table_check->num_rows > 0) {
    $support_query = "SELECT COUNT(*) as total FROM support_tickets WHERE created_at BETWEEN ? AND ?";
    if ($stmt = $conn->prepare($support_query)) {
        $stmt->bind_param("ss", $week_start, $week_end);
        $stmt->execute();
        $result = $stmt->get_result();
        $weekly_stats['support'] = $result->fetch_assoc()['total'];
        $stmt->close();
    }
}

// New reviews
$reviews_query = "SELECT COUNT(*) as total FROM product_reviews WHERE created_at BETWEEN ? AND ?";
if ($stmt = $conn->prepare($reviews_query)) {
    $stmt->bind_param("ss", $week_start, $week_end);
    $stmt->execute();
    $result = $stmt->get_result();
    $weekly_stats['reviews'] = $result->fetch_assoc()['total'];
    $stmt->close();
}

// Total products
$products_query = "SELECT 
    COUNT(product_id) as total_products,
    COUNT(CASE WHEN quantity <= reorder_point AND quantity > 0 THEN 1 END) as low_stock,
    COUNT(CASE WHEN quantity = 0 THEN 1 END) as out_of_stock
FROM products 
WHERE deleted_at IS NULL";
$result = $conn->query($products_query);
if ($result) {
    $products_stats = $result->fetch_assoc();
}

// New customers this week
$customers_query = "SELECT COUNT(*) as total FROM customers WHERE created_at BETWEEN ? AND ?";
if ($stmt = $conn->prepare($customers_query)) {
    $stmt->bind_param("ss", $week_start, $week_end);
    $stmt->execute();
    $result = $stmt->get_result();
    $weekly_stats['new_customers'] = $result->fetch_assoc()['total'];
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ - Quản trị</title>
    <link rel="icon" type="image/png" href="../images/Logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --hover-color: #2980b9;
            --text-light: #ecf0f1;
            --text-dark: #2c3e50;
            --border-color: #eee;
            --bg-light: #f8f9fa;
            --bg-dark: #2c3e50;
            --danger-color: #e74c3c;
            --success-color: #2ecc71;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            height: 100%;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .stat-card .card-body {
            padding: 1.5rem;
            background: white;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .stat-card .value {
            font-size: 1.8rem;
            font-weight: 600;
            margin: 0.5rem 0;
            color: var(--text-dark);
        }

        .stat-card .title {
            color: var(--secondary-color);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .stat-card .icon {
            font-size: 2rem;
            opacity: 0.8;
            color: var(--accent-color);
        }

        .welcome-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: var(--text-light);
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        .welcome-section::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0.1) 75%, transparent 75%, transparent);
            background-size: 3rem 3rem;
            opacity: 0.1;
        }

        .welcome-section h1 {
            color: #fff;
            font-size: 2rem;
            margin-bottom: 1rem;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }

        .welcome-section p {
            opacity: 0.9;
            position: relative;
            z-index: 1;
            font-size: 1.1rem;
        }

        .content {
            margin-left: 260px;
            padding: 2rem;
            min-height: 100vh;
            background-color: var(--bg-light);
            transition: all 0.3s ease;
            position: relative;
            width: calc(100% - 260px);
        }

        .recent-orders {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 1.5rem;
            margin: 0;
            width: 100%;
        }

        .recent-orders h2 {
            color: var(--text-dark);
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
            font-weight: 600;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th,
        .orders-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .orders-table th {
            font-weight: 600;
            color: #fff;
            background: #2c3e50;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .orders-table tbody tr:hover {
            background: var(--bg-light);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-badge::before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .status-pending {
            background: #fff8e1;
            color: #f57c00;
        }
        .status-pending::before {
            background: #f57c00;
        }

        .status-completed {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .status-completed::before {
            background: #2e7d32;
        }

        .status-cancelled {
            background: #ffebee;
            color: var(--danger-color);
        }
        .status-cancelled::before {
            background: var(--danger-color);
        }

        @media (max-width: 1600px) {
            .stat-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 1200px) {
            .stat-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 992px) {
            .content {
                margin-left: 0;
                width: 100%;
            }
            .recent-orders {
                margin: 0;
            }
        }

        @media (max-width: 768px) {
            .stat-grid {
                grid-template-columns: 1fr;
            }
            .content {
                padding: 1rem;
            }
            .recent-orders {
                margin: 0;
            }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="content">
    <div class="container-fluid p-4">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1>Chào mừng, <?php echo htmlspecialchars($admin_name); ?>!</h1>
            <p>Đây là tổng quan hoạt động trong tuần của cửa hàng</p>
        </div>

        <!-- Stat Cards -->
        <div class="stat-grid">
            <!-- Revenue -->
            <a href="revenue.php" class="text-decoration-none">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="title">Doanh thu tuần này</div>
                                <div class="value"><?php echo number_format($weekly_stats['revenue'], 0, ',', '.'); ?>đ</div>
                            </div>
                            <div class="icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Orders -->
            <a href="orders.php" class="text-decoration-none">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="title">Đơn hàng tuần này</div>
                                <div class="value"><?php echo $weekly_stats['orders']; ?></div>
                            </div>
                            <div class="icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Notifications -->
            <a href="notifications.php" class="text-decoration-none">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="title">Thông báo mới</div>
                                <div class="value"><?php echo $weekly_stats['notifications']; ?></div>
                            </div>
                            <div class="icon">
                                <i class="fas fa-bell"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Discount Codes -->
            <a href="ma-giam-gia.php" class="text-decoration-none">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="title">Mã giảm giá mới</div>
                                <div class="value"><?php echo $weekly_stats['discounts']; ?></div>
                            </div>
                            <div class="icon">
                                <i class="fas fa-tags"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Support Tickets -->
            <a href="support.php" class="text-decoration-none">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="title">Hỗ trợ mới</div>
                                <div class="value"><?php echo $weekly_stats['support']; ?></div>
                            </div>
                            <div class="icon">
                                <i class="fas fa-headset"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Reviews -->
            <a href="reviews.php" class="text-decoration-none">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="title">Đánh giá mới</div>
                                <div class="value"><?php echo $weekly_stats['reviews']; ?></div>
                            </div>
                            <div class="icon">
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Total Products -->
            <a href="products.php" class="text-decoration-none">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="title">Tổng sản phẩm</div>
                                <div class="value"><?php echo $products_stats['total_products']; ?></div>
                            </div>
                            <div class="icon">
                                <i class="fas fa-box"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Low Stock Products -->
            <a href="inventory.php" class="text-decoration-none">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="title">Sản phẩm sắp hết</div>
                                <div class="value"><?php echo $products_stats['low_stock']; ?></div>
                            </div>
                            <div class="icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Out of Stock Products -->
            <a href="inventory.php" class="text-decoration-none">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="title">Sản phẩm hết hàng</div>
                                <div class="value"><?php echo $products_stats['out_of_stock']; ?></div>
                            </div>
                            <div class="icon">
                                <i class="fas fa-box-open"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>

            <!-- New Customers -->
            <a href="customers.php" class="text-decoration-none">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="title">Khách hàng mới</div>
                                <div class="value"><?php echo $weekly_stats['new_customers']; ?></div>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <!-- Recent Orders Section -->
        <div class="recent-orders">
            <h2>Các đơn hàng gần đây</h2>
    <div class="table-responsive">
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Mã đơn hàng</th>
                    <th>Khách hàng</th>
                    <th>Sản phẩm</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Ngày đặt</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Get recent orders
                $recent_orders_query = "SELECT o.order_id, c.name as customer_name, 
                    GROUP_CONCAT(p.name SEPARATOR ', ') as products,
                    o.total_amount, o.status, o.created_at
                    FROM orders o
                    JOIN customers c ON o.customer_id = c.customer_id
                    JOIN orderdetails od ON o.order_id = od.order_id
                    JOIN products p ON od.product_id = p.product_id
                    GROUP BY o.order_id
                    ORDER BY o.created_at DESC
                    LIMIT 5";
                
                $recent_orders_result = mysqli_query($conn, $recent_orders_query);
                
                if ($recent_orders_result && mysqli_num_rows($recent_orders_result) > 0) {
                    while ($order = mysqli_fetch_assoc($recent_orders_result)) {
                        $status_class = '';
                        switch (strtolower($order['status'])) {
                            case 'pending':
                                $status_class = 'status-pending';
                                $status_text = 'Đang xử lý';
                                break;
                            case 'completed':
                                $status_class = 'status-completed';
                                $status_text = 'Hoàn thành';
                                break;
                            case 'cancelled':
                                $status_class = 'status-cancelled';
                                $status_text = 'Đã hủy';
                                break;
                            default:
                                $status_class = 'status-pending';
                                $status_text = $order['status'];
                        }
                        ?>
                        <tr>
                            <td>#<?php echo $order['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['products']); ?></td>
                            <td><?php echo number_format($order['total_amount'], 0, ',', '.') . 'đ'; ?></td>
                            <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                        </tr>
                        <?php
                    }
                } else {
                    echo '<tr><td colspan="6" class="text-center">Không có đơn hàng nào</td></tr>';
                }
                ?>
                                </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
