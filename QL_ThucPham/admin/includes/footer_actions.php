<?php
header('Content-Type: application/json');
require_once('../../config/connect.php');

// Get footer by ID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    $id = $_GET['id'];
    $sql = "SELECT * FROM footers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy footer']);
    }
    exit;
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete footer
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = $_POST['id'];
        $sql = "DELETE FROM footers WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa footer']);
        }
        exit;
    }
    
    // Update footer
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        $id = $_POST['id'];
        $section_name = $_POST['section_name'];
        $content = $_POST['content'];
        $type = $_POST['type'];
        $icon = $_POST['icon'];
        $url = $_POST['url'] ?? null;
        $position = $_POST['position'];
        $status = $_POST['status'];
        
        $sql = "UPDATE footers SET 
                section_name = ?,
                content = ?,
                type = ?,
                icon = ?,
                url = ?,
                position = ?,
                status = ?,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssiis", $section_name, $content, $type, $icon, $url, $position, $status, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật footer']);
        }
        exit;
    }
    
    // Add new footer
    $section_name = $_POST['section_name'];
    $content = $_POST['content'];
    $type = $_POST['type'];
    $icon = $_POST['icon'];
    $url = $_POST['url'] ?? null;
    $position = $_POST['position'];
    $status = $_POST['status'];
    
    $sql = "INSERT INTO footers (section_name, content, type, icon, url, position, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssii", $section_name, $content, $type, $icon, $url, $position, $status);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi thêm footer']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']); 