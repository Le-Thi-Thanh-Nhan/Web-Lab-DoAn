<?php
session_start();
require_once 'includes/db_helper.php';

// Check admin login
if (!isset($_SESSION['admin'])) {
    header('Location: ../auth/auth.php');
    exit;
}

// Initialize message variables
$message = '';
$messageType = '';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    $data = [
                        'code' => $_POST['code'],
                        'description' => $_POST['description'],
                        'discount_type' => $_POST['discount_type'],
                        'discount_value' => $_POST['discount_value'],
                        'min_order_value' => $_POST['min_order_value'] ?: null,
                        'max_discount' => $_POST['max_discount'] ?: null,
                        'start_date' => $_POST['start_date'],
                        'end_date' => $_POST['end_date'],
                        'usage_limit' => $_POST['usage_limit'] ?: null,
                        'is_active' => isset($_POST['is_active']) ? 1 : 0
                    ];

                    if (handleCRUD('CREATE', 'discount_codes', $data)) {
                        $message = "Mã giảm giá đã được thêm thành công!";
                        $messageType = "success";
                    }
                    break;

                case 'update':
                    $data = [
                        'code' => $_POST['code'],
                        'description' => $_POST['description'],
                        'discount_type' => $_POST['discount_type'],
                        'discount_value' => $_POST['discount_value'],
                        'min_order_value' => $_POST['min_order_value'] ?: null,
                        'max_discount' => $_POST['max_discount'] ?: null,
                        'start_date' => $_POST['start_date'],
                        'end_date' => $_POST['end_date'],
                        'usage_limit' => $_POST['usage_limit'] ?: null,
                        'is_active' => isset($_POST['is_active']) ? 1 : 0
                    ];

                    if (handleCRUD('UPDATE', 'discount_codes', $data, "code_id = " . $_POST['code_id'])) {
                        $message = "Mã giảm giá đã được cập nhật thành công!";
                        $messageType = "success";
                    }
                    break;

                case 'delete':
                    $code_id = $_POST['code_id'];
                    if (handleCRUD('DELETE', 'discount_codes', null, "code_id = $code_id")) {
                        $message = "Mã giảm giá đã được xóa thành công!";
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
$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total number of discount codes
$conn = getDBConnection();
$total_query = "SELECT COUNT(*) as total FROM discount_codes";
$total_result = $conn->query($total_query);
$total_codes = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_codes / $items_per_page);

// Get paginated discount codes
$query = "SELECT * FROM discount_codes ORDER BY created_at DESC LIMIT ?, ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $offset, $items_per_page);
$stmt->execute();
$discount_codes = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Mã Giảm Giá - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/admin-style.css">
    <link rel="icon" type="image/png" href="../images/Logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .status-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        
        .status-active {
            background-color: #28a745;
            color: white;
        }
        
        .status-inactive {
            background-color: #dc3545;
            color: white;
        }
        
        .status-expired {
            background-color: #6c757d;
            color: white;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .form-group {
            flex: 1;
        }
        
        .modal-content {
            max-width: 800px;
        }
        
        /* Enhanced Pagination Styling */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 20px 0;
            gap: 8px;
            padding: 15px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .pagination a, .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 35px;
            height: 35px;
            padding: 0 12px;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            color: #333;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .pagination a:hover {
            background: #e9ecef;
            border-color: #dee2e6;
            color: #0056b3;
        }

        .pagination .active {
            background: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
        }

        .pagination .disabled {
            background: #f8f9fa;
            border-color: #dee2e6;
            color: #6c757d;
            cursor: not-allowed;
            pointer-events: none;
        }

        .pagination i {
            font-size: 14px;
        }

        .pagination-info {
            text-align: center;
            color: #6c757d;
            margin-top: 10px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Quản Lý Mã Giảm Giá</h2>
                    <button class="btn btn-primary" onclick="showAddForm()">
                        <i class="fas fa-plus"></i> Thêm Mã Giảm Giá
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
                                <th>Mã</th>
                                <th>Mô Tả</th>
                                <th>Loại</th>
                                <th>Giá Trị</th>
                                <th>Thời Gian</th>
                                <th>Trạng Thái</th>
                                <th>Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($code = $discount_codes->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $code['code']; ?></td>
                                    <td><?php echo $code['description']; ?></td>
                                    <td>
                                        <?php 
                                        echo $code['discount_type'] === 'percentage' 
                                            ? 'Phần trăm' 
                                            : 'Số tiền cố định';
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($code['discount_type'] === 'percentage') {
                                            echo $code['discount_value'] . '%';
                                        } else {
                                            echo number_format($code['discount_value'], 0, ',', '.') . 'đ';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        echo date('d/m/Y', strtotime($code['start_date'])) . ' - ' . 
                                             date('d/m/Y', strtotime($code['end_date']));
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $now = new DateTime();
                                        $end_date = new DateTime($code['end_date']);
                                        $status_class = '';
                                        $status_text = '';
                                        
                                        if (!$code['is_active']) {
                                            $status_class = 'status-inactive';
                                            $status_text = 'Không kích hoạt';
                                        } elseif ($end_date < $now) {
                                            $status_class = 'status-expired';
                                            $status_text = 'Hết hạn';
                                        } else {
                                            $status_class = 'status-active';
                                            $status_text = 'Đang hoạt động';
                                        }
                                        ?>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <?php echo $status_text; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-warning" onclick="showEditForm(<?php echo htmlspecialchars(json_encode($code)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger" onclick="confirmDelete(<?php echo $code['code_id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Enhanced Pagination -->
                <div class="pagination-container">
                    <div class="pagination">
                        <?php if ($total_pages > 1): ?>
                            <?php if ($current_page > 1): ?>
                                <a href="?page=1" title="Trang đầu" class="pagination-btn">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                                <a href="?page=<?php echo $current_page - 1; ?>" title="Trang trước" class="pagination-btn">
                                    <i class="fas fa-angle-left"></i>
                                </a>
                            <?php else: ?>
                                <span class="disabled pagination-btn">
                                    <i class="fas fa-angle-double-left"></i>
                                </span>
                                <span class="disabled pagination-btn">
                                    <i class="fas fa-angle-left"></i>
                                </span>
                            <?php endif; ?>

                            <?php
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $current_page + 2);

                            if ($start_page > 1) {
                                echo '<span class="pagination-ellipsis">...</span>';
                            }

                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                                <a href="?page=<?php echo $i; ?>" 
                                   class="<?php echo ($i == $current_page) ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php
                            if ($end_page < $total_pages) {
                                echo '<span class="pagination-ellipsis">...</span>';
                            }
                            ?>

                            <?php if ($current_page < $total_pages): ?>
                                <a href="?page=<?php echo $current_page + 1; ?>" title="Trang sau" class="pagination-btn">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                                <a href="?page=<?php echo $total_pages; ?>" title="Trang cuối" class="pagination-btn">
                                    <i class="fas fa-angle-double-right"></i>
                                </a>
                            <?php else: ?>
                                <span class="disabled pagination-btn">
                                    <i class="fas fa-angle-right"></i>
                                </span>
                                <span class="disabled pagination-btn">
                                    <i class="fas fa-angle-double-right"></i>
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="pagination-info">
                        Hiển thị <?php echo ($offset + 1); ?> - 
                        <?php echo min($offset + $items_per_page, $total_codes); ?> 
                        trên tổng số <?php echo $total_codes; ?> mã giảm giá
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Discount Code Modal -->
    <div id="discountModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle">Thêm Mã Giảm Giá Mới</h2>
            <form id="discountForm" method="POST">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="code_id" id="codeId">

                <div class="form-row">
                    <div class="form-group">
                        <label for="code">Mã giảm giá:</label>
                        <input type="text" id="code" name="code" required>
                    </div>
                    <div class="form-group">
                        <label for="discount_type">Loại giảm giá:</label>
                        <select id="discount_type" name="discount_type" required>
                            <option value="percentage">Phần trăm</option>
                            <option value="fixed">Số tiền cố định</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="discount_value">Giá trị:</label>
                        <input type="number" id="discount_value" name="discount_value" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Mô tả:</label>
                    <textarea id="description" name="description" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="min_order_value">Giá trị đơn hàng tối thiểu:</label>
                        <input type="number" id="min_order_value" name="min_order_value">
                    </div>
                    <div class="form-group">
                        <label for="max_discount">Giảm giá tối đa:</label>
                        <input type="number" id="max_discount" name="max_discount">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="start_date">Ngày bắt đầu:</label>
                        <input type="datetime-local" id="start_date" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date">Ngày kết thúc:</label>
                        <input type="datetime-local" id="end_date" name="end_date" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="usage_limit">Giới hạn sử dụng:</label>
                        <input type="number" id="usage_limit" name="usage_limit">
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="is_active" name="is_active" checked>
                            Kích hoạt
                        </label>
                    </div>
                </div>

                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('discountModal');
        const closeBtn = document.getElementsByClassName('close')[0];
        const form = document.getElementById('discountForm');

        function showAddForm() {
            document.getElementById('modalTitle').textContent = 'Thêm Mã Giảm Giá Mới';
            document.getElementById('formAction').value = 'add';
            form.reset();
            modal.style.display = 'block';
        }

        function showEditForm(code) {
            document.getElementById('modalTitle').textContent = 'Cập Nhật Mã Giảm Giá';
            document.getElementById('formAction').value = 'update';
            document.getElementById('codeId').value = code.code_id;
            document.getElementById('code').value = code.code;
            document.getElementById('description').value = code.description;
            document.getElementById('discount_type').value = code.discount_type;
            document.getElementById('discount_value').value = code.discount_value;
            document.getElementById('min_order_value').value = code.min_order_value || '';
            document.getElementById('max_discount').value = code.max_discount || '';
            document.getElementById('start_date').value = code.start_date.slice(0, 16);
            document.getElementById('end_date').value = code.end_date.slice(0, 16);
            document.getElementById('usage_limit').value = code.usage_limit || '';
            document.getElementById('is_active').checked = code.is_active == 1;
            modal.style.display = 'block';
        }

        function closeModal() {
            modal.style.display = 'none';
        }

        function confirmDelete(codeId) {
            if (confirm('Bạn có chắc chắn muốn xóa mã giảm giá này?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="code_id" value="${codeId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }

        closeBtn.onclick = closeModal;

        form.onsubmit = function() {
            return true; // Add validation if needed
        }
    </script>
</body>
</html> 