<?php
session_start();
require_once('../config/db_connect.php');
header('Content-Type: application/json');

if (!isset($_SESSION['user']['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn chưa đăng nhập!']);
    exit;
}

$user_id = $_SESSION['user']['customer_id'];
$notification_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if (!$notification_id) {
    echo json_encode(['success' => false, 'message' => 'Thiếu ID thông báo!']);
    exit;
}
// Kiểm tra quyền đánh dấu: chỉ cho phép đánh dấu các thông báo chung hoặc dành cho đúng khách hàng
$sql = "SELECT * FROM notifications WHERE notification_id = $notification_id AND (recipient_type = 'all' OR (recipient_type = 'customer' AND (recipient_id = $user_id OR recipient_id IS NULL)))";
$result = $conn->query($sql);
if (!$result || $result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông báo hoặc không có quyền!']);
    exit;
}
// Đánh dấu đã đọc
$update = $conn->query("UPDATE notifications SET read_at = NOW() WHERE notification_id = $notification_id");
if ($update) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật trạng thái!']);
} 