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

// Pagination settings
$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total number of customers
$conn = getDBConnection();
$total_query = "SELECT COUNT(*) as total FROM customers";
$total_result = $conn->query($total_query);
$total_customers = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_customers / $items_per_page);

// Get paginated customers with their order statistics
$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM orders o WHERE o.customer_id = c.customer_id) as order_count,
          (SELECT SUM(total_amount) FROM orders o WHERE o.customer_id = c.customer_id) as total_spent
          FROM customers c 
          ORDER BY c.created_at DESC
          LIMIT $offset, $items_per_page";
$customers = $conn->query($query);

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = '';
    $messageType = '';

    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    $data = [
                        'username' => $_POST['username'],
                        'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                        'name' => $_POST['name'],
                        'email' => $_POST['email'],
                        'phone_number' => $_POST['phone_number'],
                        'address' => $_POST['address']
                    ];

                    if (handleCRUD('CREATE', 'customers', $data)) {
                        $message = "Khách hàng đã được thêm thành công!";
                        $messageType = "success";
                    }
                    break;

                case 'update':
                    $data = [
                        'name' => $_POST['name'],
                        'email' => $_POST['email'],
                        'phone_number' => $_POST['phone_number'],
                        'address' => $_POST['address']
                    ];

                    // Only update password if a new one is provided
            if (!empty($_POST['password'])) {
                        $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    }

                    if (handleCRUD('UPDATE', 'customers', $data, "customer_id = " . $_POST['customer_id'])) {
                        $message = "Thông tin khách hàng đã được cập nhật thành công!";
                        $messageType = "success";
                    }
                    break;

                case 'delete':
                    $customerId = $_POST['customer_id'];
                    
                    // Check if customer has orders
                    $conn = getDBConnection();
                    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE customer_id = ?");
                    $stmt->bind_param("i", $customerId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $count = $result->fetch_assoc()['count'];

                    if ($count > 0) {
                        $message = "Không thể xóa khách hàng này vì có đơn hàng!";
                        $messageType = "danger";
            } else {
                        if (handleCRUD('DELETE', 'customers', null, "customer_id = $customerId")) {
                            $message = "Khách hàng đã được xóa thành công!";
                            $messageType = "success";
            }
        }
                    break;
            }
        }
    } catch (Exception $e) {
        $message = "Lỗi: " . $e->getMessage();
        $messageType = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Khách Hàng - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/admin-style.css">
    <link rel="icon" type="image/png" href="../images/Logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Override default styles to ensure proper layout */
        .admin-container {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        .main-content {
            flex: 1;
            width: calc(100% - var(--sidebar-width));
            margin-left: var(--sidebar-width);
            padding: 20px;
            background-color: var(--bg-light);
        }

        @media (max-width: 768px) {
            .main-content {
                width: 100%;
                margin-left: 0;
                padding: 15px;
            }
        }

        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
            margin: 20px 0;
        }

        .table-container table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-container th {
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid #eee;
        }

        .table-container td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            color: #444;
        }

        .table-container tbody tr:hover {
            background-color: #f8f9fa;
        }

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

        .btn-group {
            display: flex;
            gap: 5px;
        }

        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-warning {
            background-color: #ffc107;
            color: #000;
            }

        .btn-danger {
            background-color: #dc3545;
            color: white;
            }

        .btn:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Quản Lý Khách Hàng</h2>
                    <button class="btn btn-primary" onclick="showAddForm()">
                        <i class="fas fa-plus"></i> Thêm Khách Hàng
                    </button>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên Khách Hàng</th>
                                <th>Email</th>
                                <th>Số Điện Thoại</th>
                                <th>Địa Chỉ</th>
                                <th>Số Đơn Hàng</th>
                                <th>Tổng Chi Tiêu</th>
                                <th>Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                    <?php while ($customer = $customers->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $customer['customer_id']; ?></td>
                                    <td><?php echo $customer['name']; ?></td>
                                    <td><?php echo $customer['email']; ?></td>
                                    <td><?php echo $customer['phone_number']; ?></td>
                                    <td><?php echo $customer['address']; ?></td>
                                    <td><?php echo $customer['order_count']; ?></td>
                                    <td><?php echo number_format($customer['total_spent'] ?? 0, 0, ',', '.') . 'đ'; ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-warning" onclick="showEditForm(<?php echo htmlspecialchars(json_encode($customer)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                            <button class="btn btn-danger" onclick="confirmDelete(<?php echo $customer['customer_id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($current_page > 1): ?>
                            <a href="?page=1" title="Trang đầu"><i class="fas fa-angle-double-left"></i></a>
                            <a href="?page=<?php echo $current_page - 1; ?>" title="Trang trước"><i class="fas fa-angle-left"></i></a>
                        <?php else: ?>
                            <span class="disabled"><i class="fas fa-angle-double-left"></i></span>
                            <span class="disabled"><i class="fas fa-angle-left"></i></span>
                        <?php endif; ?>

                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);

                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <?php if ($i == $current_page): ?>
                                <span class="active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages): ?>
                            <a href="?page=<?php echo $current_page + 1; ?>" title="Trang sau"><i class="fas fa-angle-right"></i></a>
                            <a href="?page=<?php echo $total_pages; ?>" title="Trang cuối"><i class="fas fa-angle-double-right"></i></a>
                        <?php else: ?>
                            <span class="disabled"><i class="fas fa-angle-right"></i></span>
                            <span class="disabled"><i class="fas fa-angle-double-right"></i></span>
                                <?php endif; ?>
                            </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Customer Modal -->
    <div id="customerModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle">Thêm Khách Hàng Mới</h2>
            <form id="customerForm" method="POST">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="customer_id" id="customerId">

                <div class="form-group">
                    <label for="username">Tên Đăng Nhập:</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Mật Khẩu:</label>
                    <input type="password" id="password" name="password">
                    <small class="text-muted">Để trống nếu không muốn thay đổi mật khẩu</small>
                </div>

                <div class="form-group">
                    <label for="name">Họ Tên:</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="phone_number">Số Điện Thoại:</label>
                    <input type="text" id="phone_number" name="phone_number">
                </div>

                <div class="form-group">
                    <label for="address">Địa Chỉ:</label>
                    <textarea id="address" name="address"></textarea>
                </div>

                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal handling
        const modal = document.getElementById('customerModal');
        const closeBtn = document.getElementsByClassName('close')[0];
        const form = document.getElementById('customerForm');

        function showAddForm() {
            document.getElementById('modalTitle').textContent = 'Thêm Khách Hàng Mới';
            document.getElementById('formAction').value = 'add';
            document.getElementById('username').disabled = false;
            document.getElementById('password').required = true;
            form.reset();
            modal.style.display = 'block';
        }

        function showEditForm(customer) {
            document.getElementById('modalTitle').textContent = 'Cập Nhật Khách Hàng';
            document.getElementById('formAction').value = 'update';
            document.getElementById('customerId').value = customer.customer_id;
            document.getElementById('username').value = customer.username;
            document.getElementById('username').disabled = true;
            document.getElementById('password').required = false;
            document.getElementById('name').value = customer.name;
            document.getElementById('email').value = customer.email;
            document.getElementById('phone_number').value = customer.phone_number;
            document.getElementById('address').value = customer.address;
            modal.style.display = 'block';
        }

        function closeModal() {
            modal.style.display = 'none';
        }

        function confirmDelete(customerId) {
            if (confirm('Bạn có chắc chắn muốn xóa khách hàng này?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="customer_id" value="${customerId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }

        // Close modal with close button
        closeBtn.onclick = closeModal;

        // Handle form submission
        form.onsubmit = function() {
            return true; // Add any validation if needed
        }
    </script>
</body>
</html> 