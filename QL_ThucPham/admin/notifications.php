<?php
session_start();
require_once 'includes/db_helper.php';

// Initialize message variables
$message = '';
$messageType = '';

// Check admin login
if (!isset($_SESSION['admin'])) {
    header('Location: ../auth/auth.php');
    exit;
}

// Initialize database connection
$conn = getDBConnection();

// Get all customers and admins for recipient selection
$customers = $conn->query("SELECT customer_id, name FROM customers ORDER BY name ASC");
$admins = $conn->query("SELECT admin_id, name FROM administrators ORDER BY name ASC");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    $title = $_POST['title'];
                    $content = $_POST['message'];
                    $recipient_type = $_POST['recipient_type'];
                    $recipient_id = null;
                    
                    if ($recipient_type === 'customer' && !empty($_POST['customer_id'])) {
                        $recipient_id = $_POST['customer_id'];
                    } elseif ($recipient_type === 'admin' && !empty($_POST['admin_id'])) {
                        $recipient_id = $_POST['admin_id'];
                    }
                    
                    $created_at = date('Y-m-d H:i:s');
                    
                    if ($recipient_type === 'all') {
                        $stmt = $conn->prepare("INSERT INTO notifications (subject, message, recipient_type, created_at) VALUES (?, ?, 'all', ?)");
                        $stmt->bind_param("sss", $title, $content, $created_at);
                    } else {
                        $stmt = $conn->prepare("INSERT INTO notifications (subject, message, recipient_type, recipient_id, created_at) VALUES (?, ?, ?, ?, ?)");
                        $stmt->bind_param("sssis", $title, $content, $recipient_type, $recipient_id, $created_at);
                    }
                    
                    if ($stmt->execute()) {
                        $message = "Thông báo đã được tạo thành công!";
                        $messageType = "success";
                    } else {
                        $message = "Lỗi khi tạo thông báo: " . $stmt->error;
                        $messageType = "danger";
                    }
                    break;

                case 'update':
                    $notification_id = $_POST['notification_id'];
                    $title = $_POST['title'];
                    $content = $_POST['message'];
                    $recipient_type = $_POST['recipient_type'];
                    $recipient_id = null;
                    
                    if ($recipient_type === 'customer' && !empty($_POST['customer_id'])) {
                        $recipient_id = $_POST['customer_id'];
                    } elseif ($recipient_type === 'admin' && !empty($_POST['admin_id'])) {
                        $recipient_id = $_POST['admin_id'];
                    }
                    
                    if ($recipient_type === 'all') {
                        $stmt = $conn->prepare("UPDATE notifications SET subject=?, message=?, recipient_type='all', recipient_id=NULL WHERE notification_id=?");
                        $stmt->bind_param("ssi", $title, $content, $notification_id);
                    } else {
                        $stmt = $conn->prepare("UPDATE notifications SET subject=?, message=?, recipient_type=?, recipient_id=? WHERE notification_id=?");
                        $stmt->bind_param("sssii", $title, $content, $recipient_type, $recipient_id, $notification_id);
                    }
                    
                    if ($stmt->execute()) {
                        $message = "Thông báo đã được cập nhật thành công!";
                        $messageType = "success";
                    } else {
                        $message = "Lỗi khi cập nhật thông báo: " . $stmt->error;
                        $messageType = "danger";
                    }
                    break;

                case 'delete':
                    $notification_id = $_POST['notification_id'];
                    $stmt = $conn->prepare("DELETE FROM notifications WHERE notification_id = ?");
                    $stmt->bind_param("i", $notification_id);
                    
                    if ($stmt->execute()) {
                        $message = "Thông báo đã được xóa thành công!";
                        $messageType = "success";
                    }
                    break;
            }
        }
    } catch (Exception $e) {
        $message = "Lỗi: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Pagination settings
$items_per_page = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Get total notifications count
$total_notifications = $conn->query("SELECT COUNT(*) as total FROM notifications")->fetch_assoc()['total'];
$total_pages = ceil($total_notifications / $items_per_page);

// Get notifications with recipient info
$notifications = $conn->query("
    SELECT n.*, 
           CASE 
               WHEN n.recipient_type = 'customer' THEN c.name
               WHEN n.recipient_type = 'admin' THEN a.name
               ELSE NULL
           END as recipient_name
    FROM notifications n
    LEFT JOIN customers c ON n.recipient_type = 'customer' AND n.recipient_id = c.customer_id
    LEFT JOIN administrators a ON n.recipient_type = 'admin' AND n.recipient_id = a.admin_id
    ORDER BY n.created_at DESC
    LIMIT $offset, $items_per_page
");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Thông Báo - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/admin-style.css">
    <link rel="icon" type="image/png" href="../images/Logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .main-content {
            padding: 2rem;
            background-color: #f8f9fa;
        }

        .notifications-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 30px;
        }

        .notification-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .notification-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .notification-card h3 {
            color: #2c3e50;
            margin: 0 0 15px 0;
            font-size: 1.2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-card .actions {
            position: absolute;
            top: 15px;
            right: 15px;
            display: flex;
            gap: 5px;
        }

        .notification-card .btn {
            padding: 5px 10px;
            font-size: 0.9rem;
        }

        .notification-info {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .notification-info p {
            margin: 5px 0;
            color: #666;
            line-height: 1.5;
            max-height: 4.5em;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }

        .notification-meta {
            display: flex;
            flex-direction: column;
            gap: 8px;
            font-size: 0.9rem;
            color: #666;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .meta-item i {
            width: 16px;
            text-align: center;
            color: #4CAF50;
        }

        .card-header {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-group {
            display: flex;
            gap: 10px;
        }

        /* Modal Styles */
        .modal-content {
            max-width: 500px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
            outline: none;
        }

        .recipient-type {
            display: flex;
            gap: 20px;
            margin: 15px 0;
        }

        .recipient-option {
            position: relative;
            padding-left: 28px;
            cursor: pointer;
            user-select: none;
        }

        .recipient-option input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .recipient-option .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 20px;
            width: 20px;
            background-color: #fff;
            border: 2px solid #ddd;
            border-radius: 50%;
            transition: all 0.2s ease;
        }

        .recipient-option:hover .checkmark {
            border-color: #4CAF50;
        }

        .recipient-option input:checked ~ .checkmark {
            background-color: #4CAF50;
            border-color: #4CAF50;
        }

        .recipient-option .checkmark:after {
            content: '';
            position: absolute;
            display: none;
            top: 50%;
            left: 50%;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: white;
            transform: translate(-50%, -50%);
        }

        .recipient-option input:checked ~ .checkmark:after {
            display: block;
        }

        .select-wrapper {
            position: relative;
            margin-top: 10px;
        }

        .select-wrapper:after {
            content: '▼';
            font-size: 12px;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            pointer-events: none;
        }

        /* Pagination Styling */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            gap: 5px;
        }

        .pagination a, .pagination span {
            padding: 8px 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #2c3e50;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background-color: #e9ecef;
        }

        .pagination .active {
            background-color: #4CAF50;
            color: white;
            border-color: #4CAF50;
        }

        .pagination .disabled {
            color: #6c757d;
            pointer-events: none;
            background-color: #f8f9fa;
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .btn-submit {
            background: #4CAF50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-submit:hover {
            background: #388E3C;
        }

        .recipient-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .recipient-all {
            background: #e3f2fd;
            color: #1976d2;
        }

        .recipient-customer {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .recipient-admin {
            background: #e8f5e9;
            color: #388e3c;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Quản Lý Thông Báo</h2>
                    <button class="btn btn-primary" onclick="showAddNotificationForm()">
                        <i class="fas fa-plus"></i> Thêm Thông Báo Mới
                    </button>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <i class="fas fa-<?php echo $messageType === 'success' ? 'check' : 'exclamation'; ?>-circle"></i>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <div class="notifications-grid">
                    <?php while ($notification = $notifications->fetch_assoc()): ?>
                        <div class="notification-card">
                            <div class="actions">
                                <button class="btn btn-warning" onclick='showEditNotificationForm(<?php echo json_encode($notification); ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger" onclick="confirmDeleteNotification(<?php echo $notification['notification_id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <h3><?php echo htmlspecialchars($notification['subject']); ?></h3>
                            <div class="notification-info">
                                <p><?php echo htmlspecialchars($notification['message']); ?></p>
                            </div>
                            <div class="notification-meta">
                                <div class="meta-item">
                                    <i class="far fa-clock"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?>
                                </div>
                                <div class="meta-item">
                                    <i class="far fa-user"></i>
                                    <?php
                                    $recipientClass = '';
                                    if ($notification['recipient_type'] === 'all') {
                                        echo '<span class="recipient-badge recipient-all">Tất cả người dùng</span>';
                                    } elseif ($notification['recipient_type'] === 'customer') {
                                        echo '<span class="recipient-badge recipient-customer">Khách hàng: ' . 
                                             htmlspecialchars($notification['recipient_name'] ?? 'Không tồn tại') . '</span>';
                                    } else {
                                        echo '<span class="recipient-badge recipient-admin">Quản trị viên: ' . 
                                             htmlspecialchars($notification['recipient_name'] ?? 'Không tồn tại') . '</span>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=1" title="Trang đầu"><i class="fas fa-angle-double-left"></i></a>
                            <a href="?page=<?php echo $page - 1; ?>" title="Trang trước"><i class="fas fa-angle-left"></i></a>
                        <?php endif; ?>

                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);

                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <?php if ($i == $page): ?>
                                <span class="active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" title="Trang sau"><i class="fas fa-angle-right"></i></a>
                            <a href="?page=<?php echo $total_pages; ?>" title="Trang cuối"><i class="fas fa-angle-double-right"></i></a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Notification Modal -->
    <div id="notificationModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="notificationModalTitle">Thêm Thông Báo Mới</h2>
            <form id="notificationForm" method="POST">
                <input type="hidden" name="action" id="notificationFormAction" value="add">
                <input type="hidden" name="notification_id" id="notificationId">

                <div class="form-group">
                    <label for="title">Tiêu đề thông báo:</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="message">Nội dung thông báo:</label>
                    <textarea id="message" name="message" class="form-control" rows="4" required></textarea>
                </div>

                <div class="form-group">
                    <label>Người nhận:</label>
                    <div class="recipient-type">
                        <label class="recipient-option">
                            <input type="radio" name="recipient_type" value="all" checked>
                            <span class="checkmark"></span>
                            Tất cả người dùng
                        </label>
                        <label class="recipient-option">
                            <input type="radio" name="recipient_type" value="customer">
                            <span class="checkmark"></span>
                            Khách hàng
                        </label>
                        <label class="recipient-option">
                            <input type="radio" name="recipient_type" value="admin">
                            <span class="checkmark"></span>
                            Quản trị viên
                        </label>
                    </div>

                    <div id="customerSelect" class="select-wrapper" style="display: none;">
                        <select name="customer_id" class="form-control" id="customerSelectInput">
                            <option value="">-- Chọn khách hàng --</option>
                            <?php 
                            $customers->data_seek(0);
                            while ($customer = $customers->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $customer['customer_id']; ?>">
                                    ID: <?php echo $customer['customer_id']; ?> - <?php echo htmlspecialchars($customer['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div id="adminSelect" class="select-wrapper" style="display: none;">
                        <select name="admin_id" class="form-control" id="adminSelectInput">
                            <option value="">-- Chọn quản trị viên --</option>
                            <?php 
                            $admins->data_seek(0);
                            while ($admin = $admins->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $admin['admin_id']; ?>">
                                    ID: <?php echo $admin['admin_id']; ?> - <?php echo htmlspecialchars($admin['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('notificationModal')">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Gửi thông báo
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Global function for recipient select handling
        function updateRecipientSelects(type) {
            const customerSelect = document.getElementById('customerSelect');
            const adminSelect = document.getElementById('adminSelect');
            const customerSelectInput = document.getElementById('customerSelectInput');
            const adminSelectInput = document.getElementById('adminSelectInput');
            
            // Hide all selects first
            customerSelect.style.display = 'none';
            adminSelect.style.display = 'none';
            customerSelectInput.required = false;
            adminSelectInput.required = false;
            
            // Show appropriate select based on type
            if (type === 'customer') {
                customerSelect.style.display = 'block';
                customerSelectInput.required = true;
                document.getElementById('notificationForm').onsubmit = function(e) {
                    if (!customerSelectInput.value) {
                        e.preventDefault();
                        alert('Vui lòng chọn khách hàng!');
                        return false;
                    }
                };
            } else if (type === 'admin') {
                adminSelect.style.display = 'block';
                adminSelectInput.required = true;
                document.getElementById('notificationForm').onsubmit = function(e) {
                    if (!adminSelectInput.value) {
                        e.preventDefault();
                        alert('Vui lòng chọn quản trị viên!');
                        return false;
                    }
                };
            } else {
                // For 'all' type, reset form validation
                document.getElementById('notificationForm').onsubmit = null;
            }
            
            // Reset values when switching types
            customerSelectInput.value = '';
            adminSelectInput.value = '';
        }

        // Modal handling
        function showModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.getElementById('notificationForm').reset();
            // Reset recipient selects when closing
            updateRecipientSelects('all');
        }

        // Notification functions
        function showAddNotificationForm() {
            document.getElementById('notificationModalTitle').textContent = 'Thêm Thông Báo Mới';
            document.getElementById('notificationFormAction').value = 'add';
            document.getElementById('notificationId').value = '';
            document.getElementById('notificationForm').reset();
            
            // Set default recipient type to 'all'
            const allRecipientRadio = document.querySelector('input[name="recipient_type"][value="all"]');
            if (allRecipientRadio) {
                allRecipientRadio.checked = true;
                updateRecipientSelects('all');
            }
            
            showModal('notificationModal');
        }

        function showEditNotificationForm(notification) {
            document.getElementById('notificationModalTitle').textContent = 'Cập Nhật Thông Báo';
            document.getElementById('notificationFormAction').value = 'update';
            document.getElementById('notificationId').value = notification.notification_id;
            document.getElementById('title').value = notification.subject;
            document.getElementById('message').value = notification.message;
            
            // Set recipient type
            const recipientTypeInputs = document.querySelectorAll('input[name="recipient_type"]');
            recipientTypeInputs.forEach(input => {
                if (input.value === notification.recipient_type) {
                    input.checked = true;
                }
            });
            
            // Update recipient selects visibility
            updateRecipientSelects(notification.recipient_type);
            
            // Set recipient if applicable
            if (notification.recipient_type === 'customer' && notification.recipient_id) {
                setTimeout(() => {
                    document.getElementById('customerSelectInput').value = notification.recipient_id;
                }, 100);
            } else if (notification.recipient_type === 'admin' && notification.recipient_id) {
                setTimeout(() => {
                    document.getElementById('adminSelectInput').value = notification.recipient_id;
                }, 100);
            }

            showModal('notificationModal');
        }

        function confirmDeleteNotification(notificationId) {
            if (confirm('Bạn có chắc chắn muốn xóa thông báo này?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="notification_id" value="${notificationId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Initialize when document is ready
        document.addEventListener('DOMContentLoaded', function() {
            const recipientTypeInputs = document.querySelectorAll('input[name="recipient_type"]');
            
            // Add change event listeners to recipient type inputs
            recipientTypeInputs.forEach(input => {
                input.addEventListener('change', function() {
                    updateRecipientSelects(this.value);
                });
            });

            // Form validation
            document.getElementById('notificationForm').addEventListener('submit', function(e) {
                const recipientType = document.querySelector('input[name="recipient_type"]:checked').value;
                if (recipientType !== 'all') {
                    const select = document.querySelector(`#${recipientType}Select select`);
                    if (!select.value) {
                        e.preventDefault();
                        alert('Vui lòng chọn người nhận thông báo!');
                    }
                }
            });

            // Initialize select visibility based on current recipient type
            const checkedInput = document.querySelector('input[name="recipient_type"]:checked');
            if (checkedInput) {
                updateRecipientSelects(checkedInput.value);
            }

            // Close modal when clicking outside
            window.onclick = function(event) {
                if (event.target.className === 'modal') {
                    closeModal(event.target.id);
                }
            };

            // Close modal with close button
            document.querySelector('.close').onclick = function() {
                closeModal(this.closest('.modal').id);
            };
        });
    </script>
</body>
</html> 