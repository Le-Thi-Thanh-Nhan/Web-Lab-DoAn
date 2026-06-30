<?php
session_start();
require_once('../config/db_connect.php');

// Bước 1: Lấy thông báo từ database - lấy cho tất cả, cho khách hàng hiện tại, và trạng thái đã đọc
$notifications_query = "SELECT * FROM notifications WHERE recipient_type = 'all'";
if (isset($_SESSION['user']) && isset($_SESSION['user']['customer_id'])) {
    $user_id = $_SESSION['user']['customer_id'];
    $notifications_query .= " OR (recipient_type = 'customer' AND (recipient_id = $user_id OR recipient_id IS NULL))";
}
$notifications_query .= " ORDER BY created_at DESC";
$notifications = $conn->query($notifications_query);
if (!$notifications) {
    die("Lỗi truy vấn: " . $conn->error);
}

// Lấy riêng thông báo cho khách hàng này, cho tất cả khách hàng customer, và cho tất cả khách hàng (recipient_type = 'all')
$customer_notifications = [];
if (isset($user_id)) {
    $customer_query = "SELECT * FROM notifications WHERE (recipient_type = 'customer' AND (recipient_id = $user_id OR recipient_id IS NULL)) OR recipient_type = 'all' ORDER BY created_at DESC";
    $customer_result = $conn->query($customer_query);
    if ($customer_result && $customer_result->num_rows > 0) {
        // Đảm bảo không lặp lại thông báo (theo notification_id)
        $seen = [];
        while ($row = $customer_result->fetch_assoc()) {
            if (!isset($seen[$row['notification_id']])) {
                $customer_notifications[] = $row;
                $seen[$row['notification_id']] = true;
            }
        }
    }
}

// Bước 2: Lấy các loại notification_type để tạo tab
$types = ['all' => 'Tất cả', 'system' => 'Hệ thống', 'order' => 'Đơn hàng', 'promotion' => 'Khuyến mãi', 'support' => 'Hỗ trợ'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông Báo - Thực Phẩm Mộc</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/thong-bao.css">
    <link rel="icon" type="image/png" href="../images/Logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="script.js" defer></script>
    <style>
        .tabs { display: flex; gap: 10px; margin-bottom: 20px; }
        .tab-btn { padding: 8px 16px; border: none; background: #eee; cursor: pointer; border-radius: 5px; }
        .tab-btn.active { background: #4CAF50; color: #fff; }
        .notification-item.read { opacity: 0.6; }
        .mark-read-btn { background: #2196F3; color: #fff; border: none; padding: 4px 10px; border-radius: 4px; cursor: pointer; margin-left: 10px; font-size: 0.9em; }
        .mark-read-btn[disabled] { background: #aaa; cursor: not-allowed; }
        .notification-meta { font-size: 0.95em; color: #666; margin-bottom: 4px; }
        .notification-type-label { display: inline-block; background: #e0e0e0; color: #333; border-radius: 3px; padding: 2px 8px; font-size: 0.85em; margin-right: 8px; }
        .customer-notifications-box { background: #f6fff6; border: 2px solid #4CAF50; border-radius: 8px; padding: 18px 20px; margin-bottom: 28px; }
        .customer-notifications-title { color: #388e3c; font-size: 1.2em; font-weight: bold; margin-bottom: 10px; }
    </style>
</head>
<body>
    <?php include 'slide-menu.php'; ?>

    <!-- Main Content -->
    <main class="notifications-container">
        <h2><i class="fas fa-bell"></i> Thông Báo</h2>
        <!-- Tabs phân loại -->
        <div class="tabs">
            <?php foreach ($types as $type_key => $type_label): ?>
                <button class="tab-btn<?php echo $type_key === 'all' ? ' active' : ''; ?>" data-type="<?php echo $type_key; ?>"><?php echo $type_label; ?></button>
            <?php endforeach; ?>
        </div>
        <!-- Thông báo dành cho bạn (bao gồm cả thông báo cho tất cả khách hàng) -->
        <?php if (isset($user_id)): ?>
            <div class="customer-notifications-box">
                <div class="customer-notifications-title"><i class="fas fa-user-check"></i> Thông báo dành cho bạn</div>
                <?php if (count($customer_notifications) > 0): ?>
                    <?php foreach ($customer_notifications as $notification): ?>
                        <?php 
                            $is_read = !empty($notification['read_at']);
                            $type = $notification['notification_type'];
                            $type_label = isset($types[$type]) ? $types[$type] : ucfirst($type);
                            if ($notification['recipient_type'] === 'all') {
                                $recipient = 'Tất cả khách hàng';
                            } elseif ($notification['recipient_id'] === null) {
                                $recipient = 'Tất cả khách hàng (loại khách hàng)';
                            } else {
                                $recipient = 'Bạn';
                            }
                        ?>
                        <div class="notification-item<?php echo $is_read ? ' read' : ''; ?> customer-noti" data-type="<?php echo $type; ?>">
                            <div class="notification-meta">
                                <span class="notification-type-label"><?php echo $type_label; ?></span>
                                <span>Gửi cho: <?php echo htmlspecialchars($recipient); ?></span>
                            </div>
                            <h3><?php echo htmlspecialchars($notification['subject']); ?></h3>
                            <p><?php echo nl2br(htmlspecialchars($notification['message'])); ?></p>
                            <span class="notification-date">
                                <i class="far fa-clock"></i>
                                <?php 
                                $date = new DateTime($notification['created_at']);
                                echo $date->format('d/m/Y H:i'); 
                                ?>
                            </span>
                            <?php if (!$is_read): ?>
                                <button class="mark-read-btn" data-id="<?php echo $notification['notification_id']; ?>">Đánh dấu đã đọc</button>
                            <?php else: ?>
                                <button class="mark-read-btn" disabled>Đã đọc</button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-notifications">
                        <i class="fas fa-bell-slash"></i>
                        <p>Không có thông báo nào dành cho bạn.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <div class="notifications-list">
            <?php if ($notifications->num_rows > 0): ?>
                <?php while ($notification = $notifications->fetch_assoc()): ?>
                    <?php 
                        $is_read = !empty($notification['read_at']);
                        $type = $notification['notification_type'];
                        $recipient = $notification['recipient_type'] === 'all' ? 'Tất cả khách hàng' : 'Khách hàng ID: ' . $notification['recipient_id'];
                        $type_label = isset($types[$type]) ? $types[$type] : ucfirst($type);
                    ?>
                    <div class="notification-item<?php echo $is_read ? ' read' : ''; ?> general-noti" data-type="<?php echo $type; ?>">
                        <div class="notification-meta">
                            <span class="notification-type-label"><?php echo $type_label; ?></span>
                            <span>Gửi cho: <?php echo htmlspecialchars($recipient); ?></span>
                        </div>
                        <h3><?php echo htmlspecialchars($notification['subject']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($notification['message'])); ?></p>
                        <span class="notification-date">
                            <i class="far fa-clock"></i>
                            <?php 
                            $date = new DateTime($notification['created_at']);
                            echo $date->format('d/m/Y H:i'); 
                            ?>
                        </span>
                        <?php if (!$is_read): ?>
                            <button class="mark-read-btn" data-id="<?php echo $notification['notification_id']; ?>">Đánh dấu đã đọc</button>
                        <?php else: ?>
                            <button class="mark-read-btn" disabled>Đã đọc</button>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-notifications">
                    <i class="fas fa-bell-slash"></i>
                    <p>Không có thông báo nào.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'footer.php'; ?>
    <script>
    // Tab filter hoạt động cho cả 2 khu vực
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const type = this.getAttribute('data-type');
            // Lọc cả thông báo riêng và chung
            document.querySelectorAll('.notification-item').forEach(item => {
                if (type === 'all' || item.getAttribute('data-type') === type) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
    // Mark as read
    document.querySelectorAll('.mark-read-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            if (!id) return;
            fetch('mark_notification_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + encodeURIComponent(id)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.textContent = 'Đã đọc';
                    this.disabled = true;
                    this.closest('.notification-item').classList.add('read');
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể đánh dấu đã đọc.'));
                }
            });
        });
    });
    </script>
</body>
</html> 