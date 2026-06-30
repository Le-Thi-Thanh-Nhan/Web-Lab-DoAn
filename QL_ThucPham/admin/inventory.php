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

// Handle stock updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'update_stock') {
            $product_id = $_POST['product_id'];
            $new_quantity = $_POST['stock_quantity'];
            
            $conn = getDBConnection();
            $stmt = $conn->prepare("UPDATE products SET stock_quantity = ? WHERE product_id = ?");
            $stmt->bind_param("ii", $new_quantity, $product_id);
            
            if ($stmt->execute()) {
                $message = "Số lượng tồn kho đã được cập nhật thành công!";
                $messageType = "success";
            } else {
                throw new Exception("Không thể cập nhật số lượng tồn kho.");
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

// Get total number of products
$conn = getDBConnection();
$total_query = "SELECT COUNT(*) as total FROM products";
$total_result = $conn->query($total_query);
$total_products = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_products / $items_per_page);

// Get products with their categories and stock information
$query = "SELECT p.*, c.name as category_name, s.name as subcategory_name 
          FROM products p 
          JOIN subcategories s ON p.subcategory_id = s.subcategory_id 
          JOIN categories c ON s.category_id = c.category_id 
          ORDER BY p.stock_quantity ASC, p.name ASC
          LIMIT ?, ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $offset, $items_per_page);
$stmt->execute();
$products = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Tồn Kho - Admin Panel</title>
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

        .stock-warning { color: #dc3545; }
        .stock-low { color: #ffc107; }
        .stock-ok { color: #28a745; }
        
        .stock-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        
        .quick-edit {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quick-edit input {
            width: 80px;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .filter-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .filter-section select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        
        .stock-chart {
            margin: 20px 0;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
                    <h2 class="card-title">Quản Lý Tồn Kho</h2>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <div class="filter-section">
                    <select id="stockFilter" onchange="filterStock(this.value)">
                        <option value="all">Tất cả sản phẩm</option>
                        <option value="low">Sắp hết hàng (≤ 10)</option>
                        <option value="out">Hết hàng</option>
                    </select>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Hình Ảnh</th>
                                <th>Tên Sản Phẩm</th>
                                <th>Danh Mục</th>
                                <th>Số Lượng Tồn</th>
                                <th>Trạng Thái</th>
                                <th>Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($product = $products->fetch_assoc()): ?>
                                <tr class="stock-row" data-stock="<?php echo $product['stock_quantity']; ?>">
                                    <td><?php echo $product['product_id']; ?></td>
                                    <td>
                                        <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" style="width: 50px; height: 50px; object-fit: cover;">
                                    </td>
                                    <td><?php echo $product['name']; ?></td>
                                    <td><?php echo $product['category_name'] . ' > ' . $product['subcategory_name']; ?></td>
                                    <td>
                                        <div class="quick-edit">
                                            <input type="number" min="0" value="<?php echo $product['stock_quantity']; ?>" 
                                                   id="stock_<?php echo $product['product_id']; ?>" 
                                                   onchange="updateStock(<?php echo $product['product_id']; ?>)">
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $stock = $product['stock_quantity'];
                                        if ($stock == 0) {
                                            echo '<span class="stock-status stock-warning">Hết hàng</span>';
                                        } elseif ($stock <= 10) {
                                            echo '<span class="stock-status stock-low">Sắp hết</span>';
                                        } else {
                                            echo '<span class="stock-status stock-ok">Còn hàng</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary" onclick="location.href='products.php?edit=<?php echo $product['product_id']; ?>'">
                                            <i class="fas fa-edit"></i> Chi tiết
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
                        <?php echo min($offset + $items_per_page, $total_products); ?> 
                        trên tổng số <?php echo $total_products; ?> sản phẩm
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function updateStock(productId) {
            const newQuantity = document.getElementById(`stock_${productId}`).value;
            const formData = new FormData();
            formData.append('action', 'update_stock');
            formData.append('product_id', productId);
            formData.append('stock_quantity', newQuantity);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(() => {
                location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi cập nhật số lượng tồn kho');
            });
        }

        function filterStock(value) {
            const rows = document.querySelectorAll('.stock-row');
            rows.forEach(row => {
                const stock = parseInt(row.dataset.stock);
                switch(value) {
                    case 'low':
                        row.style.display = (stock <= 10 && stock > 0) ? '' : 'none';
                        break;
                    case 'out':
                        row.style.display = stock === 0 ? '' : 'none';
                        break;
                    default:
                        row.style.display = '';
                }
            });
        }
    </script>
</body>
</html> 