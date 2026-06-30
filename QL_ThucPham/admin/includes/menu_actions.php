<?php
header('Content-Type: application/json');
require_once('../../config/connect.php');

// Function to validate menu data
function validateMenuData($data) {
    $errors = [];
    
    if (empty($data['name'])) {
        $errors[] = "Tên menu không được để trống";
    }
    
    if (empty($data['url'])) {
        $errors[] = "URL không được để trống";
    }
    
    if (empty($data['icon'])) {
        $errors[] = "Icon không được để trống";
    }
    
    if (!in_array($data['menu_type'], ['top_menu', 'slide_menu'])) {
        $errors[] = "Loại menu không hợp lệ";
    }
    
    if (!empty($data['parent_id']) && !is_numeric($data['parent_id'])) {
        $errors[] = "Parent ID phải là số";
    }
    
    if (!is_numeric($data['position']) || $data['position'] < 1) {
        $errors[] = "Vị trí phải là số nguyên dương";
    }
    
    if (!in_array($data['status'], ['0', '1'])) {
        $errors[] = "Trạng thái không hợp lệ";
    }
    
    return $errors;
}

// Get menu by ID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $sql = "SELECT * FROM menus WHERE id = '$id'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && $menu = mysqli_fetch_assoc($result)) {
        echo json_encode(['success' => true, 'data' => $menu]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Menu không tồn tại']);
    }
}

// Create or update menu
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : 'create';
    
    // Validate input data
    $errors = validateMenuData($_POST);
    
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode(", ", $errors)]);
        exit;
    }
    
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $url = mysqli_real_escape_string($conn, $_POST['url']);
    $icon = mysqli_real_escape_string($conn, $_POST['icon']);
    $menu_type = mysqli_real_escape_string($conn, $_POST['menu_type']);
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : 'NULL';
    $position = (int)$_POST['position'];
    $status = (int)$_POST['status'];
    
    if ($action === 'update' && isset($_POST['id'])) {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $sql = "UPDATE menus SET 
                name = '$name',
                url = '$url',
                icon = '$icon',
                menu_type = '$menu_type',
                parent_id = $parent_id,
                position = $position,
                status = $status,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = '$id'";
    } else {
        $sql = "INSERT INTO menus (name, url, icon, menu_type, parent_id, position, status) 
                VALUES ('$name', '$url', '$icon', '$menu_type', $parent_id, $position, $status)";
    }
    
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . mysqli_error($conn)]);
    }
}

// Delete menu
else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    
    // First update any child menus to have no parent
    $sql = "UPDATE menus SET parent_id = NULL WHERE parent_id = '$id'";
    mysqli_query($conn, $sql);
    
    // Then delete the menu
    $sql = "DELETE FROM menus WHERE id = '$id'";
    
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . mysqli_error($conn)]);
    }
}

mysqli_close($conn);
?> 