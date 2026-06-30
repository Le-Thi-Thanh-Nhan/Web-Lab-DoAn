<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập để đặt hàng'
    ]);
    exit;
}

require_once('../config/db_connect.php');

try {
    $conn->begin_transaction();

    $customer_id = $_SESSION['user']['customer_id'];
    $shipping_address = $_POST['shipping_address'] ?? $_SESSION['user']['address'];
    $payment_method = $_POST['payment_method'] ?? 'cod';
    $notes = $_POST['notes'] ?? '';
    
    // Get cart items and calculate total
    $stmt = $conn->prepare("
        SELECT c.*, p.price, p.name, p.stock_quantity 
        FROM carts c 
        JOIN products p ON c.product_id = p.product_id 
        WHERE c.customer_id = ?
    ");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $cart_items = $stmt->get_result();

    if ($cart_items->num_rows === 0) {
        throw new Exception('Giỏ hàng trống');
    }

    $total_amount = 0;
    $items = [];
    while ($item = $cart_items->fetch_assoc()) {
        if ($item['quantity'] > $item['stock_quantity']) {
            throw new Exception("Sản phẩm '{$item['name']}' không đủ số lượng trong kho");
        }
        $subtotal = $item['price'] * $item['quantity'];
        $total_amount += $subtotal;
        $items[] = $item;
    }

    // Apply discount if available
    $discount_amount = 0;
    if (isset($_SESSION['discount'])) {
        $discount = $_SESSION['discount'];
        
        // Verify discount code is still valid
        $stmt = $conn->prepare("
            SELECT * FROM discount_codes 
            WHERE code_id = ? 
            AND is_active = 1 
            AND start_date <= NOW() 
            AND end_date >= NOW()
            AND (usage_limit IS NULL OR times_used < usage_limit)
        ");
        $stmt->bind_param("i", $discount['code_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $discount_amount = $discount['amount'];
            $total_amount -= $discount_amount;

            // Update usage count
            $stmt = $conn->prepare("
                UPDATE discount_codes 
                SET times_used = times_used + 1 
                WHERE code_id = ?
            ");
            $stmt->bind_param("i", $discount['code_id']);
            $stmt->execute();

            // Record customer usage
            $stmt = $conn->prepare("
                INSERT INTO customer_discount_usage (customer_id, code_id)
                VALUES (?, ?)
            ");
            $stmt->bind_param("ii", $customer_id, $discount['code_id']);
            $stmt->execute();
        }
    }

    // Create order
    $stmt = $conn->prepare("
        INSERT INTO orders (
            customer_id, 
            shipping_address, 
            total_amount,
            payment_method,
            notes,
            status
        ) VALUES (?, ?, ?, ?, ?, 'Pending')
    ");
    $stmt->bind_param("isdss", $customer_id, $shipping_address, $total_amount, $payment_method, $notes);
    $stmt->execute();
    $order_id = $conn->insert_id;

    // Add order details
    $stmt = $conn->prepare("
        INSERT INTO orderdetails (
            order_id,
            product_id,
            quantity,
            unit_price,
            subtotal
        ) VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($items as $item) {
        $subtotal = $item['price'] * $item['quantity'];
        $stmt->bind_param("iiids", $order_id, $item['product_id'], $item['quantity'], $item['price'], $subtotal);
        $stmt->execute();

        // Update stock quantity
        $new_quantity = $item['stock_quantity'] - $item['quantity'];
        $update_stock = $conn->prepare("UPDATE products SET stock_quantity = ? WHERE product_id = ?");
        $update_stock->bind_param("ii", $new_quantity, $item['product_id']);
        $update_stock->execute();
    }

    // Clear cart
    $stmt = $conn->prepare("DELETE FROM carts WHERE customer_id = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();

    // Clear discount session
    unset($_SESSION['discount']);

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Đặt hàng thành công',
        'order_id' => $order_id
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($update_stock)) {
        $update_stock->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
} 