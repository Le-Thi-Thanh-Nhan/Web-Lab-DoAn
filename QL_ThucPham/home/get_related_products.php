<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../config/db_connect.php');

try {
    $subcategory_id = isset($_GET['subcategory_id']) ? (int)$_GET['subcategory_id'] : 0;
    $exclude_id = isset($_GET['exclude_id']) ? (int)$_GET['exclude_id'] : 0;

    if ($subcategory_id <= 0) {
        throw new Exception('Invalid subcategory ID');
    }

    // Query to get related products with additional information
    $query = "SELECT 
                p.*,
                c.name as category_name,
                s.name as subcategory_name
            FROM products p 
            LEFT JOIN subcategories s ON p.subcategory_id = s.subcategory_id 
            LEFT JOIN categories c ON s.category_id = c.category_id 
            WHERE p.subcategory_id = ? 
            AND p.product_id != ?
            AND p.quantity > 0
            ORDER BY RAND() 
            LIMIT 4";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }

    $stmt->bind_param('ii', $subcategory_id, $exclude_id);
    if (!$stmt->execute()) {
        throw new Exception('Database execute error: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception('Database result error: ' . $stmt->error);
    }

    $products = [];
    while ($row = $result->fetch_assoc()) {
        // Format price and add additional information
        $row['formatted_price'] = number_format($row['price'], 0, ',', '.');
        $row['stock_status'] = $row['quantity'] > 0 ? 'Còn hàng' : 'Hết hàng';
        $products[] = $row;
    }

    $response = [
        'success' => true,
        'data' => $products
    ];

    // Ensure clean output buffer
    while (ob_get_level()) ob_end_clean();
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;

} catch (Exception $e) {
    http_response_code(400);
    
    $error_response = [
        'success' => false,
        'error' => $e->getMessage()
    ];

    // Ensure clean output buffer
    while (ob_get_level()) ob_end_clean();
    
    echo json_encode($error_response, JSON_UNESCAPED_UNICODE);
    exit;

} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
