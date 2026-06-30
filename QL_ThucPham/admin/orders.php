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

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = '';
    $messageType = '';

    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'update':
                    $data = [
                        'status' => $_POST['status'],
                        'notes' => $_POST['notes']
                    ];

                    if (handleCRUD('UPDATE', 'orders', $data, "order_id = " . $_POST['order_id'])) {
                        $message = "Đơn hàng đã được cập nhật thành công!";
                        $messageType = "success";
                    }
                    break;

                case 'delete':
                    $orderId = $_POST['order_id'];
                    
                    // Start transaction
                    $conn = getDBConnection();
                    $conn->begin_transaction();

                    try {
                        // Delete order details first
                        $stmt = $conn->prepare("DELETE FROM orderdetails WHERE order_id = ?");
                        $stmt->bind_param("i", $orderId);
                        $stmt->execute();

                        // Then delete the order
                        $stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
                        $stmt->bind_param("i", $orderId);
                        $stmt->execute();

                        $conn->commit();
                        $message = "Đơn hàng đã được xóa thành công!";
                        $messageType = "success";
                    } catch (Exception $e) {
                        $conn->rollback();
                        throw $e;
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
$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total number of orders
$conn = getDBConnection();
$total_query = "SELECT COUNT(*) as total FROM orders";
$total_result = $conn->query($total_query);
$total_orders = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_orders / $items_per_page);

// Get paginated orders with customer information
$query = "SELECT o.*, c.name as customer_name, c.email, c.phone_number,
          (SELECT COUNT(*) FROM orderdetails od WHERE od.order_id = o.order_id) as item_count
    FROM orders o
    JOIN customers c ON o.customer_id = c.customer_id
          ORDER BY o.created_at DESC
          LIMIT $offset, $items_per_page";
$orders = $conn->query($query);

if (!$orders) {
    die("Lỗi truy vấn: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đơn Hàng - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/admin-style.css">
    <link rel="icon" type="image/png" href="../images/Logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Override default styles to ensure proper layout */
        .admin-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            width: calc(100% - var(--sidebar-width));
            margin-left: var(--sidebar-width);
            padding: 2rem;
            background-color: var(--bg-light);
        }

        @media (max-width: 768px) {
            .main-content {
                width: 100%;
                margin-left: 0;
                padding: 1rem;
            }
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .status-badge::before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 5px;
        }

        .status-pending {
            background-color: #fff3e0;
            color: #f57c00;
        }
        .status-pending::before { background-color: #f57c00; }

        .status-processing {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        .status-processing::before { background-color: #1976d2; }

        .status-shipping {
            background-color: #e8f5e9;
            color: #388e3c;
        }
        .status-shipping::before { background-color: #388e3c; }

        .status-completed {
            background-color: #e8f5e9;
            color: #388e3c;
        }
        .status-completed::before { background-color: #388e3c; }

        .status-cancelled {
            background-color: #ffebee;
            color: #d32f2f;
        }
        .status-cancelled::before { background-color: #d32f2f; }

        .text-muted {
            color: #6c757d;
            font-size: 0.85em;
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
            padding: 20px;
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

        .order-details {
            padding: 20px;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .order-status {
            font-weight: bold;
            padding: 8px 15px;
            border-radius: 4px;
            background: rgba(0,0,0,0.1);
        }

        .order-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }

        .info-section h3 {
            margin-top: 0;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .order-items {
            margin-bottom: 20px;
        }

        .items-list {
            display: grid;
            gap: 15px;
        }

        .order-item {
            display: flex;
            gap: 15px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }

        .order-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
        }

        .item-details h4 {
            margin: 0 0 10px 0;
        }

        .item-details p {
            margin: 5px 0;
            color: #666;
        }

        .order-summary {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #ddd;
        }

        .summary-row.total {
            font-weight: bold;
            font-size: 1.1em;
            border-bottom: none;
        }

        .summary-row.discount {
            color: #e74c3c;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-top: 1px solid #ddd;
        }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-process { background: #3498db; color: white; }
        .btn-ship { background: #2ecc71; color: white; }
        .btn-complete { background: #27ae60; color: white; }
        .btn-cancel { background: #e74c3c; color: white; }
        .btn-secondary { background: #95a5a6; color: white; }

        .btn i {
            margin-right: 5px;
        }

        .loading {
            text-align: center;
            padding: 20px;
        }

        .error-message {
            text-align: center;
            color: #e74c3c;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Quản Lý Đơn Hàng</h2>
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
                                <th>Khách Hàng</th>
                                <th>Số Sản Phẩm</th>
                                <th>Tổng Tiền</th>
                                <th>Trạng Thái</th>
                                <th>Ngày Đặt</th>
                                <th>Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $orders->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $order['order_id']; ?></td>
                                    <td>
                                        <div><?php echo $order['customer_name']; ?></div>
                                        <small class="text-muted">
                                            <?php echo $order['email']; ?><br>
                                            <?php echo $order['phone_number']; ?>
                                        </small>
                                    </td>
                                    <td><?php echo $order['item_count']; ?></td>
                                    <td><?php echo number_format($order['total_amount'], 0, ',', '.') . 'đ'; ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                            <?php echo $order['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-info" onclick="viewOrderDetails(<?php echo $order['order_id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-warning" onclick="showEditForm(<?php echo htmlspecialchars(json_encode($order)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger" onclick="confirmDelete(<?php echo $order['order_id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
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

    <!-- Order Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle">Cập Nhật Đơn Hàng</h2>
            <form id="orderForm" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="order_id" id="orderId">

                <div class="form-group">
                    <label for="status">Trạng Thái:</label>
                    <select id="status" name="status" required>
                        <option value="Pending">Chờ xử lý</option>
                        <option value="Processing">Đang xử lý</option>
                        <option value="Shipping">Đang giao hàng</option>
                        <option value="Completed">Đã hoàn thành</option>
                        <option value="Cancelled">Đã hủy</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="notes">Ghi Chú:</label>
                    <textarea id="notes" name="notes"></textarea>
                </div>

                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('orderModal')">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="orderDetailsContent">
                <div class="loading">
                    <i class="fas fa-spinner fa-spin"></i> Đang tải...
                </div>
            </div>
            <div class="modal-actions" style="display: none;">
                <button type="button" class="btn btn-process" onclick="updateOrderStatus('Processing')">
                    <i class="fas fa-cog"></i> Xử lý đơn hàng
                </button>
                <button type="button" class="btn btn-ship" onclick="updateOrderStatus('Shipping')">
                    <i class="fas fa-truck"></i> Giao hàng
                </button>
                <button type="button" class="btn btn-complete" onclick="updateOrderStatus('Completed')">
                    <i class="fas fa-check-circle"></i> Hoàn thành
                </button>
                <button type="button" class="btn btn-cancel" onclick="updateOrderStatus('Cancelled')">
                    <i class="fas fa-times-circle"></i> Hủy đơn
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('orderDetailsModal')">
                    <i class="fas fa-times"></i> Đóng
                </button>
            </div>
        </div>
    </div>

    <script>
        // Modal handling
        function showModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function showEditForm(order) {
            document.getElementById('modalTitle').textContent = 'Cập Nhật Đơn Hàng #' + order.order_id;
            document.getElementById('orderId').value = order.order_id;
            document.getElementById('status').value = order.status;
            document.getElementById('notes').value = order.notes;
            showModal('orderModal');
        }

        function confirmDelete(orderId) {
            if (confirm('Bạn có chắc chắn muốn xóa đơn hàng này?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="order_id" value="${orderId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function viewOrderDetails(orderId) {
            const modal = document.getElementById('orderDetailsModal');
            const content = document.getElementById('orderDetailsContent');
            const actions = document.querySelector('.modal-actions');
            
            // Show loading state
            content.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>';
            actions.style.display = 'none';
            modal.style.display = "block";
            
            // Fetch order details
            fetch('get_order_details.php?order_id=' + orderId)
                .then(response => response.json())
                .then(data => {
                    const order = data.order;
                    const items = data.items;
                    const actions = data.actions;

                    let html = `
                        <div class="order-details">
                            <div class="order-header">
                                <h2>Chi tiết đơn hàng #${order.order_id}</h2>
                                <div class="order-status" style="color: ${order.status.color}">
                                    <i class="fas fa-${order.status.icon}"></i> ${order.status.text}
                                </div>
                            </div>

                            <div class="order-info">
                                <div class="info-section">
                                    <h3><i class="fas fa-info-circle"></i> Thông tin đơn hàng</h3>
                                    <p><strong>Ngày đặt:</strong> ${order.order_date}</p>
                                    <p><strong>Phương thức thanh toán:</strong> ${order.payment.method === 'cod' ? 'Thanh toán khi nhận hàng' : 'Chuyển khoản ngân hàng'}</p>
                                    ${order.notes ? `<p><strong>Ghi chú:</strong> ${order.notes}</p>` : ''}
                                </div>

                                <div class="info-section">
                                    <h3><i class="fas fa-user"></i> Thông tin khách hàng</h3>
                                    <p><strong>Tên:</strong> ${order.customer.name}</p>
                                    <p><strong>Email:</strong> ${order.customer.email}</p>
                                    <p><strong>Số điện thoại:</strong> ${order.customer.phone}</p>
                                    <p><strong>Địa chỉ giao hàng:</strong> ${order.customer.shipping_address}</p>
                                </div>
                            </div>

                            <div class="order-items">
                                <h3><i class="fas fa-shopping-cart"></i> Sản phẩm</h3>
                                <div class="items-list">`;

                    items.forEach(item => {
                        html += `
                            <div class="order-item">
                                <img src="${item.image_url}" alt="${item.name}" onerror="this.src='../images/no-image.jpg'">
                                <div class="item-details">
                                    <h4>${item.name}</h4>
                                    <p>Số lượng: ${item.quantity}</p>
                                    <p>Đơn giá: ${formatCurrency(item.unit_price)}</p>
                                    <p>Thành tiền: ${formatCurrency(item.subtotal)}</p>
                                </div>
                            </div>`;
                    });

                    html += `
                                </div>
                            </div>

                            <div class="order-summary">
                                <div class="summary-row">
                                    <span>Tạm tính:</span>
                                    <span>${formatCurrency(order.payment.subtotal)}</span>
                                </div>`;

                    if (order.payment.discount > 0) {
                        html += `
                                <div class="summary-row discount">
                                    <span>Giảm giá:</span>
                                    <span>-${formatCurrency(order.payment.discount)}</span>
                                </div>`;
                    }

                    if (order.payment.shipping_fee > 0) {
                        html += `
                                <div class="summary-row">
                                    <span>Phí vận chuyển:</span>
                                    <span>${formatCurrency(order.payment.shipping_fee)}</span>
                                </div>`;
                    }

                    html += `
                                <div class="summary-row total">
                                    <span>Tổng cộng:</span>
                                    <span>${formatCurrency(order.payment.total)}</span>
                                </div>
                            </div>
                        </div>`;

                    content.innerHTML = html;

                    // Show/hide action buttons based on order status
                    const actionsDiv = document.querySelector('.modal-actions');
                    actionsDiv.style.display = 'flex';
                    
                    document.querySelector('.btn-process').style.display = actions.can_process ? 'inline-block' : 'none';
                    document.querySelector('.btn-ship').style.display = actions.can_ship ? 'inline-block' : 'none';
                    document.querySelector('.btn-complete').style.display = actions.can_complete ? 'inline-block' : 'none';
                    document.querySelector('.btn-cancel').style.display = actions.can_cancel ? 'inline-block' : 'none';
                })
                .catch(error => {
                    content.innerHTML = `
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <p>Có lỗi xảy ra khi tải thông tin đơn hàng: ${error.message}</p>
                        </div>`;
                });
        }

        function updateOrderStatus(newStatus) {
            const orderId = document.querySelector('.order-details h2').textContent.match(/\d+/)[0];
            
            // Create form data
            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('order_id', orderId);
            formData.append('status', newStatus);

            // Send update request
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(() => {
                // Refresh order details
                viewOrderDetails(orderId);
                // Refresh the orders table
                location.reload();
            })
            .catch(error => {
                alert('Có lỗi xảy ra khi cập nhật trạng thái đơn hàng: ' + error.message);
            });
        }

        // Format currency function
        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount);
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
        }
        }

        // Close modals with close buttons
        document.querySelectorAll('.close').forEach(function(closeBtn) {
            closeBtn.onclick = function() {
                this.closest('.modal').style.display = 'none';
        }
        });
    </script>
</body>
</html> 