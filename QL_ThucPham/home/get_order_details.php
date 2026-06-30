<?php
// Prevent any output before JSON response
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Start output buffering to catch any unwanted output
ob_start();

session_start();
require_once('../config/db_connect.php');

// Function to send JSON response and exit
function sendJsonResponse($data) {
    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    sendJsonResponse([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
}

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    sendJsonResponse([
        'success' => false,
        'message' => 'Order ID is required'
    ]);
}

try {
    $order_id = intval($_GET['order_id']);
    $customer_id = $_SESSION['user']['customer_id'];

    // Get order details
    $order_query = "
        SELECT 
            o.*,
            c.name as customer_name,
            c.phone_number,
            c.email,
            c.address as shipping_address
        FROM orders o
        JOIN customers c ON o.customer_id = c.customer_id
        WHERE o.order_id = ? AND o.customer_id = ?
    ";

    $stmt = $conn->prepare($order_query);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $stmt->bind_param("ii", $order_id, $customer_id);
    if (!$stmt->execute()) {
        throw new Exception("Query execution failed: " . $stmt->error);
    }

    $order_result = $stmt->get_result();
    if ($order_result->num_rows === 0) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Order not found'
        ]);
    }

    $order = $order_result->fetch_assoc();

    // Get order items
    $items_query = "
        SELECT 
            od.*,
            p.name,
            p.image_url,
            p.product_id,
            CASE 
                WHEN o.status = 'Completed' AND pr.review_id IS NULL THEN 1
                ELSE 0
            END as can_rate
        FROM orderdetails od
        JOIN products p ON od.product_id = p.product_id
        JOIN orders o ON od.order_id = o.order_id
        LEFT JOIN product_reviews pr ON p.product_id = pr.product_id 
            AND pr.customer_id = o.customer_id
        WHERE od.order_id = ?
    ";

    $stmt = $conn->prepare($items_query);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $stmt->bind_param("i", $order_id);
    if (!$stmt->execute()) {
        throw new Exception("Query execution failed: " . $stmt->error);
    }

    $items_result = $stmt->get_result();
    $items = [];

    while ($item = $items_result->fetch_assoc()) {
        $items[] = [
            'product_id' => $item['product_id'],
            'name' => $item['name'],
            'image_url' => $item['image_url'],
            'quantity' => intval($item['quantity']),
            'unit_price' => floatval($item['unit_price']),
            'subtotal' => floatval($item['unit_price'] * $item['quantity']),
            'can_rate' => $item['can_rate'] == 1
        ];
    }

    // Prepare status information
    $status_info = [
        'Pending' => [
            'text' => 'Chờ xử lý',
            'color' => '#f39c12',
            'icon' => 'clock'
        ],
        'Processing' => [
            'text' => 'Đang xử lý',
            'color' => '#3498db',
            'icon' => 'spinner'
        ],
        'Shipping' => [
            'text' => 'Đang giao hàng',
            'color' => '#2ecc71',
            'icon' => 'shipping-fast'
        ],
        'Completed' => [
            'text' => 'Đã hoàn thành',
            'color' => '#27ae60',
            'icon' => 'check-circle'
        ],
        'Cancelled' => [
            'text' => 'Đã hủy',
            'color' => '#e74c3c',
            'icon' => 'times-circle'
        ]
    ];

    // Format payment method
    $payment_methods = [
        'cod' => 'Thanh toán khi nhận hàng',
        'bank_transfer' => 'Chuyển khoản ngân hàng',
        'momo' => 'Ví MoMo',
        'vnpay' => 'VNPay',
        'zalopay' => 'ZaloPay'
    ];

    // Prepare response data
    $response = [
        'success' => true,
        'order' => [
            'order_id' => $order['order_id'],
            'order_date' => date('d/m/Y H:i', strtotime($order['created_at'])),
            'status' => $status_info[$order['status']],
            'customer' => [
                'name' => $order['customer_name'],
                'phone' => $order['phone_number'],
                'email' => $order['email'],
                'shipping_address' => $order['shipping_address']
            ],
            'payment' => [
                'method' => $payment_methods[$order['payment_method']] ?? $order['payment_method'],
                'subtotal' => floatval($order['subtotal']),
                'discount' => floatval($order['discount']),
                'shipping_fee' => floatval($order['shipping_fee']),
                'total' => floatval($order['total_amount'])
            ],
            'notes' => $order['notes']
        ],
        'items' => $items
    ];

    sendJsonResponse($response);

} catch (Exception $e) {
    sendJsonResponse([
        'success' => false,
        'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
    ]);
}
?> 