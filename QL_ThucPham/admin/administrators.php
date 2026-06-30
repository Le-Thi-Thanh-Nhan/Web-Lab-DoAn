<?php
session_start();
require_once 'includes/db_helper.php';

// Initialize message variables
$message = '';
$messageType = '';

// Check if it's an AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    try {
        if (!isset($_SESSION['admin'])) {
            throw new Exception("Phiên đăng nhập đã hết hạn!");
        }

        if (isset($_POST['action'])) {
            $conn = getDBConnection();
            if (!$conn) {
                throw new Exception("Không thể kết nối đến cơ sở dữ liệu!");
            }
            $response = ['success' => false, 'message' => ''];

            switch ($_POST['action']) {
                case 'add':
                    // Validate required fields
                    if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['name']) || empty($_POST['email']) || empty($_POST['phone'])) {
                        throw new Exception("Vui lòng điền đầy đủ thông tin!");
                    }

                    // Validate email format
                    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                        throw new Exception("Email không hợp lệ!");
                    }

                    // Check if username exists
                    $stmt = $conn->prepare("SELECT admin_id FROM administrators WHERE username = ?");
                    $stmt->bind_param("s", $_POST['username']);
                    $stmt->execute();
                    if ($stmt->get_result()->num_rows > 0) {
                        throw new Exception("Tên đăng nhập đã tồn tại!");
                    }

                    // Check if email exists
                    $stmt = $conn->prepare("SELECT admin_id FROM administrators WHERE email = ?");
                    $stmt->bind_param("s", $_POST['email']);
                    $stmt->execute();
                    if ($stmt->get_result()->num_rows > 0) {
                        throw new Exception("Email đã được sử dụng!");
                    }

                    // Insert new admin
                    $stmt = $conn->prepare("INSERT INTO administrators (username, password, name, email, phone) VALUES (?, ?, ?, ?, ?)");
                    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt->bind_param("sssss", $_POST['username'], $hashedPassword, $_POST['name'], $_POST['email'], $_POST['phone']);
                    
                    if ($stmt->execute()) {
                        $response = [
                            'success' => true,
                            'message' => "Thêm quản trị viên thành công!",
                            'admin_id' => $conn->insert_id
                        ];
                    } else {
                        throw new Exception("Lỗi khi thêm quản trị viên: " . $stmt->error);
                    }
                    break;

                case 'update':
                    // Validate required fields
                    if (empty($_POST['admin_id']) || empty($_POST['name']) || empty($_POST['email']) || empty($_POST['phone'])) {
                        throw new Exception("Vui lòng điền đầy đủ thông tin!");
                    }

                    // Validate email format
                    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                        throw new Exception("Email không hợp lệ!");
                    }

                    // Check if email exists for other admins
                    $stmt = $conn->prepare("SELECT admin_id FROM administrators WHERE email = ? AND admin_id != ?");
                    $stmt->bind_param("si", $_POST['email'], $_POST['admin_id']);
                    $stmt->execute();
                    if ($stmt->get_result()->num_rows > 0) {
                        throw new Exception("Email đã được sử dụng!");
                    }

                    // Build update query
                    $query = "UPDATE administrators SET name = ?, email = ?, phone = ?";
                    $params = [$_POST['name'], $_POST['email'], $_POST['phone']];
                    $types = "sss";

                    // Add password to update if provided
                    if (!empty($_POST['password'])) {
                        $query .= ", password = ?";
                        $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        $params[] = $hashedPassword;
                        $types .= "s";
                    }

                    $query .= " WHERE admin_id = ?";
                    $params[] = $_POST['admin_id'];
                    $types .= "i";

                    $stmt = $conn->prepare($query);
                    $stmt->bind_param($types, ...$params);
                    
                    if ($stmt->execute()) {
                        $response = [
                            'success' => true,
                            'message' => "Cập nhật thông tin thành công!"
                        ];
                    } else {
                        throw new Exception("Lỗi khi cập nhật thông tin: " . $stmt->error);
                    }
                    break;

                case 'delete':
                    if (empty($_POST['admin_id'])) {
                        throw new Exception("ID quản trị viên không hợp lệ!");
                    }

                    if ($_POST['admin_id'] == $_SESSION['admin']['admin_id']) {
                        throw new Exception("Không thể xóa tài khoản đang đăng nhập!");
                    }

                    $stmt = $conn->prepare("DELETE FROM administrators WHERE admin_id = ?");
                    $stmt->bind_param("i", $_POST['admin_id']);
                    
                    if ($stmt->execute()) {
                        $response = [
                            'success' => true,
                            'message' => "Xóa quản trị viên thành công!"
                        ];
                    } else {
                        throw new Exception("Lỗi khi xóa quản trị viên: " . $stmt->error);
                    }
                    break;

                default:
                    throw new Exception("Hành động không hợp lệ!");
            }

            echo json_encode($response);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

// Check admin login for regular page load
if (!isset($_SESSION['admin'])) {
    header('Location: ../auth/auth.php');
    exit;
}

// Pagination settings
$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total number of administrators
$conn = getDBConnection();
if (!$conn) {
    $message = "Không thể kết nối đến cơ sở dữ liệu!";
    $messageType = "danger";
} else {
    $total_query = "SELECT COUNT(*) as total FROM administrators";
    $total_result = $conn->query($total_query);
    if (!$total_result) {
        $message = "Lỗi khi truy vấn dữ liệu: " . $conn->error;
        $messageType = "danger";
    } else {
        $total_administrators = $total_result->fetch_assoc()['total'];
        $total_pages = ceil($total_administrators / $items_per_page);

        // Get paginated administrators
        $query = "SELECT * FROM administrators ORDER BY created_at DESC LIMIT $offset, $items_per_page";
        $administrators = $conn->query($query);
        if (!$administrators) {
            $message = "Lỗi khi truy vấn dữ liệu: " . $conn->error;
            $messageType = "danger";
        }
    }
}

// Define admin roles
$adminRoles = [
    'super_admin' => 'Super Admin',
    'admin' => 'Admin',
    'editor' => 'Editor'
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Admin - Admin Panel</title>
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

        .role-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
        }
        
        .role-badge.super_admin {
            background-color: rgba(155, 89, 182, 0.1);
            color: #9b59b6;
            border: 1px solid #9b59b6;
        }
        
        .role-badge.admin {
            background-color: rgba(52, 152, 219, 0.1);
            color: var(--accent-color);
            border: 1px solid var(--accent-color);
        }
        
        .role-badge.editor {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success-color);
            border: 1px solid var(--success-color);
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

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.875rem;
        }

        .modal-content {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            position: relative;
            margin: 20px auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.1);
            outline: none;
        }

        .form-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            display: block;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        .btn-group button {
            flex: 1;
            padding: 12px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-secondary {
            background: #e9ecef;
            border: 1px solid #ddd;
            color: #2c3e50;
        }

        .btn-secondary:hover {
            background: #dee2e6;
        }

        .btn-primary {
            background: #4CAF50;
            border: 1px solid #43A047;
            color: white;
        }

        .btn-primary:hover {
            background: #43A047;
        }

        .btn-primary:disabled {
            background: #a5d6a7;
            cursor: not-allowed;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 24px;
            color: #666;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: #2c3e50;
        }

        #modalTitle {
            margin: 0 0 25px 0;
            color: #2c3e50;
            font-size: 1.5em;
        }

        .alert {
            padding: 15px;
            margin: 15px 0;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .alert i {
            font-size: 1.2em;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Quản Lý Admin</h2>
                    <button class="btn btn-primary" onclick="showAddForm()">
                        <i class="fas fa-plus"></i> Thêm Admin
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
                                <th>Tên</th>
                                <th>Tên đăng nhập</th>
                                <th>Email</th>
                                <th>Số điện thoại</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($admin = $administrators->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $admin['admin_id']; ?></td>
                                    <td><?php echo $admin['name']; ?></td>
                                    <td><?php echo $admin['username']; ?></td>
                                    <td><?php echo $admin['email']; ?></td>
                                    <td><?php echo isset($admin['phone']) ? $admin['phone'] : 'N/A'; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($admin['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm" onclick='showEditForm(<?php echo json_encode($admin); ?>)'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($admin['admin_id'] != $_SESSION['admin']['admin_id']): ?>
                                            <button class="btn btn-danger btn-sm" onclick="deleteAdmin(<?php echo $admin['admin_id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
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

    <!-- Add/Edit Admin Modal -->
    <div id="adminModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle">Thêm Admin Mới</h2>
            <form id="adminForm" method="POST">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="admin_id" id="adminId">

                <div class="form-group">
                    <label for="name">Tên:</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="username">Tên đăng nhập:</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Mật khẩu:</label>
                    <input type="password" id="password" name="password">
                    <small class="form-text text-muted">Để trống nếu không muốn thay đổi mật khẩu</small>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="phone">Số điện thoại:</label>
                    <input type="tel" id="phone" name="phone" required>
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
        const modal = document.getElementById('adminModal');
        const span = document.getElementsByClassName('close')[0];
        
        span.onclick = function() {
            modal.style.display = "none";
        }
        
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        function showAddForm() {
            document.getElementById('modalTitle').textContent = 'Thêm Admin Mới';
            document.getElementById('formAction').value = 'add';
            document.getElementById('adminForm').reset();
            document.getElementById('username').readOnly = false;
            document.getElementById('password').required = true;
            modal.style.display = "block";
        }

        function showEditForm(admin) {
            document.getElementById('modalTitle').textContent = 'Cập Nhật Thông Tin Admin';
            document.getElementById('formAction').value = 'update';
            document.getElementById('adminId').value = admin.admin_id;
            document.getElementById('name').value = admin.name;
            document.getElementById('username').value = admin.username;
            document.getElementById('username').readOnly = true;
            document.getElementById('password').required = false;
            document.getElementById('email').value = admin.email;
            document.getElementById('phone').value = admin.phone || '';
            
            modal.style.display = "block";
        }

        function closeModal() {
            modal.style.display = "none";
            document.getElementById('adminForm').reset();
        }

        // Form submission handling
        document.getElementById('adminForm').onsubmit = async function(e) {
            e.preventDefault();
            
            try {
                const submitButton = this.querySelector('button[type="submit"]');
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

                const formData = new FormData(this);

                // Client-side validation
                const requiredFields = {
                    'name': 'Tên',
                    'email': 'Email',
                    'phone': 'Số điện thoại'
                };

                if (formData.get('action') === 'add') {
                    requiredFields['username'] = 'Tên đăng nhập';
                    requiredFields['password'] = 'Mật khẩu';
                }

                for (const [field, label] of Object.entries(requiredFields)) {
                    if (!formData.get(field)?.trim()) {
                        throw new Error(`Vui lòng nhập ${label}`);
                    }
                }

                // Validate email format
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(formData.get('email'))) {
                    throw new Error('Email không hợp lệ');
                }

                // Validate phone number format (Vietnam)
                const phoneRegex = /^(0|\+84)(\s|\.)?((3[2-9])|(5[689])|(7[06-9])|(8[1-689])|(9[0-46-9]))(\d)(\s|\.)?(\d{3})(\s|\.)?(\d{3})$/;
                if (!phoneRegex.test(formData.get('phone'))) {
                    throw new Error('Số điện thoại không hợp lệ. Vui lòng nhập số điện thoại Việt Nam hợp lệ');
                }

                // Submit form with AJAX header
                const response = await fetch('administrators.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server response was not JSON');
                }

                const result = await response.json();
                
                if (result.success) {
                    // Show success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success';
                    alert.innerHTML = `<i class="fas fa-check-circle"></i> ${result.message}`;
                    document.querySelector('.card-header').insertAdjacentElement('afterend', alert);

                    // Close modal and reload page after delay
                    setTimeout(() => {
                        closeModal();
                        location.reload();
                    }, 1500);
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                // Show error message
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger';
                alert.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${error.message}`;
                this.insertBefore(alert, this.firstChild);

                // Remove error message after 3 seconds
                setTimeout(() => alert.remove(), 3000);
            } finally {
                // Reset submit button
                const submitButton = this.querySelector('button[type="submit"]');
                submitButton.disabled = false;
                submitButton.innerHTML = 'Lưu';
            }
        };

        async function deleteAdmin(adminId) {
            if (!confirm('Bạn có chắc chắn muốn xóa quản trị viên này?')) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('admin_id', adminId);

                const response = await fetch('administrators.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server response was not JSON');
                }

                const result = await response.json();
                
                if (result.success) {
                    // Show success message and reload
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success';
                    alert.innerHTML = `<i class="fas fa-check-circle"></i> ${result.message}`;
                    document.querySelector('.card-header').insertAdjacentElement('afterend', alert);

                    setTimeout(() => location.reload(), 1500);
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                // Show error message
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger';
                alert.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${error.message}`;
                document.querySelector('.card-header').insertAdjacentElement('afterend', alert);

                // Remove error message after 3 seconds
                setTimeout(() => alert.remove(), 3000);
            }
        }
    </script>
</body>
</html> 