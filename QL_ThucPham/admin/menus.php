<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: ../auth/auth.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý Menu</title>
    <link rel="icon" type="image/png" href="../images/Logo.png">
    <!-- AdminLTE + Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Override default styles to ensure proper layout */
        .wrapper {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        .content-wrapper {
            flex: 1;
            width: calc(100% - var(--sidebar-width));
            margin-left: var(--sidebar-width);
            padding: 20px;
            background-color: var(--bg-light);
        }

        @media (max-width: 768px) {
            .content-wrapper {
                width: 100%;
                margin-left: 0;
                padding: 15px;
            }
        }

        .menu-preview {
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .menu-preview .preview-section {
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
        }
        .menu-preview .top-menu-section {
            background: #4CAF50;
        }
        .menu-preview .slide-menu-section {
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .menu-preview .slide-menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            background: #4CAF50;
            color: white;
        }
        .menu-preview .slide-menu-header h3 {
            margin: 0;
            font-size: 1.1em;
        }
        .menu-preview .slide-menu-user {
            padding: 15px;
            border-bottom: 1px solid #eee;
            background: #f8f9fa;
        }
        .menu-preview .user-details {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .menu-preview .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .menu-preview .user-avatar i {
            font-size: 20px;
            color: #6c757d;
        }
        .menu-preview .user-info-text {
            flex: 1;
        }
        .menu-preview .user-name {
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 2px;
            font-size: 14px;
        }
        .menu-preview .user-email {
            color: #6c757d;
            font-size: 12px;
        }
        .menu-preview .top-menu {
            background: #4CAF50;
            padding: 10px 0;
        }
        .menu-preview .top-menu ul {
            list-style: none;
            display: flex;
            justify-content: center;
            gap: 5px;
            padding: 0;
            margin: 0;
        }
        .menu-preview .top-menu ul li a {
            color: #FFFFFF;
            text-decoration: none;
            padding: 12px 18px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        .menu-preview .top-menu ul li a:hover {
            background: #388E3C;
        }
        .menu-preview .top-menu ul li a i {
            font-size: 14px;
        }
        .menu-preview .slide-menu-preview {
            padding: 8px 0;
        }
        .menu-preview .nav-section {
            margin-bottom: 8px;
        }
        .menu-preview .nav-section-title {
            padding: 8px 15px;
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }
        .menu-preview .nav-item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            color: #2c3e50;
            text-decoration: none;
            transition: 0.2s;
            font-size: 14px;
        }
        .menu-preview .nav-item:hover {
            background: #f8f9fa;
            color: #1B5E20;
        }
        .menu-preview .nav-item i {
            width: 20px;
            margin-right: 8px;
            font-size: 14px;
        }
        .menu-preview .nav-divider {
            height: 1px;
            background: #eee;
            margin: 8px 0;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            min-width: 80px;
            text-align: center;
        }
        .status-active {
            background-color: #28a745;
            color: white;
        }
        .status-inactive {
            background-color: #dc3545;
            color: white;
        }
        .modal {
            background: rgba(0, 0, 0, 0.5);
        }
        .modal-backdrop {
            display: none;
        }
        .modal-dialog {
            margin: 1.75rem auto;
        }
        .modal-content {
            position: relative;
            z-index: 1050;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-tools {
            margin-left: auto;
        }

        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .toast {
            background: white;
            border-radius: 4px;
            padding: 16px 20px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 300px;
            transform: translateX(120%);
            transition: transform 0.3s ease;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast.success {
            border-left: 4px solid #28a745;
        }

        .toast.success i {
            color: #28a745;
        }

        .toast.error {
            border-left: 4px solid #dc3545;
        }

        .toast.error i {
            color: #dc3545;
        }

        .toast-message {
            flex: 1;
            font-size: 14px;
            color: #333;
        }

        .toast i {
            font-size: 20px;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
    <!-- Toast Container -->
    <div class="toast-container"></div>

    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <h1>Quản lý Menu</h1>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <!-- Menu Preview -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Xem trước Menu</h3>
                        </div>
                        <div class="card-body">
                            <div class="menu-preview">
                                <!-- Top Menu Preview -->
                                <div class="preview-section top-menu-section">
                                    <nav class="top-menu">
                                        <ul>
                                            <?php
                                            require_once(__DIR__ . '/../config/connect.php');
                                            $sql = "SELECT * FROM menus WHERE menu_type = 'top_menu' AND status = 1 ORDER BY position";
                                            $result = mysqli_query($conn, $sql);
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                echo '<li><a href="' . $row['url'] . '">';
                                                echo '<i class="' . $row['icon'] . '"></i> ' . $row['name'];
                                                echo '</a></li>';
                                            }
                                            ?>
                                        </ul>
                                    </nav>
                                </div>

                                <!-- Slide Menu Preview -->
                                <div class="preview-section slide-menu-section">
                                    <div class="slide-menu-header">
                                        <h3>Menu</h3>
                                        <button class="slide-menu-close" style="background:none;border:none;color:white;font-size:22px;">&times;</button>
                                    </div>
                                    
                                    <div class="slide-menu-user">
                                        <div class="user-details">
                                            <div class="user-avatar">
                                                <i class="fas fa-user-circle"></i>
                                            </div>
                                            <div class="user-info-text">
                                                <div class="user-name">Người dùng mẫu</div>
                                                <div class="user-email">user@example.com</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="slide-menu-preview">
                                        <?php
                                        // Get parent menus first
                                        $sql = "SELECT * FROM menus WHERE menu_type = 'slide_menu' AND parent_id IS NULL AND status = 1 ORDER BY position";
                                        $result = mysqli_query($conn, $sql);
                                        
                                        // Main section
                                        echo '<div class="nav-section">';
                                        echo '<div class="nav-section-title">Menu chính</div>';
                                        while ($parent = mysqli_fetch_assoc($result)) {
                                            echo '<a href="' . $parent['url'] . '" class="nav-item">';
                                            echo '<i class="' . $parent['icon'] . '"></i>' . $parent['name'];
                                            echo '</a>';
                                            
                                            // Get child menus
                                            $sql_children = "SELECT * FROM menus WHERE parent_id = {$parent['id']} AND status = 1 ORDER BY position";
                                            $result_children = mysqli_query($conn, $sql_children);
                                            while ($child = mysqli_fetch_assoc($result_children)) {
                                                echo '<a href="' . $child['url'] . '" class="nav-item" style="padding-left: 43px;">';
                                                echo '<i class="' . $child['icon'] . '"></i>' . $child['name'];
                                                echo '</a>';
                                            }
                                        }
                                        echo '</div>';
                                        
                                        // Logout section
                                        echo '<div class="nav-divider"></div>';
                                        echo '<a href="../auth/logout.php" class="nav-item" style="color: #dc3545;"><i class="fas fa-sign-out-alt"></i>Đăng xuất</a>';
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Menu List -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Danh sách Menu</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addMenuModal">
                                    <i class="fas fa-plus"></i> Thêm Menu
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tên</th>
                                            <th>URL</th>
                                            <th>Icon</th>
                                            <th>Loại</th>
                                            <th>Menu cha</th>
                                            <th>Vị trí</th>
                                            <th>Trạng thái</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT m1.*, m2.name as parent_name 
                                               FROM menus m1 
                                               LEFT JOIN menus m2 ON m1.parent_id = m2.id 
                                               ORDER BY m1.menu_type, m1.position";
                                        $result = mysqli_query($conn, $sql);
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo '<tr>';
                                            echo '<td>' . $row['id'] . '</td>';
                                            echo '<td>' . $row['name'] . '</td>';
                                            echo '<td>' . $row['url'] . '</td>';
                                            echo '<td><i class="' . $row['icon'] . '"></i> ' . $row['icon'] . '</td>';
                                            echo '<td>' . ($row['menu_type'] == 'top_menu' ? 'Top Menu' : 'Slide Menu') . '</td>';
                                            echo '<td>' . ($row['parent_name'] ? $row['parent_name'] : '-') . '</td>';
                                            echo '<td>' . $row['position'] . '</td>';
                                            echo '<td><span class="status-badge ' . ($row['status'] ? 'status-active' : 'status-inactive') . '">' 
                                                . ($row['status'] ? 'Hoạt động' : 'Không hoạt động') . '</span></td>';
                                            echo '<td>
                                                <button class="btn btn-info btn-sm edit-menu" data-id="' . $row['id'] . '">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm delete-menu" data-id="' . $row['id'] . '">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>';
                                            echo '</tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Add/Edit Menu Modal -->
    <div class="modal fade" id="addMenuModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modalTitle">Thêm Menu</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="menuForm" method="POST">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Tên Menu</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="url">URL</label>
                            <input type="text" class="form-control" id="url" name="url" required>
                        </div>
                        <div class="form-group">
                            <label for="icon">Icon (Font Awesome class)</label>
                            <input type="text" class="form-control" id="icon" name="icon" required>
                        </div>
                        <div class="form-group">
                            <label for="menu_type">Loại Menu</label>
                            <select class="form-control" id="menu_type" name="menu_type">
                                <option value="top_menu">Top Menu</option>
                                <option value="slide_menu">Slide Menu</option>
                            </select>
                        </div>
                        <div class="form-group parent-menu-group" style="display: none;">
                            <label for="parent_id">Menu cha (cho Slide Menu)</label>
                            <select class="form-control" id="parent_id" name="parent_id">
                                <option value="">Không có</option>
                                <?php
                                $sql = "SELECT * FROM menus WHERE menu_type = 'slide_menu' AND parent_id IS NULL ORDER BY position";
                                $result = mysqli_query($conn, $sql);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="position">Vị trí</label>
                            <input type="number" class="form-control" id="position" name="position" required min="1">
                        </div>
                        <div class="form-group">
                            <label for="status">Trạng thái</label>
                            <select class="form-control" id="status" name="status">
                                <option value="1">Hoạt động</option>
                                <option value="0">Không hoạt động</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Core JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

    <script>
        // Toast notification function
        function showToast(message, type = 'success') {
            const toastContainer = document.querySelector('.toast-container');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            const icon = type === 'success' ? 'check-circle' : 'times-circle';
            toast.innerHTML = `
                <i class="fas fa-${icon}"></i>
                <div class="toast-message">${message}</div>
            `;
            
            toastContainer.appendChild(toast);
            
            // Trigger reflow and add show class
            setTimeout(() => toast.classList.add('show'), 10);
            
            // Remove toast after 5 seconds
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }

        // Handle form submission
        $('#menuForm').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            
            $.ajax({
                url: 'includes/menu_actions.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        showToast('Thao tác thành công!');
                        $('#addMenuModal').modal('hide');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast(response.message || 'Có lỗi xảy ra!', 'error');
                    }
                },
                error: function() {
                    showToast('Đã xảy ra lỗi!', 'error');
                }
            });
        });

        // Handle menu deletion
        $('.delete-menu').click(function() {
            if (confirm('Bạn có chắc chắn muốn xóa menu này?')) {
                var id = $(this).data('id');
                
                $.ajax({
                    url: 'includes/menu_actions.php',
                    type: 'POST',
                    data: {
                        action: 'delete',
                        id: id
                    },
                    success: function(response) {
                        if (response.success) {
                            showToast('Xóa menu thành công!');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showToast(response.message || 'Có lỗi xảy ra khi xóa!', 'error');
                        }
                    },
                    error: function() {
                        showToast('Đã xảy ra lỗi!', 'error');
                    }
                });
            }
        });

        // Handle menu editing
        $('.edit-menu').click(function() {
            var id = $(this).data('id');
            
            $.ajax({
                url: 'includes/menu_actions.php',
                type: 'GET',
                data: {
                    action: 'get',
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        var menu = response.data;
                        $('#menuForm [name="name"]').val(menu.name);
                        $('#menuForm [name="url"]').val(menu.url);
                        $('#menuForm [name="icon"]').val(menu.icon);
                        $('#menuForm [name="menu_type"]').val(menu.menu_type);
                        $('#menuForm [name="parent_id"]').val(menu.parent_id);
                        $('#menuForm [name="position"]').val(menu.position);
                        $('#menuForm [name="status"]').val(menu.status);
                        $('#menuForm').append('<input type="hidden" name="id" value="' + menu.id + '">');
                        $('#menuForm').append('<input type="hidden" name="action" value="update">');
                        $('.modal-title').text('Sửa Menu');
                        $('#addMenuModal').modal('show');
                    } else {
                        showToast(response.message || 'Có lỗi xảy ra khi tải thông tin!', 'error');
                    }
                },
                error: function() {
                    showToast('Đã xảy ra lỗi!', 'error');
                }
            });
        });

        // Show/hide parent menu field based on menu type
        $('#menu_type').change(function() {
            if ($(this).val() === 'slide_menu') {
                $('.parent-menu-group').show();
            } else {
                $('.parent-menu-group').hide();
                $('#parent_id').val('');
            }
        });

        // Clear form when modal is closed
        $('#addMenuModal').on('hidden.bs.modal', function() {
            $('#menuForm').trigger('reset');
            $('#menuForm input[name="id"]').remove();
            $('#menuForm input[name="action"]').remove();
            $('.modal-title').text('Thêm Menu');
        });

        // Initialize section visibility
        $('#menu_type').trigger('change');
    </script>
</body>
</html>