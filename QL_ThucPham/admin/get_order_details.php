<?php
session_start();
require_once('../config/db_connect.php');

// Check if admin is logged in and order_id is provided
if (!isset($_SESSION['admin']) || !isset($_GET['order_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$order_id = intval($_GET['order_id']);

// Fetch order details with customer information
$sql = "SELECT o.*, c.name as customer_name, c.email, c.phone_number
        FROM orders o
        JOIN customers c ON o.customer_id = c.customer_id
        WHERE o.order_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    http_response_code(404);
    echo json_encode(['error' => 'Order not found']);
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

// Get status information
$status_info = [
    'Pending' => ['text' => 'Chờ xử lý', 'icon' => 'clock', 'color' => '#f39c12'],
    'Processing' => ['text' => 'Đang xử lý', 'icon' => 'spinner fa-spin', 'color' => '#3498db'],
    'Shipping' => ['text' => 'Đang giao hàng', 'icon' => 'truck', 'color' => '#2ecc71'],
    'Completed' => ['text' => 'Đã hoàn thành', 'icon' => 'check-circle', 'color' => '#27ae60'],
    'Cancelled' => ['text' => 'Đã hủy', 'icon' => 'times-circle', 'color' => '#e74c3c']
];

// Format payment method
$payment_methods = [
    'cod' => 'Thanh toán khi nhận hàng',
    'bank_transfer' => 'Chuyển khoản ngân hàng'
];

// Prepare response data
$response = [
    'order' => [
        'order_id' => $order['order_id'],
        'order_date' => date('d/m/Y H:i', strtotime($order['created_at'])),
        'status' => [
            'code' => $order['status'],
            'text' => $status_info[$order['status']]['text'],
            'icon' => $status_info[$order['status']]['icon'],
            'color' => $status_info[$order['status']]['color']
        ],
        'customer' => [
            'name' => $order['customer_name'],
            'email' => $order['email'],
            'phone' => $order['phone_number'],
            'shipping_address' => $order['shipping_address']
        ],
        'payment' => [
            'method' => $order['payment_method'],
            'subtotal' => floatval($order['total_amount'] + $order['discount_amount']),
            'discount' => floatval($order['discount_amount']),
            'shipping_fee' => floatval($order['shipping_fee']),
            'total' => floatval($order['total_amount'])
        ],
        'notes' => $order['notes']
    ],
    'items' => array_map(function($item) {
        return [
            'name' => $item['product_name'],
            'image_url' => $item['image_url'],
            'quantity' => intval($item['quantity']),
            'unit_price' => floatval($item['unit_price']),
            'subtotal' => floatval($item['subtotal'])
        ];
    }, $order_items),
    'actions' => [
        'can_process' => $order['status'] === 'Pending',
        'can_ship' => $order['status'] === 'Processing',
        'can_complete' => $order['status'] === 'Shipping',
        'can_cancel' => in_array($order['status'], ['Pending', 'Processing'])
    ]
];

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 