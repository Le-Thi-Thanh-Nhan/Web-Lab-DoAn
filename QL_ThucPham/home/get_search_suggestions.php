<?php
require_once('../config/db_connect.php');

header('Content-Type: application/json');

$term = isset($_GET['term']) ? $conn->real_escape_string($_GET['term']) : '';

if (strlen($term) < 2) {
    echo json_encode([
        'categories' => [],
        'subcategories' => [],
        'products' => []
    ]);
    exit;
}

// Search categories
$categories_query = "SELECT category_id, name 
                    FROM categories 
                    WHERE name LIKE '%$term%' 
                    LIMIT 5";
$categories_result = $conn->query($categories_query);
$categories = [];

if ($categories_result) {
    while ($row = $categories_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Search subcategories
$subcategories_query = "SELECT s.subcategory_id, s.name, s.category_id 
                       FROM subcategories s 
                       WHERE s.name LIKE '%$term%' 
                       LIMIT 5";
$subcategories_result = $conn->query($subcategories_query);
$subcategories = [];

if ($subcategories_result) {
    while ($row = $subcategories_result->fetch_assoc()) {
        $subcategories[] = $row;
    }
}

// Search products
$products_query = "SELECT product_id, name 
                  FROM products 
                  WHERE name LIKE '%$term%' 
                  OR description LIKE '%$term%' 
                  LIMIT 5";
$products_result = $conn->query($products_query);
$products = [];

if ($products_result) {
    while ($row = $products_result->fetch_assoc()) {
        $products[] = $row;
    }
}

echo json_encode([
    'categories' => $categories,
    'subcategories' => $subcategories,
    'products' => $products
]); 