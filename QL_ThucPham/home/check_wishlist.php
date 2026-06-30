<?php
session_start();
require_once __DIR__ . '/../includes/db_helper.php';

header('Content-Type: application/json');

// Default response
$response = ['in_wishlist' => false];

// Check if user is logged in and product_id is provided
if (isset($_SESSION['user']) && isset($_GET['product_id'])) {
    try {
        $conn = getDBConnection();
        $customer_id = $_SESSION['user']['customer_id'];
        $product_id = (int)$_GET['product_id'];
        
        // Check if product is in wishlist
        $query = "SELECT wishlist_id FROM wishlists WHERE customer_id = ? AND product_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $customer_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $response['in_wishlist'] = $result->num_rows > 0;
        
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        $response['error'] = $e->getMessage();
    }
}

echo json_encode($response);
?> 