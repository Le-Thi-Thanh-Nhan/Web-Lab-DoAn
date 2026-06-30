<?php
session_start();
require_once 'includes/db_helper.php';

// Check admin login
if (!isset($_SESSION['admin'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Initialize database connection
$conn = getDBConnection();

// Handle POST request for updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action']) && $_POST['action'] === 'update_sub') {
            $subcategory_id = (int)$_POST['subcategory_id'];
            $category_id = (int)$_POST['category_id'];
            $name = $_POST['name'];
            $description = $_POST['description'];

            $stmt = $conn->prepare("UPDATE subcategories SET category_id = ?, name = ?, description = ? WHERE subcategory_id = ?");
            $stmt->bind_param("issi", $category_id, $name, $description, $subcategory_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Cập nhật thành công']);
                exit;
            } else {
                throw new Exception("Lỗi khi cập nhật: " . $stmt->error);
            }
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}

// Handle GET request for fetching subcategories
if (!isset($_GET['category_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Category ID is required']);
    exit;
}

try {
    // Get subcategories for the specified category
    $category_id = (int)$_GET['category_id'];
    $query = "SELECT s.*, 
              (SELECT COUNT(*) FROM products p WHERE p.subcategory_id = s.subcategory_id) as product_count 
              FROM subcategories s 
              WHERE s.category_id = ?
              ORDER BY s.name";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $subcategories = [];
    while ($row = $result->fetch_assoc()) {
        $subcategories[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($subcategories);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?> 