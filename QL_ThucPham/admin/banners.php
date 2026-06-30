<?php
session_start();
require_once(__DIR__ . '/../config/connect.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý Banner</title>
    <link rel="icon" type="image/png" href="../images/Logo.png">
    <!-- AdminLTE + Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Swiper -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.4.5/swiper-bundle.min.css">
    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
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

        .swiper {
            width: 850px;
            height: 590px;
            margin: 0 auto 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .swiper-slide {
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .swiper-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .swiper-button-next,
        .swiper-button-prev {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 25px;
            border-radius: 50%;
            color: #333;
            transition: all 0.3s ease;
        }

        .swiper-button-next:hover,
        .swiper-button-prev:hover {
            background-color: #fff;
            transform: scale(1.1);
        }

        .swiper-pagination-bullet {
            width: 10px;
            height: 10px;
            background: rgba(255, 255, 255, 0.9);
        }

        .swiper-pagination-bullet-active {
            background: #fff;
        }

        .banner-preview {
            width: 200px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .banner-preview:hover {
            transform: scale(1.05);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            min-width: 100px;
            text-align: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .status-active {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .status-inactive {
            background-color: #ffebee;
            color: #c62828;
        }

        /* Enhanced table styles */
        .table-responsive {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background-color: #2c3e50;
            border-bottom: 2px solid #e9ecef;
            color: #ffffff;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            padding: 1rem;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* Enhanced card styles */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e9ecef;
            padding: 1.25rem;
        }

        .card-title {
            color: #2c3e50;
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }

        /* Enhanced button styles */
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #3498db;
            border-color: #3498db;
        }

        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
            transform: translateY(-2px);
        }

        .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
            color: white;
        }

        .btn-info:hover {
            background-color: #138496;
            border-color: #138496;
            transform: translateY(-2px);
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #c82333;
            transform: translateY(-2px);
        }

        /* Action buttons container */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }

        .action-buttons .btn {
            padding: 0.375rem 0.75rem;
        }

        /* Modal enhancements */
        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            border-radius: 12px 12px 0 0;
        }

        .modal-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #e9ecef;
            border-radius: 0 0 12px 12px;
        }

        .form-group label {
            color: #2c3e50;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 0.625rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <h1>Quản lý Banner</h1>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <!-- Banner Slider Preview -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Xem trước Banner</h3>
                        </div>
                        <div class="card-body">
                            <div class="swiper">
                                <div class="swiper-wrapper">
                                    <?php
                                    $sql = "SELECT * FROM banners WHERE status = 1 ORDER BY position";
                                    $result = mysqli_query($conn, $sql);
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo '<div class="swiper-slide">';
                                        echo '<img src="' . $row['image_url'] . '" alt="' . $row['name'] . '">';
                                        echo '</div>';
                                    }
                                    ?>
                                </div>
                                <div class="swiper-pagination"></div>
                                <div class="swiper-button-next"></div>
                                <div class="swiper-button-prev"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Banner List -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Danh sách Banner</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addBannerModal">
                                    <i class="fas fa-plus"></i> Thêm Banner
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
                                            <th>Hình ảnh</th>
                                            <th>Vị trí</th>
                                            <th>Trạng thái</th>
                                            <th>Ngày tạo</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT * FROM banners ORDER BY position";
                                        $result = mysqli_query($conn, $sql);
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo '<tr>';
                                            echo '<td>' . $row['id'] . '</td>';
                                            echo '<td>' . $row['name'] . '</td>';
                                            echo '<td><img src="' . $row['image_url'] . '" class="banner-preview"></td>';
                                            echo '<td>' . $row['position'] . '</td>';
                                            echo '<td><span class="status-badge ' . ($row['status'] ? 'status-active' : 'status-inactive') . '">' 
                                                . ($row['status'] ? 'Hoạt động' : 'Không hoạt động') . '</span></td>';
                                            echo '<td>' . $row['created_at'] . '</td>';
                                            echo '<td>
                                                <button class="btn btn-info btn-sm edit-banner" data-id="' . $row['id'] . '">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm delete-banner" data-id="' . $row['id'] . '">
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

    <!-- Add/Edit Banner Modal -->
    <div class="modal fade" id="addBannerModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modalTitle">Thêm Banner</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="bannerForm" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Tên Banner</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="image">Hình ảnh</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.4.5/swiper-bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        // Configure toastr
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: "toast-top-right",
            timeOut: 5000,
            extendedTimeOut: 2000,
        };

        // Initialize Swiper
        new Swiper('.swiper', {
            loop: true,
            pagination: {
                el: '.swiper-pagination',
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            autoplay: {
                delay: 3000,
            },
        });

        // Handle form submission
        $('#bannerForm').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            
            // Add action if not set
            if (!formData.has('action')) {
                formData.append('action', 'add');
            }
            
            $.ajax({
                url: 'includes/banner_actions.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message || 'Thao tác thành công!');
                        $('#addBannerModal').modal('hide');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr.error(response.message || 'Có lỗi xảy ra!');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('XHR:', xhr);
                    var errorMessage = 'Có lỗi xảy ra!';
                    try {
                        var response = JSON.parse(xhr.responseText);
                        errorMessage = response.message || errorMessage;
                    } catch (e) {
                        errorMessage = xhr.responseText || errorMessage;
                    }
                    toastr.error(errorMessage);
                }
            });
        });

        // Handle banner deletion
        $('.delete-banner').on('click', function() {
            if (confirm('Bạn có chắc chắn muốn xóa banner này?')) {
                var bannerId = $(this).data('id');
                $.ajax({
                    url: 'includes/banner_actions.php',
                    type: 'POST',
                    data: {
                        action: 'delete',
                        id: bannerId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message || 'Xóa banner thành công!');
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            toastr.error(response.message || 'Có lỗi xảy ra khi xóa banner!');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('XHR:', xhr);
                        var errorMessage = 'Có lỗi xảy ra khi xóa banner!';
                        try {
                            var response = JSON.parse(xhr.responseText);
                            errorMessage = response.message || errorMessage;
                        } catch (e) {
                            errorMessage = xhr.responseText || errorMessage;
                        }
                        toastr.error(errorMessage);
                    }
                });
            }
        });

        // Handle banner editing
        $('.edit-banner').on('click', function() {
            var bannerId = $(this).data('id');
            $.ajax({
                url: 'includes/banner_actions.php',
                type: 'POST',
                data: {
                    action: 'get',
                    id: bannerId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        var banner = response.data;
                        $('#bannerForm')[0].reset();
                        $('#bannerForm input[name="id"]').remove();
                        $('#bannerForm input[name="action"]').remove();
                        $('#bannerForm').append('<input type="hidden" name="id" value="' + banner.id + '">');
                        $('#bannerForm').append('<input type="hidden" name="action" value="edit">');
                        $('#name').val(banner.name);
                        $('#position').val(banner.position);
                        $('#status').val(banner.status);
                        $('#modalTitle').text('Chỉnh sửa Banner');
                        $('#addBannerModal').modal('show');
                    } else {
                        toastr.error(response.message || 'Không thể tải thông tin banner!');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('XHR:', xhr);
                    var errorMessage = 'Có lỗi xảy ra khi tải thông tin banner!';
                    try {
                        var response = JSON.parse(xhr.responseText);
                        errorMessage = response.message || errorMessage;
                    } catch (e) {
                        errorMessage = xhr.responseText || errorMessage;
                    }
                    toastr.error(errorMessage);
                }
            });
        });

        // Reset form when modal is closed
        $('#addBannerModal').on('hidden.bs.modal', function() {
            $('#bannerForm')[0].reset();
            $('#bannerForm input[name="id"]').remove();
            $('#bannerForm input[name="action"]').remove();
            $('#modalTitle').text('Thêm Banner');
        });
    </script>
</body>
</html>