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
    <title>Quản lý Footer</title>
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

        /* Enhanced Card Styles */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            background: white;
            overflow: hidden;
        }

        .card-header {
            background: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            color: #2c3e50;
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Enhanced Table Styles */
        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background-color: #2c3e50;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            padding: 1rem 1.5rem;
            border: none;
            vertical-align: middle;
        }

        .table tbody td {
            padding: 1.2rem 1.5rem;
            vertical-align: middle;
            border-bottom: 1px solid #eee;
            color: #2c3e50;
            font-size: 0.95rem;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* Status Badge Styles */
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .status-active {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .status-inactive {
            background-color: #ffebee;
            color: #c62828;
        }

        /* Button Styles */
        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #4CAF50;
            border: none;
            color: white;
        }

        .btn-primary:hover {
            background: #43A047;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.2);
        }

        .btn-info {
            background: #03A9F4;
            border: none;
            color: white;
        }

        .btn-info:hover {
            background: #039BE5;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(3, 169, 244, 0.2);
        }

        .btn-danger {
            background: #F44336;
            border: none;
            color: white;
        }

        .btn-danger:hover {
            background: #E53935;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(244, 67, 54, 0.2);
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.875rem;
        }

        /* Action Buttons Container */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }

        /* Footer Preview Styles */
        :root {
            --footer-bg-start: #1B5E20;
            --footer-bg-end: #0A2A0C;
            --footer-heading: #81C784;
            --footer-text: #E8F5E9;
            --footer-link-hover: #4CAF50;
            --footer-divider: rgba(255,255,255,0.1);
            --social-facebook: #1877F2;
            --social-instagram: #E4405F;
            --social-youtube: #FF0000;
            --social-tiktok: #000000;
        }

        .footer-preview {
            background: linear-gradient(135deg, var(--footer-bg-start), var(--footer-bg-end));
            color: var(--footer-text);
            padding: 30px 20px 15px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            transform: scale(0.9);
            transform-origin: top center;
        }

        .footer-preview::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.2), transparent);
        }

        .footer-preview .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
            position: relative;
        }

        .footer-preview .footer-sections {
            display: flex;
            flex-wrap: nowrap;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 25px;
        }

        .footer-preview .footer-section {
            flex: 1;
            min-width: 180px;
            padding: 0 10px;
        }

        .footer-preview .footer-section h3 {
            color: var(--footer-heading);
            font-size: 16px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .footer-preview .footer-section h3 i {
            color: var(--footer-heading);
            font-size: 18px;
        }

        .footer-preview .footer-section p {
            color: var(--footer-text);
            line-height: 1.5;
            margin-bottom: 12px;
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .footer-preview .footer-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-preview .footer-section ul li {
            margin-bottom: 10px;
        }

        .footer-preview .footer-section ul li a {
            color: var(--footer-text);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .footer-preview .footer-section ul li a:hover {
            color: var(--footer-link-hover);
            transform: translateX(5px);
            opacity: 1;
        }

        .footer-preview .team-info {
            margin-top: 12px;
        }

        .footer-preview .team-member p {
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .footer-preview .team-member i {
            width: 18px;
            color: var(--footer-heading);
        }

        .footer-preview .social-links {
            display: flex;
            gap: 12px;
            margin-top: 15px;
        }

        .footer-preview .social-link {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--footer-text);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .footer-preview .social-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.2), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .footer-preview .social-link:hover::before {
            opacity: 1;
        }

        .footer-preview .social-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .footer-preview .social-link.facebook:hover {
            background: var(--social-facebook);
        }

        .footer-preview .social-link.instagram:hover {
            background: var(--social-instagram);
        }

        .footer-preview .social-link.youtube:hover {
            background: var(--social-youtube);
        }

        .footer-preview .social-link.tiktok:hover {
            background: var(--social-tiktok);
        }

        .footer-preview .footer-bottom {
            text-align: center;
            padding-top: 15px;
            border-top: 1px solid var(--footer-divider);
            position: relative;
            margin-top: 20px;
        }

        .footer-preview .footer-bottom::before {
            content: '';
            position: absolute;
            top: -1px;
            left: 50%;
            transform: translateX(-50%);
            width: 50%;
            height: 1px;
            background: linear-gradient(to right, transparent, var(--footer-text), transparent);
            opacity: 0.2;
        }

        .footer-preview .footer-bottom p {
            color: var(--footer-text);
            font-size: 12px;
            opacity: 0.8;
            margin-bottom: 8px;
        }

        @media (max-width: 1200px) {
            .footer-preview .footer-sections {
                flex-wrap: wrap;
            }
            
            .footer-preview .footer-section {
                flex: 1 1 calc(33.333% - 20px);
                min-width: 220px;
            }
        }

        @media (max-width: 768px) {
            .footer-preview .footer-sections {
                flex-direction: column;
            }
            
            .footer-preview .footer-section {
                flex: 1 1 100%;
                text-align: center;
                padding: 0 15px;
            }
            
            .footer-preview .footer-section ul li a {
                justify-content: center;
            }
            
            .footer-preview .social-links {
                justify-content: center;
            }

            .footer-preview .footer-section h3 {
                justify-content: center;
            }

            .footer-preview .team-member p {
                justify-content: center;
            }
        }

        /* Modal Styles */
        .modal-content {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
            border-radius: 15px 15px 0 0;
            padding: 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #eee;
            border-radius: 0 0 15px 15px;
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            color: #2c3e50;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
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
            border-radius: 12px;
            padding: 1rem 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 300px;
            transform: translateX(120%);
            transition: transform 0.3s ease;
            border-left: 4px solid;
        }

        .toast.success {
            border-color: #4CAF50;
        }

        .toast.error {
            border-color: #F44336;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast i {
            font-size: 1.25rem;
        }

        .toast.success i {
            color: #4CAF50;
        }

        .toast.error i {
            color: #F44336;
        }

        .toast-message {
            flex: 1;
            color: #2c3e50;
            font-size: 0.95rem;
        }

        /* Card Header Button Position */
        .card-header .card-tools {
            margin-left: auto;
            display: flex;
            align-items: center;
        }

        .card-header .btn-add-footer {
            margin-left: auto;
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
                    <h1>Quản lý Footer</h1>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <!-- Footer Preview -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Xem trước Footer</h3>
                        </div>
                        <div class="card-body">
                            <div class="footer-preview">
                                <div class="footer-content">
                                    <div class="footer-sections">
                                        <?php
                                        require_once(__DIR__ . '/../config/connect.php');
                                        
                                        // Get footer sections grouped by type
                                        $sections = array();
                                        $sql = "SELECT * FROM footers WHERE status = 1 ORDER BY position";
                                        $result = mysqli_query($conn, $sql);
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $type = $row['type'];
                                            if (!isset($sections[$type])) {
                                                $sections[$type] = array();
                                            }
                                            $sections[$type][] = $row;
                                        }

                                        // Store section
                                        if (isset($sections['text'])) {
                                            foreach ($sections['text'] as $row) {
                                                if (strpos(strtolower($row['section_name']), 'thực phẩm mộc') !== false) {
                                                    echo '<div class="footer-section">';
                                                    echo '<h3><i class="' . $row['icon'] . '"></i> ' . $row['section_name'] . '</h3>';
                                                    echo '<p>' . $row['content'] . '</p>';
                                                    echo '</div>';
                                                    break;
                                                }
                                            }
                                        }

                                        // Quick Links section (link_1)
                                        if (isset($sections['link_1'])) {
                                            echo '<div class="footer-section">';
                                            echo '<h3><i class="fas fa-link"></i> Liên kết nhanh</h3>';
                                            echo '<ul>';
                                            foreach ($sections['link_1'] as $row) {
                                                echo '<li><a href="' . $row['url'] . '">';
                                                echo '<i class="' . $row['icon'] . '"></i> ' . $row['section_name'];
                                                echo '</a></li>';
                                            }
                                            echo '</ul>';
                                            echo '</div>';
                                        }

                                        // Additional Info section (link_2)
                                        if (isset($sections['link_2'])) {
                                            echo '<div class="footer-section">';
                                            echo '<h3><i class="fas fa-info-circle"></i> Thông tin thêm</h3>';
                                            echo '<ul>';
                                            foreach ($sections['link_2'] as $row) {
                                                echo '<li><a href="' . $row['url'] . '">';
                                                echo '<i class="' . $row['icon'] . '"></i> ' . $row['section_name'];
                                                echo '</a></li>';
                                            }
                                            echo '</ul>';
                                            echo '</div>';
                                        }

                                        // Contact section
                                        if (isset($sections['contact'])) {
                                            echo '<div class="footer-section">';
                                            echo '<h3><i class="fas fa-phone"></i> Liên hệ với chúng tôi</h3>';
                                            foreach ($sections['contact'] as $row) {
                                                echo '<p><i class="' . $row['icon'] . '"></i> ' . $row['content'] . '</p>';
                                            }
                                            // Social links
                                            if (isset($sections['social'])) {
                                                echo '<div class="social-links">';
                                                foreach ($sections['social'] as $row) {
                                                    echo '<a href="' . $row['url'] . '" class="social-link ' . strtolower($row['section_name']) . '">';
                                                    echo '<i class="' . $row['icon'] . '"></i></a>';
                                                }
                                                echo '</div>';
                                            }
                                            echo '</div>';
                                        }

                                        // Team section
                                        if (isset($sections['team'])) {
                                            echo '<div class="footer-section">';
                                            echo '<h3><i class="fas fa-users"></i> Thiết kế & Phát triển</h3>';
                                            echo '<div class="team-info">';
                                            echo '<div class="team-member">';
                                            foreach ($sections['team'] as $row) {
                                                echo '<p><i class="' . $row['icon'] . '"></i> ' . $row['section_name'] . '</p>';
                                            }
                                            echo '</div>';
                                            echo '</div>';
                                            echo '</div>';
                                        }
                                        ?>
                                    </div>
                                    <div class="footer-bottom">
                                        <p>© 2024 Thực Phẩm Mộc. All Rights Reserved.</p>
                                        <p>Website được thiết kế bởi nhóm sinh viên DHMT16A2HN - Cơ sở Hà Nội - Trường Đại học Kinh tế - Kỹ thuật Công nghiệp</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer List -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Danh sách Footer</h3>
                            <button type="button" class="btn btn-primary btn-add-footer" data-toggle="modal" data-target="#addFooterModal">
                                <i class="fas fa-plus"></i> Thêm Footer
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tên</th>
                                            <th>Nội dung</th>
                                            <th>Loại</th>
                                            <th>Icon</th>
                                            <th>URL</th>
                                            <th>Vị trí</th>
                                            <th>Trạng thái</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT * FROM footers ORDER BY position";
                                        $result = mysqli_query($conn, $sql);
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo '<tr>';
                                            echo '<td>' . $row['id'] . '</td>';
                                            echo '<td>' . $row['section_name'] . '</td>';
                                            echo '<td>' . $row['content'] . '</td>';
                                            echo '<td>' . $row['type'] . '</td>';
                                            echo '<td><i class="' . $row['icon'] . '"></i> ' . $row['icon'] . '</td>';
                                            echo '<td>' . ($row['url'] ? $row['url'] : '-') . '</td>';
                                            echo '<td>' . $row['position'] . '</td>';
                                            echo '<td><span class="status-badge ' . ($row['status'] ? 'status-active' : 'status-inactive') . '">' 
                                                . ($row['status'] ? 'Hoạt động' : 'Không hoạt động') . '</span></td>';
                                            echo '<td>
                                                <button class="btn btn-info btn-sm edit-footer" data-id="' . $row['id'] . '">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm delete-footer" data-id="' . $row['id'] . '">
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

    <!-- Add/Edit Footer Modal -->
    <div class="modal fade" id="addFooterModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modalTitle">Thêm Footer</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="footerForm" method="POST">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="section_name">Tên Section</label>
                            <input type="text" class="form-control" id="section_name" name="section_name" required>
                        </div>
                        <div class="form-group">
                            <label for="content">Nội dung</label>
                            <textarea class="form-control" id="content" name="content" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="type">Loại</label>
                            <select class="form-control" id="type" name="type">
                                <option value="text">Text</option>
                                <option value="link_1">Liên kết nhanh</option>
                                <option value="link_2">Thông tin thêm</option>
                                <option value="social">Social</option>
                                <option value="contact">Contact</option>
                                <option value="team">Team</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="icon">Icon (Font Awesome class)</label>
                            <input type="text" class="form-control" id="icon" name="icon" required>
                        </div>
                        <div class="form-group url-group" style="display: none;">
                            <label for="url">URL</label>
                            <input type="text" class="form-control" id="url" name="url">
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
        $('#footerForm').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            
            $.ajax({
                url: 'includes/footer_actions.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        showToast('Thao tác thành công!');
                        $('#addFooterModal').modal('hide');
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

        // Handle footer deletion
        $('.delete-footer').click(function() {
            if (confirm('Bạn có chắc chắn muốn xóa footer này?')) {
                var id = $(this).data('id');
                
                $.ajax({
                    url: 'includes/footer_actions.php',
                    type: 'POST',
                    data: {
                        action: 'delete',
                        id: id
                    },
                    success: function(response) {
                        if (response.success) {
                            showToast('Xóa footer thành công!');
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

        // Handle footer editing
        $('.edit-footer').click(function() {
            var id = $(this).data('id');
            
            $.ajax({
                url: 'includes/footer_actions.php',
                type: 'GET',
                data: {
                    action: 'get',
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        var footer = response.data;
                        $('#footerForm [name="section_name"]').val(footer.section_name);
                        $('#footerForm [name="content"]').val(footer.content);
                        $('#footerForm [name="type"]').val(footer.type);
                        $('#footerForm [name="icon"]').val(footer.icon);
                        $('#footerForm [name="url"]').val(footer.url);
                        $('#footerForm [name="position"]').val(footer.position);
                        $('#footerForm [name="status"]').val(footer.status);
                        $('#footerForm').append('<input type="hidden" name="id" value="' + footer.id + '">');
                        $('#footerForm').append('<input type="hidden" name="action" value="update">');
                        $('.modal-title').text('Sửa Footer');
                        $('#addFooterModal').modal('show');
                    } else {
                        showToast(response.message || 'Có lỗi xảy ra khi tải thông tin!', 'error');
                    }
                },
                error: function() {
                    showToast('Đã xảy ra lỗi!', 'error');
                }
            });
        });

        // Show/hide URL field based on type
        $('#type').change(function() {
            if ($(this).val() === 'link_1' || $(this).val() === 'link_2' || $(this).val() === 'social') {
                $('.url-group').show();
            } else {
                $('.url-group').hide();
                $('#url').val('');
            }
        });

        // Clear form when modal is closed
        $('#addFooterModal').on('hidden.bs.modal', function() {
            $('#footerForm').trigger('reset');
            $('#footerForm input[name="id"]').remove();
            $('#footerForm input[name="action"]').remove();
            $('.modal-title').text('Thêm Footer');
            $('.url-group').hide();
        });

        // Initialize section visibility
        $('#type').trigger('change');
    </script>
</body>
</html>