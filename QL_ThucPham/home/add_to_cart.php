<?php
session_start();
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Check if user is logged in
if (!isset($_SESSION['user']) && !isset($_SESSION['customer_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng'
    ]);
    exit;
}

if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Thiếu thông tin sản phẩm'
    ]);
    exit;
}

// Get customer ID from session
$customer_id = null;
if (isset($_SESSION['user']['customer_id'])) {
    $customer_id = $_SESSION['user']['customer_id'];
} elseif (isset($_SESSION['customer_id'])) {
    $customer_id = $_SESSION['customer_id'];
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Không tìm thấy thông tin khách hàng'
    ]);
    exit;
}

$product_id = (int)$_POST['product_id'];
$quantity = (int)$_POST['quantity'];
$buy_now = isset($_POST['buy_now']) ? true : false;

try {
    // Database connection
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'ql_thucpham';
    
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    $conn->set_charset("utf8mb4");
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Check if product exists and has enough stock
    $stmt = $conn->prepare("SELECT stock_quantity, name FROM products WHERE product_id = ?");
    if (!$stmt) {
        throw new Exception('Lỗi chuẩn bị câu lệnh SQL');
    }
    
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Sản phẩm không tồn tại');
    }

    $product = $result->fetch_assoc();
    if ($quantity > $product['stock_quantity']) {
        throw new Exception('Số lượng sản phẩm trong kho không đủ');
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // If buy now, clear cart first
        if ($buy_now) {
            $stmt = $conn->prepare("DELETE FROM carts WHERE customer_id = ?");
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
        }

        // Check if product already in cart
        $stmt = $conn->prepare("SELECT quantity FROM carts WHERE customer_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $customer_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0 && !$buy_now) {
            // Update quantity
            $current_quantity = $result->fetch_assoc()['quantity'];
            $new_quantity = $current_quantity + $quantity;
            
            if ($new_quantity > $product['stock_quantity']) {
                throw new Exception('Tổng số lượng trong giỏ vượt quá số lượng tồn kho');
            }
            
            $stmt = $conn->prepare("UPDATE carts SET quantity = ? WHERE customer_id = ? AND product_id = ?");
            $stmt->bind_param("iii", $new_quantity, $customer_id, $product_id);
        } else {
            // Insert new cart item
            $stmt = $conn->prepare("INSERT INTO carts (customer_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $customer_id, $product_id, $quantity);
        }

        if (!$stmt->execute()) {
            throw new Exception('Lỗi khi cập nhật giỏ hàng');
        }

        // Get cart count
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM carts WHERE customer_id = ?");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $cart_count = $stmt->get_result()->fetch_assoc()['count'];

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => $buy_now ? 'Đang chuyển đến trang thanh toán' : "Đã thêm {$product['name']} vào giỏ hàng",
            'cart_count' => $cart_count
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close(); 
    }
}
?> 