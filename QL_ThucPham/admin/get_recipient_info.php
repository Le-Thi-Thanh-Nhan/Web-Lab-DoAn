<?php
session_start();
require_once 'includes/db_helper.php';

// Check admin login
if (!isset($_SESSION['admin'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

try {
    if (!isset($_GET['type']) || !isset($_GET['id'])) {
        throw new Exception('Missing required parameters');
    }

    $type = $_GET['type'];
    $id = (int)$_GET['id'];
    $conn = getDBConnection();

    if ($type === 'customer') {
        $stmt = $conn->prepare("SELECT name, email, phone_number, address FROM customers WHERE customer_id = ?");
    } else if ($type === 'admin') {
        $stmt = $conn->prepare("SELECT name, email, phone FROM administrators WHERE admin_id = ?");
    } else {
        throw new Exception('Invalid recipient type');
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if (!$data) {
        throw new Exception('Recipient not found');
    }

    echo json_encode([
        'success' => true,
        'name' => $data['name'],
        'email' => $data['email'],
        'phone_number' => $data[$type === 'customer' ? 'phone_number' : 'phone'],
        'address' => $type === 'customer' ? $data['address'] : null
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 